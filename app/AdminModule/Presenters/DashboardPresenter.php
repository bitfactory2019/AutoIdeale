<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use \Nette\Application\Responses\JsonResponse;
use App\AdminModule\Components;

use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

final class DashboardPresenter extends _BasePresenter
{
  public function startup()
  {
    parent::startup();

    $todayStats = $this->db->table('car_posts_stats')
      ->select('COUNT(*) AS tot, event, MAX(FROM_UNIXTIME(datetime, ?)) AS creation_date', '%Y-%m-%d')
      ->where('car_posts.users_id', $this->section->user_id)
      ->having('creation_date = ?', \date("Y-m-d"))
      ->group('event')
      ->fetchPairs('event');

    $this->template->todayStats = $todayStats;
  }

  public function renderIndex()
  {
    if ($this->authWrapper->isAdmin()) {
      $this->redirect(":Admin:Dashboard:indexAdministrator");
    }
  }

  public function renderIndexAdministrator()
  {
    if (!$this->authWrapper->isAdmin()) {
      $this->redirect(":Admin:Dashboard:index");
    }

    $this->template->administrator->usersToApproveNo = $this->_getUsersToApprove()->count("*");
    $this->template->administrator->postsToApproveNo = $this->_getPostsToApprove()->count("*");
  }

  private function _getUsersToApprove()
  {
    return $this->db->table("users")
      ->where("last_login", null)
      ->whereOr(["enabled" => false, "approved" => false]);
  }

  public function createComponentNewUsersGrid($name)
  {
      $grid = new DataGrid($this, $name);

      $grid->setDataSource($this->_getUsersToApprove());
      $grid->setDefaultSort(['creation_time' => 'DESC']);
      $grid->setItemsDetail(__DIR__ . '/../templates/Users/detailPreview.latte');

      $grid->addAction('enable_callback', '', 'enableUser!', ['userId' => 'id'])
           ->setIcon('check')
           ->setClass('btn btn-xs ajax btn-success')
           ->setConfirmation(
             new StringConfirmation('Vuoi approvare questo utente?')
           );

       $grid->addFilterSelect(
         'group',
         'Gruppo: ',
         $this->utils->getDbOptions('groups'),
         'groups_id'
       );

      $grid->addFilterText('email', 'Indirizzo email: ');
      $grid->addFilterDate('creation_time', '')
           ->setFormat('d.m.Y', 'dd/mm/yyyy')
           ->setCondition(function($fluent, $value) {
                $fluent->select('*, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%d/%m/%Y')
                       ->having('creation_date = ?', $value);
            });

      $grid->addFilterText('ip_address', 'IP di registrazione: ');

      $grid->addColumnText('name', 'Nome');
      $grid->addColumnCallback('name', function($column, $item) {
        $column->setRenderer(function() use ($item) {
          return $item->groups->name === 'company'
            ? $item->company_name
            : $item->name.' '.$item->surname;
        });
      });

      $grid->addColumnText('group', 'Gruppo');
      $grid->addColumnCallback('group', function($column, $item) {
        $column->setRenderer(function() use ($item) {
          return $item->groups->name === 'company' ? 'Azienda' : 'Privato';
        });
      });

      $grid->addColumnText('email', 'Email');
      $grid->addColumnText('telephone', 'Telefono');

      $grid->addColumnDateTime('creation_time', 'Data di registrazione')
          ->setSortable()
          ->setFormat('d/m/Y');
      $grid->addColumnText('ip_address', 'Indirizzo IP');

      $grid->setTranslator($this->_getUblabooDatagridTranslator());
  }

  public function createComponentPostsGrid($name)
  {
    $grid = new Components\PostsGrid($this, $name);
    $grid->setDataSource($this->_getPostsToApprove());
  }

  public function handleEnableUser($userId)
  {
    $this->db->table('users')
      ->where('id', $userId)
      ->update([
          'enabled' => true,
          'approved' => true
      ]);

    $this->flashMessage('Utente abilitato con successo', 'success');

    $this->template->administrator->usersToApproveNo = $this->_getUsersToApprove()->count("*");

    $this->redrawControl('flashes');
    $this->redrawControl('usersToApproveNo');
    $this['newUsersGrid']->reload();
  }

  public function handleLoadAdministratorChartData($days = 30)
  {
    $newUsers = $this->db->table('users')
      ->select('COUNT(*) AS tot, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%Y-%m-%d')
      ->where('creation_time > ?', \strtotime("-".$days." days"))
      ->order('creation_time')
      ->group('creation_date')
      ->fetchAssoc('creation_date');

    $newPosts = $this->db->table('car_posts')
      ->select('COUNT(*) AS tot, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%Y-%m-%d')
      ->where('creation_time > ?', \strtotime("-".$days." days"))
      ->order('creation_time')
      ->group('creation_date')
      ->fetchAssoc('creation_date');

    $impressionSearch = $this->_loadPostsStats('impression_search', $days, false);
    $impressionDetail = $this->_loadPostsStats('impression_detail', $days, false);
    $wishlist = $this->_loadPostsStats('wishlist', $days, false);
    $requests = $this->_loadPostsStats('request', $days, false);

    $this->sendJson([
      'labels' => $this->_get_labels($days),
      'stats' => [
        'Nuovi utenti' => $this->_parse_stats($days, $newUsers),
        'Nuovi annunci' => $this->_parse_stats($days, $newPosts),
        'Risultati di ricerca' => $this->_parse_stats($days, $impressionSearch),
        'Visualizzazioni' => $this->_parse_stats($days, $impressionDetail),
        'Richieste ricevute' => $this->_parse_stats($days, $requests),
        'Annunci salvati' => $this->_parse_stats($days, $wishlist)
      ]
    ]);
  }

  private function _get_labels($days)
  {
    $labels = [];

    for ($i = $days; $i >= 0; $i--) {
      $date = \strtotime("-".$i." days");
      $labels[] = \date("D d", $date);
    }

    return $labels;
  }

  private function _parse_stats($days, $stats)
  {
    $data = [];

    for ($i = $days; $i >= 0; $i--) {
      $date = \date("Y-m-d", \strtotime("-".$i." days"));

      $data[$date] = $stats[$date]['tot'] ?? 0;
    }

    return $data;
  }

  public function handleLoadPostsChartData($days = 30)
  {
    $impressionSearch = $this->_loadPostsStats('impression_search', $days);
    $impressionDetail = $this->_loadPostsStats('impression_detail', $days);
    $wishlist = $this->_loadPostsStats('wishlist', $days);
    $requests = $this->_loadPostsStats('request', $days);

    $this->sendJson([
      'labels' => $this->_get_labels($days),
      'stats' => [
        'Risultati di ricerca' => $this->_parse_stats($days, $impressionSearch),
        'Visualizzazioni' => $this->_parse_stats($days, $impressionDetail),
        'Richieste ricevute' => $this->_parse_stats($days, $requests),
        'Annunci salvati' => $this->_parse_stats($days, $wishlist)
      ]
    ]);
  }

  private function _loadPostsStats($event, $days, $checkUser = true)
  {
    $postsDbo = $this->db->table('car_posts_stats')
      ->select('COUNT(*) AS tot, datetime, FROM_UNIXTIME(datetime, ?) AS datetime_formatted', '%Y-%m-%d')
      ->where('event', $event)
      ->where('datetime >= ?', \strtotime("-".$days." days"))
      ->order('datetime')
      ->group('datetime_formatted');

    if ($checkUser) {
      $postsDbo->where('car_posts.users_id', $this->section->user_id);
    }

    return $postsDbo->fetchAssoc('datetime_formatted');
  }
}
