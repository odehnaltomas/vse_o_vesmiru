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
        $article = $this->database->table(self::TABLE_ARTICLE)->get($articleId);
        return $article;
    }


    /**
     * @param $articleId
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getComments($articleId){
        $article = $this->getArticle($articleId);
        return $this->database->table(self::TABLE_COMMENT)
                    ->where(self::COMMENT_ARTICLE_ID, $articleId)
                    ->order(self::COMMENT_CREATED . ' DESC')
                    ->fetchAll();
    }


    /**
     * @param $articleId
     * @return array
     */
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


    /**
     * @param $articleId
     * @param null $userId
     * @return array
     */
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


    /**
     * @param $articleId
     * @return array
     */
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


    /**
     * @param $userId
     * @param $commentId
     * @param $value
     * @return int
     */
    public function alreadyRated($userId, $commentId, $value){
        $row = $this->database->table(self::TABLE_COMMENT_RATING)->where(
            self::COMMENT_RATING_USER_ID. '=? AND ' .self::COMMENT_RATING_COMMENT_ID. '=? AND ' .self::COMMENT_RATING_VALUE. '=?',
            $userId, $commentId, $value)->fetch();
        if($row) {
            return $row->delete();
        }
        return 0;
    }

    /**
     * @param $commentId
     * @param $userId
     * @param $value
     */
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
                    ->where(self::ARTICLE_COLUMN_LANGUAGE_ID, $languageId)
                    ->where(self::ARTICLE_COLUMN_DELETED, 1)
                    ->order(self::ARTICLE_COLUMN_CREATED . ' DESC');
    }


    public function getUsersKarma($articleId){
        $rows = $this->database->query("SELECT CR.value, C.id, C.user_id
                                        FROM comment_rating CR RIGHT JOIN
                                            (SELECT comment.id, comment.user_id FROM comment LEFT JOIN
                                                (SELECT comment.user_id
                                                 FROM comment LEFT JOIN user
                                                 ON comment.user_id = user.id
                                                 WHERE comment.article_id = $articleId
                                                 GROUP BY user.id) AS USER
                                            ON comment.user_id = USER.user_id) AS C
                                        ON CR.comment_id = C.id
                                        ORDER BY C.id")->fetchAll();

        $karma = array();
        foreach($rows as $row){
            $karma[$row->user_id] = array('plus' => 0, 'minus' => 0);
        }
        foreach($rows as $row){
            if($row->value === -1) $karma[$row->user_id]['minus']--;
            elseif($row->value === 1) $karma[$row->user_id]['plus']++;
        }
        return $karma;
    }


    /**
     * @param $userId
     * @param $values
     * @param null $request
     * @return Nette\Database\Table\IRow
     */
    public function addArticle($userId, $values, $request = NULL) {
        $deleted = 1;

        if($request === 'request'){
            $deleted = 0;
        }

        $data = array();
        foreach($values as $value){
            $data[] = $value;
        }

        list($language, $title, $caption, $content) = $data;

        $languageId = $this->languageManager->getLanguageId($language);

        return $this->database->table(self::TABLE_ARTICLE)->insert(array(
            self::ARTICLE_COLUMN_LANGUAGE_ID => $languageId,
            self::ARTICLE_COLUMN_TITLE => $title,
            self::ARTICLE_COLUMN_CAPTION => $caption,
            self::ARTICLE_COLUMN_CONTENT => $content,
            self::ARTICLE_COLUMN_USER_ID => $userId,
            self::ARTICLE_COLUMN_DELETED => $deleted
        ));
    }


    /**
     * @param $articleId
     * @return int
     */
    public function delArticle($articleId){
        $article = $this->database->table(self::TABLE_ARTICLE)
                                ->get($articleId);
        if($article->translation_id === NULL) {
            return $this->database->table(self::TABLE_ARTICLE)
                ->where(self::ARTICLE_COLUMN_ID, $articleId)
                ->update(array(self::ARTICLE_COLUMN_DELETED => 0));
        }
        else {
            $this->database->table(self::TABLE_ARTICLE)
                ->where(self::ARTICLE_COLUMN_ID, $article->translation_id)
                ->update(array(self::ARTICLE_COLUMN_TRANSLATION_ID => NULL));

            $this->database->table(self::TABLE_ARTICLE)
                ->where(self::ARTICLE_COLUMN_ID, $articleId)
                ->update(array(self::ARTICLE_COLUMN_DELETED => 0, self::ARTICLE_COLUMN_TRANSLATION_ID => NULL));
        }

    }


    /**
     * @param $userId
     * @param $values
     * @throws App\Exceptions\DuplicateNameException
     */
    public function addTranslation($userId, $values){

        $data = array();
        foreach($values as $value){
            $data[] = $value;
        }

        list($language, $title, $caption, $content, $originalArticleId) = $data;

        $languageId = $this->languageManager->getLanguageId($language);

        try {
            $translation = $this->database->table(self::TABLE_ARTICLE)->insert(array(
                self::ARTICLE_COLUMN_TRANSLATION_ID => $originalArticleId,
                self::ARTICLE_COLUMN_LANGUAGE_ID => $languageId,
                self::ARTICLE_COLUMN_TITLE => $title,
                self::ARTICLE_COLUMN_CAPTION => $caption,
                self::ARTICLE_COLUMN_CONTENT => $content,
                self::ARTICLE_COLUMN_USER_ID => $userId,
                self::ARTICLE_COLUMN_DELETED => 1
            ));

            $this->database->table(self::TABLE_ARTICLE)
                ->where(self::ARTICLE_COLUMN_ID, $originalArticleId)
                ->update(array(self::ARTICLE_COLUMN_TRANSLATION_ID => $translation->id));

        } catch(Nette\Database\UniqueConstraintViolationException $e) {
            throw new App\Exceptions\DuplicateNameException("messages.exceptions.duplicateTitle");
        }
    }


    /**
     * @param $values
     * @param $articleId
     * @param $userId
     */
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


    public function delComment($commentId){
        $this->database->table(self::TABLE_COMMENT_RATING)
            ->where(self::COMMENT_RATING_COMMENT_ID, $commentId)
            ->delete();

        $this->database->table(self::TABLE_COMMENT)
            ->where(self::COMMENT_ID, $commentId)
            ->delete();
    }


    /**
     * @return Nette\Database\Table\Selection
     */
    public function getArticlesToTranslate(){
        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_TRANSLATION_ID, NULL)
                    ->where(self::ARTICLE_COLUMN_DELETED, 1)
                    ->order(self::ARTICLE_COLUMN_CREATED);
    }


    public function saveArticleRating($articleId, $value){

    }


    public function visibleArticle($articleId){
        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_ID, $articleId)
                    ->update(array(self::ARTICLE_COLUMN_DELETED => 1));
    }


    public function editArticle($values){
        $data = array();
        foreach($values as $value){
            $data[] = $value;
        }

        list($language, $title, $caption, $content, $articleId) = $data;

        $languageId = $this->languageManager->getLanguageId($language);

        return $this->database->table(self::TABLE_ARTICLE)
                    ->where(self::ARTICLE_COLUMN_ID, $articleId)
                    ->update(array(
                        self::ARTICLE_COLUMN_LANGUAGE_ID => $languageId,
                        self::ARTICLE_COLUMN_TITLE => $title,
                        self::ARTICLE_COLUMN_CAPTION => $caption,
                        self::ARTICLE_COLUMN_CONTENT => $content
                    ));
    }
}