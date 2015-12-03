<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 24. 11. 2015
 * Time: 21:01
 */

namespace App\Presenters;


use App\Model\BaseManager;
use Nette;

class ArticleManager extends BaseManager
{
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getArticle($id, $locale)
    {

    }

    public function addArticle($id, array $data)
    {
        list($lang, $title, $content) = $data;
    }
}