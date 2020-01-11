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
          $this->template->posts = $this->dbWrapper->getPosts($this->template->user['id']);
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

     public function handleSortPosts($order)
     {
         $this->template->requests = $this->dbWrapper->getPosts($this->template->user['id'], $order);

         $this->presenter->redrawControl('posts');
     }

     public function handleDeletePost($postId)
     {
         $result = $this->dbWrapper->deletePost($postId);

         if ($result === true) {
             $this->flashMessage("Post cancellato correttamente", "success");
         }
         else {
             $this->flashMessage("Non è stato possibile cancellare il post, riprova.", "danger");
         }

         $this->presenter->redrawControl('flashes');
         $this->presenter->redrawControl('posts');
     }

     protected function createComponentAddForm(): UI\Form
     {
        $form = new UI\Form;

        $form->addHidden('postId')
             ->setDefaultValue($this->template->post['data']->id ?? "");

       $form->addHidden('tempPath')
            ->setDefaultValue($this->template->tempPath ?? "");

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

        $form->addSelect('brands_models_types_id', 'Configurazione')
             //->setRequired('Scegli il modello di auto')
             ->setPrompt('-- Scegli prima il modello --')
             ->setItems(!empty($this->template->post)
                ? $this->utils->getModelTypesOptions($this->template->post["data"]->brands_models_id)
                : []
             )
             ->setHtmlAttribute('class', 'wide')
             ->setDefaultValue($this->template->post["data"]->brands_models_types_id ?? null);

        $form->addSelect('year', 'Anno')
             ->setRequired('Scegli l\'anno')
             ->setPrompt('----')
             ->setItems(!empty($this->template->post)
                ? $this->utils->getTypeYearsOptions($this->template->post["data"]->brands_models_types_id)
                : []
             )
             ->setHtmlAttribute('class', 'wide')
             ->setDefaultValue($this->template->post["data"]->year ?? null);

        $form->addSelect('month', 'Mese')
             ->setRequired('Scegli il mese')
             ->setPrompt('--')
             ->setItems(!empty($this->template->post)
                ? $this->utils->getTypeYearMonthsOptions(
                    $this->template->post["data"]->brands_models_types_id,
                    $this->template->post["data"]->year
                )
                : []
             )
             ->setHtmlAttribute('class', 'wide')
             ->setDefaultValue($this->template->post["data"]->month ?? null);

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
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->name ?? "");

        $form->addText('surname', 'Cognome')
             ->setRequired('Inserisci il tuo cognome')
             ->setHtmlAttribute('placeholder', 'Inserisci Cognome')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->surname ?? "");

        $form->addText('city', 'Città')
             ->setRequired('Inserisci la tua città')
             ->setHtmlAttribute('placeholder', 'Inserisci Città')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->city ?? "");

        $form->addText('address', 'Indirizzo')
             ->setRequired('Inserisci il tuo indirizzo')
             ->setHtmlAttribute('placeholder', 'Via e numero civico')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->address ?? "");

        $form->addText('county', 'Provincia')
             ->setRequired('Inserisci la tua provincia')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->county ?? "");

        $form->addText('cap', 'Cap')
             ->setRequired('Inserisci il tuo CAP')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->cap ?? "");

        $form->addText('telephone', 'Telefono')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->telephone ?? "");

        $form->addText('website', 'Sito web')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->website ?? "");

        $form->addText('email', 'E-mail')
             ->setRequired('Inserisci la tua e-mail')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->email ?? "");

        $form->addText('facebook', 'Link di Facebook')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->facebook ?? "");

        $form->addText('twitter', 'Link di Twitter')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->twitter ?? "");

        $form->addText('instagram', 'Link di Instagram')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->instagram ?? "");

        $form->addText('price')
             ->setRequired('Inserisci il prezzo di vendita')
             ->setHtmlAttribute('placeholder', 'Prezzo')
             ->setHtmlAttribute('class', 'form-control')
             ->setDefaultValue($this->template->post["data"]->price ?? "");

        $form->addSubmit('save', 'Salva');

        if (!empty($_POST["postId"])) {
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

     public function handleAddImages()
     {
          $images = $this->getHttpRequest()->getFile('images');

          $postFiles = $this->filesWrapper->uploadFiles(
               $this->getHttpRequest()->getQuery('postId'),
               $images
          );

          $this->sendResponse(new JsonResponse($postFiles));
     }

     public function handleDeleteTempImage($imageName)
     {
          $this->filesWrapper->deleteTempImage($imageName);

          $this->sendResponse(new JsonResponse(['result' => true]));
     }

     public function handleDeleteImage($imageId)
     {
          $this->filesWrapper->deleteImage($imageId);

          $this->sendResponse(new JsonResponse(['result' => true]));
     }

     public function submitAddPost(UI\Form $form): void
     {
          $values = $form->getValues();

          // hack necessario per select dinamico
          $values->brands_models_id = $_POST["brands_models_id"];
          $values->brands_models_types_id = $_POST["brands_models_types_id"];
          $values->year = $_POST["year"];
          $values->month = $_POST["month"];

          $postId = $this->dbWrapper->addNewPost($this->template->user['id'], $values);

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
               $this->redirect('Posts:Listing');
               return;
          }
     }

     public function submitEditPost(UI\Form $form): void
     {
          $postId = $_POST["postId"];
          $values = $form->getValues();

          // hack necessario per select dinamico
          $values->brands_models_id = $_POST["brands_models_id"];
          $values->brands_models_types_id = $_POST["brands_models_types_id"];
          $values->year = $_POST["year"];
          $values->month = $_POST["month"];

          $result = $this->dbWrapper->editPost($postId, $values);

          if ($result === false) {
               $this->flashMessage("Post non salvato, riprova.", "danger");
          }
          else {
               /*$postFiles = $this->filesWrapper->moveTempFiles(
                    $values->tempPath,
                    $postId
               );

               if (!empty($postFiles)) {
                    $this->dbWrapper->addPostFiles($postId, $postFiles);
               }*/

               $this->flashMessage("Post salvato con successo!", "success");
               $this->redirect('Posts:Listing');
          }
     }

     public function handleLoadBrandModels($brandId)
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

     public function handleLoadModelTypes($modelId)
     {
          if ($modelId) {
               $this['addForm']['brands_models_types_id']
                    ->setPrompt("-- Scegli una configurazione --")
                    ->setItems($this->utils->getModelTypesOptions($modelId));
          }
          else {
               $this['addForm']['brands_models_types_id']
                    ->setPrompt('-- Scegli prima il modello --')
                    ->setItems([]);
          }

          $this->redrawControl('wrapper');
          $this->redrawControl('brands_models_types');
     }

     public function handleLoadTypeYears($typeId)
     {
          if ($typeId) {
               $this['addForm']['year']
                    ->setPrompt("-- Anno --")
                    ->setItems($this->utils->getTypeYearsOptions($typeId));
          }
          else {
               $this['addForm']['year']
                    ->setPrompt('----')
                    ->setItems([]);
          }

          $this->redrawControl('wrapper');
          $this->redrawControl('type_years');
     }

     public function handleLoadTypeYearMonths($typeId, $year)
     {
          if ($typeId && $year) {
               $this['addForm']['month']
                    ->setPrompt("-- Mese --")
                    ->setItems($this->utils->getTypeYearMonthsOptions($typeId, $year));
          }
          else {
               $this['addForm']['month']
                    ->setPrompt('----')
                    ->setItems([]);
          }

          $this->redrawControl('wrapper');
          $this->redrawControl('type_months');
     }
}
