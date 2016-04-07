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
        1 => 'forms.user.roleUser',
        2 => 'forms.user.roleModerator',
        3 => 'forms.user.roleAdmin'
    );

    private $ban = array(
        0 => 'forms.user.yes',
        1 => 'forms.user.no'
    );

    private $sex = array(
        1 => "forms.sign.male",
        2 => "forms.sign.female"
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

        if(!$user)
            throw new Nette\Application\BadRequestException;

        $this->template->userData = $user;
        $this->template->locale = $this->locale;
        $this->template->numberOfComments = $this->userManager->getUserComments($userId)->count($userId);
        $this->template->userKarma = $this->userManager->getUserKarma($this->user->getId());
    }

    protected function createComponentChangeRole(){
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->novalidate('novalidate');

        $form->addSelect('role', 'forms.user.role', $this->roles);
        $form->addHidden('userId');

        $form->addSubmit('send', 'forms.user.change');

        $form->onSuccess[] = array($this, 'changeRoleSucceeded');

        return $form;
    }


    public function changeRoleSucceeded($form, $values){
        if($this->user->isAllowed('userSource', 'changeRoles')) {
            $this->userManager->changeUserRole($values->userId, $values->role);
            $this->flashMessage($this->translator->translate('messages.flash.roleChanged'));
            $this->redirect('User:userList');
        } else
            throw new Nette\Application\UI\BadSignalException;
    }

    public function handleChangeRole($userId, $role){
        if($this->user->isAllowed('userSource', 'changeRoles')) {

            $this['changeRole']['userId']->setDefaultValue($userId);
            $this['changeRole']['role']->setValue($role);
            $this->userId = $userId;
            if ($this->isAjax()) {
                $this->redrawControl('popUp');
            }
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    protected function createComponentChangeBan(){
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->novalidate('novalidate');

        $form->addSelect('banned', 'forms.user.ban', $this->ban);
        $form->addHidden('userId');

        $form->addSubmit('send', 'forms.user.change');

        $form->onSuccess[] = array($this, 'changeBanSucceeded');

        return $form;
    }


    public function changeBanSucceeded($form, $values){
        if($this->user->isAllowed('userSource', 'ban')) {
            $this->userManager->changeUserBan($values->userId, $values->banned);
            if($values->banned !== 0)
                $this->flashMessage($this->translator->translate('messages.flash.accountUnlocked'));
            else
                $this->flashMessage($this->translator->translate('messages.flash.accountLocked'));
            $this->redirect('User:userList');
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function handleChangeBan($userId, $ban){
        if($this->user->isAllowed('userSource', 'ban')) {
            $this['changeBan']['userId']->setDefaultValue($userId);
            $this['changeBan']['banned']->setValue($ban);
            $this->userId = $userId;
            if ($this->isAjax()) {
                $this->redrawControl('popUpBan');
            }
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function renderUserList(){
        $this->template->locale = $this->locale;
        $this->template->usersData = $this->userManager->getUsers();
        $this->template->userId = $this->userId;
        $this->template->thisUserId = $this->user->getId();
    }


    public function renderEditProfile($userId){
        if($this->user->isLoggedIn()) {
            $user = $this->userManager->getUserData($userId);

            if (!$user)
                throw new Nette\Application\BadRequestException;

            $this['editProfileForm']['first_name']->setDefaultValue($user->first_name);
            $this['editProfileForm']['last_name']->setDefaultValue($user->last_name);
            $this['editProfileForm']['email']->setDefaultValue($user->email);
            $this['editProfileForm']['sex']->setDefaultValue($user->sex['id']);
            $this['editProfileForm']['userId']->setDefaultValue($userId);
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    protected function createComponentEditProfileForm(){
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->novalidate('novalidate');

        $form->addText('first_name', 'forms.user.firstName')
            ->addCondition(Form::FILLED)
                ->addRule(FORM::MIN_LENGTH, "forms.sign.firstNameMinLength", 2)
                ->addRule(FORM::MAX_LENGTH, "forms.sign.firstNameMaxLength", 50)
                ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\/?!<>()]+");

        $form->addText('last_name', 'forms.user.lastName')
            ->addCondition(Form::FILLED)
                ->addRule(FORM::MIN_LENGTH, "forms.sign.lastNameMinLength", 2)
                ->addRule(FORM::MAX_LENGTH, "forms.sign.lastNameMaxLength", 50)
                ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\/?!<>()]+");

        $form->addText('email', 'forms.user.email')
            ->addCondition(Form::FILLED)
                ->addRule(FORM::MIN_LENGTH, "forms.sign.emailMinLength", 3)
                ->addRule(FORM::MAX_LENGTH, "forms.sign.emailMaxLength", 64)
                ->addRule(FORM::EMAIL, "forms.sign.correctEmail");

        $form->addRadioList('sex', 'forms.user.sex', $this->sex)
            ->getSeparatorPrototype()->setName(NULL);

        $form->addSubmit('send', 'forms.user.save');

        $form->addHidden('userId');

        $form->onSuccess[] = $this->editProfileFormSucceeded;

        return $form;
    }


    public function editProfileFormSucceeded($form, $values){
        if($this->user->isLoggedIn()){
            $this->userManager->changeUserData($values);
            $this->flashMessage($this->translator->translate('messages.flash.profileChanged'));
            $this->redirect('User:showYourProfile');
        } else
            throw new Nette\Application\UI\BadSignalException;
    }

}