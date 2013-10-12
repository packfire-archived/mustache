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

use \Exception;

class TemplateNotFoundException extends Exception
{
    public function __construct($name)
    {
        parent::__construct('The template "' . $name . '" was not found through the loader.');
    }
}
