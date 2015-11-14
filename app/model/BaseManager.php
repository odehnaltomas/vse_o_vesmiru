<?php
/**
 * Created by PhpStorm.
 * User: Tom�
 * Date: 6. 11. 2015
 * Time: 22:29
 */
//TODO: komentare
namespace App\Model;


use Nette\Object;
use Nette;

/**
 * Základní model pro všechny ostatní modely.
 * @package App\Model
 */
abstract class BaseManager extends Object
{
    const
        TABLE_USER = 'user',
        USER_COLUMN_ID = 'id',
        USER_COLUMN_NAME = 'username',
        USER_COLUMN_PASSWORD = 'password',
        USER_COLUMN_FIRST_NAME = 'first_name',
        USER_COLUMN_LAST_NAME = 'last_name',
        USER_COLUMN_SEX = 'sex',
        USER_COLUMN_ROLE = 'role_id',

        TABLE_USER_ROLE = 'user_role',
        ROLE_COLUMN_ID = 'id',
        ROLE_COLUMN_NAME = 'role';

}