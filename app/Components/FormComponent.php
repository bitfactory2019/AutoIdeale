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

        $form->addSelect('brands_id', 'Marca auto')
            ->setItems($this->presenter->getUtils()->getDbOptions('brands'))
            ->setPrompt('-- Marca --')
            ->setHtmlAttribute('class', 'form-control wide')
            ->setDefaultValue($sessionSearch->brands_id ?? null);

        $form->addSelect('brands_models_id', 'Modello auto')
            ->setPrompt('-- Modello --')
            ->setItems($this->presenter->getUtils()->getDbOptions(
                 'brands_models',
                 !empty($sessionSearch->brands_id) ? ["brands_id" => $sessionSearch->brands_id] : []
            ))
            ->setHtmlAttribute('class', 'form-control wide')
            ->setDefaultValue(!empty($sessionSearch->brands_models_id) ? $sessionSearch->brands_models_id : null);

        $form->addSelect('year', 'Anno')
            ->setPrompt('Anno da')
            ->setItems(\range(2000, \date('Y')), false)
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

    public function submitSearchPost(UI\Form $form, \stdClass $values): void
    {
        // hack necessario per select dinamico
        $values->brands_models_id = $_POST["brands_models_id"];
        
        $this->presenter->getSession('frontend')->remove();
        $this->presenter->getSession('frontend')->offsetSet('search', $values);

        $this->presenter->redirect('Listing:searchResults');
    }

    public function handleLoadBrands($brandId)
    {
        if ($brandId) {
            $this['searchForm']['brands_models_id']
                ->setPrompt('-- Modello --')
                ->setItems($this->presenter->getUtils()->getDbOptions("brands_models", ["brands_id" => $brandId]));
        }
        else {
            $this['searchForm']['brands_models_id']
                ->setPrompt('-- Modello --')
                ->setItems([]);
        }

        $this->presenter->redrawControl('searchWrapper');
        $this->presenter->redrawControl('brands_models');
    }
}