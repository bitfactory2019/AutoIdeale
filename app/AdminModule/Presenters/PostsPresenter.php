<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use App\Utils;

final class PostsPresenter extends _BasePresenter
{
     public function renderListing()
     {
          $this->template->posts = $this->dbWrapper->getPosts($this->getAdminUser()['id']);
     }

     protected function createComponentAddForm(): UI\Form
     {
          $form = new UI\Form;

          $form->addText('title', 'Titolo')
               ->setRequired('Inserisci il titolo dell\'annuncio')
               ->setHtmlAttribute('placeholder', 'Inserisci titolo')
               ->setHtmlAttribute('class', 'form-control');
          
          $form->addSelect('year', 'Anno')
               ->setRequired('Scegli l\'anno')
               ->setItems(\range(2000, \date('Y')), false)
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('brands_id', 'Marca auto')
               ->setRequired('Scegli la casa automobilistica')
               ->setItems($this->utils->getDbOptions('brands'))
               ->setPrompt('-- Casa Automobilistica --')
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('brands_models_id', 'Modello auto')
               //->setRequired('Scegli il modello di auto')
               ->setPrompt('-- Scegli prima la Casa Automobilistica --')
               ->setItems([1 => "OK"])
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('fuel_type_id', 'Carburante')
               ->setRequired('Scegli il tipo di carburante')
               ->setItems($this->utils->getDbOptions('fuel_types'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('kilometers_id', 'Km')
               ->setRequired('Indica il chilometraggio attuale')
               ->setItems($this->utils->getKilometersOptions())
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('model_id', 'Modello')
               ->setRequired('Indica il modello di auto')
               ->setItems($this->utils->getDbOptions('models'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('vehicle_type_id', 'Tipo di veicolo')
               ->setRequired('Scegli il tipo di veicolo')
               ->setItems($this->utils->getDbOptions('vehicle_types'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('color_id', 'Colore')
               ->setRequired('Indica il colore dell\'auto')
               ->setItems($this->utils->getDbOptions('colors'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('shift_type_id', 'Cambio')
               ->setRequired('Scegli il tipo di cambio')
               ->setItems($this->utils->getDbOptions('shift_types'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('euro_class_id', 'Classe Emissioni')
               ->setRequired('Scegli la classe di emissioni dei gas di scarico')
               ->setItems($this->utils->getDbOptions('euro_class'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('doors_id', 'Porte')
               ->setRequired('Scegli il numero di porte della tua auto')
               ->setItems($this->utils->getDbOptions('doors'))
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('seats_id', 'Posti')
               ->setRequired('Scegli il numero di posti')
               ->setItems($this->utils->getSeatsOptions())
               ->setHtmlAttribute('class', 'wide');

          $form->addTextArea('description', 'Descrizione')
               ->setHtmlAttribute('class', 'editor');

          $form->addMultiUpload('images', 'Foto');

          $form->addText('price')
               ->setRequired('Inserisci il prezzo di vendita')
               ->setHtmlAttribute('placeholder', 'Prezzo')
               ->setHtmlAttribute('class', 'form-control');

          $form->addSubmit('save', 'Salva'); 
          
          $form->onSuccess[] = [$this, 'addFormSubmitted'];
          
          return $form;
     }

     public function addFormSubmitted(UI\Form $form, \stdClass $values): void
     {
          $postId = $this->dbWrapper->addNewPost($this->getAdminUser()['id'], $values);

          if ($postId === false) {
               $this->flashMessage("Post non salvato, riprova.", "danger");
          }
          else {
               $postFiles = $this->filesWrapper->uploadPostFiles(
                    $postId,
                    $this->getHttpRequest()->getFile('images')
               );
     
               if (!empty($postFiles)) {
                    $this->dbWrapper->addPostFiles($postId, $postFiles);
               }

               $this->flashMessage("Post salvato con successo!", "success");
               $this->redirect('Dashboard:Index');
          }
     }

     public function handleLoadBrands($brandId)
     {
          if ($brandId) {
               $this['addForm']['brands_models_id']
                    ->setPrompt("-- Scegli un modello --")
                    ->setItems($this->utils->getDbOptions("brands_models", ["brands_id" => $brandId]));
          }
          else {
               $this['addForm']['brands_models_id']
                    ->setPrompt('-- Scegli prima la Casa Automobilistica --')
                    ->setItems([]);
          }

          $this->redrawControl('wrapper');
          $this->redrawControl('brands_models');
     }
}
