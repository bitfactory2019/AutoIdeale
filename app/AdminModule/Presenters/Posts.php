<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use App\Utils;

final class PostsPresenter extends _BasePresenter
{
    protected function createComponentAddForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addText('title', 'Titolo')
             ->setHtmlAttribute('placeholder', 'Inserisci titolo')
             ->setHtmlAttribute('class', 'form-control');
        
        $form->addSelect('year', 'Anno')
             ->setItems(\range(2000, \date('Y')), false)
             ->setHtmlAttribute('class', 'wide');

        $form->addText('brand', 'Marca auto')
             ->setHtmlAttribute('placeholder', 'Casa Automobilistica')
             ->setHtmlAttribute('class', 'form-control');

        $form->addSelect('fuel_type', 'Carburante')
            ->setItems($this->utils->getDbOptions('fuel_types'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('kilometers', 'Km')
            ->setItems($this->utils->getKilometersOptions())
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('model', 'Modello')
            ->setItems($this->utils->getDbOptions('models'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('vehicle_type', 'Tipo di veicolo')
            ->setItems($this->utils->getDbOptions('vehicle_types'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('color', 'Colore')
            ->setItems($this->utils->getDbOptions('colors'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('shift_type', 'Cambio')
            ->setItems($this->utils->getDbOptions('shift_types'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('euro_class', 'Classe Emissioni')
            ->setItems($this->utils->getDbOptions('euro_class'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('doors', 'Porte')
            ->setItems($this->utils->getDbOptions('doors'))
            ->setHtmlAttribute('class', 'wide');

        $form->addSelect('seats', 'Posti')
            ->setItems($this->utils->getSeatsOptions())
            ->setHtmlAttribute('class', 'wide');

        $form->addMultiUpload('files', 'Files');

        $form->addSubmit('save', 'Salva');
        $form->onSuccess[] = [$this, 'addPostSuccess'];

        return $form;
    }

    public function addPostSuccess(UI\Form $form, \stdClass $values): void
    {
        $this->flashMessage('You have successfully signed up.');
        //$this->redirect('Homepage:');
    }
}
