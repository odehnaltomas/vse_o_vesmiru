<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 2. 12. 2015
 * Time: 16:20
 */

//TODO: Mazání článků přímo u výpisu článků

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ArticleManager;
use Nette\Security\User;
use App\Model\LanguageManager;
use App;
use IPub\VisualPaginator\Components as VisualPaginator;
use App\Model\RequestManager;


class ArticlePresenter extends BasePresenter
{



    private $userId;

    /** @var  int */
    private $articleId;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ArticleManager
     */
    public $articleManager;

    /** @var LanguageManager */
    private $languageManager;

    /** @var array */
    private $language = array(
        'cs' => 'forms.article.czech',
        'en' => 'forms.article.english'
    );

    /** @var App\Model\RequestManager  */
    public $requestManager;

    /** @var array */
    private $ratingArticleValues = array(
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
     * @param RequestManager $requestManager
     */
    public function __construct(ArticleManager $articleManager, User $user, LanguageManager $languageManager, RequestManager $requestManager)
    {
        $this->articleManager = $articleManager;
        $this->user = $user;
        $this->languageManager = $languageManager;
        $this->requestManager = $requestManager;
    }


    public function actionAdd() {
        if(!$this->user->isAllowed('article', 'add') AND !$this->user->isAllowed('article', 'addRequest'))
            throw new Nette\Application\UI\BadSignalException;
    }


    /**
     * @return Form
     */
    protected function createComponentAddArticleForm() {
        $form = new Form;
        $form->getElementPrototype()->novalidate('novalidate');
        $form->setTranslator($this->translator);

        $form->addRadioList('language', 'forms.article.selectLanguage', $this->language)
            ->setValue($this->locale)
            ->getSeparatorPrototype()->setName(null);

        $form->addText('title', 'forms.article.title')
            ->addRule(FORM::MIN_LENGTH, "forms.article.titleMinLength", 3)
            ->addRule(FORM::MAX_LENGTH, "forms.article.titleMaxLength", 100)
            ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\<>]+")
            ->setRequired('forms.article.requiredTitle');

        $form->addTextArea('caption', 'forms.article.caption')
            ->addRule(FORM::MIN_LENGTH, "forms.article.captionMinLength", 5)
            ->addRule(FORM::MAX_LENGTH, "forms.article.captionMaxLength", 500)
            ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\<>]+")
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
     * @throws Nette\Application\UI\BadSignalException
     */
    public function addArticleFormSucceeded(Form $form, $values)
    {
        if ($this->user->isAllowed('article', 'add')) {
            $this->articleManager->addArticle($this->user->getId(), $values);
            $this->flashMessage($this->translator->translate('forms.article.savedArticle'));
            $this->redirect('Article:articleList');

        } elseif($this->user->isAllowed('article','addRequest')){
            $article = $this->articleManager->addArticle($this->user->getId(), $values, 'request');
            $this->requestManager->addRequest($this->user->getId(), 2, $article->id);
            $this->flashMessage($this->translator->translate('forms.article.requestAddArticle'));
            $this->redirect('Article:articleList');
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    protected function createComponentVisualPaginator()
    {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile(__DIR__.'/templates/VisualPaginator/VPTemplate.latte');
        $control->disableAjax();

        $that = $this;
        $control->onShowPage[] = function($component, $page) use($that){
            if(!$that->isAjax()) {
                $that->redirect('this', ['visualPaginator-page' => $page]);
            }
        };
        return $control;
    }


    public function renderArticleList(){
        $articles = $this->articleManager->getArticles($this->locale);

        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemCount = $articles->count('*');
        $paginator->itemsPerPage = 2;

        $articles->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->articles = $articles->fetchAll();
        $this->template->itemCount = $articles->count('*');
        $this->template->userId = $this->userId;
        $this->template->articleId = $this->articleId;
        if($this->userId !== NULL)
            $this['deleteArticleForm']['userId']->setDefaultValue($this->userId);
        $this['deleteArticleForm']['articleId']->setDefaultValue($this->articleId);
    }


    protected function createComponentCommentForm(){
        $form = new Form();
        $form->getElementPrototype()->novalidate('novalidate');
        $form->setTranslator($this->translator);

        if($this->user->isAllowed('comment','write')){
            $form->addTextArea('content', 'forms.article.commentWrite')
                ->addRule(FORM::MIN_LENGTH, "forms.article.commentMinLength", 3)
                ->addRule(FORM::MAX_LENGTH, "forms.article.commentMaxLength", 500)
                ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\\<>]+");

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
     * @throws Nette\Application\UI\BadSignalException
     */
    public function commentFormSucceeded(Form $form, $values){
        if($this->user->isAllowed('comment', 'write')) {
            $articleId = $this->getParameter('articleId');
            $userId = $this->user->getId();
            $this->articleManager->addComment($values, $articleId, $userId);

            $this->redirect('this');
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    /**
     * @param $articleId
     * @throws Nette\Application\BadRequestException
     */
    public function actionShow($articleId){
        $article = $this->articleManager->getArticle($articleId);

        if(!$article)
            throw new Nette\Application\BadRequestException;

        $articleLang = $this->languageManager->getLangugage($article->language_id);

        if($article->deleted === 0){
            $this->flashMessage($this->translator->translate('messages.flash.deletedArticle'));
            $this->redirect('Article:articleList');
        }
        if($this->locale !== $articleLang){
            $id = $article->translation_id;
            if($id !== NULL) {
                $this->redirect('Article:show', array('articleId' => $id));
            } else {
                $this->flashMessage($this->translator->translate('messages.flash.nonexistsTranslation'));
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
        $this->template->ratingArticleValues = $this->ratingArticleValues;
        $this->template->articleRating = $this->articleManager->getArticleRating($articleId);
        $this->template->userArticleRating = $this->articleManager->ratedArticle($this->user->getId(), $articleId);
        $this->template->usersKarma = $this->articleManager->getUsersKarma($articleId);
        //$this->template->articleId = $articleId;
        //$this->template->userId = $this->user->getId();
        $this->template->counter = count($this->articleManager->getComments($articleId));
        if($this->userId !== NULL)
            $this['deleteArticleForm']['userId']->setDefaultValue($this->userId);
        $this['deleteArticleForm']['articleId']->setDefaultValue($articleId);
    }


    public function handleLike($commentId) {
        if($this->user->isAllowed('comment', 'like')) {
            if (!$this->articleManager->alreadyRated($this->user->getId(), $commentId, 1)) {
                $this->articleManager->alreadyRated($this->user->getId(), $commentId, -1);
                $this->articleManager->addCommentRating($commentId, $this->user->getId(), 1);
            }

            if ($this->isAjax()) {
                $this->redrawControl('comments');
            }
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function handleDislike($commentId){
        if($this->user->isAllowed('comment', 'dislike')) {
            if (!$this->articleManager->alreadyRated($this->user->getId(), $commentId, -1)) {
                $this->articleManager->alreadyRated($this->user->getId(), $commentId, 1);
                $this->articleManager->addCommentRating($commentId, $this->user->getId(), -1);
            }

            if ($this->isAjax()) {
                $this->redrawControl('comments');
            }
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function handleDeleteComment($commentId){
        $this->articleManager->delComment($commentId);
        if($this->isAjax()){
            $this->redrawControl('comments');
        }
    }


    public function handleRateArticle($articleId, $userId, $value){
        if($this->user->isLoggedIn()){
            $rate = $this->articleManager->ratedArticle($userId, $articleId);

            if(!$rate) {
                $this->articleManager->rateArticle($articleId, $userId, $value);
            } else
                $this->articleManager->rateArticle($articleId, $userId, $value, $rate->id);


            if($this->isAjax()){
                $this->redrawControl('articleRating');
            }

        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function renderTranslationList(){
        if($this->user->isAllowed('translation', 'list')) {

            $articles = $this->articleManager->getArticlesToTranslate();

            $this->template->articles = $articles;
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function actionTranslation($articleId){
        if($this->user->isAllowed('translation', 'add')) {
            $article = $this->articleManager->getArticle($articleId);

            if (!$article)
                throw new Nette\Application\BadRequestException;

            $this['addTranslationForm']['originalArticleId']->setDefaultValue($articleId);

            if ($article->language['language'] === 'cs') {
                $this['addTranslationForm']['language']->setDefaultValue('en');
                $this['addTranslationForm']['language']->setDisabled(['cs']);
            } else {
                $this['addTranslationForm']['language']->setDefaultValue('cs');
                $this['addTranslationForm']['language']->setDisabled(['en']);
            }
        } else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function renderTranslation($articleId){
        $this->template->articleId = $articleId;
    }


    protected function createComponentAddTranslationForm()
    {
        $form = new Form;
        $form->getElementPrototype()->novalidate('novalidate');
        $form->setTranslator($this->translator);

        $form->addRadioList('language', 'forms.article.selectLanguage', $this->language)
            ->setValue($this->locale)
            ->getSeparatorPrototype()->setName(null);

        $form->addText('title', 'forms.article.title')
            ->addRule(FORM::MIN_LENGTH, "forms.article.titleMinLength", 3)
            ->addRule(FORM::MAX_LENGTH, "forms.article.titleMaxLength", 255)
            ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\<>]+")
            ->setRequired('forms.article.requiredTitle');

        $form->addTextArea('caption', 'forms.article.caption')
            ->addRule(FORM::MIN_LENGTH, "forms.article.captionMinLength", 5)
            ->addRule(FORM::MAX_LENGTH, "forms.article.captionMaxLength", 500)
            ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\<>]+")
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
                $this->flashMessage($this->translator->translate('messages.flash.translationSaved'));
                $this->redirect('Article:articleList');
            } catch(App\Exceptions\DuplicateNameException $e) {
                $form->addError($this->translator->translate($e->getMessage()));
            }
        }
        else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function renderTranslationOriginal($articleId){
        if($this->user->isAllowed('translation', 'original'))
            $this->template->article = $this->articleManager->getArticle($articleId);
        else
            throw new Nette\Application\UI\BadSignalException;
    }


    public function handleDeleteArticle($articleId){
        if($this->user->isAllowed('article', 'delRequest')) {
            $this->userId = $this->user->getId();
        }
        $this->articleId = $articleId;
        if($this->isAjax()) {
            $this->redrawControl('popUp');
        }

    }


    public function renderEdit($articleId){
        if($this->user->isLoggedIn()) {
            $article = $this->articleManager->getArticle($articleId);

            if (!$article)
                throw new Nette\Application\BadRequestException;

            $this['editArticleForm']['language']->setDefaultValue($article->language['language']);
            $this['editArticleForm']['title']->setDefaultValue($article->title);
            $this['editArticleForm']['caption']->setDefaultValue($article->caption);
            $this['editArticleForm']['content']->setDefaultValue($article->content);
            $this['editArticleForm']['articleId']->setDefaultValue($articleId);

        }
    }


    protected function createComponentEditArticleForm() {
        $form = new Form;
        $form->getElementPrototype()->novalidate('novalidate');
        $form->setTranslator($this->translator);

        $form->addRadioList('language', 'forms.article.selectLanguage', $this->language)
            ->getSeparatorPrototype()->setName(null);

        $form->addText('title', 'forms.article.title')
            ->addRule(FORM::MIN_LENGTH, "forms.article.titleMinLength", 3)
            ->addRule(FORM::MAX_LENGTH, "forms.article.titleMaxLength", 255)
            ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\<>]+")
            ->setRequired('forms.article.requiredTitle');

        $form->addTextArea('caption', 'forms.article.caption')
            ->addRule(FORM::MIN_LENGTH, "forms.article.captionMinLength", 5)
            ->addRule(FORM::MAX_LENGTH, "forms.article.captionMaxLength", 500)
            ->addRule(FORM::PATTERN, "forms.sign.forbiddenChars", "[^\"\\<>]+")
            ->setRequired('forms.article.requiredCaption');

        $form->addTextArea('content', 'forms.article.content')
            ->setRequired('forms.article.requiredContent')
            ->setAttribute('class', 'mceEditor_' . $this->locale);

        $form->addHidden('articleId');

        $form->addSubmit('send', 'forms.article.change');

        $form->onSuccess[] = array($this, 'editArticleFormSucceeded');
        return $form;
    }


    public function editArticleFormSucceeded($form, $values){
        if ($this->user->isAllowed('article', 'edit')) {
            $this->articleManager->editArticle($values);
            $this->flashMessage($this->translator->translate('forms.article.articleChanged'));
            $this->redirect('Article:show', array('articleId' => $values->articleId));

        } elseif($this->user->isAllowed('article','editRequest')){
            $article = $this->articleManager->addArticle($this->user->getId(), $values, 'request');
            $this->requestManager->addRequest($this->user->getId(), 3, $article->id, $values->articleId);
            $this->flashMessage($this->translator->translate('forms.article.requestEditArticle'));
            $this->redirect('Article:show', array('articleId' => $values->articleId));
        } else
            throw new Nette\Application\UI\BadSignalException;
    }

    protected function createComponentDeleteArticleForm(){
        $form = new Form;
        $form->setTranslator($this->translator);

        if($this->user->isAllowed('article', 'delRequest')) {
            $form->addTextArea('message', 'forms.article.purposeOfDeleting');

            $form->addSubmit('send', 'forms.article.articleDeleteRequest');

            $form->addHidden('userId');

            $form->addHidden('requestCounterId', 1);
        } elseif($this->user->isAllowed('article', 'del')) {
            $form->addSubmit('send', 'forms.article.articleDelete');
        }

        $form->addHidden('articleId');

        $form->onSuccess[] = array($this, "deleteArticleSucceeded");

        return $form;
    }

    public function deleteArticleSucceeded($form, $values){
        $result = NULL;
        if($this->user->isAllowed('article', 'del')) {
            $this->articleManager->delArticle($values->articleId);
            $this->flashMessage($this->translator->translate('forms.article.articleDeleted'));
        } elseif($this->user->isAllowed('article', 'delRequest')){
            $result = $this->requestManager->addRequest($values->userId, 1, $values->articleId, $values->message);
            if($result === 0) {
                $this->flashMessage($this->translator->translate('messages.flash.alreadyDelRequest'));
            } else
                $this->flashMessage($this->translator->translate('messages.flash.requestDeleteArticle'));
        }
        $this->redirect('this');
    }
}