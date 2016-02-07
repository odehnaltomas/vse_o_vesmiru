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
    public function getComments($articleId){
        $article = $this->getArticle($articleId);
        return $this->database->table(self::TABLE_COMMENT)
                    ->where(self::COMMENT_ARTICLE_ID, $articleId)
                    ->order(self::COMMENT_CREATED . ' DESC')
                    ->fetchAll();
    }


    public function getCommentsRating($articleId){
        $ratingsArray = array();

        $comments = $this->getComments($articleId);
        foreach( $comments as $comment){
            $ratingsArray[$comment->id] = $this->database->query('SELECT R.*
                                                                FROM comment_rating R RIGHT JOIN (SELECT C.id
                                                                FROM comment C LEFT JOIN article A
                                                                ON C.article_id = A.id WHERE A.id = '. $articleId .') C
                                                                ON R.comment_id = C.id WHERE C.id = '. $comment->id)->fetchAll();
        }
        return $ratingsArray;
    }

    //TODO: vyresit vraceni pro sablonu
    public function getUserRatings($articleId, $userId=NULL){
        $ratings =$this->database->query('SELECT R.*
                                        FROM comment_rating R RIGHT JOIN (SELECT C.id
                                        FROM comment C LEFT JOIN article A
                                        ON C.article_id = A.id WHERE A.id = '. $articleId .') C
                                        ON R.comment_id = C.id WHERE R.user_id = "'. $userId.'"')->fetchAll();
        $ratArray = array();
        foreach($ratings as $rating){
            $ratArray[$rating->comment_id] = $rating;
        }
        return $ratArray;
    }


    public function getRating($articleId){
        $array = $this->getCommentsRating($articleId);
        $values = array();
        foreach($array as $key => $comment){
            $value = 0;
            foreach($comment as $rating){
                $value += $rating->value;
            }
            $values[$key] = $value;
        }
        return $values;
    }


    public function addCommentRating($commentId, $userId, $value){
        $this->database->table(self::TABLE_COMMENT_RATING)->insert(array(
            self::COMMENT_RATING_USER_ID => $userId,
            self::COMMENT_RATING_COMMENT_ID => $commentId,
            self::COMMENT_RATING_VALUE => $value
        ));
    }


    /**
     * @param $locale
     * @return Nette\Database\Table\Selection
     */
    public function getArticles($locale){
        $languageId = $this->languageManager->getLanguageId($locale);
        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_LANGUAGE_ID ,$languageId)
                    ->order(self::ARTICLE_COLUMN_CREATED . ' DESC');
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
                self::ARTICLE_COLUMN_USER_ID => $id
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

    public function getArticlesToTranslate(){
        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_TRANSLATION_ID, NULL)
                    ->order(self::ARTICLE_COLUMN_CREATED);
    }
}