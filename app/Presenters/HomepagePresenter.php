<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

final class HomepagePresenter extends _BasePresenter
{
    public function createComponentSearchForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addText('place', 'Dove ti trovi?')
            ->setHtmlAttribute('placeholder', 'Dove ti trovi?')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSelect('brands_id', 'Marca auto')
            ->setItems($this->utils->getDbOptions('brands'))
            ->setPrompt('-- Marca --')
            ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('brands_models_id', 'Modello auto')
            ->setPrompt('-- Modello --')
            ->setHtmlAttribute('class', 'form-control wide');

        $form->addSelect('year', 'Anno')
            ->setPrompt('Anno da')
            ->setItems(\range(2000, \date('Y')), false)
            ->setHtmlAttribute('class', 'form-control wide');

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
            ->setHtmlAttribute('class', 'form-control wide');

        $form->addSubmit('search', 'Cerca');

        $form->onSuccess[] = [$this, 'submitSearchPost'];

        return $form;
    }

    public function submitSearchPost(UI\Form $form, \stdClass $values): void
    {
        // hack necessario per select dinamico
        $values->brands_models_id = $_POST["brands_models_id"];
        
        $this->getSession('frontend')->remove();
        $this->getSession('frontend')->offsetSet('search', $values);

        $this->redirect('Listing:searchResults');
    }

    public function handleLoadBrands($brandId)
    {
        if ($brandId) {
            $this['searchForm']['brands_models_id']
                ->setPrompt('-- Modello --')
                ->setItems($this->utils->getDbOptions("brands_models", ["brands_id" => $brandId]));
        }
        else {
            $this['searchForm']['brands_models_id']
                ->setPrompt('-- Modello --')
                ->setItems([]);
        }

        $this->redrawControl('searchWrapper');
        $this->redrawControl('brands_models');
    }
}
