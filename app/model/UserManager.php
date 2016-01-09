<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use App;


/**
 * Model, který se doplňuje třídu User (Nette\Secourity\User).
 * Metody:
 * 		add (registrace uživatelů)
 * 		getSex (zíkání pohlaví z databáze)
 */
class UserManager extends BaseManager
{
	/** @var $database Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database){
		$this->database = $database;
	}


	/**
	 * Registruje uživatele
	 *
	 * @param $values - Hodnoty získané z odeslaného formuláře SignUpForm
	 *
	 * @throws DuplicateNameException - Vyhodí chybu pokud je již předané uživatelské jméno v datbázi (uřivatelské jméno je v databázi unikátní)
	 */
	public function add($values)
	{
		$array= array();
		foreach($values as $value){
			$array[] = $value;
		}
		list($username, $password, $firstName, $lastName, $sex) = $array;

		try {
			$this->database->table(self::TABLE_USER)->insert(array(
				self::USER_COLUMN_NAME => $username,
				self::USER_COLUMN_PASSWORD => Passwords::hash($password),
				self::USER_COLUMN_FIRST_NAME => $firstName,
				self::USER_COLUMN_LAST_NAME => $lastName,
				self::USER_COLUMN_SEX => $sex,
				self::USER_COLUMN_ROLE => 1
			));
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException("messages.exceptions.duplicateUsername");
		}
	}
}




