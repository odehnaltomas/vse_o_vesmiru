<?php
namespace App\Model;

use Nette;
use Nette\Security\IAuthenticator;
use Nette\Security\Passwords;

/**
 * Třída Authenticator slouží pro autentizaci přihlašovaného uživatele. Je volána třídou User (Nette\Secourity\User).
 * @package App\Model
 */
class Authenticator extends BaseManager implements IAuthenticator
{
    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database){
        $this->database = $database;
    }

    /**
     * Metoda, která se volá při autentizaci uživatele (př. třída User při přihlášení).
     * Pokud proběhne autentizace bez problému, vezmou se data o daném uživateli z databáze
     * a vytvoří se instance třídy Identity, do které se vloží data o uživateli -> přihlášení uživatele.
     *
     * @param array $credentials - Pole, které obsahuje hodnoty (uživ. jméno a heslo) získané z odeslaného formuláře SignInForm
     * @return Nette\Security\Identity - Vrací instanci třídy Identity, do které se vloží data o přihlašovaném uživateli (v případé úspěšného přihlášení)
     * @throws Nette\Security\AuthenticationException - Vyhodí výjimku, pokud předané uživ. jméno v databázi neexistuje nebo pokud je předané heslo nesprávné
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        $row = $this->database->table(self::TABLE_USER)->where(self::USER_COLUMN_NAME, $username)->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException("messages.exceptions.wrongLogin", self::IDENTITY_NOT_FOUND);

        } elseif (!Passwords::verify($password, $row[self::USER_COLUMN_PASSWORD])) {
            throw new Nette\Security\AuthenticationException("messages.exceptions.wrongLogin", self::INVALID_CREDENTIAL);

        } elseif (Passwords::needsRehash($row[self::USER_COLUMN_PASSWORD])) {
            $row->update(array(
                self::USER_COLUMN_PASSWORD => Passwords::hash($password),
            ));
        }

        $data = array(
            'id' => $row->id,
            'username' => $row->username,
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'cs_role' => $row->role[self::ROLE_COLUMN_NAME_CS],
            'en_role' => $row->role[self::ROLE_COLUMN_NAME_EN],
            'cs_sex' => $row->sex[self::SEX_COLUMN_NAME_CS],
            'en_sex' => $row->sex[self::SEX_COLUMN_NAME_EN]
        );

        return new Nette\Security\Identity($row[self::USER_COLUMN_ID], $row->role[self::ROLE_COLUMN_ROLE], $data);
    }
}