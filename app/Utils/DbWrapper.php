<?php

namespace App\Utils;

use Tracy\Debugger;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

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

    public function getUserById($id)
    {
        return $this->db->table('users')->get($id);
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
                'enabled' => true,
                'approved' => !$isCompany,
                'creation_time' => \time(),
                'ip_address' => $this->presenter->getHttpRequest()->getRemoteAddress()
            ]);

            return $user->getPrimary();
        }
        catch (\PDOException $e) {
            //Debugger::dump($e);
            $this->presenter->flashMessage($e->getMessage(), "danger");
            return false;
        }
    }

    public function editUser($values)
    {
        try {
            $data = [
                "name" => $values->name,
                "surname" => $values->surname,
                "address" => $values->address,
                "city" => $values->city,
                "cap" => $values->cap,
                "telephone" => $values->telephone,
                "info" => $values->info,
                "enabled" => $this->presenter->getAuthWrapper()->isAdmin()
                  ? $values->enabled
                  : $values->enabledHidden
            ];

            if (!empty($values->new_password) && ($values->new_password === $values->confirm_password)) {
                $data['password'] = \md5($values->new_password);
            }

            if (!empty($values->new_email) && ($values->new_email === $values->confirm_password)) {
                $data['email'] = \md5($values->new_email);
            }

            $this->db->table('users')
                ->where('id', $values->userId)
                ->update($data);

            return true;
        }
        catch (\PDOException $e) {
            return false;
        }
    }

    public function addUserImages($userId, $images)
    {
        $this->_addItemImage('user', $userId, $images);
    }

    private function _addItemImage($itemType, $itemId, $images)
    {
      $insertImages = [];

      foreach ($images as $image) {
          $img = \file_get_contents($image['path']);

          $insertImages[] = [
              $itemType.'s_id' => $itemId,
              'name' => $image['name'],
              'url' => $image['url'],
              'path' => $image['path'],
              'base64' => 'data:image/png;base64,'.\base64_encode($img),
              'size' => \filesize($image['path'])
          ];
      }

      $this->db->table($itemType.'s_images')->insert($insertImages);
    }

    public function getUserLogin($values)
    {
        return $this->db->table('users')
            ->where('email', $values->email)
            ->where('password', \md5($values->password))
            ->where('enabled', true)
            ->where('approved', true)
            ->fetch();
    }

    public function saveUserLogin($userId)
    {
        $this->db->table('users_logins')
          ->insert([
            'user_id' => $userId,
            'ip_address' => $this->presenter->getHttpRequest()->getRemoteAddress(),
            'login_time' => \time()
          ]);

        $this->db->table('users')
          ->where(['id' => $userId])
          ->update(['last_login' => \time()]);
    }

    public function addNewPost($userId, $values)
    {
        try {
            $car_make = $this->db->table('car_make')->get($values->car_make_id);
            $car_model = $this->db->table('car_model')->get($values->car_model_id);

            $post = $this->db->table('car_posts')->insert([
                'users_id' => $userId,
                'car_make_id' => $values->car_make_id,
                'car_model_id' => $values->car_model_id,
                'car_serie_id' => $values->car_serie_id ?? null,
                'car_trim_id' => $values->car_trim_id,
                'car_equipment_id' => $values->car_equipment_id,
                'year' => $values->year,
                'month' => $values->month,
                'kilometers_id' => $values->kilometers_id,
                'vehicle_types_id' => $values->vehicle_types_id,
                'colors_id' => $values->colors_id,
                'title' => $car_make->name.' '.$car_model->name,
                'description' => $values->description,
                'name' => $values->name,
                'surname' => $values->surname,
                'city' => $values->city,
                'address' => $values->address,
                'county' => $values->county,
                'cap' => $values->cap,
                'lat' => $values->mapCoordsLat,
                'long' => $values->mapCoordsLong,
                'telephone' => $values->telephone,
                'website' => $values->website,
                'email' => $values->email,
                'facebook' => $values->facebook,
                'twitter' => $values->twitter,
                'instagram' => $values->instagram,
                'price' => $values->price,
                'approved' => true,
                'creation_time' => \time(),
                'ip_address' => $this->presenter->getHttpRequest()->getRemoteAddress()
            ]);

            return $post->getPrimary();
        }
        catch (\PDOException $e) {
            Debugger::dump($e);
            return false;
        }
    }

    public function addPostImages($postId, $images)
    {
        $this->_addItemImage('car_post', $postId, $images);
    }

    public function editPost($postId, $values)
    {
        try {
            $car_make = $this->db->table('car_make')->get($values->car_make_id);
            $car_model = $this->db->table('car_model')->get($values->car_model_id);

            $post = $this->db->table('car_posts')
                ->where('id', $postId)
                ->update([
                    'car_make_id' => $values->car_make_id,
                    'car_model_id' => $values->car_model_id,
                    'car_serie_id' => $values->car_serie_id,
                    'car_trim_id' => $values->car_trim_id,
                    'car_equipment_id' => $values->car_equipment_id,
                    'year' => $values->year,
                    'month' => $values->month,
                    'kilometers_id' => $values->kilometers_id,
                    'vehicle_types_id' => $values->vehicle_types_id,
                    'colors_id' => $values->colors_id,
                    'title' => $car_make->name.' '.$car_model->name,
                    'description' => $values->description,
                    'name' => $values->name,
                    'surname' => $values->surname,
                    'city' => $values->city,
                    'address' => $values->address,
                    'county' => $values->county,
                    'cap' => $values->cap,
                    'lat' => $values->mapCoordsLat,
                    'long' => $values->mapCoordsLong,
                    'telephone' => $values->telephone,
                    'website' => $values->website,
                    'email' => $values->email,
                    'facebook' => $values->facebook,
                    'twitter' => $values->twitter,
                    'instagram' => $values->instagram,
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
            $this->db->table('car_posts')
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

        $dbo = $this->db->table('car_posts')
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
        $row = $this->db->table('car_posts')
            ->where('id', $id)
            ->fetch();

        return $this->utils->formatPostResult($row);
    }

    public function searchPosts($page = 1, $limit = 10)
    {
        $search = $this->presenter->getSession('frontend')->offsetGet('search');

        $posts = [];

        $search_dbo = $this->db->table('car_posts');

        if (!empty($search->place)) {
          $mapbox = new \App\Components\Mapbox\Mapbox('pk.eyJ1IjoiYXV0b2lkZWFsZSIsImEiOiJjazZreGhjZHQwMzYwM2Zxc2Iza2QzenB5In0.gSyjkkYqOh8ngSMnLvz3Aw');
          $address = $search->place;
        	$res = $mapbox->geocode($address)->getData();

          $search_dbo->select("*")
            ->select("(
              6371 * acos(
                cos(
                  radians(".$res[0]['center'][0].")
                ) * cos(
                  radians(car_posts.lat)
                ) * cos(
                  radians(car_posts.long) - radians(".$res[0]['center'][1].")
                ) + sin(
                  radians(".$res[0]['center'][0].")
                ) * sin(
                  radians(car_posts.lat)
                )
              )
            ) AS distance ")
            ->where("car_posts.lat IS NOT NULL")
            ->where("car_posts.long IS NOT NULL")
            ->having("distance < ?", 25)
            ->order('distance ASC');
        }
        else {
          $search_dbo->order('creation_time DESC');
        }

        if (!empty($search->car_make_id)) {
            $search_dbo->where('car_make_id', $search->car_make_id);
        }

        if (!empty($search->car_model_id)) {
            $search_dbo->where('car_model_id', $search->car_model_id);
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

        if (!empty($search->kilometers_from)) {
            $search_dbo->where('kilometers.from >= ?', $search->kilometers_from);
        }

        if (!empty($search->kilometers_to)) {
            $search_dbo->where('kilometers.to <= ?', $search->kilometers_to);
        }

        $search_dbo_page = clone $search_dbo;

        $search_dbo_car_makes = clone $search_dbo;
        $search_dbo_car_makes->select('COUNT(car_posts.id) AS tot, car_make_id')
                          ->group('car_make_id');

        $filters = $this->presenter->getSession('frontend')->offsetGet('filters');

        if (!empty($filters->car_make_id)) {
            $search_dbo_page->where('car_make_id', $filters->car_make_id);
        }

        if (!empty($filters->price)) {
            $search_dbo_page->where('price <= ?', $filters->price);
        }

        $tot = \count($search_dbo_page);
        $page_tot = \intval(\ceil($tot / $limit));
        $page_count = 5;
        $page_from = \max(1, $page - $page_count);
        $page_to = \min($page + $page_count, $page_tot);

        return [
            'tot' => $tot,
            'posts' => $search_dbo_page->page($page, $limit),
            'car_makes' => $search_dbo_car_makes,
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
            $request = $this->db->table('car_posts_requests')->insert([
                'car_posts_id' => $values->postId,
                'status' => 'pending',
                'name' => $values->name,
                'email' => $values->email,
                'telephone' => $values->telephone,
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

    public function sendPostMessage($values)
    {
      try {
        $thread = $this->db->table('car_posts_threads')->insert([
          'car_posts_id' => $values->postId,
          'hash' => Random::generate(45),
          'name' => $values->name,
          'email' => $values->email,
          'telephone' => $values->telephone,
          'creation_time' => \time()
        ]);

        $threadId = $thread->getPrimary();

        $message = $this->db->table('car_posts_threads_messages')->insert([
          'car_posts_threads_id' => $threadId,
          'new' => true,
          'from' => 'visitor',
          'message' => $values->message,
          'datetime' => \time()
          ]);

        return $threadId;
      }
      catch (\PDOException $e) {
        Debugger::dump($e);
        return false;
      }
    }

    public function addThreadMessage($values)
    {
      try {
        $message = $this->db->table('car_posts_threads_messages')->insert([
          'car_posts_threads_id' => $values->threadId,
          'new' => $values->new,
          'from' => $values->from,
          'message' => $values->message,
          'datetime' => \time()
        ]);

        return $message;
      }
      catch (\PDOException $e) {
          Debugger::dump($e);
          return false;
      }
    }

    public function getAllCarMake()
    {
      return $this->_getCarMake();
    }

    public function getTopCarMake($carMakeNo = false, $modelsNo = false)
    {
      return $this->_getCarMake($carMakeNo, $modelsNo, true);
    }

    public function getRandomCarMake($carMakeNo = false, $modelsNo = false)
    {
        return $this->_getCarMake($carMakeNo, $modelsNo, false, true);
    }

    private function _getCarMake($carMakeNo = false, $modelsNo = false, $top = false, $random = false)
    {
      $car_make = [];

      $car_make_dbo = $this->db->table('car_make')
        ->order($random !== false ? 'RAND()' : 'name');

      if ($top !== false) {
        $car_make_dbo->where('top', true);
      }

      if ($modelsNo !== false) {
        $car_make_dbo->limit($carMakeNo);
      }

      $rows = $car_make_dbo->fetchPairs('id');

      foreach ($rows as $row) {
        $models = $row->related('car_model')
          ->order('RAND()');

        if ($top !== false) {
          $models->where('top', true);
        }

        if ($modelsNo !== false) {
          $models->limit($modelsNo);
        }

        $car_make[] = [
          "data" => $row,
          "models" => $models->fetchPairs('id')
        ];
      }

      return $car_make;
    }

    public function getShowcase($postsNo = false)
    {
        $posts = [];

        $rows_dbo = $this->db->table('car_posts')
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

    public function getThreads($userId, $only_new = false)
    {
      return $this->db->table("car_posts_threads")
        ->where("car_posts.users_id", $userId);
    }

    public function getThread($threadId)
    {
      return $this->db->table("car_posts_threads")->get($threadId);
    }

    public function getThreadMessages($threadId)
    {
      return $this->db->table("car_posts_threads_messages")
        ->where("car_posts_threads_id", $threadId);
    }

    public function getMessages($userId)
    {
      return $this->db->table('car_posts_threads_messages')
        ->group('car_posts_threads.posts.users_id');
    }

    public function saveAllRead($threadId)
    {
      $this->db->table('car_posts_threads_messages')
        ->where('car_posts_threads_id', $threadId)
        ->update(['new' => false]);
    }

    public function getRequests($userId, $status = 'all')
    {
        $messages = [];

        $rows = $this->db->table('car_posts_requests')
            ->where('car_posts.users_id', $userId)
            ->order('creation_time DESC');

        if ($status !== 'all') {
            $rows->where('status', $status);
        }

        $rows = $rows->fetchPairs('id');

        foreach ($rows as $row) {
            $messages[] = [
                "data" => $row,
                "post" => $this->utils->formatPostResult($row->car_posts)
            ];
        }

        return $messages;
    }

    public function getRequest($requestId)
    {
        $row = $this->db->table('car_posts_requests')
            ->get($requestId);

        return [
            "data" => $row,
            "post" => $this->utils->formatPostResult($row->car_posts)
        ];
    }

    public function addPostToWishlist($postId, $userId)
    {
      $this->db->table('users_wishlists')
        ->insert([
          'car_posts_id' => $postId,
          'users_id' => $userId,
          'creation_time' => \time()
        ]);
    }

    public function removePostFromWishlist($postId, $userId)
    {
      $this->db->table('users_wishlists')
        ->where([
          'car_posts_id' => $postId,
          'users_id' => $userId
        ])
        ->delete();
    }

    public function getUserWishlist($userId)
    {
      $posts = [];

      $rows = $this->db->table('users_wishlists')
        ->where('users_id', $userId)
        ->fetchPairs('car_posts_id');

      foreach ($rows as $row) {
          $posts[$row->car_posts_id] = $this->utils->formatPostResult($row->car_posts);
      }

      return $posts;
    }
}
