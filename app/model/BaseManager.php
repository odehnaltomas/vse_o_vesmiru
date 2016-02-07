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
        USER_COLUMN_SEX = 'sex_id',
        USER_COLUMN_ROLE = 'role_id',

    TABLE_USER_ROLE = 'user_role',
        ROLE_COLUMN_ID = 'id',
        ROLE_COLUMN_ROLE = 'role',
        ROLE_COLUMN_NAME_CS = 'cs_role',
        ROLE_COLUMN_NAME_EN = 'en_role',

    TABLE_USER_SEX = 'user_sex',
        SEX_COLUMN_ID = 'id',
        SEX_COLUMN_NAME_CS = 'cs_sex',
        SEX_COLUMN_NAME_EN = 'en_sex',

    TABLE_ARTICLE = 'article',
        ARTICLE_COLUMN_ID = 'id',
        ARTICLE_COLUMN_TRANSLATION_ID = 'translation_id',
        ARTICLE_COLUMN_LANGUAGE_ID = 'language_id',
        ARTICLE_COLUMN_TITLE = 'title',
        ARTICLE_COLUMN_CAPTION = 'caption',
        ARTICLE_COLUMN_CONTENT = 'content',
        ARTICLE_COLUMN_CREATED = 'created',
        ARTICLE_COLUMN_USER_ID = 'user_id',
        ARTICLE_COLUMN_ARTICLE_RATING = 'article_rating_id',

    TABLE_LANGUAGE = 'language',
        LANGUAGE_COLUMN_ID = 'id',
        LANGUAGE_COLUMN_LANGUAGE = 'language',

    TABLE_COMMENT = 'comment',
        COMMENT_ID = 'id',
        COMMENT_ARTICLE_ID = 'article_id',
        COMMENT_USER_ID = 'user_id',
        COMMENT_CONTENT = 'content',
        COMMENT_CREATED = 'created',

    TABLE_COMMENT_RATING = 'comment_rating',
        COMMENT_RATING_ID = 'id',
        COMMENT_RATING_USER_ID = 'user_id',
        COMMENT_RATING_COMMENT_ID = 'comment_id',
        COMMENT_RATING_VALUE = 'value';

}