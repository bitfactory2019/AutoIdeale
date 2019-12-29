<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

final class ListingPresenter extends _BasePresenter
{
    public function createComponentSearchForm(): UI\Form
    {
        return $this->formComponent->createComponentSearchForm();
    }

    public function createComponentAdvancedSearchForm(): UI\Form
    {
        return $this->formComponent->createComponentAdvancedSearchForm();
    }

    public function handleChangeYear($year)
    {
        $this['advancedSearchForm']['year_to']
             ->setItems(\array_reverse(\range($year, \date('Y')), false));

        $this->presenter->redrawControl('wrapper');
        $this->presenter->redrawControl('year_to');
    }

    public function handleChangePrice($price)
    {
        $priceRanges = \array_filter($this->formComponent->getPriceRanges(), function($priceVal) use ($price) {
            return $priceVal > $price;
        });

        $this['advancedSearchForm']['price_to']
             ->setItems($priceRanges, false);

        $this->redrawControl('wrapper');
        $this->redrawControl('price_to');
    }

    public function handleChangeKilometers($kilometers)
    {
        $kilometersRanges = \array_filter($this->formComponent->getKilometersRanges(), function($kilometersVal) use ($kilometers) {
            return $kilometersVal > $kilometers;
        });

        $this['advancedSearchForm']['kilometers_to']
             ->setItems($kilometersRanges, false);

        $this->redrawControl('wrapper');
        $this->redrawControl('kilometers_to');
    }

    public function handleChangeSeats($seats)
    {
        $seatsRange = \array_filter(\range(1, 12), function($seatsVal) use ($seats) {
            return $seatsVal > $seats;
        });

        $this['advancedSearchForm']['seats_to']
             ->setItems($seatsRange, false);

        $this->redrawControl('wrapper');
        $this->redrawControl('seats_to');
    }

    public function renderSearchResults($page = 1, $limit = 10)
    {
        $results = $this->dbWrapper->searchPosts($page, $limit);

        foreach ($results['posts'] as $i => $post) {
            $results['page'][] = $this->utils->formatPostResult($post);
        }

        $this->template->searchResults = $results;
        $this->template->search = $this->getSession('frontend')->offsetGet('search');

        $this->template->view = $this->getSession('frontend')->offsetGet('search')->view ?? 'grid';
    }

    public function handleLoadMoreResults($page)
    {

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
        $this->template->post = $this->dbWrapper->getPost($postId);
    }

    public function renderRequestConfirmed($postId)
    {
        $this->template->post = $this->dbWrapper->getPost($postId);
    }

    public function createComponentRequestForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addHidden('postId')
             ->setDefaultValue($this->template->post['data']->id ?? '');

        $form->addText('name', 'Nome e cognome')
            ->setRequired('Inserisci nome e cognome')
            ->setHtmlAttribute('placeholder', 'Il tuo nome...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('email', 'Indirizzo e-mail')
            ->setRequired('Campo obbligatorio')
            ->addRule(UI\Form::EMAIL, 'Inserisci un indirizzo email valido')
            ->setHtmlAttribute('placeholder', 'La tua email...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('telephone', 'Numero di cellulare')
            ->setRequired('Campo obbligatorio')
            ->addRule(UI\Form::PATTERN, 'Inserisci un numero di telefono valido', '([0-9]\s*{8,}')
            ->setHtmlAttribute('placeholder', 'Numero di telefono...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('date', 'Data')
            ->setRequired('Scegli una data')
            ->setHtmlAttribute('placeholder', 'Scegli un giorno...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSelect('time', 'Ora')
             ->setRequired("Scegli un'ora")
             ->setItems($this->utils->getHours(), false)
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addSubmit('book', 'Prenota');

        $form->onSubmit[] = [$this, 'submitSendRequest'];

        return $form;
    }

    public function submitSendRequest(UI\Form $form): void
    {
        $values = $form->getValues();

        $result = $this->dbWrapper->sendPostRequest($form->getValues());

        if ($result === false) {
            $this->flashMessage("Richiesta non inviata, riprova.", "danger");
            return;
        }
        else {
            $this->flashMessage("La tua richiesta è stata inviata correttamente!", "success");
            $this->redirect('Listing:detail', $values->postId);
            return;
        }
    }
}
