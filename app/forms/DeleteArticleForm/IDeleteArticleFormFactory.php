<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 29. 2. 2016
 * Time: 10:11
 */

namespace App\Forms;


interface IDeleteArticleFormFactory
{
    /** @return DeleteArticleForm */
    function create();

}