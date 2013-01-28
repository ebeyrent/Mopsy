<?php

/**
 * Autoload classes in defined load paths, automatically stacks into the
 * spl_register_autoload() method.  Default load path is the directory of the
 * autoloader.
 *
 * PHP Version 5
 *
 * @category Mopsy
 * @package  Mopsy
 * @author   Erich Beyrent <erich.beyrent@pearson.com>
 * @license  Pearson http://polaris.fen.com/
 * @version  $Revision$
 * @link     $HeadURL$
 *
 */

namespace Mopsy;

class Autoloader
{
    /**
     * All paths associated with autloading
     * @var array
     */
    private static $loadPaths = array();

    /**
     * Register a root path for autload to start its search.
     *
     * @param string $path
     */
    public static function registerAutoloadPath($path)
    {
        self::__initialize();
        self::$loadPaths[] = $path;
    }

    /**
     * Initialize the $loadPaths with the path of the Autoloader.
     */
    private static function __initialize()
    {
        // Should only be run once
        if (count(self::$loadPaths) > 0) {
            return;
        }

        // By default, look in our src tree
        self::$loadPaths[] = dirname(__DIR__);
    }

    /**
     * Autoload function
     *
     * @param string $className - the name of the class to autoload
     */
    public static function autoload($className)
    {
        self::__initialize();
        foreach (self::$loadPaths as $load_path) {
            $path = self::getPathToClass($load_path, $className);

            /*
             * An empty path signifies that the class is unknown to this load
             * path, so try again in another load path
             */
            if(empty($path)) continue;

            require_once $path;
            return;
        }
    }

    /**
     * Given a class, return the path to the class
     *
     * @param $root root directory from which to start namespace
     * @param $class_name Name of class referenced, but not yet in scope
     *
     * @return string
     */
    private static function getPathToClass($root, $className)
    {
        $namespace_parts = explode('\\', $className);

        $path = $root . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $namespace_parts) . '.php';
        if (file_exists($path)) {
            return $path;
        }

        // Couldn't find path to class in given load path root
        return '';
    }
}

spl_autoload_register('\Mopsy\Autoloader::autoload');
