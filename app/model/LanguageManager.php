<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 13. 12. 2015
 * Time: 17:52
 */

namespace App\Model;

use Nette;

class LanguageManager extends BaseManager
{
    /**
     * @var Nette\Database\Context
     */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getLanguageId($locale) {
        return $this->database->table(self::TABLE_LANGUAGE)
                    ->select(self::LANGUAGE_COLUMN_ID)
                    ->where(self::LANGUAGE_COLUMN_LANGUAGE, $locale);
    }

}