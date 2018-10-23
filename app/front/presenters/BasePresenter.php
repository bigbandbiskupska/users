<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/04/2018
 * Time: 10:27
 */

namespace App\FrontModule\Presenters;



use Tulinkry\Application\UI\Presenter;
use Tulinkry\Services\ParameterService;

class BasePresenter extends Presenter
{

    /**
     * @var ParameterService
     * @inject
     */
    public $parameters;

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect(':User:Homepage:');
    }

    public function startup() {
        parent::startup();
        $timestamp = $this->parameters->params['appDir'] . DIRECTORY_SEPARATOR . 'timestamp';
        if(file_exists($timestamp)) {
            $this->template->lastUpdated = DateTime::from(@filemtime($timestamp));
        }
    }

    public static function update() {
        $timestamp = APP_DIR . DIRECTORY_SEPARATOR . 'timestamp';
        if(file_exists($timestamp)) {
            FileSystem::delete($timestamp);
        }
        FileSystem::write($timestamp, "");
    }
}