<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/04/2018
 * Time: 10:18
 */

namespace App\FrontModule\Presenters;

use Nette;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;
use Tulinkry\Application\UI\Form;


class SignPresenter extends BasePresenter
{
    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->addText('username', 'Váš email:')
            ->setRequired('Prosím vyplňte svůj email.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit')
        ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, ArrayHash $values)
    {
        try {
            $this->getUser()->login($values->username, $values->password);

            if($this->getHttpRequest()->getQuery('redirect') !== null) {
                $this->redirect(Url::unescape($this->getHttpRequest()->getQuery('redirect')));
            }

            $this->redirect(':User:Homepage:');
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }
}