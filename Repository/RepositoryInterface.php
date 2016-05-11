<?php
/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/9/2016
 * Time: 3:34 PM
 */

namespace mvc;

interface RepositoryInterface
{
    const DB_HOST = 'localhost'; //Host name<br>
    const DB_USER = 'root'; //Host Username<br>
    const DB_PASS = ''; //Host Password<br>
    const DB_NAME = 'php-final'; //Database name<br><br>

    public static function find($object);

    public static function findAll();

    public function save(&$object);

    public function destroy($object);
}
