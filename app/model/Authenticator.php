<?php
/**
 * Created by PhpStorm.
 * User: Tom�
 * Date: 7. 11. 2015
 * Time: 21:49
 */
//TODO: dodělat komentáře
namespace App\Model;

use Nette;
use Nette\Security\IAuthenticator;
use Nette\Security\Passwords;


class Authenticator extends BaseManager implements IAuthenticator
{
    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database){
        $this->database = $database;
    }

    /**
     * @param array $credentials
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        $row = $this->database->table(self::TABLE_USER)->where(self::USER_COLUMN_NAME, $username)->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('Nesprávné uživatelské jméno!', self::IDENTITY_NOT_FOUND);

        } elseif (!Passwords::verify($password, $row[self::USER_COLUMN_PASSWORD])) {
            throw new Nette\Security\AuthenticationException('Nesprávné heslo!', self::INVALID_CREDENTIAL);

        } elseif (Passwords::needsRehash($row[self::USER_COLUMN_PASSWORD])) {
            $row->update(array(
                self::USER_COLUMN_PASSWORD => Passwords::hash($password),
            ));
        }

        $arr = $row->toArray();
        unset($arr[self::USER_COLUMN_PASSWORD]);
        return new Nette\Security\Identity($row[self::USER_COLUMN_ID], $row->role[self::ROLE_COLUMN_NAME], $arr);
    }
}