<?php
namespace AMC\Classes;

use AMC\Exceptions\ArrayKeyNotSetException;
use AMC\Exceptions\FileDoesntExistException;

class Config {

    private static $_config = [];

    public static function load($file = '../config.php') {
        if (\file_exists($file)) {
            include($file);
            self::$_config = $config;
        }
        else {
            throw new FileDoesntExistException("Config file at " . $config . " not found");
        }
    }

    public static function write($file = '../config.php') {
        \file_put_contents($file, '<?php $config = ' . \var_export(self::$_config,true) . ';');
    }

    public static function set($settingName, $settingValue) {
        self::$_config[$settingName] = $settingValue;
    }

    public static function get($settingName) {
        $return = null;
        if(\array_key_exists($settingName, self::$_config)) {
            $return = self::$_config[$settingName];
        }
        return $return;
    }

    public static function delete($settingName) {
        if(\array_key_exists($settingName, self::$_config)) {
            unset(self::$_config[$settingName]);
        }
        else {
            throw new ArrayKeyNotSetException($settingName . " was not set but was attempted to be unset");
        }
    }

    public static function exists($settingName) {
        return \array_key_exists($settingName, self::$_config);
    }

}