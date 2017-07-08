<?php
namespace AMC\Classes;

class Database {

    // TODO ########### ADD A FUNCTION FOR SQLITE DBs ##########

    private static $_connection = null;

    public static function newConnection($host, $username, $password, $database) {
        self::$_connection = new \mysqli($host, $username, $password, $database);
    }

    public static function getConnection() {
        return self::$_connection;
    }

    public static function isConnected() {
        if(\is_null(self::$_connection)) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function toNumeric($boolean) {
        if(\is_bool($boolean) && $boolean) {
            return 1;
        }
        else if(\is_bool($boolean) && !$boolean) {
            return 0;
        }
        else {
            return null;
        }
    }

    public static function toBoolean($numeric) {
        if(\is_numeric($numeric) && $numeric == 1) {
            return true;
        }
        else if(\is_numeric($numeric) && $numeric == 0) {
            return false;
        }
        else {
            return null;
        }
    }

}