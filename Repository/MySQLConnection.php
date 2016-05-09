<?php
/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/9/2016
 * Time: 1:52 PM
 */

namespace mvc;

use mysqli;

trait MySQLConnection
{
    private static $DB_HOST = 'localhost'; //Host name<br>
    private static $DB_USER = 'root'; //Host Username<br>
    private static $DB_PASS = ''; //Host Password<br>
    private static $DB_NAME = 'php-final'; //Database name<br><br>

    protected static $mysqli = null;

    protected static function openMySQLConnection()
    {
        if (!isset(self::$mysqli)) {
            $tmysqli = new mysqli(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME);
            if ($tmysqli->connect_errno) {
                self::errors("Failed to connect to MySQL: (" . $tmysqli->connect_errno . ") " .
                    $tmysqli->connect_error);
                return null;
            }
            self::$mysqli = $tmysqli;
        }
        return self::$mysqli;
    }

    protected static function closeMySQLConnection()
    {
        if (isset(self::$mysqli)) {
            self::$mysqli->close();
        }
        self::$mysqli = null;
    }

}