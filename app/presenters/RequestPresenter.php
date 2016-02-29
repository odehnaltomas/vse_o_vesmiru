<?php
/**
 * Created by PhpStorm.
 * User: Tomáš
 * Date: 28. 2. 2016
 * Time: 17:46
 */

namespace App\Presenters;


use App\Forms\TCreateComponentDeleteArticleForm;
use App\Model\RequestManager;

class RequestPresenter extends BasePresenter
{
    use TCreateComponentDeleteArticleForm;

    private $requestManager;


    public function __construct(RequestManager $requestManager)
    {
        $this->requestManager = $requestManager;
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
    public function handleAcceptRequest($userId, $requestId){

    }
}