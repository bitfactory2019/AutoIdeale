<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class BrandsPresenter extends _BasePresenter
{
  public function renderIndex()
  {
    $this->template->allBrands = $this->dbWrapper->getAllCarMake();
  }
}
