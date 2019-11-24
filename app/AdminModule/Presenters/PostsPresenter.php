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

     public function renderEdit(int $id)
     {
          $this->template->post = $this->dbWrapper->getPost($id);
     }

     protected function createComponentAddForm(): UI\Form
     {
          $form = new UI\Form;

          $form->addText('title', 'Titolo')
               ->setRequired('Inserisci il titolo dell\'annuncio')
               ->setHtmlAttribute('placeholder', 'Inserisci titolo')
               ->setHtmlAttribute('class', 'form-control')
               ->setDefaultValue($this->template->post["data"]->title ?? "");
          
          $form->addSelect('year', 'Anno')
               ->setRequired('Scegli l\'anno')
               ->setItems(\range(2000, \date('Y')), false)
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->year ?? null);

          $form->addSelect('brands_id', 'Marca auto')
               ->setRequired('Scegli la casa automobilistica')
               ->setItems($this->utils->getDbOptions('brands'))
               ->setPrompt('-- Casa Automobilistica --')
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->brands_id ?? null);

          $form->addSelect('brands_models_id', 'Modello auto')
               //->setRequired('Scegli il modello di auto')
               ->setPrompt('-- Scegli prima la Casa Automobilistica --')
<<<<<<< HEAD
               ->setItems($this->utils->getDbOptions(
                    'brands_models',
                    !empty($this->template->post) ? ["brands_id" => $this->template->post["data"]->brands_id] : []
               ))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->brands_models_id ?? null);

          $form->addSelect('fuel_types_id', 'Carburante')
=======
               ->setItems([1 => "OK"])
               ->setHtmlAttribute('class', 'wide');

          $form->addSelect('fuel_type_id', 'Carburante')
>>>>>>> ad14b9879377916823ce0e078ccf8ddf00153f16
               ->setRequired('Scegli il tipo di carburante')
               ->setItems($this->utils->getDbOptions('fuel_types'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->fuel_types_id ?? null);

          $form->addSelect('kilometers_id', 'Km')
               ->setRequired('Indica il chilometraggio attuale')
               ->setItems($this->utils->getKilometersOptions())
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->kilometers_id ?? null);

          $form->addSelect('models_id', 'Modello')
               ->setRequired('Indica il modello di auto')
               ->setItems($this->utils->getDbOptions('models'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->models_id ?? null);

          $form->addSelect('vehicle_types_id', 'Tipo di veicolo')
               ->setRequired('Scegli il tipo di veicolo')
               ->setItems($this->utils->getDbOptions('vehicle_types'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->vehicle_types_id ?? null);

          $form->addSelect('colors_id', 'Colore')
               ->setRequired('Indica il colore dell\'auto')
               ->setItems($this->utils->getDbOptions('colors'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->colors_id ?? null);

          $form->addSelect('shift_types_id', 'Cambio')
               ->setRequired('Scegli il tipo di cambio')
               ->setItems($this->utils->getDbOptions('shift_types'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->shift_types_id ?? null);

          $form->addSelect('euro_class_id', 'Classe Emissioni')
               ->setRequired('Scegli la classe di emissioni dei gas di scarico')
               ->setItems($this->utils->getDbOptions('euro_class'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->euro_class_id ?? null);

          $form->addSelect('doors_id', 'Porte')
               ->setRequired('Scegli il numero di porte della tua auto')
               ->setItems($this->utils->getDbOptions('doors'))
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->doors_id ?? null);

          $form->addSelect('seats_id', 'Posti')
               ->setRequired('Scegli il numero di posti')
               ->setItems($this->utils->getSeatsOptions())
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->seats_id ?? null);

          $form->addTextArea('description', 'Descrizione')
               ->setHtmlAttribute('class', 'editor')
               ->setDefaultValue($this->template->post["data"]->description ?? "");

          $form->addMultiUpload('images', 'Foto');

          $form->addText('price')
               ->setRequired('Inserisci il prezzo di vendita')
               ->setHtmlAttribute('placeholder', 'Prezzo')
               ->setHtmlAttribute('class', 'form-control')
               ->setDefaultValue($this->template->post["data"]->price ?? "");

          $form->addSubmit('save', 'Salva'); 
          
          if (!empty($this->template->post)) {
               $form->onSuccess[] = [$this, 'submitEditPost'];
          }
          else {
               $form->onSuccess[] = [$this, 'submitAddPost'];
          }

          return $form;
     }

     public function submitAddPost(UI\Form $form, \stdClass $values): void
     {
<<<<<<< HEAD
          // hack necessario per select dinamico
=======
          // hack per select caricato dinamicamente
>>>>>>> ad14b9879377916823ce0e078ccf8ddf00153f16
          $values->brands_models_id = $_POST["brands_models_id"];

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

     public function submitEditPost(UI\Form $form, \stdClass $values): void
     {
          // hack necessario per select dinamico
          $values->brands_models_id = $_POST["brands_models_id"];

          $result = $this->dbWrapper->editPost($this->template->post["data"]->id, $values);

          if ($result === false) {
               $this->flashMessage("Post non salvato, riprova.", "danger");
          }
          else {
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
