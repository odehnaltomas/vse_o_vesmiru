<?php
//TODO: dodělat komentaře
namespace App\Presenters;

use App\Model\UserManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignPresenter extends BasePresenter
{

	/** @var UserManager */
	private $userManager;

	/** @var User */
	private $user;

	/**
	 * @var array $sex - Pole pro radio button
	 */
	private $sex = array();


	/**
	 * Konstruktor presenteru SignPresenter
	 *
	 * @param User $user Instance třídy User, kde jsou informace o přihlášeném uživateli.
	 *
	 * @param UserManager $userManager Instance třídy UserManager, která obsahuje metody týkající se uživatele.
	 */
	public function __construct(User $user, UserManager $userManager){
		$this->user = $user;
		$this->userManager = $userManager;
	}


	/**
	 * Komponenta pro vytvoření formuláře na přihlášení uživatelů.
	 * 
	 * @return Nette\Application\UI\Form
	 */
	public function createComponentSignInForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->addText('username', 'forms.sign.username')
			->setRequired('forms.sign.requiredUsername');

		$form->addPassword('password', 'forms.sign.password')
			->setRequired('forms.sign.requiredPassword');

		$form->addCheckbox('remember', 'forms.sign.remember');

		$form->addSubmit('send', 'forms.sign.signIn');

		$form->onSuccess[] = array($this, 'signInFormSucceeded');
		return $form;
	}


	/**
	 * @param Form $form
	 * @param $values
	 */
	public function signInFormSucceeded(Form $form, $values)
	{
		if ($values->remember) {
			$this->user->setExpiration('14 days', FALSE);
		} else {
			$this->user->setExpiration('0', TRUE);
		}

		try {
			$this->user->login($values->username, $values->password);
			$form->getPresenter()->redirect('Homepage:');
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($this->translator->translate($e->getMessage()));
		}
	}


	public function actionOut()
	{
		$this->user->logout(TRUE);
		$this->flashMessage($this->translator->translate('messages.flash.signedOut'));
		$this->redirect('in');
	}


	/**
	 *
	 * @return Form
	 */
	protected function createComponentSignUpForm(){
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->addText('username', 'forms.sign.username')
			->setRequired('forms.sign.requiredUsername');

		$form->addPassword('password', 'forms.sign.password')
			->setRequired('forms.sign.requiredPassword');

		$form->addText('first_name', 'forms.sign.first_name');

		$form->addText('last_name', 'forms.sign.last_name');

		$form->addRadioList('sex', 'forms.sign.sex', $this->userManager->getSex($this->locale))
			->getSeparatorPrototype()->setName(NULL);

		$form->addSubmit('send', 'forms.sign.signUp');

		$form->onSuccess[] = array($this, 'signUpFormSucceeded');
		return $form;
	}


	/**
	 * Volá se při úspěšném odeslání formuláře SignUpForm.
	 * Přes instanci třídy UserManager se pokouší registrovat uživatele.
	 *
	 * @param $form -
	 * @param $values - Hodnoty z úspěšné odeslaného formuláře
	 */
	public function signUpFormSucceeded(Form $form, $values){

		try {
			$this->userManager->add($values);
			$form->getPresenter()->redirect('Homepage:');
		} catch(\App\DuplicateNameException $e){
			$form->addError($this->translator->translate($e->getMessage()));
		}
	}

}
