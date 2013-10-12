<?php

/**
 * Packfire Framework for PHP
 * By Sam-Mauris Yong
 *
 * Released open source under New BSD 3-Clause License.
 * Copyright (c) Sam-Mauris Yong <sam@mauris.sg>
 * All rights reserved.
 */

namespace Packfire\Template\Mustache\Loader;

class FileSystemLoader extends ArrayLoader
{
    protected $root;

    protected $extension = 'mustache';

    public function __construct($root = null, array $options = array())
    {
        if (null === $root) {
            $root = getcwd();
        }
        $this->root = $root;

        if (array_key_exists('extension', $options)) {
            if ($options['extension']) {
                $this->extension = $options['extension'];

                // remove leading . in extension if added
                if (substr($this->extension, 0, 1) == '.') {
                    $this->extension = substr($this->extension, 1);
                }
            } else {
                $this->extension = '';
            }
        }
    }

    public function load($name)
    {
        if (!isset($this->template[$name])) {
            $path = $root . '/' . $name;
            if ($this->extension) {
                $path .= '.' . $this->extension;
            }
            if (!file_exists($path)) {
                throw new TemplateNotFoundException($name);
            }
            $this->templates[$name] = file_get_contents($path);
        }
        return $this->templates[$name];
    }
}
