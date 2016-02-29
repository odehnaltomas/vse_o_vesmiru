<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 28. 2. 2016
 * Time: 17:46
 */

namespace App\Presenters;


use App\Forms\TCreateComponentDeleteArticleForm;
use App\Model\ArticleManager;
use App\Model\RequestManager;

class RequestPresenter extends BasePresenter
{

    private $requestManager;

    private $articleManager;


    public function __construct(RequestManager $requestManager, ArticleManager $articleManager)
    {
        $this->requestManager = $requestManager;
        $this->articleManager = $articleManager;
    }


    public function renderRequestList(){
        $this->template->locale = $this->locale;
        $this->template->requests = $this->requestManager->getRequests();
    }


    public function handleRejectRequest($requestId){
        $this->requestManager->rejectRequest($requestId);
        $this->flashMessage('Požadavek byl zamítnut!');

        if($this->isAjax()) {
            $this->redrawControl('requests');
            $this->redrawControl('flashmessages');
        }
    }


    //TODO: vyřešit více requestů na jeden článek (accepted)
    public function handleAcceptDelRequest($articleId, $requestId){
        $this->requestManager->acceptDelRequest($requestId, $articleId);
        $this->articleManager->delArticle($articleId);
        $this->flashMessage('Článek byl smazán!');

        if($this->isAjax()){
            $this->redrawControl('requests');
            $this->redrawControl('flashmessages');
        }
    }
}