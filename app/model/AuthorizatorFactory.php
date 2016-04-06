<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 17. 1. 2016
 * Time: 13:43
 */

namespace App;

use Nette\Security\Permission;


class AuthorizatorFactory {

    /**
     * @return \Nette\Security\IAuthorizator
     */
    public static function create(){
        $authorizator = new Permission();

        // Definování rolí uživatelů
        $authorizator->addRole('guest');
        $authorizator->addRole('user', 'guest');
        $authorizator->addRole('moderator', 'user');
        $authorizator->addRole('admin', 'moderator');

        // Definování jednotlivých zdrojů
        $authorizator->addResource('sign');
        $authorizator->addResource('article');
        $authorizator->addResource('translation');
        $authorizator->addResource('comment');
        $authorizator->addResource('userSource');
        $authorizator->addResource('popUp');
        $authorizator->addResource('request');

        // Určení práv hosta
        $authorizator->allow('guest', 'sign', array('in', 'up'));
        $authorizator->allow('guest', 'article', 'view');

        // Určení práv přihlášeného (normálního) uživatele
        $authorizator->deny('user', 'sign', array('in', 'up'));
        $authorizator->allow('user', 'sign', 'out');
        $authorizator->allow('user', 'comment', array('write', 'like', 'dislike'));
        $authorizator->allow('user', 'article', array('addRequest', 'delRequest', 'editRequest'));

        // Určení práv moderátora
        $authorizator->allow('moderator', 'article', array('add', 'edit', 'del'));
        $authorizator->allow('moderator', 'translation', array('list', 'original', 'add'));
        $authorizator->allow('moderator', 'popUp', 'articlePopUp');
        $authorizator->allow('moderator', 'comment', 'del');
        $authorizator->allow('moderator', 'request', array('list', 'accept', 'reject', 'showArticle'));

        $authorizator->deny('moderator', 'article', array('addRequest', 'delRequest', 'editRequest'));

        // Určení práv administrátora
        $authorizator->allow('admin', 'userSource', array('changeRoles', 'ban'));
        $authorizator->allow('admin', 'popUp', 'userPopUp');


        return $authorizator;
    }
}