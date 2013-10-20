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
     * @param mixed $scope The parameter scope to work with
     * @param array $tokens The array of tokens to parse
     * @return string Returns the parsed text
     * @since 1.2.0
     */
    private function parse($scope, $tokens)
    {
        $buffer = '';
            }
        }
        return $buffer;
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
     * @param mixed $scope The scope to be checked
     * @return boolean Returns true if the scope is an array of objects,
     *                  false otherwise.
     * @since 1.0-sofia
     */
    private function isArrayOfObjects($scope)
    {
        return is_array($scope) && count($scope) > 0 && array_keys($scope) === range(0, count($scope) - 1) && !is_scalar($scope[0]);
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
        $tokens = $tokenizer->parse($this->template);
        $buffer = $this->parse($scope, $tokens);
        return $buffer;
    }
}
