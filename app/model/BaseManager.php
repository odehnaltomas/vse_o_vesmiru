<?php
/**
 * Created by PhpStorm.
 * User: Tom�
 * Date: 6. 11. 2015
 * Time: 22:29
 */
//TODO: komentare
//TODO: synchronizovat konstanty s databazi
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
        USER_COLUMN_USERNAME = 'username',
        USER_COLUMN_PASSWORD = 'password',
        USER_COLUMN_FIRST_NAME = 'first_name',
        USER_COLUMN_LAST_NAME = 'last_name',
        USER_COLUMN_EMAIL = 'email',
        USER_COLUMN_SEX = 'sex_id',
        USER_COLUMN_ROLE = 'role_id',
        USER_COLUMN_BAN = 'banned',

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
        ARTICLE_COLUMN_DELETED = 'deleted',

    TABLE_ARTICLE_RATING = 'article_rating',
        ARTICLE_RATING_ID = 'id',
        ARTICLE_RATING_USER_ID = 'user_id',
        ARTICLE_RATING_ARTICLE_ID = 'article_id',
        ARTICLE_RATING_VALUE = 'value',

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
        COMMENT_RATING_VALUE = 'value',

    TABLE_REQUEST = 'request',
        REQUEST_ID = 'id',
        REQUEST_REQUEST_COUNTER_ID = 'request_counter_id',
        REQUEST_ARTICLE_ID = 'article_id',
        REQUEST_USER_ID = 'user_id',
        REQUEST_MESSAGE = 'message',
        REQUEST_CREATED = 'created',
        REQUEST_STATE = 'request_state_id',

    TABLE_REQUEST_COUNTER = 'request',
        REQUEST_COUNTER_ID = 'id',
        REQUEST_COUNTER_REQUEST = 'request',
        REQUEST_COUNTER_CS_REQUEST = 'cs_request',
        REQUEST_COUNTER_EN_REQUEST = 'en_request',

    TABLE_REQUEST_STATE = 'request_state',
        REQUEST_STATE_ID = 'id',
        REQUEST_STATE_CS_STATE = 'cs_state',
        REQUEST_STATE_EN_STATE = 'en_state';

}