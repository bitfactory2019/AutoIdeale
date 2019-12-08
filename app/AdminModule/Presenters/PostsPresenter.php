<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use \Nette\Application\Responses\JsonResponse;
use App\Utils;

final class PostsPresenter extends _BasePresenter
{
     public function renderListing()
     {
          $this->template->posts = $this->dbWrapper->getPosts($this->getAdminUser()['id']);
     }

     public function renderEdit(int $id)
     {
          $this->template->tempPath = \Nette\Utils\Random::generate(10);
          $this->template->post = $this->dbWrapper->getPost($id);
     }

     public function renderAdd()
     {
          $this->template->tempPath = \Nette\Utils\Random::generate(10);
     }

     protected function createComponentAddForm(): UI\Form
     {
          $form = new UI\Form;

          $form->addHidden('tempPath')
               ->setDefaultValue($this->template->tempPath ?? "");

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
               ->setItems(!empty($this->template->post)
                  ? $this->utils->getDbOptions('brands_models', ["brands_id" => $this->template->post["data"]->brands_id])
                  : []
               )
               ->setHtmlAttribute('class', 'wide')
               ->setDefaultValue($this->template->post["data"]->brands_models_id ?? null);

          $form->addSelect('fuel_types_id', 'Carburante')
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

          $form->addText('name', 'Nome')
               ->setRequired('Inserisci il tuo nome')
               ->setHtmlAttribute('placeholder', 'Inserisci Nome')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('surname', 'Cognome')
               ->setRequired('Inserisci il tuo cognome')
               ->setHtmlAttribute('placeholder', 'Inserisci Cognome')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('city', 'Città')
               ->setRequired('Inserisci la tua città')
               ->setHtmlAttribute('placeholder', 'Inserisci Città')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('address', 'Indirizzo')
               ->setRequired('Inserisci il tuo indirizzo')
               ->setHtmlAttribute('placeholder', 'Via e numero civico')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('county', 'Provincia')
               ->setRequired('Inserisci la tua provincia')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('cap', 'Cap')
               ->setRequired('Inserisci il tuo CAP')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('telephone', 'Telefono')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('website', 'Sito web')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('email', 'E-mail')
               ->setRequired('Inserisci la tua e-mail')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('facebook', 'Link di Facebook')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('twitter', 'Link di Twitter')
               ->setHtmlAttribute('class', 'form-control');
          $form->addText('instagram', 'Link di Instagram')
               ->setHtmlAttribute('class', 'form-control');

          $form->addText('price')
               ->setRequired('Inserisci il prezzo di vendita')
               ->setHtmlAttribute('placeholder', 'Prezzo')
               ->setHtmlAttribute('class', 'form-control')
               ->setDefaultValue($this->template->post["data"]->price ?? "");

          $form->addSubmit('save', 'Salva');

          if (!empty($this->template->post)) {
              $form->onSubmit[] = [$this, 'submitEditPost'];
          }
          else {
              $form->onSubmit[] = [$this, 'submitAddPost'];
          }

          return $form;
     }

     public function handleAddTempImages()
     {
          $images = $this->getHttpRequest()->getFile('images');

          $postFiles = $this->filesWrapper->uploadTempFiles(
               $this->getHttpRequest()->getQuery('tempPath'),
               $images
          );

          $this->sendResponse(new JsonResponse($postFiles));
     }

     public function handleDeleteTempImage($imageName)
     {
          $this->filesWrapper->deleteTempImage($imageName);

          $this->sendResponse(new JsonResponse(['result' => true]));
     }

     public function submitAddPost(UI\Form $form): void
     {
          $values = $form->getValues();

          // hack necessario per select dinamico
          $values->brands_models_id = $_POST["brands_models_id"];

          $postId = $this->dbWrapper->addNewPost($this->getAdminUser()['id'], $values);

          if ($postId === false) {
               $this->flashMessage("Post non salvato, riprova.", "danger");
               return;
          }
          else {
               $postFiles = $this->filesWrapper->moveTempFiles(
                    $values->tempPath,
                    $postId
               );

               if (!empty($postFiles)) {
                    $this->dbWrapper->addPostFiles($postId, $postFiles);
               }

               $this->flashMessage("Post salvato con successo!", "success");
               $this->redirect('Dashboard:Index');
               return;
          }
     }

     public function submitEditPost(UI\Form $form): void
     {
          $values = $form->getValues();

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
