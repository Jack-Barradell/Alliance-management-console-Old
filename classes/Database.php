<?php
namespace AMC\Classes;

class Database {

    const TYPE_MYSQLI = 0;
    const TYPE_SQLITE = 1;

    private static $_connection = null;
    private static $_databaseType = null;

    public static function newConnection($host, $username, $password, $database) {
        self::$_connection = new \mysqli($host, $username, $password, $database);
        self::$_databaseType = self::TYPE_MYSQLI;
    }

    public static function newSqliteConnection($file) {
        self::$_connection = new \SQLite3($file);
        self::$_databaseType = self::TYPE_SQLITE;
    }

    public static function getConnection() {
        return self::$_connection;
    }

    public static function getDatabaseType() {
        return self::$_databaseType;
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