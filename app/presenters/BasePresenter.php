<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;


/**
 * Základní presenter pro všechny presentery.
 * @package App\Presenters
 */
abstract class BasePresenter extends Presenter
{
    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;
}
