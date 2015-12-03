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

class ArticlePresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $database;

    /** @var array */
    private $language = array(
        'cs' => 'forms.article.czech',
        'en' => 'forms.article.english',
    );

    /**
     * ArticlePresenter constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return Form
     */
    protected function createComponentAddArticleForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addRadioList('language', 'forms.article.selectLanguage', $this->language)
            ->setValue($this->locale);

        $form->addText('title', 'forms.article.title')
            ->setRequired('forms.article.requiredTitle');

        //TODO: Přidat TinyMCE
        $form->addTextArea('content', 'forms.article.content')
            ->setRequired('forms.article.requiredContent');

        $form->addSubmit('submit', 'forms.article.save');

        $form->onSuccess[] = array($this, 'addArticleFormSucceeded');
        return $form;
    }

    public function addArticleFormSucceeded(Form $form, $values)
    {

    }
}