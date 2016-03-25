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
use Nette\Application\BadRequestException;
use Nette\Application\UI\BadSignalException;

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
        if($this->user->isAllowed('request', 'list')) {
            $this->template->locale = $this->locale;
            $this->template->requests = $this->requestManager->getRequests();
        } else
            throw new BadSignalException;
    }


    public function handleRejectRequest($requestId){
        if($this->user->isAllowed('request', 'reject')) {
            $this->requestManager->rejectRequest($requestId);
            $this->flashMessage('messages.flash.requestReject');

            if ($this->isAjax()) {
                $this->redrawControl('requests');
                $this->redrawControl('flashmessages');
            }
        } else
            throw new BadSignalException;
    }


    public function handleAcceptDelRequest($articleId, $requestId){
        if($this->user->isAllowed('request', 'accept')) {
            $this->requestManager->acceptDelRequest($requestId, $articleId);
            $this->articleManager->delArticle($articleId);
            $this->flashMessage('messages.flash.deletedArticle');

            if ($this->isAjax()) {
                $this->redrawControl('requests');
                $this->redrawControl('flashmessages');
            }
        } else
            throw new BadSignalException;
    }


    public function renderShowArticle($articleId){
        $article = $this->articleManager->getArticle($articleId);
        if($this->user->isAllowed('request', 'showArticle') || $article->user_id === $this->user->getId() ){

            if(!$article)
                throw new BadRequestException;

            $this->template->article = $article;
        } else
            throw new BadSignalException;
    }


    public function handleAcceptAddRequest($articleId, $requestId){
        if($this->user->isAllowed('request', 'accept')){
            $this->requestManager->acceptRequest($requestId);
            $this->articleManager->visibleArticle($articleId);
            $this->flashMessage('messages.flash.addedArticle');

            if($this->isAjax()){
                $this->redrawControl('requests');
                $this->redrawControl('flashmessages');
            }
        } else
            throw new BadSignalException;
    }


    public function handleAcceptEditRequest($articleId, $originalArticleId, $requestId){
        if($this->user->isAllowed('request', 'accept')){
            $this->requestManager->acceptRequest($requestId);
            $this->articleManager->acceptEditArticle($articleId, $originalArticleId);
            $this->flashMessage('messages.flash.changedArticle');

            if($this->isAjax()){
                $this->redrawControl('requests');
                $this->redrawControl('flashmessages');
            }
        } else
            throw new BadSignalException;
    }


    public function renderMyRequests(){
        $this->template->locale = $this->locale;
        $requests = $this->requestManager->getRequests($this->user->getId());
        $this->template->requests = $requests;
        if(empty($requests)) {
            $this->template->message = $this->translator->translate("templates.request.noRequests");
        }
    }
}