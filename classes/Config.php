<?php
namespace AMC\Classes;

class Config {

    private static $_config = [];

    public static function load($file = '../config.php'){
        if (\file_exists($file)){
            include($file);
            self::$_config = $config;
        }
    }

    public static function write($file = '../config.php'){
        \file_put_contents($file, '<?php $config = ' . \var_export(self::$_config,true) . ';');
    }

    public static function set($settingName, $settingValue){
        self::$_config[$settingName] = $settingValue;
    }

    public static function get($settingName){
        $return = null;
        if(\array_key_exists($settingName, self::$_config)){
            $return = self::$_config[$settingName];
        }
        return $return;
    }

    public static function delete($settingName){
        if(\array_key_exists($settingName, self::$_config)){
            unset(self::$_config[$settingName]);
        }
    }

    public static function exists($settingName){
        return \array_key_exists($settingName, self::$_config);
    }

}