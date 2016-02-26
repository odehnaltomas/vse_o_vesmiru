<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 26. 2. 2016
 * Time: 14:16
 */

namespace App\Forms;

use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use App;
use App\Model\ArticleManager;
use Nette;
use Nette\Security\User;

class DeleteArticleFactory extends Nette\Object
{

    private $articleManager;

    private $user;

    public function __construct(ArticleManager $articleManager, User $user)
    {
        $this->articleManager = $articleManager;
        $this->user = $user;
    }


    /**
     * @return Form
     */
    public function create(){
        $form = new Form;

        if($this->user->roles[0] === 'user') {
            $form->addTextArea('purpose', 'Důvod smazání: ');

            $form->addSubmit('send', 'Poslat žádost');
        } elseif($this->user->roles[0] === 'moderator' || $this->user->roles[0] === 'admin') {
            $form->addSubmit('send', 'Smazat');
        }

        $form->addHidden('articleId');

        $form->onSuccess[] = array($this, 'deleteArticleFormSucceeded');

        return $form;
    }


    public function deleteArticleFormSucceeded($form, $values){
        if($this->user->isAllowed('article', 'del')) {
            $this->articleManager->delArticle($values->articleId);
        } elseif($this->user->isAllowed('article', 'delRequest')){

        }
    }

}