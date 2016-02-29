<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 26. 2. 2016
 * Time: 14:16
 */

namespace App\Forms;

use Nette\Application\UI;
use Nette\Application\UI\Form;
use App;
use Nette;

class DeleteArticleForm extends BaseControl
{


    /** @var  App\Model\ArticleManager */
    private $articleManager;

    /** @var  App\Model\RequestManager */
    private $requestManager;


    public function render($userId, $articleId){
        if($userId !== NULL)
            $this['deleteArticleForm']['userId']->setDefaultValue($userId);
        $this['deleteArticleForm']['articleId']->setDefaultValue($articleId);
        $this->template->setFile(__DIR__ . '/deleteArticleForm.latte');
        $this->template->render();
    }


    /**
     * @return Form
     */
    public function createComponentDeleteArticleForm(){
        $form = new Form;
        $form->setTranslator($this->presenter->translator);

        if($this->presenter->getUser()->isAllowed('article', 'delRequest')) {
            $form->addTextArea('message', 'Důvod smazání: ');

            $form->addSubmit('send', 'Poslat žádost');

            $form->addHidden('userId');

            $form->addHidden('requestCounterId', 1);
        } elseif($this->presenter->getUser()->isAllowed('article', 'del')) {
            $form->addSubmit('send', 'Smazat');
        }

        $form->addHidden('articleId');

        $form->onSuccess[] = $this->success;

        return $form;
    }


    /**
     * @param $form
     * @param $values
     */
    public function success($form, $values){
        $result = NULL;
        if($this->presenter->getUser()->isAllowed('article', 'del')) {
            $this->presenter->articleManager->delArticle($values->articleId);
            $this->presenter->flashMessage('Článek byl úspěšně vymazán.');
        } elseif($this->presenter->getUser()->isAllowed('article', 'delRequest')){
            $result = $this->presenter->requestManager->addRequest($values);
            if($result === 0) {
                $this->presenter->flashMessage('Již jsi zažádal o smazání toho článku.');
            } else
                $this->presenter->flashMessage('Požadavek na smazání článku byl odeslán.');
        }
        $this->presenter->redirect('Article:articleList');
    }

}