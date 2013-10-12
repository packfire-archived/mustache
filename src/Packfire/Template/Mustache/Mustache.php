<?php

/**
 * Packfire Framework for PHP
 * By Sam-Mauris Yong
 *
 * Released open source under New BSD 3-Clause License.
 * Copyright (c) Sam-Mauris Yong <sam@mauris.sg>
 * All rights reserved.
 */

namespace Packfire\Template\Mustache;

/**
 * A PHP implementation of Mustache, a simple logic-less templating system
 *
 * @author Sam-Mauris Yong / mauris@hotmail.sg
 * @copyright Copyright (c) Sam-Mauris Yong
 * @license http://www.opensource.org/licenses/bsd-license New BSD License
 * @package Packfire\Template\Mustache
 * @since 1.0-sofia
 */
class Mustache
{

    /**
     * The tag regular expression
     * @since 1.0-sofia
     */
    const TAG_REGEX = '`((%s)([\^&#\=/{\!\>\<]{0,1})(%s)(%s))`is';

    const TYPE_NORMAL = '';
    const TYPE_OPEN = '#';
    const TYPE_CLOSE = '/';
    const TYPE_UNESCAPE = '&';
    const TYPE_UNESCAPETRIPLE = '{';
    const TYPE_INVERT = '^';
    const TYPE_COMMENT = '!';
    const TYPE_PARTIAL1 = '>';
    const TYPE_PARTIAL2 = '<';
    const TYPE_CHANGETAG = '=';

    /**
     * The opening delimiter
     * @var string
     * @since 1.0.1
     */
    protected $openDelimiter = '{{';

    /**
     * The closing delimiter
     * @var string
     * @since 1.0.1
     */
    protected $closeDelimiter = '}}';

    /**
     * The template to be parsed
     * @var string
     * @since 1.0-sofia
     */
    protected $template;

    /**
     * The parameters to work with
     * @var mixed
     * @since 1.0-sofia
     */
    protected $parameters;

    /**
     * The partials loader
     * @var Packfire\Template\Mustache\LoaderInterface
     * @since 1.1.0
     */
    protected $loader;

    /**
     * The escaper callback
     * @var Closure|callback
     * @since 1.0-sofia
     */
    protected $escaper = array(__CLASS__, 'escape');

    /**
     * Create a new Mustache object
     * @param string $template (optional) Set the template to render
     * @since 1.0-sofia
     */
    public function __construct($template = null)
    {
        $this->template = $template;
    }

    /**
     * Default escaper for escaping the text using the default
     * htmlspecialchars() function.
     * @param string $text The text to be escaped
     * @return string The escaped text.
     * @since 1.1.0
     */
    protected static function escape($text)
    {
        return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Perform parsing of a scope
     * @param mixed $scope The parameter scope to work with
     * @param integer $start The start position of the template string
     *              to start working from
     * @param integer $end The end position of the template string
     *              to stop working at
     * @return string Returns the parsed text
     * @since 1.1.0
     */
    private function parse($scopePath, $start, $end)
    {
        $buffer = '';
        $scope = $this->scope($scopePath);
        if ($this->isArrayOfObjects($scope)) {
            $keys = array_keys($scope);
            foreach ($keys as $key) {
                $buffer .= $this->parse(array_merge($scopePath, array($key)), $start, $end);
            }
        } else {
            $position = $start;
            $templateScope = substr($this->template, $start, $end - $start);
            while ($position < $end) {
                $scope = $this->scope($scopePath);
                $match = array();
                $hasMatch = preg_match(
                    $this->buildMatchingTag(),
                    $templateScope,
                    $match,
                    PREG_OFFSET_CAPTURE,
                    $position - $start
                );
                if ($hasMatch) {
                    $tagLength = strlen($match[0][0]);
                    $tagStart = $match[0][1];
                    $tagEnd = $tagStart + $tagLength;
                    $name = trim($match[5][0]);
                    $tagType = $match[3][0];
                    $buffer .= substr($this->template, $position, $tagStart + $start - $position);
                    switch($tagType){
                        case self::TYPE_COMMENT:
                            // comment, do nothing
                            $position = $tagEnd;
                            break;
                        case self::TYPE_OPEN:
                            $position = $start + $tagEnd;
                            $this->findClosingTag($name, $position, $end);
                            $property = $this->property($scope, $name);
                            if ($property) {
                                if (is_scalar($property)) {
                                    $path = $scopePath;
                                } else {
                                    $path = array_merge($scopePath, array($name));
                                }
                                $buffer .= $this->parse($path, $start + $tagEnd, $position);
                            }
                            $position += $tagLength;
                            break;
                        case self::TYPE_INVERT:
                            $position = $start + $tagEnd;
                            $this->findClosingTag($name, $position, $end);
                            $property = $this->property($scope, $name);
                            if (!$property) {
                                $buffer .= $this->parse($scopePath, $start + $tagEnd, $position);
                            }
                            $position += $tagLength;
                            break;
                        case self::TYPE_PARTIAL1:
                        case self::TYPE_PARTIAL2:
                            $buffer .= $this->partial($name, $scope);
                            $position = $start + $tagEnd;
                            break;
                        case self::TYPE_UNESCAPETRIPLE:
                        case self::TYPE_UNESCAPE:
                            $this->addToBuffer($buffer, $scope, $name, false);
                            $position = $start + $tagEnd;
                            break;
                            break;
                        case self::TYPE_CHANGETAG:
                            if (substr($name, -1) == '=') {
                                $name = substr($name, 0, strlen($name) - 1);
                            }
                            list($this->openDelimiter, $this->closeDelimiter) = explode(' ', $name);
                            $position = $tagEnd;
                            break;
                        default:
                            $this->addToBuffer($buffer, $scope, $name);
                            $position = $start + $tagEnd;
                            break;
                    }
                } else {
                    // no more found
                    $buffer .= substr($this->template, $position, $end - $position);
                    $position = $end;
                }
            }
        }
        return $buffer;
    }

    protected function scope($path)
    {
        $scope = $this->parameters;
        foreach ($path as $item) {
            if (isset($scope[$item])) {
                $scope = $scope[$item];
            } else {
                $scope = null;
                break;
            }
        }
        return $scope;
    }

    /**
     * Get the property from the scope.
     * Note that if property is not found in the scope, the
     * method will look from the parent-most scope.
     * @param mixed $scope The scope to get the property from
     * @param string $name Name of the property
     * @return mixed Returns the property fetched.
     * @since 1.0-sofia
     */
    private function property($scope, $name)
    {
        $result = null;
        if (is_object($scope)) {
            if (property_exists($scope, $name)) {
                $result = $scope->$name;
            } elseif (is_callable($scope, $name)) {
                $result = $scope->$name();
            }
        } elseif (is_array($scope)) {
            if (array_key_exists($name, $scope)) {
                $result = $scope[$name];
            }
        }
        return $result;
    }

    /**
     * Add a property to the buffer and determine if it should be escaped
     * @param mixed $scope The scope of to get the property from
     * @param mixed $name The name of the property
     * @param boolean $escape (optional) Set whether to escape the property. 
     *                 Set this to true for escaping, and false otherwise.
     *                 Defaults to true.
     * @since 1.0-sofia
     */
    private function addToBuffer(&$buffer, $scope, $name, $escape = true)
    {
        $result = $this->property($scope, $name);
        if (is_array($result)) {
            $result = implode('', $result);
        }
        if ($escape) {
            $result = call_user_func($this->escaper, $result);
        }
        $buffer .= $result;
    }

    /**
     * Set the partial loader for the Mustache engine
     * @param Packfire\Template\Mustache\LoaderInterface $loader The loader for partials
     * @return Mustache Returns self for chaining
     * @since 1.1.0
     */
    public function loader(LoaderInterface $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Set the escaping callback
     * @param callback $escaper The callback function / method for providing escaping
     * @return Mustache Returns self for chaining
     * @since 1.1.0
     */
    public function escaper($escaper)
    {
        $this->escaper = $escaper;
        return $this;
    }


    /**
     * Get the partial by name and add to the buffer
     * @param string $name Name of the partial
     * @return string Returns the rendered buffer
     * @since 1.0-sofia
     */
    protected function partial($name, $scope)
    {
        $buffer = '';
        if ($this->loader) {
            $template = $this->loader->load($name);
            if ($template) {
                $partial = new Mustache($template);
                $partial->parameters($this->parameters)
                        ->loader($this->loader)
                        ->escaper($this->escaper);
                $buffer .= $partial->render($scope);
            }
        }
        return $buffer;
    }

    /**
     * Check if the scope is an array of objects
     * @param mixed $scope The scope to be checked
     * @return boolean Returns true if the scope is an array of objects,
     *                  false otherwise.
     * @since 1.0-sofia
     */
    private function isArrayOfObjects($scope)
    {
        return is_array($scope) && (count($scope) == 0 || array_keys($scope) === range(0, count($scope) - 1));
    }

    /**
     * Find the closing tag and shift the position variable to the front
     * of the closing tag.
     * @param string $name The name of the closing tag to find
     * @param string $position The position to be set to
     * @param string $end The end of the tempalte scope
     * @since 1.0-sofia
     */
    private function findClosingTag($name, &$position, $end)
    {
        $nest = 0;
        $templateScope = substr($this->template, $position, $end - $position);
        $start = $position;
        $notDone = true;
        while ($position < $end && $notDone) {
            $match = array();
            $hasMatch = preg_match(
                $this->buildMatchingTag($name),
                $templateScope,
                $match,
                PREG_OFFSET_CAPTURE,
                $position - $start
            );
            if ($hasMatch) {
                $tagLength = strlen($match[0][0]);
                $tagEnd = $match[0][1] + $tagLength;
                $tagType = $match[3][0];
                switch($tagType){
                    case self::TYPE_INVERT:
                    case self::TYPE_OPEN:
                        ++$nest;
                        $position = $start + $tagEnd;
                        break;
                    case self::TYPE_CLOSE:
                        if ($nest == 0) {
                            $position = $start + $match[0][1];
                            $notDone = false;
                        } elseif ($nest > 0) {
                            $position = $start + $tagEnd;
                            --$nest;
                        }
                        break;
                    default:
                        $position = $start + $tagEnd;
                        break;
                }
            } else {
                $position = $end;
                $notDone = false;
                break;
            }
        }
    }

    /**
     * Set the template to be rendered by Mustache
     * @param string $template The template to render
     * @return Mustache Returns self for chaining
     * @since 1.0-sofia
     */
    public function template($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Set the parameters to be rendered into the template
     * @param array $parameters The parameters
     * @return Mustache Returns self for chaining
     * @since 1.0-sofia
     */
    public function parameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Performs preparation of parameters
     * @since 1.1-sofia
     */
    protected function loadParameters()
    {
        if (get_class($this) != __CLASS__) {
            $this->parameters = $this;
        }
        if (count($this->parameters) == 0) {
            $this->parameters = null;
        }
    }

    /**
     * Render the Mustache template
     * @return string Returns the parsed template
     * @since 1.0-sofia
     */
    public function render()
    {
        $this->loadParameters();
        $buffer = $this->parse(array(), 0, strlen($this->template));
        return $buffer;
    }

    /**
     * Build the tag matching regular expression
     * @param string $name (optional) The tag name to match
     * @return string Returns the final regular expression
     * @since 1.0-sofia
     */
    private function buildMatchingTag($name = '([^}].+?)([}]{0,1})')
    {
        return sprintf(self::TAG_REGEX, preg_quote($this->openDelimiter), $name, preg_quote($this->closeDelimiter));
    }
}
