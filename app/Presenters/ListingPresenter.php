<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class ListingPresenter extends _BasePresenter
{
    public function renderSearchResults($page = 1, $limit = 10)
    {
        $results = [
            'posts' => [],
            'page' => [],
            'pageNo' => 1,
            'pageTot' => 1,
            'limit' => $limit
        ];

        $results['posts'] = $this->dbWrapper->searchPosts();
        $results['pageTot'] = \count($results['posts']) / $limit;

        $rowFrom = ($page - 1) * $limit;
        $rowTo = $page * $limit;
        for ($i = $rowFrom; $i < $rowTo; $i++) {
            if (!empty($results['posts'][$i])) {
                $results['page'][] = $results['posts'][$i];
            }
        }

        $this->template->searchResults = $results;
    }

    public function renderDetail($postId)
    {

    }
}
