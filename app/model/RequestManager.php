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


    public function addRequest($values) {
        $data = array();
        foreach($values as $value){
            $data[] = $value;
        }

        list($message, $userId, $requestCounterId, $articleId) = $data;

        $request = $this->database->table(self::TABLE_REQUEST)
            ->where(self::REQUEST_REQUEST_COUNTER_ID, $requestCounterId)
            ->where(self::REQUEST_ARTICLE_ID, $articleId)
            ->where(self::REQUEST_USER_ID, $userId)->fetch();

        if(!$request) {
            return $this->database->table(self::TABLE_REQUEST)->insert(array(
                self::REQUEST_REQUEST_COUNTER_ID => $requestCounterId,
                self::REQUEST_ARTICLE_ID => $articleId,
                self::REQUEST_USER_ID => $userId,
                self::REQUEST_MESSAGE => $message
            ));
        } else
            return 0;
    }


    public function getRequests() {

    }
}