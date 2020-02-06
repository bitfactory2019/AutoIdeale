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
      'brands',
      'Marca: ',
      $presenter->getUtils()->getDbOptions('brands', [], true),
      'brands_id'
    );

    if (!empty($this->filter['brands'])) {
      $this->addFilterSelect(
        'brands_models',
        'Modello: ',
        $presenter->getUtils()->getDbOptions('brands_models', ['brands_id' => $grid->filter['brands']], true),
        'brands_models_id'
      );
    }

    $this->addFilterDate('creation_time', '')
      ->setFormat('d.m.Y', 'dd/mm/yyyy')
      ->setCondition(function($fluent, $value) {
        $fluent->select('*, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%d/%m/%Y')
          ->having('creation_date = ?', $value);
      });

    $this->addFilterText('ip_address', 'IP inserimento: ');

    $this->addColumnText('title', 'Titolo');
    $this->addColumnText('brands', 'Marca', 'brands.name')
      ->setSortable();
    $this->addColumnText('brands_models', 'Modello', 'brands_models.name')
      ->setSortable();
    $this->addColumnText('year_month', 'Immatricolazione')
      ->setSortable('year')
      ->setRenderer(function($post) {
        return \App\Library::MONTHS[$post->month].' '.$post->year;
      });
    $this->addColumnDateTime('creation_time', 'Data creazione')
      ->setSortable()
      ->setFormat('d/m/Y');
    $this->addColumnText('ip_address', 'IP inserimento');

    $this->setTranslator($presenter->_getUblabooDatagridTranslator());
  }
}
