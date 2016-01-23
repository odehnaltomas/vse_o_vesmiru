<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 17. 1. 2016
 * Time: 13:43
 */

namespace App;

use Nette\Security\Permission;


class AuthorizatorFactory
{

    /**
     * @return \Nette\Security\IAuthorizator
     */
    public static function create(){
        $authorizator = new Permission();

        $authorizator->addRole('guest');
        $authorizator->addRole('user', 'guest');
        $authorizator->addRole('moderator', 'user');
        $authorizator->addRole('admin', 'moderator');

        $authorizator->addResource('sign');
        $authorizator->addResource('article');
        $authorizator->addResource('comment');

        $authorizator->allow('guest', 'sign', array('in', 'up'));
        $authorizator->allow('guest', 'article', 'view');

        $authorizator->deny('user', 'sign', array('in', 'up'));
        $authorizator->allow('user', 'sign', 'out');
        $authorizator->allow('user', 'comment', 'write');

        $authorizator->allow('moderator', 'article', array('add', 'edit'));

        return $authorizator;
    }
}