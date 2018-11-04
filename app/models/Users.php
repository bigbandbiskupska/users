<?php

namespace App\Model;

use DateInterval;
use DateTimeInterval;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

class Users extends BaseModel
{
    public function renewPassword($password_token, $new_password, $new_password_repeast) {
        if($new_password !== $new_password_repeast) {
            throw new BadRequestException("Hesla se neshodují.", IResponse::S400_BAD_REQUEST);
        }

        if(($user = $this->findOneBy(['new_password_token' => $password_token])) == null) {
            throw new BadRequestException("Token na změnu hesla již expiroval.", IResponse::S400_BAD_REQUEST);
        }

        if(DateTime::from($user->new_password_token_expires_at) < new DateTime()) {
            throw new BadRequestException("Token na změnu hesla již expiroval.", IResponse::S400_BAD_REQUEST);
        }

        $password_hash = Passwords::hash($new_password);

        $user->update([
            'password' => $password_hash,
            'new_password_token' => null,
            'new_password_token_expires_at' => null
        ]);
    }

    public function getUserWithNewPasswordToken($id) {
        if(($user = $this->entity($id)) === null) {
            throw new BadRequestException("Uživatel neexistuje ${id}.", IResponse::S400_BAD_REQUEST);
        }

        $token = Random::generate(64);
        $user->update([
            'new_password_token' => $token,
            'new_password_token_expires_at' => (new DateTime())->add(new DateInterval("PT2H")),
        ]);

        return $token;
    }


    public function findTickets($id)
    {
        return array_map(function ($e) {
            return $e->toArray();
        }, $this->database->table('users')->get($id)->related('tickets.user_id')->fetchPairs('id'));
    }

    public function create($parameters)
    {
        $user = $this->database->table($this->table)->insert($parameters);
        foreach ($this->database->table('schemas')->fetchAll() as $schema) {
            $this->database->table('allowed_limit')->insert([
                'schema_id' => $schema->id,
                'user_id' => $user->id,
                'limit' => $schema->limit,
            ]);
        }
        return self::toArray($user);
    }

    public function findOneBy($conditions) {

        $users = $this->database->table($this->table);

        foreach($conditions as $param => $value) {
            $users = $users->where($param, $value);
        }

        $rows = $users->fetchAll();

        if(count($rows) > 1 || count($rows) === 0) {
            return null;
        }

        foreach($users as $user) {
            return $user;
        }
    }
}
