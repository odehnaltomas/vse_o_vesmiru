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

    /** @var  int */
    private $userId = 0;

    /** @var Nette\Database\Context  */
    private $database;

    /** @var UserManager  */
    private $userManager;

    /** @var User  */
    private $user;

    /** @var array  */
    private $roles = array(
        1 => 'Uživatel',
        2 => 'Moderátor',
        3 => 'Admin'
    );

    private $ban = array(
        0 => 'Yes',
        1 => 'No'
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

    public function handleChangeRole($userId, $role){
        if($this->user->isAllowed('userSource', 'changeRoles')) {

            $this['changeRole']['userId']->setDefaultValue($userId);
            $this['changeRole']['role']->setValue($role);
            $this->userId = $userId;
            if ($this->isAjax()) {
                $this->redrawControl('popUp');
            }
        }
    }


    protected function createComponentChangeBan(){
        $form = new Form;

        $form->setTranslator($this->translator);
        $form->addSelect('banned', 'Ban:', $this->ban);
        $form->addHidden('userId');

        $form->addSubmit('send', 'Změnit');

        $form->onSuccess[] = array($this, 'changeBanSucceeded');

        return $form;
    }


    public function changeBanSucceeded($form, $values){
        if($this->user->isAllowed('userSource', 'ban')) {
            $this->userManager->changeUserBan($values->userId, $values->banned);
            if($values->banned !== 0)
                $this->flashMessage('Účet uživatele byl odemčen.');
            else
                $this->flashMessage('Účet uživatele byl zamčen.');
            $this->redirect('User:userList');
        }
    }


    public function handleChangeBan($userId, $ban){
        if($this->user->isAllowed('userSource', 'ban')) {
            $this['changeBan']['userId']->setDefaultValue($userId);
            $this['changeBan']['banned']->setValue($ban);
            $this->userId = $userId;
            if ($this->isAjax()) {
                $this->redrawControl('popUpBan');
            }
        }
    }


    public function renderUserList(){
        $this->template->locale = $this->locale;
        $this->template->usersData = $this->userManager->getUsers();
        $this->template->userId = $this->userId;
    }
}