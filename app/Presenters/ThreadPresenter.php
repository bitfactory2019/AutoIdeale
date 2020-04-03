<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class ThreadPresenter extends _BasePresenter
{
  public function renderIndex($hash)
  {
    $this->template->thread = $this->db->table("car_posts_threads")
      ->where("hash", $hash);
  }
}
