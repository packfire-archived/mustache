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
     * The current processing line number
     * @var string
     * @since 1.2.0
     */
    protected $line = 1;

    /**
     * The current number of tokens on the line
     * @var string
     * @since 1.2.0
     */
    protected $lineToken = 0;

    /**
     * Create a new Mustache object
     * @param string $template (optional) Set the template to render
     * @param array $options (optional) Set a variety of options
     * @since 1.0-sofia
     */
    public function __construct($template = null, array $options = array())
    {
        $this->template = $template;

        if (array_key_exists('loader', $options) && $options['loader'] instanceof LoaderInterface) {
            $this->loader = $options['loader'];
        }

        if (array_key_exists('escaper', $options) && is_callable($options['escaper'])) {
            $this->escaper = $options['escaper'];
        }

        if (array_key_exists('open', $options)) {
            $this->openDelimiter = $options['open'];
        }

        if (array_key_exists('close', $options)) {
            $this->closeDelimiter = $options['close'];
        }
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
     * @param array $scope The parameter scope to work with
     * @param array $tokens The array of tokens to parse
     * @return string Returns the parsed text
     * @since 1.2.0
     */
    private function parse(array $scope, array $tokens)
    {
        $buffer = '';
        $line = array();
        foreach ($tokens as $token) {
            if ($token[Tokenizer::TOKEN_LINE] === $this->line) {
                ++$this->lineToken;
                $line[] = $token;
            } else {
                $buffer .= $this->processLine($scope, $line);
                $line = array($token);
                $this->lineToken = 1;
                $this->line = $token[Tokenizer::TOKEN_LINE];
            }
        }
        if ($line) {
            $buffer .= $this->processLine($scope, $line);
        }
        return $buffer;
    }

    private function processLine(array $scope, array $tokens)
    {
        $buffer = '';
        if (count($tokens) == 2) {
            if (self::isTokenStandaloneClear($tokens[0])
                    && $tokens[1][Tokenizer::TOKEN_TYPE] == Tokenizer::TYPE_LINE) {
                array_pop($tokens);
            }
        } elseif (count($tokens) == 3) {
            if (self::isTokenWhitespace($tokens[0])
                    && self::isTokenStandaloneClear($tokens[1])
                    && $tokens[2][Tokenizer::TOKEN_TYPE] == Tokenizer::TYPE_LINE) {
                array_shift($tokens);
                array_pop($tokens);
            }
        }
        foreach ($tokens as $token) {
            switch ($token[Tokenizer::TOKEN_TYPE]) {
                case Tokenizer::TYPE_OPEN:
                    $name = $token[Tokenizer::TOKEN_NAME];
                    $property = $this->scope(array_merge($scope, array($name)));
                    if ($property) {
                        if ($this->isArrayOfObjects($property)) {
                            foreach ($property as $idx => $item) {
                                $path = array_merge($scope, array($name, $idx));
                                $buffer .= $this->parse($path, $token[Tokenizer::TOKEN_NODES]);
                            }
                        } else {
                            $path = $scope;
                            if (!is_scalar($property)) {
                                $path = array_merge($scope, array($name));
                            }
                            $buffer .= $this->parse($path, $token[Tokenizer::TOKEN_NODES]);
                        }
                    }
                    break;
                case Tokenizer::TYPE_INVERT:
                    $name = $token[Tokenizer::TOKEN_NAME];
                    $property = $this->scope(array_merge($scope, array($name)));
                    if (!$property) {
                        $buffer .= $this->parse($scope, $token[Tokenizer::TOKEN_NODES]);
                    }
                    break;
                case Tokenizer::TYPE_NORMAL:
                    $name = $token[Tokenizer::TOKEN_NAME];
                    if ($name == '.') {
                        $property = $this->scope($scope);
                    } else {
                        $property = $this->scope(array_merge($scope, array($name)));
                    }
                    if ($property) {
                        if (is_array($property)) {
                            $property = implode('', $property);
                        }
                        $buffer .= call_user_func($this->escaper, $property);
                    }
                    break;
                case Tokenizer::TYPE_UNESCAPETRIPLE:
                case Tokenizer::TYPE_UNESCAPE:
                    $name = $token[Tokenizer::TOKEN_NAME];
                    $property = $this->scope(array_merge($scope, array($name)));
                    if ($property) {
                        if (is_array($property)) {
                            $property = implode('', $property);
                        }
                        $buffer .= $property;
                    }
                    break;
                case Tokenizer::TYPE_TEXT:
                    $buffer .= $token[Tokenizer::TOKEN_VALUE];
                    break;
                case Tokenizer::TYPE_LINE:
                    $buffer .= $token[Tokenizer::TOKEN_VALUE];
                    break;
                case Tokenizer::TYPE_PARTIAL1:
                case Tokenizer::TYPE_PARTIAL2:
                    $name = $token[Tokenizer::TOKEN_NAME];
                    $buffer .= $this->partial($name, $scope);
                    break;
            }
        }
        return $buffer;
    }

    public static function isTokenStandaloneClear(array $token)
    {
        $types = array(
            Tokenizer::TYPE_COMMENT,
            Tokenizer::TYPE_CHANGETAG,
            Tokenizer::TYPE_OPEN,
            Tokenizer::TYPE_CLOSE,
            Tokenizer::TYPE_COMMENT,
            Tokenizer::TYPE_PARTIAL1,
            Tokenizer::TYPE_PARTIAL2,
            Tokenizer::TYPE_INVERT
        );
        return in_array($token[Tokenizer::TOKEN_TYPE], $types);
    }

    public static function isTokenWhitespace(array $token)
    {
        if ($token[Tokenizer::TOKEN_TYPE] == Tokenizer::TYPE_TEXT) {
            return preg_match('/^\s*$/', $token[Tokenizer::TOKEN_VALUE]);
        }

        return false;
    }

    protected function scope($path)
    {
        $path = self::processDotNotation($path);
        $originalPath = $path;
        $scope = null;
        while (count($path) > 0) {
            $scope = $this->parameters;
            foreach ($path as $item) {
                $scope = (array)$scope;
                if (isset($scope[$item])) {
                    $scope = $scope[$item];
                } else {
                    $scope = null;
                    break;
                }
            }
            if ($scope !== null) {
                break;
            }
            array_shift($path);
        }
        if (is_callable($scope)) {
            $scope = call_user_func($scope);
        }
        return $scope;
    }

    protected static function processDotNotation($path)
    {
        $result = array();
        foreach ($path as $item) {
            $items = explode('.', $item);
            $result = array_merge($result, $items);
        }
        return $result;
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
     * @param mixed $value The value to be checked
     * @return boolean Returns true if the value is an array of objects,
     *                  false otherwise.
     * @since 1.0-sofia
     */
    private function isArrayOfObjects($value)
    {
        if (is_object($value)) {
            return $value instanceof \Traversable;
        } elseif (is_array($value)) {
            return array_keys($value) === range(0, count($value) - 1);
        }
        return false;
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
     * @param array $scope (optional) The scope to start working from on the parameters.
     * @return string Returns the parsed template
     * @since 1.0-sofia
     */
    public function render($scope = array())
    {
        $this->loadParameters();
        $tokenizer = new Tokenizer();
        $tokenizer->changeDelimiters($this->openDelimiter, $this->closeDelimiter);
        $tokens = $tokenizer->parse($this->template);
        $this->line = 1;
        $buffer = $this->parse($scope, $tokens);
        return $buffer;
    }
}
