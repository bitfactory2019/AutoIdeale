<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\UI;
use \Nette\Application\Responses\JsonResponse;
use App\Utils;
use Ublaboo\DataGrid\DataGrid;

final class UsersPresenter extends _BasePresenter
{
    public function renderIndex(): void
    {
        $this->_load_users();
    }

    private function _load_users()
    {
        //if (empty($this->section->usersSearch)) {
            $this->section->usersSearch = new \StdClass();
            $this->section->usersSearch->orderField = 'creation_time';
            $this->section->usersSearch->orderDirection = 'DESC';
        //}

        $users = $this->db->table('users');
        $users->order($this->section->usersSearch->orderField.' '.$this->section->usersSearch->orderDirection ?? 'ASC');

        $this->template->users = $users->fetchAll();
        $this->template->usersSearch = $this->section->usersSearch;
    }

    public function handleOrderUsers($field, $direction)
    {
        $this->section->usersSearch->orderField = $field;
        $this->section->usersSearch->orderDirection = $direction;

        $this->_load_users();

        $this->redrawControl('usersSearch');
        $this->redrawControl('users');
    }
}
