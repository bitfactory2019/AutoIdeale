<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

final class HomepagePresenter extends _BasePresenter
{
    public function renderIndex()
    {
        $this->template->showcase = $this->dbWrapper->getShowcase(6);
    }

    public function createComponentSearchForm(): UI\Form
    {
        return $this->formComponent->createComponentSearchForm();
    }
}
