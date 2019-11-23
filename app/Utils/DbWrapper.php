<?php

namespace App\Utils;

use Nette\Diagnostic\Debugger;

class DbWrapper
{
    private $presenter;
    private $db;

    public function __construct(\Nette\Application\UI\Presenter $presenter)
    {
        $this->presenter = $presenter;
        $this->db = $this->presenter->getDbService();
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
            $user = $this->db->table('users')->insert([
                'groups_id' => 2,
                'name' => $values->name,
                'surname' => $values->surname,
                'email' => $values->email,
                'password' => \md5($values->password),
                'enabled' => false,
                'creation_time' => \time()
            ]);

            return $user->getPrimary();
        }
        catch (\PDOException $e) {
            Debugger::dump($e);
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
            $post = $this->db->table('posts')->insert([
                'users_id' => $userId,
                'brands_id' => $values->brands_id,
                'brands_models_id' => $values->brands_models_id,
                'fuel_types_id' => $values->fuel_type_id,
                'kilometers_id' => $values->kilometers_id,
                'models_id' => $values->model_id,
                'vehicle_types_id' => $values->vehicle_type_id,
                'colors_id' => $values->color_id,
                'shift_types_id' => $values->shift_type_id,
                'euro_class_id' => $values->euro_class_id,
                'doors_id' => $values->doors_id,
                'seats_id' => $values->seats_id,
                'year' => $values->year,
                'brand' => $values->brand,
                'title' => $values->title,
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
            return false;
        }
    }

    public function addPostFiles($postId, $files)
    {
        $insertFiles = [];

        foreach ($files as $file) {
            $insertFiles[] = [
                'post_id' => $postId,
                'name' => $file['name'],
                'url' => $file['url'],
                'path' => $file['path']
            ];
        }

        $this->db->table('posts_images')->insert($insertFiles);
    }

    public function getPosts($userId)
    {
        $posts = [];

        $rows = $this->db->table('posts')
            ->where('users_id', $userId)
            ->order('creation_time DESC')
            ->fetchPairs('id');

        foreach ($rows as $row) {
            $posts[] = [
                "data" => $row,
                "thumbnail" => $row->related('posts_images.post_id')->limit(1)->fetch(),
                "images" => $row->related('posts_images.post_id')
            ]; 
        }

        return $posts;
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
}
