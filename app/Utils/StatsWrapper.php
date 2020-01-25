<?php

namespace App\Utils;

use Nette\Mail;
use Nette\Http\Url;

class StatsWrapper
{
  private $presenter;
  private $db;

  public function __construct(\Nette\Application\UI\Presenter $presenter)
  {
    $this->presenter = $presenter;
    $this->db = $this->presenter->getDbService();
  }

  private function _addPostStat($postId, $event)
  {
    $this->db->table("posts_stats")
      ->insert([
        "posts_id" => $postId,
        "event" => $event,
        "datetime" => \time(),
        "ip_address" => $this->presenter->getHttpRequest()->getRemoteAddress()
      ]);
  }

  public function addImpressionDetail($postId)
  {
    $this->_addPostStat($postId, "impression_detail");
  }

  public function addImpressionSearch($postId)
  {
    $this->_addPostStat($postId, "impression_search");
  }

  public function addWishlist($postId)
  {
    $this->_addPostStat($postId, "wishlist");
  }
}
