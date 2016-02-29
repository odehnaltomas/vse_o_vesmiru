<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 29. 2. 2016
 * Time: 10:14
 */

namespace App\Forms;


trait TCreateComponentDeleteArticleForm
{
    /** @var  IDeleteArticleFormFactory */
    protected $deleteArticleFormFactory;


    public function injectDeleteArticleFormFactory(IDeleteArticleFormFactory $deleteArticleFormFactory){
        $this->deleteArticleFormFactory = $deleteArticleFormFactory;
    }


    /**
     * @return DeleteArticleForm
     */
    protected function createComponentDeleteArticleForm(){
        return $this->deleteArticleFormFactory->create();
    }
}