<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 2. 12. 2015
 * Time: 16:20
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ArticleManager;
use Nette\Security\User;
use App;
use IPub\VisualPaginator\Components as VisualPaginator;
use Tester\Environment;


class ArticlePresenter extends BasePresenter
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var ArticleManager
     */
    private $articleManager;

    /** @var array */
    private $language = array(
        'cs' => 'forms.article.czech',
        'en' => 'forms.article.english',
    );

    /**
     * ArticlePresenter constructor.
     * @param ArticleManager $articleManager
     * @param User $user
     */
    public function __construct(ArticleManager $articleManager, User $user)
    {
        $this->articleManager = $articleManager;
        $this->user = $user;
    }


    public function actionAdd()
    {
        if(!$this->user->isAllowed('article', 'add')){
            $this->flashMessage($this->translator->translate('messages.flash.permissionAddArticle'), 'error');
            $this->redirect('Homepage:');
        }
    }

    /**
     * @return Form
     */
    protected function createComponentAddArticleForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addRadioList('language', 'forms.article.selectLanguage', $this->language)
            ->setValue($this->locale)
            ->getSeparatorPrototype()->setName(null);

        $form->addText('title', 'forms.article.title')
            ->setRequired('forms.article.requiredTitle');

        $form->addTextArea('caption', 'forms.article.caption')
            ->setRequired('forms.article.requiredCaption');

        $form->addTextArea('content', 'forms.article.content')
            ->setRequired('forms.article.requiredContent')
            ->setAttribute('class', 'mceEditor_' . $this->locale);

        $form->addSubmit('submit', 'forms.article.save');

        $form->onSuccess[] = array($this, 'addArticleFormSucceeded');
        return $form;
    }

    /**
     * @param Form $form
     * @param $values
     */
    public function addArticleFormSucceeded(Form $form, $values)
    {
        try {
            $this->articleManager->addArticle($this->user->getId(), $values);
        } catch(App\Exceptions\DuplicateNameException $e) {
            $form->addError($this->translator->translate($e->getMessage()));
        }
    }

    protected function createComponentVisualPaginator()
    {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile(__DIR__.'\templates\VisualPaginator\VPTemplate.latte');
        $control->disableAjax();
        return $control;
    }

    public function renderArticleList(){
        $articles = $this->articleManager->getArticles($this->locale);

        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemCount = $articles->count('*');
        $paginator->itemsPerPage = 2;

        $articles->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->articles = $articles;
    }

    public function renderShow($articleId){
        $this->template->article = $this->articleManager->getArticle($articleId, $this->locale);
        $this->template->comments = $this->articleManager->getComments($articleId, $this->locale);
    }
}