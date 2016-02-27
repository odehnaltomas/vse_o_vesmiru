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

class UserPresenter extends BasePresenter
{

    private $database;

    private $userManager;

    private $user;


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
            throw new Nette\Application\UI\BadSignalException();
        }
    }


    public function renderUserList(){
        $this->template->locale = $this->locale;
        $this->template->users = $this->userManager->getUsers();
    }
}