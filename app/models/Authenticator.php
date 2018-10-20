<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 21/04/2018
 * Time: 10:11
 */

namespace App\Model;

use Nette;
use Nette\Security;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\Passwords;

/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements Security\IAuthenticator
{
    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;
        $row = $this->database->table('users')->where('email', $email)->fetch();

        if (!$row) {
            throw new AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $row->password)) {
            throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        }

        $arr = $row->toArray();
        unset($arr['password']);
        return new Identity($row->id, explode(',', $row['roles']), $arr);
    }
}
