<?php

/**
 * Aquino API Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim
 * @copyright Copyright (c) 2016 Leonardo Neto
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */

namespace Aquino;

use Slim;
use Aquino\Middleware\ApiModuleMiddleware;
use Aquino\Core\ApiModule;
use Aquino\JWT\JWT;

define("_APP",  dirname(__FILE__));
$container  = [];
$app = NULL;

//require _APP
class App
{   
	/**
     * Current version
     *
     * @var string
     */
    const VERSION = '1.0.0';
    private $dirProject = _APP . '/../../../../';


    public function __construct()
    {   
        global $container;
        global $app;

        $this->projectUpdate();

        $config = ['settings' => [
            'addContentLengthHeader' => false,
            'displayErrorDetails' => true
        ]];

        //Load Slim Container
        $container = new \Slim\Container($config);
        $container['auth_class'] = NULL;
        $app = new \Slim\App($container);

        //Load config file
        require $this->dirProject . '/config/config.php';

        //Add reference to $app in Slim Container
        $container['instance'] = function(){
            return $app;
        };

        //Add Middleware
        $app->add( new ApiModuleMiddleware() );

        //Load all api routes
        foreach (glob($this->dirProject . "api/*.php") as $file)
        {
            require_once  $file;

            // get the file name of the current file without the extension
            // which is essentially the class name
            $class = basename($file, '.php');

            if (class_exists($class))
            {
                $obj = new $class;
            }
        }  

        $app->run();
    }


    public function projectUpdate(){
        $this->xcopy(_APP . '/Copy/', "./");
    }


    /**
     * Copy a file, or recursively copy a folder and its contents
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    private function xcopy($source, $dest, $permissions = 0777)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            //echo $source;
            if(file_exists($dest)){
                return true;
            }
            
            $cp = copy($source, $dest);
            chmod ($dest, $permissions);
            return $cp ;
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
            chmod ($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }
}