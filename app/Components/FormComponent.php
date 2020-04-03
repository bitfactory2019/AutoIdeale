<?php

declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI;

class FormComponent extends UI\Component
{
    private $presenter;

    public function __construct(\Nette\Application\UI\Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function createComponentSearchForm(): UI\Form
    {
        $form = new UI\Form;

        $sessionSearch = $this->presenter->getSession('frontend')->offsetGet('search');

        $form->addText('place', 'Dove ti trovi?')
            ->setHtmlAttribute('placeholder', 'Dove ti trovi?')
            ->setHtmlAttribute('class', 'form-control')
            ->setDefaultValue($sessionSearch->place ?? null);

        $form->addSelect('car_make_id', 'Marca auto')
            ->setItems($this->presenter->getUtils()->getDbOptions('car_make'))
            ->setPrompt('-- Marca --')
            ->setHtmlAttribute('class', 'form-control wide')
            ->setDefaultValue($sessionSearch->car_make_id ?? null);

        $form->addSelect('car_model_id', 'Modello auto')
            ->setPrompt('-- Modello --')
            ->setItems($this->presenter->getUtils()->getDbOptions(
                 'car_model',
                 !empty($sessionSearch->car_make_id) ? ["car_make_id" => $sessionSearch->car_make_id] : []
            ))
            ->setHtmlAttribute('class', 'form-control wide')
            ->setDefaultValue(!empty($sessionSearch->car_model_id) ? $sessionSearch->car_model_id : null);

        $form->addSelect('year', 'Anno')
            ->setPrompt('Anno da')
            ->setItems(\range(1900, \date('Y')), false)
            ->setHtmlAttribute('class', 'form-control wide')
            ->setDefaultValue($sessionSearch->year ?? null);

        $price_range = [];
        $price_range = \array_merge($price_range, \range(500, 3000, 500));
        $price_range = \array_merge($price_range, \range(4000, 10000, 1000));
        $price_range = \array_merge($price_range, \range(12500, 20000, 2500));
        $price_range = \array_merge($price_range, \range(25000, 30000, 5000));
        $price_range = \array_merge($price_range, \range(40000, 50000, 10000));
        $price_range = \array_merge($price_range, \range(75000, 100000, 25000));

        $form->addSelect('price', 'Prezzo')
            ->setPrompt('Prezzo fino a (€)')
            ->setItems($price_range, false)
            ->setHtmlAttribute('class', 'form-control wide')
            ->setDefaultValue($sessionSearch->price ?? null);

        $form->addSubmit('search', 'Cerca');

        $form->onSuccess[] = [$this, 'submitSearchPost'];

        return $form;
    }

    public function createComponentAdvancedSearchForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addSelect('car_make_id', 'Marca auto')
             //->setRequired('Scegli la casa automobilistica')
             ->setItems($this->presenter->getUtils()->getDbOptions('car_make'))
             ->setPrompt('-- Casa Automobilistica --')
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('car_model_id', 'Modello auto')
             //->setRequired('Scegli il modello di auto')
             ->setPrompt('-- Scegli prima la Casa Automobilistica --')
             ->setItems([])
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('year_from', 'Anno')
             ->setPrompt('dal')
             ->setItems(\array_reverse(\range(1900, \date('Y'))), false)
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('year_to', '')
             ->setPrompt('al')
             ->setItems(\range(1900, 2000))
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('price_from', 'Prezzo')
            ->setPrompt('da')
            ->setItems($this->getPriceRanges(), false)
            ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('price_to')
            ->setPrompt('a')
            ->setItems($this->getPriceRanges(), false)
            ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('kilometers_from', 'Chilometraggio')
             ->setItems($this->getKilometersRanges(), false)
             ->setPrompt('da')
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('kilometers_to')
             ->setItems($this->getKilometersRanges(), false)
             ->setPrompt('a')
             ->setHtmlAttribute('class', 'form-control wide');

        $form->addRadioList(
            'doors_id',
            'N. di porte',
            ['' => 'Tutto']
        )
        ->getItemLabelPrototype()
        ->addClass("btn btn-secondary");

        $form->addSubmit('search', 'Cerca');

        $form->onSuccess[] = [$this, 'submitAdvancedSearchPost'];

        return $form;
    }

    public function getPriceRanges()
    {
        $price_range = [];
        $price_range = \array_merge($price_range, \range(500, 3000, 500));
        $price_range = \array_merge($price_range, \range(4000, 10000, 1000));
        $price_range = \array_merge($price_range, \range(12500, 20000, 2500));
        $price_range = \array_merge($price_range, \range(25000, 30000, 5000));
        $price_range = \array_merge($price_range, \range(40000, 50000, 10000));
        $price_range = \array_merge($price_range, \range(75000, 100000, 25000));

        return $price_range;
    }

    public function getKilometersRanges()
    {
        $kilometers_range = [];

        $kilometers_range = \array_merge($kilometers_range, \range(2500, 5000, 2500));
        $kilometers_range = \array_merge($kilometers_range, \range(10000, 100000, 10000));
        $kilometers_range = \array_merge($kilometers_range, \range(125000, 200000, 25000));

        return $kilometers_range;
    }

    public function submitSearchPost(UI\Form $form, \stdClass $values): void
    {
        // hack necessario per select dinamico
        $values->car_model_id = $_POST["car_model_id"];

        $this->presenter->getSession('frontend')->remove();
        $this->presenter->getSession('frontend')->offsetSet('search', $values);

        $this->presenter->redirect('Listing:searchResults');
    }

    public function submitAdvancedSearchPost(UI\Form $form, \stdClass $values): void
    {
        // hack necessario per select dinamico
        $values->car_model_id = $_POST["car_model_id"];
        //$values->year_to = $_POST["year_to"];
        //$values->price_to = $_POST["price_to"];

        $this->presenter->getSession('frontend')->remove();
        $this->presenter->getSession('frontend')->offsetSet('search', $values);

        $this->presenter->redirect('Listing:searchResults');
    }

    public function handleLoadCarModels($formName, $makeId)
    {
         if ($makeId) {
              $this->presenter[$formName]['car_model_id']
                   ->setPrompt("-- Scegli un modello --")
                   ->setItems($this->presenter->getUtils()->getDbOptions("car_model", ["car_make_id" => $makeId]));
         }
         else {
              $this->presenter[$formName]['car_model_id']
                   ->setPrompt('-- Scegli prima la Casa Automobilistica --')
                   ->setItems([]);
         }

         $this->presenter->redrawControl('wrapper');
         $this->presenter->redrawControl('car_model');
    }

    public function handleLoadTypeYears($formName, $typeId)
    {
         if ($typeId) {
              $this['advancedSearchForm']['year']
                   ->setPrompt("-- Anno --")
                   ->setItems($this->presenter->getUtils()->getTypeYearsOptions($typeId));
         }
         else {
              $this['advancedSearchForm']['year']
                   ->setPrompt('----')
                   ->setItems([]);
         }

         $this->presenter->redrawControl('wrapper');
         $this->presenter->redrawControl('type_years');
    }

    public function handleLoadTypeYearMonths($formName, $typeId, $year)
    {
         if ($typeId && $year) {
              $this['advancedSearchForm']['month']
                   ->setPrompt("-- Mese --")
                   ->setItems($this->presenter->getUtils()->getTypeYearMonthsOptions($typeId, $year));
         }
         else {
              $this['advancedSearchForm']['month']
                   ->setPrompt('----')
                   ->setItems([]);
         }

         $this->presenter->redrawControl('wrapper');
         $this->presenter->redrawControl('type_months');
    }
}
