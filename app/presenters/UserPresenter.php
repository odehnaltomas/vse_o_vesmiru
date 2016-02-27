<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 29. 1. 2016
 * Time: 12:15
 */

namespace App\Presenters;

use Nette;
use App\Model\UserManager;
use Nette\Security\User;
use Nette\Application\UI\Form;

class UserPresenter extends BasePresenter
{
    private $username = "PIČO";

    private $database;

    private $userManager;

    private $user;

    private $roles = array(
        1 => 'Uživatel',
        2 => 'Moderátor',
        3 => 'Admin'
    );


    public function __construct(Nette\Database\Context $database, UserManager $userManager, User $user)
    {
        $this->database = $database;
        $this->userManager = $userManager;
        $this->user = $user;
    }


    public function renderShowYourProfile(){
        if($this->user->isLoggedIn()){
            $this->template->numberOfComments = $this->userManager->getUserComments($this->user->getId())->count($this->user->getId());
            $this->template->locale = $this->locale;
            $this->template->userData = $this->userManager->getUserData($this->user->getId());
            $this->template->userKarma = $this->userManager->getUserKarma($this->user->getId());
        } else {
            throw new Nette\Application\UI\BadSignalException;
        }
    }


    public function renderShowProfile($userId){
        $user = $this->userManager->getUserData($userId);

        if($user === NULL)
            throw new Nette\Application\BadRequestException;

        $this->template->user = $user;
    }

    protected function createComponentChangeRole(){
        $form = new Form;

        $form->setTranslator($this->translator);
        $form->addSelect('role', 'Role:', $this->roles);
        $form->addHidden('userId');

        $form->addSubmit('send', 'Změnit');

        $form->onSuccess[] = array($this, 'changeRoleSucceeded');

        return $form;
    }


    public function changeRoleSucceeded($form, $values){
        if($this->user->isAllowed('userSource', 'changeRoles')) {
            $this->userManager->changeUserRole($values->userId, $values->role);
            $this->flashMessage('Role uživatele byla změněna.');
            $this->redirect('User:userList');
        }
    }


    public function handleShowPopUp($userId, $role){
        $this['changeRole']['userId']->setDefaultValue($userId);
        $this['changeRole']['role']->setValue($role);
        if($this->isAjax()) {
            $this->redrawControl('popUp');
        }
    }


    public function renderUserList(){
        $this->template->locale = $this->locale;
        $this->template->usersData = $this->userManager->getUsers();
        $this->template->username = $this->username;
    }
}