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

        if (!$this->isAjax()) {
            $this->template->view = $this->getSession('frontend')->offsetGet('search')->view ?? 'grid';
        }
    }

    public function handleChangeView($view)
    {
        $search = $this->getSession('frontend')->offsetGet('search');
        $search->view = $view;
        $this->getSession('frontend')->offsetSet('search', $search);

        $this->template->view = $view;

        $this->redrawControl('filtersTop');
        $this->redrawControl('filters-top');
        $this->redrawControl('results');
    }

    public function renderDetail($postId)
    {

    }
}
