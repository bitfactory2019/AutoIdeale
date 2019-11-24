<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class ListingPresenter extends _BasePresenter
{
    public function renderSearch()
    {
        $this->template->searchResults = $this->dbWrapper->searchPosts();
    }
}
