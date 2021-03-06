<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

final class ListingPresenter extends _BasePresenter
{
  public function renderIndex()
  {
    $this->renderSearchResults(1, 5);
    $this->template->showcase = $this->dbWrapper->getShowcase(5);
  }

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

    public function handleLoadResultsPage($page = 1, $limit = 10)
    {
      $results = $this->dbWrapper->searchPosts($page, $limit);

      foreach ($results['posts'] as $i => $post) {
          $results['page'][] = $this->utils->formatPostResult($post);
          $this->statsWrapper->addImpressionSearch($post->id);
      }

      $this->template->searchResults = $results;

      $this->redrawControl('resultsWrapper');
      $this->redrawControl('results');
    }

    public function renderSearchResults($page = 1, $limit = 10)
    {
        $this->template->view = $this->section_frontend->search->view ?? 'grid';

        if ($this->isAjax()) {
            return;
        }

        $this->section_frontend->filters = new \StdClass();

        $request = $this->getHttpRequest();
        $search = new \stdClass();

        if (!empty($request->getQuery('car_make_id'))) {
            $search->car_make_id = $request->getQuery('car_make_id');
        }
        if (!empty($request->getQuery('car_model_id'))) {
            $search->car_model_id = $request->getQuery('car_model_id');
        }

        if (!empty($search->car_make_id) || !empty($search->car_model_id)) {
            $this->section_frontend->search = $search;
        }

        $results = $this->dbWrapper->searchPosts($page, $limit);

        foreach ($results['posts'] as $i => $post) {
            $results['page'][] = $this->utils->formatPostResult($post);
            $this->statsWrapper->addImpressionSearch($post->id);
        }

        $this->template->searchResults = $results;
        $this->template->search = $this->section_frontend->search;
        $this->template->filters = $this->section_frontend->filters;
    }

    public function handleChangeView($view)
    {
        $search = $this->section_frontend->search;
        $search->view = $view;
        $this->section_frontend->search = $search;

        $this->template->view = $view;

        $this->redrawControl('filtersTop');
        $this->redrawControl('filters-top');
        $this->redrawControl('results');
    }

    public function handleFilterCarMakeResults($car_make_id, $checked)
    {
        $this->_filterResults('car_make_id', $car_make_id, $checked);
    }

    public function handleFilterFuelTypesResults($fuel_types_id, $checked)
    {
        $this->_filterResults('fuel_types_id', $fuel_types_id, $checked);
    }

    public function handleFilterPriceResults($price)
    {
        $this->_filterResults('price', $price);
    }

    private function _filterResults($arg, $value, $checked = null)
    {
        $filters = $this->section_frontend->filters;

        if (empty($filters)) {
            $filters = new \stdClass();
        }

        if (empty($filters->$arg)) {
            $filters->$arg = [];
        }

        if ($checked === null) {
            $filters->$arg = $value;
        }
        elseif ($checked == true) {
            $filters->$arg[] = $value;
        }
        else {
            if (($key = array_search($value, $filters->$arg)) !== false) {
                unset($filters->$arg[$key]);
            }
        }

        $this->section_frontend->filters = $filters;

        $results = $this->dbWrapper->searchPosts();

        foreach ($results['posts'] as $i => $post) {
            $results['page'][] = $this->utils->formatPostResult($post);
        }

        $this->template->searchResults = $results;

        $this->redrawControl('resultsWrapper');
        $this->redrawControl('resultsMapWrapper');
        $this->redrawControl('resultsTotal');
        $this->redrawControl('results');
        $this->redrawControl('resultsMap');
    }

    public function renderDetail($postId)
    {
        $this->template->post = $this->dbWrapper->getPost($postId);
        $this->statsWrapper->addImpressionDetail($postId);

        $facebookShare = parent::_getFacebookShare();
        $facebookShare->title = $this->template->post['data']->title;
        $facebookShare->description = $this->template->post['data']->description;

        if (!empty($this->template->post['thumbnail'])) {
          $facebookShare->image = $this->template->post['thumbnail']->url;
        }

        $this->template->facebookShare = $facebookShare;
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
            //->setRequired('Campo obbligatorio')
            ->addRule(UI\Form::EMAIL, 'Inserisci un indirizzo email valido')
            ->setHtmlAttribute('placeholder', 'La tua email...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('telephone', 'Numero di cellulare')
            ->setRequired('Campo obbligatorio')
            //->addRule(UI\Form::PATTERN, 'Inserisci un numero di telefono valido', '([0-9]\s*{8,}')
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

        $form->addInvisibleReCaptcha('recaptcha')
          ->setMessage('Are you a bot?');

        $form->addSubmit('book', 'Prenota');

        $form->onSubmit[] = [$this, 'submitSendRequest'];

        return $form;
    }

    public function submitSendRequest(UI\Form $form): void
    {
        $values = $form->getValues();

        $result = $this->dbWrapper->sendPostRequest($values);

        if ($result === false) {
            $this->flashMessage("Richiesta non inviata, riprova.", "danger");
        }
        else {
            $this->statsWrapper->addRequest($values->postId);

            $this->flashMessage("La tua richiesta è stata inviata correttamente!", "success");
            $this->redirect('Listing:detail', $values->postId);
        }
    }

    public function createComponentContactForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addHidden('postId')
             ->setDefaultValue($this->template->post['data']->id ?? '');

        $form->addText('name', 'Nome e cognome')
            ->setRequired('Inserisci nome e cognome')
            ->setHtmlAttribute('placeholder', 'Il tuo nome...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('email', 'Indirizzo e-mail')
            //->setRequired('Campo obbligatorio')
            ->addRule(UI\Form::EMAIL, 'Inserisci un indirizzo email valido')
            ->setHtmlAttribute('placeholder', 'La tua email...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('telephone', 'Numero di cellulare')
            ->setRequired('Campo obbligatorio')
            //->addRule(UI\Form::PATTERN, 'Inserisci un numero di telefono valido', '([0-9]\s*{8,}')
            ->setHtmlAttribute('placeholder', 'Numero di telefono...')
            ->setHtmlAttribute('class', 'form-control');

        $form->addTextArea('message', 'Scrivi un messaggio')
          ->setRequired('Campo obbligatorio')
          ->setHtmlAttribute('placeholder', 'Scrivi un messaggio...')
          ->setHtmlAttribute('class', 'form-control');

        $form->addInvisibleReCaptcha('recaptcha')
          ->setMessage('Are you a bot?');

        $form->addSubmit('contact', 'Invia');

        $form->onSubmit[] = [$this, 'submitSendMessage'];

        return $form;
    }

    public function submitSendMessage(UI\Form $form): void
    {
        $values = $form->getValues();

        $threadId = $this->dbWrapper->sendPostMessage($values);

        if ($threadId === false) {
            $this->flashMessage("Messaggio non inviato, riprova.", "danger");
        }
        else {
            $this->emailWrapper->sendNewMessageUser($threadId);
            $this->statsWrapper->addMessage($values->postId);

            $this->flashMessage("Il tuo messaggio è stato inviato correttamente!", "success");
            $this->redirect('Listing:detail', $values->postId);
        }
    }
}
