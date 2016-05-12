<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/9/2016
 * Time: 1:52 PM
 */

use mysqli;

/**
 * Class MySQLConnection.  Initiates and destroys connection with the database.
 * 
 * @package mvc
 */
trait MySQLConnection
{
    /**
     * @type string DB_HOST Contains the server name of the database.
     */
    private static $DB_HOST = 'localhost';


    /**
     *@type string DB_User Contains the  name of the user to connect to the database.
     */
    private static $DB_USER = 'root';

    /**
     *@type string DB_PASS Contains the password for the user to connect to the database.
     */
    private static $DB_PASS = '';

    /**
     *@type string DB_NAME Contains the name of the database.
     */
    private static $DB_NAME = 'php-final';

    /**
     * @type null
     */
    protected static $mysqli = null;

    /**
     * Instantiates connection to the database.
     *
     * @return mysqli|null $mysqli MySQL database connection.
     */
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

    /**
     * Closes the connection to the database.
     */
    protected static function closeMySQLConnection()
    {
        if (isset(self::$mysqli)) {
            self::$mysqli->close();
        }
        self::$mysqli = null;
    }

}