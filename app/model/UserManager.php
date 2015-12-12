<?php
//TODO: dodělat komentáře
namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager extends BaseManager
{
	/** @var $database Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database){
		$this->database = $database;
	}

	/**
	 * @param $values
	 * @throws DuplicateNameException
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

	public function getSex($locale){
		$male = $this->database->table(self::TABLE_USER_SEX)->where(self::SEX_COMLUMN_ID, '1')->fetch();
		$female = $this->database->table(self::TABLE_USER_SEX)->where(self::SEX_COMLUMN_ID, '2')->fetch();
		if($locale === 'en') {
			return $sex = array(
				1 => $male[self::SEX_COLUMN_NAME_EN],
				2 => $female[self::SEX_COLUMN_NAME_EN]
			);
		}
		return $sex = array(
				1 => $male[self::SEX_COLUMN_NAME_CS],
				2 => $female[self::SEX_COLUMN_NAME_CS]
		);
	}

}



class DuplicateNameException extends \Exception
{}
