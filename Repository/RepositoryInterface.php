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
    public static function find($object);

    public static function findAll();

    public function save($object);

    public function destroy($object);
}
