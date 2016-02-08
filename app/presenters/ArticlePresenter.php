<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 2. 12. 2015
 * Time: 16:20
 */
//TODO: možnost psaní překladů

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
        'en' => 'forms.article.english',
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
     */
    public function renderShow($articleId){
        $article = $this->articleManager->getArticle($articleId, $this->locale);
        $articleLang = $this->languageManager->getLangugage($article->language_id);

        if($this->locale !== $articleLang){
            $id = $article->translation_id;
            $this->redirect('Article:show', array('articleId' => $id));
        }

        $this->template->article = $article;
        $this->template->comments = $this->articleManager->getComments($articleId);
        $this->template->userRatings = $this->articleManager->getUserRatings($articleId, $this->user->getId());
        $this->template->ratingValues = $this->articleManager->getRating($articleId);
        $this->template->buttonsColor = array(
            'like' => 'like',
            'like-pressed' => 'like-pressed',
            'dislike' => 'dislike',
            'dislike-pressed' => 'dislike-pressed',
            'like-gray' => 'like-gray',
            'dislike-gray' => 'dislike-gray'
        );
    }


    public function handleLike($commentId){
        $this->articleManager->addCommentRating($commentId, $this->user->getId(), 1);
        if($this->isAjax()){
            $this->redrawControl('comments');
        }
    }


    public function handleDislike($commentId){
        $this->articleManager->addCommentRating($commentId, $this->user->getId(), -1);
        if($this->isAjax()){
            $this->redrawControl('comments');
        }
    }


    public function renderTranslation(){
        $articles = $this->articleManager->getArticlesToTranslate();

        $this->template->articles = $articles;
    }
}