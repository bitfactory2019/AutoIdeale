<?php

namespace App\AdminModule\Components;

use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class PostsGrid extends DataGrid
{
  public function __construct($presenter, $name)
  {
    parent::__construct($presenter, $name);

    $this->setDefaultSort(['creation_time' => 'DESC']);
    $this->setItemsDetail(__DIR__ . '/../templates/Posts/detailPreview.latte');

    $this->addAction('approve_callback', '', 'approvePost!', ['postId' => 'id'])
      ->setIcon(function($item) { return $item->approved ? 'close' : 'check'; })
      ->setClass(function($item) { return 'btn btn-xs ajax btn-'.($item->approved ? 'danger' : 'success'); })
      ->setConfirmation(
       new CallbackConfirmation(
         function($item) {
           return $item->approved
            ? 'Vuoi approvare questo annuncio?'
            : 'Vuoi disabilitare questo annuncio?';
         }
       )
      );

    $this->addAction('delete_callback', '', 'deletePost!', ['postId' => 'id'])
      ->setIcon('trash')
      ->setClass('btn btn-xs btn-danger ajax')
      ->setConfirmation(
        new StringConfirmation(
          'Vuoi davvero cancellare l\'annuncio "%s"? L\'azione sarà IRREVERSIBILE!',
          'title'
        )
      );

    $this->addFilterText('title', 'Titolo: ');
    $this->addFilterSelect(
      'car_make',
      'Marca: ',
      $presenter->getUtils()->getDbOptions('car_make', [], true),
      'car_make_id'
    );

    if (!empty($this->filter['car_make'])) {
      $this->addFilterSelect(
        'car_model',
        'Modello: ',
        $presenter->getUtils()->getDbOptions('car_model', ['car_make_id' => $this->filter['car_make']], true),
        'car_model_id'
      );
    }

    $this->addFilterText('user', 'Utente: ')
      ->setCondition(function($fluent, $value) {
        $fluent->whereOr([
          'users.name LIKE ?' => '%'.$value.'%',
          'users.surname LIKE ?' => '%'.$value.'%',
          'users.telephone LIKE ?' => '%'.$value.'%'
        ]);
      });

    $this->addFilterDate('creation_time', '')
      ->setFormat('d.m.Y', 'dd/mm/yyyy')
      ->setCondition(function($fluent, $value) {
        $fluent->select('*, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%d/%m/%Y')
          ->having('creation_date = ?', $value);
      });

    $this->addFilterText('ip_address', 'IP inserimento: ');

    $this->addColumnText('title', 'Titolo');
    $this->addColumnText('car_make', 'Marca', 'car_make.name')
      ->setSortable();
    $this->addColumnText('car_model', 'Modello', 'car_model.name')
      ->setSortable();

    $this->addColumnText('user', 'Utente')
      ->setRenderer(function($post) {
        return $post->users->name.' '.$post->users->surname.' '.$post->users->telephone;
      });
    $this->addColumnDateTime('creation_time', 'Data creazione')
      ->setSortable()
      ->setFormat('d/m/Y');
    $this->addColumnText('ip_address', 'IP inserimento');

    $this->setTranslator($presenter->_getUblabooDatagridTranslator());
  }
}
