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
use App\Model\LanguageManager;
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

    /** @var LanguageManager */
    private $languageManager;

    /** @var array */
    private $language = array(
        'cs' => 'forms.article.czech',
        'en' => 'forms.article.english'
    );

    /** @var array */
    private $articleRating = array(
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5
    );

    /**
     * ArticlePresenter constructor.
     * @param ArticleManager $articleManager
     * @param User $user
     * @param LanguageManager $languageManager
     */
    public function __construct(ArticleManager $articleManager, User $user, LanguageManager $languageManager)
    {
        $this->articleManager = $articleManager;
        $this->user = $user;
        $this->languageManager = $languageManager;
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
            $this->flashMessage('Článek byl úspěšně uložen.');
            $this->redirect('Article:articleList');
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


    protected function createComponentCommentForm(){
        $form = new Form();
        $form->setTranslator($this->translator);

        if($this->user->isAllowed('comment','write')){
            $form->addTextArea('content', 'forms.article.commentWrite');

            $form->addSubmit('submit', 'forms.article.publish');

            $form->onSuccess[] = array($this, 'commentFormSucceeded');
        } else {
            $form->addTextArea('content', 'forms.article.commentWrite')
                ->setAttribute('placeholder','forms.article.commentPlaceholder')
                ->setDisabled();

            $form->addSubmit('submit', 'forms.article.publish')
                ->setDisabled();
        }
        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     */
    public function commentFormSucceeded(Form $form, $values){
        $articleId = $this->getParameter('articleId');
        $userId = $this->user->getId();
        $this->articleManager->addComment($values, $articleId, $userId);

        $this->redirect('this');
    }


    /**
     * @param $articleId
     * @throws Nette\Application\BadRequestException
     */
    public function actionShow($articleId){
        $article = $this->articleManager->getArticle($articleId, $this->locale);

        if(!$article)
            throw new Nette\Application\BadRequestException;

        $articleLang = $this->languageManager->getLangugage($article->language_id);

        if($article->deleted === 0){
            $this->flashMessage('Tento článek byl vymazán!');
            $this->redirect('Article:articleList');
        }
        if($this->locale !== $articleLang){
            $id = $article->translation_id;
            if($id !== NULL) {
                $this->redirect('Article:show', array('articleId' => $id));
            } else {
                $this->flashMessage('Překlad neexistuje!');
                $this->redirect('Article:articleList');
            }
        }
    }



    //TODO: hodnoceni clanku
    /**
     * @param $articleId
     */
    public function renderShow($articleId){
        $this->template->article = $article = $this->articleManager->getArticle($articleId, $this->locale);
        $this->template->comments = $this->articleManager->getComments($articleId);
        $this->template->userRatings = $this->articleManager->getUserRatings($articleId, $this->user->getId());
        $this->template->ratingValues = $this->articleManager->getRating($articleId);
        $this->template->user = $this->user;
        $this->template->articleRating = $this->articleRating;
        $this->template->usersKarma = $this->articleManager->getUsersKarma($articleId);
        $this['articleDelForm']['articleId']->setDefaultValue($articleId);
    }


    public function handleLike($commentId) {
        if(!$this->articleManager->alreadyRated($this->user->getId(), $commentId, 1)) {
            $this->articleManager->alreadyRated($this->user->getId(), $commentId, -1);
            $this->articleManager->addCommentRating($commentId, $this->user->getId(), 1);
        }

        if($this->isAjax()){
            $this->redrawControl('comments');
        }
    }


    public function handleDislike($commentId){
        if(!$this->articleManager->alreadyRated($this->user->getId(), $commentId, -1)) {
            $this->articleManager->alreadyRated($this->user->getId(), $commentId, 1);
            $this->articleManager->addCommentRating($commentId, $this->user->getId(), -1);
        }

        if($this->isAjax()){
            $this->redrawControl('comments');
        }
    }

    public function handleDeleteComment($commentId){
        $this->articleManager->delComment($commentId);
        if($this->isAjax()){
            $this->redrawControl('comments');
        }
    }


    public function handleRateArticle($articleId, $value){

    }


    public function renderTranslationList(){
        $articles = $this->articleManager->getArticlesToTranslate();

        $this->template->articles = $articles;
    }


    public function actionTranslation($articleId){
        $article = $this->articleManager->getArticle($articleId);

        if(!$article)
            throw new Nette\Application\BadRequestException;

        $this['addTranslationForm']['originalArticleId']->setDefaultValue($articleId);

        if($article->language['language'] === 'cs') {
            $this['addTranslationForm']['language']->setDefaultValue('en');
            $this['addTranslationForm']['language']->setDisabled(['cs']);
        } else {
            $this['addTranslationForm']['language']->setDefaultValue('cs');
            $this['addTranslationForm']['language']->setDisabled(['en']);
        }
    }


    public function renderTranslation($articleId){
        $this->template->articleId = $articleId;
    }


    protected function createComponentAddTranslationForm()
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

        $form->addHidden('originalArticleId');

        $form->addSubmit('submit', 'forms.article.save');

        $form->onSuccess[] = array($this, 'addTranslationFormSucceeded');
        return $form;
    }


    public function addTranslationFormSucceeded($form, $values){
        if($this->user->isAllowed('translation', 'add')){
            try {
                $this->articleManager->addTranslation($this->user->getId(), $values);
                $this->flashMessage('Překlad byl úspěšně uložen.');
                $this->redirect('Article:articleList');
            } catch(App\Exceptions\DuplicateNameException $e) {
                $form->addError($this->translator->translate($e->getMessage()));
            }
        }
    }


    public function renderTranslationOriginal($articleId){
        $this->template->article = $this->articleManager->getArticle($articleId);
    }


    public function createComponentArticleDelForm(){

        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addHidden('articleId');
        $form->addSubmit('submit', 'Smazat článek');

        $form->onSuccess[] = array($this, 'articleDelFormSucceeded');
        return $form;
    }


    public function articleDelFormSucceeded($form, $values){
        if($this->user->isAllowed('article', 'del')){
            if($this->articleManager->delArticle($values->articleId)){
                $this->flashMessage('Článek byl úspěšně vymazán!');
                $this->redirect('Article:articleList');
            }
        }
    }

    public function actionDel($articleId){
        dump($article = $this->articleManager->getArticle($articleId));
        if(!$article)
            throw new Nette\Application\BadRequestException;
    }

    public function handleDeleteArticle($articleId){

    }
}