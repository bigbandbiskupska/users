<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/04/2018
 * Time: 10:27
 */

namespace App\FrontModule\Presenters;



use Tulinkry\Application\UI\Presenter;

class BasePresenter extends Presenter
{
    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect(':User:Homepage:');
    }
}