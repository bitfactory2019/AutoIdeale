<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use Nette\Application\Responses\JsonResponse;
use App\Utils;
use App\AdminModule\Components;

final class PostsPresenter extends _BasePresenter
{
    public function renderListing()
    {
      if ($this->isAjax()) {
        return;
      }

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

    public function renderAddCarDb()
    {
        $this->template->tempPath = \Nette\Utils\Random::generate(10);
    }

    public function handleSortPosts($order)
    {
       $this->template->posts = $this->dbWrapper->getPosts($this->template->user['id'], $order);

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
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getDbOptions('brands_models', ["brands_id" => $this->template->post["data"]->brands_id])
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->brands_models_id ?? null);

      $form->addSelect('brands_models_types_id', 'Configurazione')
           //->setRequired('Scegli il modello di auto')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getModelTypesOptions($this->template->post["data"]->brands_models_id)
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->brands_models_types_id ?? null);

      $form->addSelect('year', 'Anno')
           ->setRequired('Scegli l\'anno')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
           //? $this->utils->getTypeYearsOptions($this->template->post["data"]->brands_models_types_id)
           ? $this->utils->getSequentialKeyValues(1950, 2020)
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->year ?? null);

      $months = $this->utils->getSequentialKeyValues(1, 12);
      foreach ($months as $i => $num) {
       $months[$i] = \App\Library::MONTHS[$num];
      }

      $form->addSelect('month', 'Mese')
           ->setRequired('Scegli il mese')
           ->setPrompt('----')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              /*? $this->utils->getTypeYearMonthsOptions(
                  $this->template->post["data"]->brands_models_types_id,
                  $this->template->post["data"]->year
              )*/
              ? $months
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
           ->setDefaultValue($this->template->post["data"]->name ?? $this->template->user->name);

      $form->addText('surname', 'Cognome')
           ->setRequired('Inserisci il tuo cognome')
           ->setHtmlAttribute('placeholder', 'Inserisci Cognome')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->surname ?? $this->template->user->surname);

      $form->addText('city', 'Città')
           ->setRequired('Inserisci la tua città')
           ->setHtmlAttribute('placeholder', 'Inserisci Città')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->city ?? $this->template->user->city);

      $form->addText('address', 'Indirizzo')
           ->setRequired('Inserisci il tuo indirizzo')
           ->setHtmlAttribute('placeholder', 'Via e numero civico')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->address ?? $this->template->user->address);

      $form->addText('county', 'Provincia')
           ->setRequired('Inserisci la tua provincia')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->county ?? "");

      $form->addText('cap', 'Cap')
           ->setRequired('Inserisci il tuo CAP')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->cap ?? $this->template->user->cap);

      $form->addText('telephone', 'Telefono')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->telephone ?? $this->template->user->telephone);

      $form->addText('website', 'Sito web')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->website ?? "");

      $form->addText('email', 'E-mail')
           ->setRequired('Inserisci la tua e-mail')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->email ?? $this->template->user->email);

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

    protected function createComponentAddCarDbForm(): UI\Form
    {
      $form = new UI\Form;

      $form->addHidden('postId')
           ->setDefaultValue($this->template->post['data']->id ?? "");

     $form->addHidden('tempPath')
          ->setDefaultValue($this->template->tempPath ?? "");

      $form->addSelect('car_make_id', 'Marca auto')
           ->setRequired('Scegli la casa automobilistica')
           ->setItems($this->utils->getDbOptions('car_make'))
           ->setPrompt('-- Casa Automobilistica --')
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->car_make_id ?? null);

      $form->addSelect('car_model_id', 'Modello auto')
           //->setRequired('Scegli il modello di auto')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getDbOptions('car_model', ["car_make_id" => $this->template->post["data"]->car_make_id])
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->car_model_id ?? null);

      $form->addSelect('car_serie_id', 'Serie')
           //->setRequired('Scegli il modello di auto')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getDbOptions('car_serie', ["car_model_id" => $this->template->post["data"]->car_model_id])
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->car_serie_id ?? null);

      $form->addSelect('car_generation_id', 'Generazione')
           //->setRequired('Scegli il modello di auto')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getDbOptions('car_generation', ["car_model_id" => $this->template->post["data"]->car_model_id])
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->car_generation_id ?? null);

      $form->addSelect('car_trim_id', 'Motorizzazione')
           //->setRequired('Scegli il modello di auto')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getDbOptions('car_trim', [
                  "car_model_id" => $this->template->post["data"]->car_model_id,
                  "card_serie_id" => $this->template->post["data"]->car_serie_id
                ])
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->car_trim_id ?? null);

      $form->addSelect('car_equipment_id', 'Equipaggiamento')
           //->setRequired('Scegli il modello di auto')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              ? $this->utils->getDbOptions('car_equipment', ["car_trim_id" => $this->template->post["data"]->car_trim_id])
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->car_equipment_id ?? null);









      $form->addSelect('year', 'Anno')
           ->setRequired('Scegli l\'anno')
           ->setPrompt('---')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
               //? $this->utils->getTypeYearsOptions($this->template->post["data"]->brands_models_types_id)
               ? $this->utils->getSequentialKeyValues(1950, 2020)
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->year ?? null);

      $months = $this->utils->getSequentialKeyValues(1, 12);
      foreach ($months as $i => $num) {
       $months[$i] = \App\Library::MONTHS[$num];
      }

      $form->addSelect('month', 'Mese')
           ->setRequired('Scegli il mese')
           ->setPrompt('----')
           ->setDisabled(empty($this->template->post))
           ->setItems(!empty($this->template->post)
              /*? $this->utils->getTypeYearMonthsOptions(
                  $this->template->post["data"]->brands_models_types_id,
                  $this->template->post["data"]->year
              )*/
              ? $months
              : []
           )
           ->setHtmlAttribute('class', 'wide')
           ->setDefaultValue($this->template->post["data"]->month ?? null);

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
           ->setDefaultValue($this->template->post["data"]->name ?? $this->template->user->name);

      $form->addText('surname', 'Cognome')
           ->setRequired('Inserisci il tuo cognome')
           ->setHtmlAttribute('placeholder', 'Inserisci Cognome')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->surname ?? $this->template->user->surname);

      $form->addText('city', 'Città')
           ->setRequired('Inserisci la tua città')
           ->setHtmlAttribute('placeholder', 'Inserisci Città')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->city ?? $this->template->user->city);

      $form->addText('address', 'Indirizzo')
           ->setRequired('Inserisci il tuo indirizzo')
           ->setHtmlAttribute('placeholder', 'Via e numero civico')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->address ?? $this->template->user->address);

      $form->addText('county', 'Provincia')
           ->setRequired('Inserisci la tua provincia')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->county ?? "");

      $form->addText('cap', 'Cap')
           ->setRequired('Inserisci il tuo CAP')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->cap ?? $this->template->user->cap);

      $form->addText('telephone', 'Telefono')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->telephone ?? $this->template->user->telephone);

      $form->addText('website', 'Sito web')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->website ?? "");

      $form->addText('email', 'E-mail')
           ->setRequired('Inserisci la tua e-mail')
           ->setHtmlAttribute('class', 'form-control')
           ->setDefaultValue($this->template->post["data"]->email ?? $this->template->user->email);

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
          $form->onSubmit[] = [$this, 'submitAddCarDbPost'];
      }

      return $form;
    }

    public function handleAddImages()
    {
        $images = $this->getHttpRequest()->getFile('images');

        $postFiles = $this->filesWrapper->uploadPostFiles(
             $this->getHttpRequest()->getQuery('postId'),
             $images
        );

        $this->sendResponse(new JsonResponse($postFiles));
    }

    public function handleDeleteImage($imageId)
    {
        $this->filesWrapper->deletePostImage($imageId);

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

        if (empty($_POST["map_coords_lat"]) || empty($_POST["map_coords_long"])) {
          $config = $this->getConfig();
          $mapbox = new \App\Components\Mapbox\Mapbox($config["mapboxToken"]);

        	$res = $mapbox->geocode($values->address);
          $data = $res->getData();

          $values->mapCoordsLat = $data[0]["center"][1];
          $values->mapCoordsLong = $data[0]["center"][0];
        }
        else {
          $values->mapCoordsLat = $_POST["map_coords_lat"];
          $values->mapCoordsLong = $_POST["map_coords_long"];
        }

        $postId = $this->dbWrapper->addNewPost($this->template->user['id'], $values);

        if ($postId === false) {
             $this->flashMessage("Post non salvato, riprova.", "danger");
             return;
        }
        else {
             $postFiles = $this->filesWrapper->moveTempPostImages(
                  $values->tempPath,
                  $postId
             );

             if (!empty($postFiles)) {
                  $this->dbWrapper->addPostImages($postId, $postFiles);
             }

             $this->flashMessage("Post salvato con successo!", "success");
             $this->redirect('Posts:Listing');
             return;
        }
    }

    public function submitAddCarDbPost(UI\Form $form): void
    {
        $values = $form->getValues();

        // hack necessario per select dinamico
        $values->car_model_id = $_POST["car_model_id"];
        $values->car_serie_id = $_POST["car_serie_id"];
        $values->car_trim_id = $_POST["car_trim_id"] ?? null;
        $values->car_equipment_id = $_POST["car_equipment_id"] ?? null;
        $values->year = $_POST["year"];
        $values->month = $_POST["month"];

        if (empty($_POST["map_coords_lat"]) || empty($_POST["map_coords_long"])) {
          $config = $this->getConfig();
          $mapbox = new \App\Components\Mapbox\Mapbox($config["mapboxToken"]);

        	$res = $mapbox->geocode($values->address);
          $data = $res->getData();

          $values->mapCoordsLat = $data[0]["center"][1];
          $values->mapCoordsLong = $data[0]["center"][0];
        }
        else {
          $values->mapCoordsLat = $_POST["map_coords_lat"];
          $values->mapCoordsLong = $_POST["map_coords_long"];
        }

        $postId = $this->dbWrapper->addNewCarDbPost($this->template->user['id'], $values);

        if ($postId === false) {
             $this->flashMessage("Post non salvato, riprova.", "danger");
             return;
        }
        else {
             $postFiles = $this->filesWrapper->moveTempPostImages(
                  $values->tempPath,
                  $postId
             );

             if (!empty($postFiles)) {
                  $this->dbWrapper->addCarDbPostImages($postId, $postFiles);
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

      // aggiungo le coordinate della mappa
      $values->mapCoordsLat = $_POST['map_coords_lat'];
      $values->mapCoordsLong = $_POST['map_coords_long'];

      $result = $this->dbWrapper->editPost($postId, $values);

      if ($result === false) {
         $this->flashMessage("Post non salvato, riprova.", "danger");
      }
      else {
        $postImages = $this->filesWrapper->moveTempPostImages(
           $values->tempPath,
           $postId
        );

        if (!empty($postImages)) {
           $this->dbWrapper->addPostImages($postId, $postImages);
        }

        $this->flashMessage("Post salvato con successo!", "success");
        $this->redirect('Posts:Listing');
      }
    }

    public function handleLoadBrandModels($brandId)
    {
        if ($brandId) {
             $this['addForm']['brands_models_id']
                  ->setDisabled(false)
                  ->setPrompt("-- Scegli un modello --")
                  ->setItems($this->utils->getDbOptions("brands_models", ["brands_id" => $brandId]));
        }
        else {
             $this['addForm']['brands_models_id']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('brands_models');
    }

    public function handleLoadModelTypes($modelId)
    {
        if ($modelId) {
             $this['addForm']['brands_models_types_id']
                  ->setDisabled(false)
                  ->setPrompt("-- Scegli una configurazione --")
                  ->setItems($this->utils->getModelTypesOptions($modelId));
        }
        else {
             $this['addForm']['brands_models_types_id']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('brands_models_types');
    }

    public function handleLoadTypeYears($typeId)
    {
        if ($typeId) {
             $this['addForm']['year']
                  ->setDisabled(false)
                  ->setPrompt("-- Anno --")
                  //->setItems($this->utils->getTypeYearsOptions($typeId));
                  ->setItems($this->utils->getSequentialKeyValues(1950, 2020));
        }
        else {
             $this['addForm']['year']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('type_years');
    }

    public function handleLoadTypeYearMonths($typeId, $year)
    {
        if ($typeId && $year) {
            $months = $this->utils->getSequentialKeyValues(1, 12);

            foreach ($months as $i => $num) {
              $months[$i] = \App\Library::MONTHS[$num];
            }

            $this['addForm']['month']
                ->setDisabled(false)
                ->setPrompt("-- Mese --")
                //->setItems($this->utils->getTypeYearMonthsOptions($typeId, $year));
                ->setItems($months);
        }
        else {
             $this['addForm']['month']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('type_months');
    }

    public function handleLoadCarModels($carMakeId)
    {
        if ($carMakeId) {
             $this['addCarDbForm']['car_model_id']
                  ->setDisabled(false)
                  ->setPrompt("-- Scegli un modello --")
                  ->setItems($this->utils->getDbOptions("car_model", ["car_make_id" => $carMakeId]));
        }
        else {
             $this['addCarDbForm']['car_model_id']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('car_model');
    }

    public function handleLoadCarSeries($carModelId)
    {
        $series = $this->db->table('car_serie')
            ->where('car_model_id', $carModelId);

        $rows = [];

        foreach ($series as $serie) {
            $rows[$serie->id] = $serie->name." ".($serie->car_generation->name ?? "");
        }

        \asort($rows);

        if ($carModelId) {
             $this['addCarDbForm']['car_serie_id']
                  ->setDisabled(false)
                  ->setPrompt("-- Scegli una serie --")
                  ->setItems($rows);
        }
        else {
             $this['addCarDbForm']['car_serie_id']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('car_serie');
    }

    public function handleLoadCarTrims($carModelId, $carSerieId)
    {
        if ($carModelId && $carSerieId) {
             $this['addCarDbForm']['car_trim_id']
                  ->setDisabled(false)
                  ->setPrompt("-- Scegli una motorizzazione --")
                  ->setItems($this->utils->getDbOptions("car_trim", [
                    "car_model_id" => $carModelId,
                    "car_serie_id" => $carSerieId
                  ]));
        }
        else {
             $this['addCarDbForm']['car_trim_id']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('car_trim');
    }

    public function handleLoadCarEquipments($carTrimId)
    {
        if ($carTrimId) {
             $this['addCarDbForm']['car_equipment_id']
                  ->setDisabled(false)
                  ->setPrompt("-- Scegli un equipaggiamento --")
                  ->setItems($this->utils->getDbOptions("car_equipment", ["car_trim_id" => $carTrimId]));
        }
        else {
             $this['addCarDbForm']['car_equipment_id']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('car_equipment');
    }

    public function handleLoadSerieGenerationYears($serieId)
    {
        if ($serieId) {
          $generation = $this->db->table("car_serie")->get($serieId)->car_generation;
             $this['addCarDbForm']['year']
                  ->setDisabled(false)
                  ->setPrompt("-- Anno --")
                  //->setItems($this->utils->getTypeYearsOptions($typeId));
                  ->setItems(!empty($generation)
                    ? $this->utils->getSequentialKeyValues($generation->year_begin, $generation->year_end)
                    : $this->utils->getSequentialKeyValues(1950, \date("Y"))
                  );
        }
        else {
             $this['addCarDbForm']['year']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('type_years');
    }

    public function handleLoadSerieGenerationYearMonths($serieId, $year)
    {
        if ($serieId && $year) {
            $months = $this->utils->getSequentialKeyValues(1, 12);

            foreach ($months as $i => $num) {
              $months[$i] = \App\Library::MONTHS[$num];
            }

            $this['addCarDbForm']['month']
                ->setDisabled(false)
                ->setPrompt("-- Mese --")
                //->setItems($this->utils->getTypeYearMonthsOptions($typeId, $year));
                ->setItems($months);
        }
        else {
             $this['addForm']['month']
                  ->setDisabled()
                  ->setPrompt('----')
                  ->setItems([]);
        }

        $this->redrawControl('wrapper');
        $this->redrawControl('type_months');
    }

    public function createComponentPostsGrid($name)
    {
      $grid = new Components\PostsGrid($this, $name);

      $grid->setDataSource(
        $this->db->table("posts")
      );
    }

    /*
    public function handleDelete($postId)
    {
      $post = $this->db->table('posts')->get($postId);

      $this->db->table('posts')
        ->where('id', $postId)
        ->delete();

      $this->flashMessage('Cancellato annuncio "'.$post->title.'"', 'danger');

      $this->redrawControl('flashes');
      $this['postsGrid']->reload();
    }

    public function handleApprove($postId)
    {
      $post = $this->db->table('posts')->get($postId);

      $this->db->table('posts')
        ->where('id', $postId)
        ->update(['approved' => !$post->approved]);

      if ($post->approved) {
        $this->flashMessage('Disabilitato annuncio "'.$post->title.'"', 'danger');
      }
      else {
        $this->flashMessage('Approvato annuncio "'.$post->title.'"', 'success');
      }

      $this->redrawControl('flashes');
      $this['postsGrid']->reload();
    }
    */
}
