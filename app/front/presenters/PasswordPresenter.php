<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/04/2018
 * Time: 10:18
 */

namespace App\FrontModule\Presenters;

use App\Model\Users;
use Latte\Engine;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Http\Url;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;
use Tulinkry\Application\UI\Form;
use Tulinkry\Services\ParameterService;


class PasswordPresenter extends BasePresenter
{
    /**
     * @var Users
     * @inject
     */
    public $users;

    /**
     * @var IMailer
     * @inject
     */
    public $mailer;

    /**
     * @var ParameterService
     * @inject
     */
    public $parameters;

    public function actionRenew($token)
    {
        $redirect = Url::unescape($this->getHttpRequest()->getQuery('redirect'));
        $defaults = [
                'token' => $token,
        ];
        if(!empty($redirect)) {
            $defaults = array_merge($defaults, [
                'redirect' => $redirect
            ]);
            $this['renewPasswordForm']->setAction($this['renewPasswordForm']->getAction() . '?redirect=' . urlencode($redirect));
        }
        $this['renewPasswordForm']->setValues($defaults);
    }

    public function actionForgotten() {
        $redirect = Url::unescape($this->getHttpRequest()->getQuery('redirect'));
        if(!empty($redirect)) {
            $this['forgottenPasswordForm']->setValues([
                'redirect' => $redirect
            ]);
            $this['forgottenPasswordForm']->setAction($this['forgottenPasswordForm']->getAction() . '?redirect=' . urlencode($redirect));
        }
    }

    protected function createComponentForgottenPasswordForm()
    {
        $form = new Form;
        $form->addEmail('email', 'Váš email:')
            ->setAttribute('class', 'form-control')
            ->setRequired('Prosím vyplňte váš email.');

        $form->addHidden('redirect');

        $form->addSubmit('send', 'Zaslat požadavek na změnu hesla')
            ->setAttribute('class', 'form-control btn btn-primary');

        $form->onSuccess[] = [$this, 'forgottenPasswordFormSucceeded'];
        return $form;
    }

    public function forgottenPasswordFormSucceeded(Form $form, ArrayHash $values)
    {
        try {
            $user = $this->users->findOneBy([
                'email' => $values->email
            ]);

            if ($user === null) {
                throw new BadRequestException("Uživatel neexistuje.", IResponse::S400_BAD_REQUEST);
            }

            Debugger::log($this->getHttpRequest()->getQuery(), 'info');
            $token = $this->users->getUserWithNewPasswordToken($user->id);
            $redirect = $values->redirect;

            // send mail with token

            $latte = new Engine();
            $params = [
                'link' => sprintf('%s/%s%s',
                    $this->parameters->params['api']['users']['renew_password'],
                    $token,
                    (!empty($redirect) ? '?redirect=' . urlencode($redirect) : '')),
            ];

            $message = new Message();
            $message->setFrom('vstupenky@bigbandbiskupska.cz')
                ->addTo($user->email)
                ->setSubject('Zapomenuté heslo')
                ->setHtmlBody($latte->renderToString(__DIR__ . '/../mail/forgotten_password.latte', $params));
            $this->mailer->send($message);

            $this->flashMessage('Požadavek byl zpracován a byl vám zaslán email');
            $this->redirect(':User:Homepage:');
        } catch (BadRequestException $e) {
            $form->addError($e->getMessage());
            Debugger::log($e, ILogger::ERROR);
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            $form->addError("Došlo k neznámé chybě během zpracovaní dotazu.");
            Debugger::log($e, ILogger::ERROR);
        }
    }

    protected function createComponentRenewPasswordForm()
    {
        $form = new Form;

        $form->addPassword('password', 'Nové heslo:')
            ->setAttribute('class', 'form-control')
            ->setRequired('Prosím vyplňte své nové heslo.');

        $form->addPassword('again_password', 'Nové heslo znovu:')
            ->setAttribute('class', 'form-control')
            ->setRequired('Prosím vyplňte opět své nové heslo.');

        $form->addHidden("token");
        $form->addHidden("redirect");

        $form->addSubmit('send', 'Změnit heslo')
        ->setAttribute('class', 'form-control btn btn-primary');

        $form->onSuccess[] = [$this, 'renewPasswordFormSucceeded'];
        return $form;
    }

    public function renewPasswordFormSucceeded(Form $form, ArrayHash $values)
    {
        try {
            $this->users->renewPassword($values->token, $values->password, $values->again_password);

            // heslo bylo úspěšně změněno, přesměrovat na stránku
            if (!empty($values->redirect)) {
                $this->redirectUrl($values->redirect);
                return;
            }

            $this->flashMessage('Požadavek byl zpracován a vaše heslo bylo změněno.');
            $this->redirect(':User:Homepage:');
        } catch (BadRequestException $e) {
            $form->addError($e->getMessage());
            Debugger::log($e, ILogger::ERROR);
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            $form->addError("Nepodařilo se zaznamenat změnu hesla.");
            Debugger::log($e, ILogger::ERROR);
        }
    }
}