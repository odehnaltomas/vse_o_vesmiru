<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 24. 11. 2015
 * Time: 21:01
 */

namespace App\Model;


use Nette;
use App;

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
     * @param $articleId
     * @return Nette\Database\Table\Selection
     */
    public function getArticle($articleId){
        $article = $this->database->table('article')->get($articleId);
        return $article;
    }


    /**
     * @param $articleId
     * @param $locale
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getComments($articleId, $locale){
        $article = $this->getArticle($articleId, $locale);
        return $this->database->table(self::TABLE_COMMENT)->where(self::COMMENT_ARTICLE_ID, $article->id)->fetchAll();
    }


    /**
     * @param $locale
     * @return Nette\Database\Table\Selection
     */
    public function getArticles($locale){
        $languageId = $this->languageManager->getLanguageId($locale);
        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_LANGUAGE_ID ,$languageId)
                    ->order('created DESC');
    }


    /**
     * @param $locale
     * @return int
     */
    public function getLangArticleSum($locale){
        $languageId = $this->languageManager->getLanguageId($locale);
        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_LANGUAGE_ID, $languageId)
                    ->count("id");

    }


    /**
     * @param $id
     * @param $values
     * @throws App\Exceptions\DuplicateNameException
     */
    public function addArticle($id, $values) {

        $data = array();
        foreach($values as $value){
            $data[] = $value;
        }

        list($language, $title, $caption, $content) = $data;

        $languageId = $this->languageManager->getLanguageId($language);

        try {
            $this->database->table(self::TABLE_ARTICLE)->insert(array(
                self::ARTICLE_COLUMN_LANGUAGE_ID => $languageId,
                self::ARTICLE_COLUMN_TITLE => $title,
                self::ARTICLE_COLUMN_CAPTION => $caption,
                self::ARTICLE_COLUMN_CONTENT => $content,
                self::ARTICLE_COLUMN_USER_ID => $id,
                self::ARTICLE_COLUMN_ARTICLE_RATING => 0
            ));
        } catch(Nette\Database\UniqueConstraintViolationException $e) {
            throw new App\Exceptions\DuplicateNameException("messages.exceptions.duplicateTitle");
        }
    }


    public function addComment($values, $articleId, $userId){
        $data = array();
        foreach ($values as $value){
            $data[] = $value;
        }
        list($content) = $data;
        $this->database->table(self::TABLE_COMMENT)->insert(array(
            self::COMMENT_ARTICLE_ID => $articleId,
            self::COMMENT_USER_ID => $userId,
            self::COMMENT_CONTENT => $content
        ));
    }
}