<?php

namespace App\Utils;

class DbWrapper
{
    private $db;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
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
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'login_time' => \time()
        ]);
    }

    public function addNewPost($userId, $values)
    {
        try {
            $post = $this->db->table('posts')->insert([
                'user_id' => $userId,
                'fuel_type_id' => $values->fuel_type_id,
                'kilometers_id' => $values->kilometers_id,
                'model_id' => $values->model_id,
                'vehicle_type_id' => $values->vehicle_type_id,
                'color_id' => $values->color_id,
                'shift_type_id' => $values->shift_type_id,
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
                'approved' => false,
                'creation_time' => \time()
            ]);

            $id = $post->getPrimary();
            return true;
        }
        catch (\PDOException $e) {
            return false;
        }
    }
}
