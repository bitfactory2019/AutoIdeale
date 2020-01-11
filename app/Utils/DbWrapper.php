<?php

namespace App\Utils;

use Nette\Diagnostic\Debugger;
use Nette\Utils\DateTime;

class DbWrapper
{
    private $presenter;
    private $db;
    private $utils;

    public function __construct(\Nette\Application\UI\Presenter $presenter)
    {
        $this->presenter = $presenter;
        $this->db = $this->presenter->getDbService();
        $this->utils = new \App\Utils($this->presenter);
    }

    public function getUserByEmail($email)
    {
        return $this->db->table('users')
            ->where('email', $email)
            ->fetch();
    }

    public function addUser($values)
    {
        try {
            $isPrivate = $values->client_type === 'private';
            $isCompany = $values->client_type === 'company';

            $group = $this->db->table('groups')
              ->where('name', $values->client_type)
              ->fetch();

            $user = $this->db->table('users')->insert([
                'groups_id' => $group->id,
                'name' => $isPrivate ? $values->name : null,
                'surname' => $isPrivate ? $values->surname : null,
                'company_name' => $isCompany ? $values->company_name : null,
                'unique_code' => $isCompany ? $values->unique_code : null,
                'iban' => $isCompany ? $values->iban : null,
                'address' => $values->address,
                'city' => $values->city,
                'cap' => $values->cap,
                'country' => $values->country,
                'telephone' => $values->telephone,
                'mobile' => $values->mobile,
                'email' => $values->email,
                'email_pec' => $isCompany ? $values->email_pec : null,
                'password' => \md5($values->password),
                'enabled' => false,
                'creation_time' => \time()
            ]);

            return $user->getPrimary();
        }
        catch (\PDOException $e) {
            //Debugger::dump($e);
            $this->presenter->flashMessage($e->getMessage(), "danger");
            return false;
        }
    }

    public function getUserLogin($values)
    {
        return $this->db->table('users')
            ->where('email', $values->email)
            ->where('password', \md5($values->password))
            ->where('enabled', true)
            ->fetch();
    }

    public function saveUserLogin($userId)
    {
        $this->db->table('users_logins')->insert([
            'user_id' => $userId,
            'ip_address' => $this->presenter->getHttpRequest()->getRemoteAddress(),
            'login_time' => \time()
        ]);
    }

    public function addNewPost($userId, $values)
    {
        try {
            $brand = $this->db->table('brands')->get($values->brands_id);
            $brand_model = $this->db->table('brands_models')->get($values->brands_models_id);

            $post = $this->db->table('posts')->insert([
                'users_id' => $userId,
                'brands_id' => $values->brands_id,
                'brands_models_id' => $values->brands_models_id,
                'brands_models_types_id' => $values->brands_models_types_id,
                'year' => $values->year,
                'month' => $values->month,
                'fuel_types_id' => $values->fuel_types_id,
                'kilometers_id' => $values->kilometers_id,
                'models_id' => $values->models_id,
                'vehicle_types_id' => $values->vehicle_types_id,
                'colors_id' => $values->colors_id,
                'shift_types_id' => $values->shift_types_id,
                'euro_class_id' => $values->euro_class_id,
                'doors_id' => $values->doors_id,
                'seats_id' => $values->seats_id,
                'title' => $brand->name.' '.$brand_model->name,
                'description' => $values->description,
                'name' => $values->name,
                'surname' => $values->surname,
                'city' => $values->city,
                'address' => $values->address,
                'county' => $values->county,
                'cap' => $values->cap,
                'telephone' => $values->telephone,
                'website' => $values->website,
                'email' => $values->email,
                'facebook' => $values->facebook,
                'twitter' => $values->twitter,
                'instagram' => $values->instagram,
                'price' => $values->price,
                'approved' => true,
                'creation_time' => \time()
            ]);

            return $post->getPrimary();
        }
        catch (\PDOException $e) {
            Debugger::dump($e);
            return false;
        }
    }

    public function addPostFiles($postId, $files)
    {
        $insertFiles = [];

        foreach ($files as $file) {
            $img = \file_get_contents($file['path']);

            $insertFiles[] = [
                'post_id' => $postId,
                'name' => $file['name'],
                'url' => $file['url'],
                'path' => $file['path'],
                'base64' => 'data:image/png;base64,'.\base64_encode($img),
                'size' => \filesize($file['path'])
            ];
        }

        $this->db->table('posts_images')->insert($insertFiles);
    }

    public function editPost($postId, $values)
    {
        try {
            $post = $this->db->table('posts')
                ->where('id', $postId)
                ->update([
                    'brands_id' => $values->brands_id,
                    'brands_models_id' => $values->brands_models_id,
                    'brands_models_types_id' => $values->brands_models_types_id,
                    'year' => $values->year,
                    'month' => $values->month,
                    'fuel_types_id' => $values->fuel_types_id,
                    'kilometers_id' => $values->kilometers_id,
                    'models_id' => $values->models_id,
                    'vehicle_types_id' => $values->vehicle_types_id,
                    'colors_id' => $values->colors_id,
                    'shift_types_id' => $values->shift_types_id,
                    'euro_class_id' => $values->euro_class_id,
                    'doors_id' => $values->doors_id,
                    'seats_id' => $values->seats_id,
                    'title' => $values->title,
                    'description' => $values->description,
                    'price' => $values->price
                ]);

            return true;
        }
        catch (\PDOException $e) {
            return false;
        }
    }

    public function deletePost($postId)
    {
        try {
            $post = $this->db->table('posts')
                ->where('id', $postId)
                ->delete();

            return true;
        }
        catch (\PDOException $e) {
            return false;
        }
    }

    public function getPosts($userId, $sort = 'latest')
    {
        $posts = [];

        $dbo = $this->db->table('posts')
            ->where('users_id', $userId);

        switch ($sort) {
            case 'random': $dbo->order('RAND()'); break;
            case 'oldest': $dbo->order('creation_time ASC'); break;
            case 'latest': $dbo->order('creation_time DESC'); break;
            default: $dbo->order('creation_time DESC'); break;
        }

        $rows = $dbo->fetchPairs('id');

        foreach ($rows as $row) {
            $posts[] = $this->utils->formatPostResult($row);
        }

        return $posts;
    }

    public function getPost($id)
    {
        $row = $this->db->table('posts')
            ->where('id', $id)
            ->fetch();

        return $this->utils->formatPostResult($row);
    }

    public function searchPosts($page = 1, $limit = 10)
    {
        $search = $this->presenter->getSession('frontend')->offsetGet('search');

        $posts = [];

        $search_dbo = $this->db->table('posts')
            ->order('creation_time DESC');

        if (!empty($search->brands_id)) {
            $search_dbo->where('brands_id', $search->brands_id);
        }

        if (!empty($search->brands_models_id)) {
            $search_dbo->where('brands_models_id', $search->brands_models_id);
        }

        if (!empty($search->brands_models_types_id)) {
            $search_dbo->where('brands_models_types_id', $search->brands_models_types_id);
        }

        if (!empty($search->models_id)) {
            $search_dbo->where('models_id', $search->models_id);
        }

        if (!empty($search->year)) {
            $search_dbo->where('year', $search->year);
        }

        if (!empty($search->year_from)) {
            $search_dbo->where('year >= ?', $search->year_from);
        }

        if (!empty($search->year_to)) {
            $search_dbo->where('year <= ?', $search->year_to);
        }

        if (!empty($search->price)) {
            $search_dbo->where('price <= ?', $search->price);
        }

        if (!empty($search->price_from)) {
            $search_dbo->where('price >= ?', $search->price_from);
        }

        if (!empty($search->price_to)) {
            $search_dbo->where('price <= ?', $search->price_to);
        }

        if (!empty($search->fuel_types_id)) {
            $search_dbo->where('fuel_types_id', $search->fuel_types_id);
        }

        if (!empty($search->kilometers_from)) {
            $search_dbo->where('kilometers.from >= ?', $search->kilometers_from);
        }

        if (!empty($search->kilometers_to)) {
            $search_dbo->where('kilometers.to <= ?', $search->kilometers_to);
        }

        if (!empty($search->seats_from)) {
            $search_dbo->where('seats.from >= ?', $search->seats_from);
        }

        if (!empty($search->seats_to)) {
            $search_dbo->where('seats.to <= ?', $search->seats_to);
        }

        if (!empty($search->shift_types_id)) {
            $search_dbo->where('shift_types_id', $search->shift_types_id);
        }

        if (!empty($search->power_from)) {
            $search_dbo->where('brands_models_types.' . $search->power_type . ' >= ?', $search->power_from);
        }

        if (!empty($search->power_to)) {
            $search_dbo->where('brands_models_types.' . $search->power_type . ' <= ?', $search->power_to);
        }

        $search_dbo_page = clone $search_dbo;

        $search_dbo_brands = clone $search_dbo;
        $search_dbo_brands->select('COUNT(id) AS tot, brands_id')
                          ->group('brands_id');

        $search_dbo_fuel_types = clone $search_dbo;
        $search_dbo_fuel_types->select('COUNT(id) AS tot, fuel_types_id')
                              ->group('fuel_types_id');

        $filters = $this->presenter->getSession('frontend')->offsetGet('filters');

        if (!empty($filters->brands_id)) {
            $search_dbo_page->where('brands_id', $filters->brands_id);
        }

        if (!empty($filters->fuel_types_id)) {
            $search_dbo_page->where('fuel_types_id', $filters->fuel_types_id);
        }

        if (!empty($filters->price)) {
            $search_dbo_page->where('price <= ?', $filters->price);
        }

        $tot = \count($search_dbo_page);
        $page_tot = \ceil($tot / $limit);
        $page_count = 5;
        $page_from = \max(1, $page - $page_count);
        $page_to = \min($page + $page_count, $page_tot);

        return [
            'tot' => $tot,
            'posts' => $search_dbo_page->page($page, $limit),
            'brands' => $search_dbo_brands,
            'fuel_types' => $search_dbo_fuel_types,
            'page' => [],
            'pagination' => [
              'current' => $page,
              'prev' => \max(1, $page - 1),
              'next' => \min($page + 1, $page_tot),
              'tot' => $page_tot,
              'limit' => $limit,
              'from' => $page_from,
              'to' => $page_to,
              'count' => $page_count
            ]
        ];
    }

    public function sendPostRequest($values)
    {
        $dateTime = DateTime::createFromFormat("l d/m/Y h:i", $values->date." ".$values->time);

        try {
            $request = $this->db->table('posts_requests')->insert([
                'posts_id' => $values->postId,
                'status' => 'pending',
                'name' => $values->name,
                'email' => $values->email,
                'telephone', $values->telephone,
                'date_time' => $dateTime->getTimeStamp(),
                'creation_time' => \time()
            ]);

            return $request->getPrimary();
        }
        catch (\PDOException $e) {
            Debugger::dump($e);
            return false;
        }
    }

    public function getRandomBrands($brandsNo = false, $modelsNo = false)
    {
        $brands = [];

        $brands_dbo = $this->db->table('brands')
            ->order('RAND()');

        if ($brandsNo !== false) {
            $brands_dbo->limit($brandsNo);
        }

        $rows = $brands_dbo->fetchPairs('id');

        foreach ($rows as $row) {
            $models = $row->related('brands_models')
                ->order('RAND()');

            if ($modelsNo !== false) {
                $models->limit($modelsNo);
            }

            $brands[] = [
                "data" => $row,
                "models" => $models->fetchPairs('id')
            ];
        }

        return $brands;
    }

    public function getTopBrands($brandsNo = false, $modelsNo = false)
    {
        $brands = [];

        $brands_dbo = $this->db->table('brands')
            ->where('top', true)
            ->order('name');

        if ($brandsNo !== false) {
            $brands_dbo->limit($brandsNo);
        }

        $rows = $brands_dbo->fetchPairs('id');

        foreach ($rows as $row) {
            $models = $row->related('brands_models')
                ->where('top', true)
                ->order('name');

            if ($modelsNo !== false) {
                $models->limit($modelsNo);
            }

            $brands[] = [
                "data" => $row,
                "models" => $models->fetchPairs('id')
            ];
        }

        return $brands;
    }

    public function getShowcase($postsNo = false)
    {
        $posts = [];

        $rows_dbo = $this->db->table('posts')
            ->order('creation_time DESC');

        if ($postsNo !== false) {
            $rows_dbo->limit($postsNo);
        }

        $rows = $rows_dbo->fetchPairs('id');

        foreach ($rows as $row) {
            $posts[] = $this->utils->formatPostResult($row);
        }

        return $posts;
    }

    public function getMessages($userId, $only_new = false)
    {
        return [];
    }

    public function getRequests($userId, $status = 'all')
    {
        $messages = [];

        $rows = $this->db->table('posts_requests')
            ->where('posts.users_id', $userId)
            ->order('creation_time DESC');

        if ($status !== 'all') {
            $rows->where('status', $status);
        }

        $rows = $rows->fetchPairs('id');

        foreach ($rows as $row) {
            $messages[] = [
                "data" => $row,
                "post" => $this->utils->formatPostResult($row->posts)
            ];
        }

        return $messages;
    }

    public function getRequest($requestId)
    {
        $row = $this->db->table('posts_requests')
            ->get($requestId);

        return [
            "data" => $row,
            "post" => $this->utils->formatPostResult($row->posts)
        ];
    }
}
