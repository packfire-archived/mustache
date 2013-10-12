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

use Packfire\Template\Mustache\LoaderInterface;

class ArrayLoader implements LoaderInterface
{
    protected $templates = array();

    public function __construct(array $templates = array())
    {
        $this->templates = $templates;
    }

    public function templates($templates)
    {
        $this->templates = $templates;
    }

    public function add($name, $template)
    {
        $this->templates[$name] = $template;
    }

    public function load($name)
    {
        if (!isset($this->templates[$name])) {
            throw new TemplateNotFoundException($name);
        }
        return $this->templates[$name];
    }
}
