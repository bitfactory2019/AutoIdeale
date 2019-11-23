<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class HomepagePresenter extends _BasePresenter
{
    public function renderIndex()
    {
        $this->template->brands = $this->dbWrapper->getRandomBrands(6, 4);
    }
}
