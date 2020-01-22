<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;
use \Nette\Application\Responses\JsonResponse;

use Nette;

final class DashboardPresenter extends _BasePresenter
{
  public function handleLoadNewUsersChartData($days = 30)
  {
    $newUsers = $this->db->table('users')
      ->select('COUNT(*) AS tot, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%Y-%m-%d')
      ->where('creation_time > ?', \strtotime("-".$days." days"))
      ->order('creation_time')
      ->group('creation_date')
      ->fetchAssoc('creation_date');

    $newPosts = $this->db->table('posts')
      ->select('COUNT(*) AS tot, FROM_UNIXTIME(creation_time, ?) AS creation_date', '%Y-%m-%d')
      ->where('creation_time > ?', \strtotime("-".$days." days"))
      ->order('creation_time')
      ->group('creation_date')
      ->fetchAssoc('creation_date');

    $this->sendJson([
      'labels' => $this->_get_labels($days),
      'stats' => [
        'Nuovi utenti' => $this->_parse_stats($days, $newUsers),
        'Nuovi annunci' => $this->_parse_stats($days, $newPosts)
      ]
    ]);
  }

  private function _get_labels($days)
  {
    $labels = [];

    for ($i = $days; $i >= 1; $i--) {
      $date = \strtotime("-".$i." days");
      $labels[] = \date("D d", $date);
    }

    return $labels;
  }

  private function _parse_stats($days, $stats)
  {
    $data = [];

    for ($i = $days; $i >= 1; $i--) {
      $date = \date("Y-m-d", \strtotime("-".$i." days"));

      $data[$date] = $stats[$date]['tot'] ?? 0;
    }

    return $data;
  }
}
