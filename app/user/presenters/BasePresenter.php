<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/04/2018
 * Time: 10:27
 */

namespace App\UserModule\Presenters;


use App\FrontModule\Presenters\BasePresenter as FrontBasePresenter;

class BasePresenter extends FrontBasePresenter
{
    public function startup()
    {
        parent::startup();
        if(!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Front:Sign:in');
        }
        $user = $this->getUser()->getIdentity();
        $this->template->apiKey = $user->token;
    }

}