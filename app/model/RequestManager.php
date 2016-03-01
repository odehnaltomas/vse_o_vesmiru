<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 28. 2. 2016
 * Time: 17:45
 */

namespace App\Model;

use Nette;

class RequestManager extends BaseManager
{
    private $database;


    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }


    public function addRequest($userId, $requestCounterId, $articleId, $message = "") {

        $request = $this->database->table(self::TABLE_REQUEST)
            ->where(self::REQUEST_REQUEST_COUNTER_ID, $requestCounterId)
            ->where(self::REQUEST_ARTICLE_ID, $articleId)
            ->where(self::REQUEST_USER_ID, $userId)
            ->where(self::REQUEST_STATE, 1)->fetch();

        if(!$request) {
            return $this->database->table(self::TABLE_REQUEST)->insert(array(
                self::REQUEST_REQUEST_COUNTER_ID => $requestCounterId,
                self::REQUEST_ARTICLE_ID => $articleId,
                self::REQUEST_USER_ID => $userId,
                self::REQUEST_MESSAGE => $message,
                self::REQUEST_STATE => 1
            ));
        } else
            return 0;
    }


    public function getRequests($userId = NULL) {
        if($userId === NULL) {
            return $this->database->table(self::TABLE_REQUEST)
                ->where('NOT ' . self::REQUEST_STATE, 2)
                ->where('NOT ' . self::REQUEST_STATE, 3)
                ->where('NOT ' . self::REQUEST_STATE, 4)
                ->order(self::REQUEST_CREATED . " DESC")->fetchAll();
        } else {
            return $this->database->table(self::TABLE_REQUEST)
                ->where(self::REQUEST_USER_ID, $userId)
                ->order(self::REQUEST_CREATED . " DESC")->fetchAll();
        }
    }


    public function rejectRequest($requestId){
        $this->database->table(self::TABLE_REQUEST)
                    ->where(self::REQUEST_ID, $requestId)
                    ->update(array(self::REQUEST_STATE => 2));
    }


    public function acceptDelRequest($requestId, $articleId){
        $requests = $this->database->table(self::TABLE_REQUEST)
                    ->where(self::REQUEST_ARTICLE_ID, $articleId)
                    ->where(self::REQUEST_STATE, 1)->fetchAll();

        foreach($requests as $request) {
            if($request->id === (int)$requestId){
                $this->database->table(self::TABLE_REQUEST)
                    ->where(self::REQUEST_ID, $requestId)
                    ->update(array(self::REQUEST_STATE => 3));
            } else {
                $this->database->table(self::TABLE_REQUEST)
                    ->where(self::REQUEST_ID, $request->id)
                    ->update(array(self::REQUEST_STATE => 4));
            }
        }
    }


    public function acceptRequest($requestId){
        return $this->database->table(self::TABLE_REQUEST)
                    ->where(self::REQUEST_ID, $requestId)
                    ->update(array(self::REQUEST_STATE => 3));
    }
}