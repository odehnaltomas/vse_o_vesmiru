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
     * @param Nette\Database\Context $database
     */
    public function __construct(ArticleManager $articleManager, User $user)
    {
        $this->articleManager = $articleManager;
        $this->user = $user;
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
        } catch(App\DuplicateNameException $e) {
            $form->addError($this->translator->translate($e->getMessage()));
        }

    }

    public function renderArticleList(){
        $this->template->articles = $this->articleManager->getArticles($this->locale);
    }
}