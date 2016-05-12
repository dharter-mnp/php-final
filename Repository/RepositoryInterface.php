<?php
namespace mvc;
    
/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/9/2016
 * Time: 3:34 PM
 */

/**
 * Interface RepositoryInterface
 * Defines the required Repository methods and default database connection values.
 *
 * @package mvc
 */
interface RepositoryInterface
{
    /**
     * @type string DB_HOST Contains the server name of the database.
     */
    const DB_HOST = 'localhost';
    
    /**
     *@type string DB_User Contains the  name of the user to connect to the database.
     */
    const DB_USER = 'root';
    
    /**
     *@type string DB_PASS Contains the password for the user to connect to the database.
     */
    const DB_PASS = '';
    
    /**
     *@type string DB_NAME Contains the name of the database.
     */
    const DB_NAME = 'php-final';

    /**
     * Retrieves a single object from the database.
     *
     * @param integer $id Id of the object to be retrive from the database.
     * @return mixed Object retrieved from the database.
     */
    public static function find($id);

    /**
     * Retrieves all objects from the database.
     *
     * @return mixed Objects retrieved from the database.
     */
    public static function findAll();

    /**
     * Saves an object to the database.
     *
     * @param $object Object to be saved to the database.
     * @return mixed Response from saving the object to the database.
     */
    public function save(&$object);

    /**
     * Deletes an object from the database.
     *
     * @param $object Object to be deleted from the database
     * @return mixed Response from deleting the object from the database.
     */
    public function destroy($object);
}
