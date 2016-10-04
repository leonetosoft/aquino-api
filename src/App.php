<?php

/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim
 * @copyright Copyright (c) 2016 Leonardo Neto
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */

namespace Aquino;


class App
{   
	define("_APP", dirname(__FILE__));
	/**
     * Current version
     *
     * @var string
     */
    const VERSION = '1.0.0';


    public function __construct()
    {
        echo _APP;
    }
}