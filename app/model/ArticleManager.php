<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 24. 11. 2015
 * Time: 21:01
 */

namespace App\Model;


use Nette;

class ArticleManager extends BaseManager
{
    /**
     * @var Nette\Database\Context
     */
    private $database;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * ArticleManager constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database, LanguageManager $languageManager)
    {
        $this->database = $database;
        $this->languageManager = $languageManager;
    }

    /**
     * @param $id
     * @param $locale
     */
    public function getArticle($id, $locale)
    {

    }

    /**
     * @param $id
     * @param $values
     * @throws DuplicateNameException
     */
    public function addArticle($id, $values) {

        $data = array();
        foreach($values as $value){
            $data[] = $value;
        }

        list($language, $title, $content) = $data;

        $languageId = $this->languageManager->getLanguageId($language);

        try {
            $this->database->table(self::TABLE_ARTICLE)->insert(array(
                self::ARTICLE_COLUMN_LANGUAGE_ID => $languageId,
                self::ARTICLE_COLUMN_TITLE => $title,
                self::ARTICLE_COLUMN_CONTENT => $content,
                self::ARTICLE_COLUMN_USER_ID => $id,
                self::ARTICLE_COLUMN_ARTICLE_RATING => 0
            ));
        } catch(Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException("messages.exceptions.duplicateTitle");
        }
    }
}