<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class ListingPresenter extends _BasePresenter
{
<<<<<<< HEAD
    public function renderSearch()
    {
        $this->template->searchResults = $this->dbWrapper->searchPosts();
=======
    public function renderDetail($postId)
    {
        
>>>>>>> ad14b9879377916823ce0e078ccf8ddf00153f16
    }
}
