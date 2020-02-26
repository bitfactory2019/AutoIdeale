<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

final class HomepagePresenter extends _BasePresenter
{
    public function renderIndex()
    {
      $showcase = $this->dbWrapper->getShowcase(12);
      $this->template->showcase = \array_slice($showcase, 0, 6);
      $this->template->showcaseMore = \array_slice($showcase, 6, 6);
    }

    public function createComponentSearchForm(): UI\Form
    {
        return $this->formComponent->createComponentSearchForm();
    }
}
