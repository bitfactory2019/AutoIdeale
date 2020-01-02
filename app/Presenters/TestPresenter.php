<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

final class TestPresenter extends _BasePresenter
{
    public function renderIndex()
    {
      exit;
      $names = [
        'Pietro',
        'Giovanni',
        'Giacomo',
        'Andrea',
        'Filippo',
        'Tommaso',
        'Bartolomeo',
        'Matteo',
        'Simone',
        'Giuda',
        'Mattia'
      ];

      $surnames = [
        'Rossi',
        'Ferrari',
        'Russo',
        'Bianchi',
        'Romano',
        'Costa',
        'Gallo',
        'Fontana',
        'Conti',
        'Esposito',
        'Ricci',
        'Bruno'
      ];

      $cities = [
        'Napoli',
        'Milano',
        'Torino',
        'Firenze',
        'Palermo',
        'Bari',
        'Genova',
        'Bolzano',
        'Olbia'
      ];

      foreach ($names as $name) {
        foreach ($surnames as $surname) {
          $email = \strtolower($name).\strtolower($surname).'@autoideale.it';
          $user = $this->db->table('users')->insert([
              'groups_id' => 2,
              'name' => $name,
              'surname' => $surname,
              'email' => $email,
              'password' => \md5('autoideale'),
              'enabled' => true,
              'creation_time' => \time()
          ]);

          $userId = $user->getPrimary();

          for ($i = 1; $i <= 5; $i++) {
            $randomBrand = $this->db->table('brands')->where('top', true)->order('RAND()')->fetchField('id');
            $randomBrandModel = $this->db->table('brands_models')->where('brands_id', $randomBrand)->where('top', true)->order('RAND()')->fetchField('id');
            $randomModelType = $this->db->table('brands_models_types')->where('brands_models_id', $randomBrandModel)->order('RAND()')->fetchField('id');
            $randomYear = $this->_randomYear($randomModelType);
            $randomMonth = $this->_randomMonth($randomModelType, $randomYear);

            $randomFuelType = $this->db->table('fuel_types')->order('RAND()')->fetchField('id');
            $randomKilometers = $this->db->table('kilometers')->order('RAND()')->fetchField('id');
            $randomModel = $this->db->table('models')->order('RAND()')->fetchField('id');
            $randomVehicleType = $this->db->table('vehicle_types')->order('RAND()')->fetchField('id');
            $randomColor = $this->db->table('colors')->order('RAND()')->fetchField('id');
            $randomShiftType = $this->db->table('shift_types')->order('RAND()')->fetchField('id');
            $randomEuroClass = $this->db->table('euro_class')->order('RAND()')->fetchField('id');
            $randomDoors = $this->db->table('doors')->order('RAND()')->fetchField('id');
            $randomSeats = $this->db->table('seats')->order('RAND()')->fetchField('id');

            $randomPrice = \rand(10, 200) * 100;

            $brand = $this->db->table('brands')->get($randomBrand);
            $brand_model = $this->db->table('brands_models')->get($randomBrandModel);

            $post = $this->db->table('posts')->insert([
                'users_id' => $userId,
                'brands_id' => $randomBrand,
                'brands_models_id' => $randomBrandModel,
                'brands_models_types_id' => $randomModelType,
                'year' => $randomYear,
                'month' => $randomMonth,
                'fuel_types_id' => $randomFuelType,
                'kilometers_id' => $randomKilometers,
                'models_id' => $randomModel,
                'vehicle_types_id' => $randomVehicleType,
                'colors_id' => $randomColor,
                'shift_types_id' => $randomShiftType,
                'euro_class_id' => $randomEuroClass,
                'doors_id' => $randomDoors,
                'seats_id' => $randomSeats,
                'title' => $brand->name.' '.$brand_model->name,
                'description' => $this->_description($randomBrand, $randomBrandModel, $randomYear),
                'name' => $name,
                'surname' => $surname,
                'city' => 'Milano',
                'address' => 'Via Gustavo Fara, 8',
                'county' => 'Milano',
                'cap' => '20124',
                'telephone' => '0813216549',
                'website' => 'http://www.autoideale.it',
                'email' => $email,
                'facebook' => 'http://www.facebook.com',
                'twitter' => 'http://www.twitter.com',
                'instagram' => 'http://www.instagram.com',
                'price' => $randomPrice,
                'approved' => true,
                'creation_time' => \time()
            ]);

            $postId = $post->getPrimary();

            $filesWrapper = new \App\Utils\FilesWrapper($this);
            $files = $filesWrapper->moveTempFiles('bulk-images-'.\rand(1, 5), $postId, false);
            $this->dbWrapper->addPostFiles($postId, $files);
          }
        }
      }
    }

    private function _description($brandId, $modelId, $year)
    {
      $brand = $this->db->table('brands')->get($brandId);
      $model = $this->db->table('brands_models')->get($modelId);

      return $brand->name.' '.$model->name.' del '.$year;
    }

    private function _randomYear($modelTypeId)
    {
      $model = $this->db->table('brands_models_types')->get($modelTypeId);

      return \rand($model->production_from_year, !empty($model->production_to_year) ? $model->production_to_year : \intval(\date('Y')));
    }

    private function _randomMonth($modelTypeId, $year)
    {
      $model = $this->db->table('brands_models_types')->get($modelTypeId);

      if ($year == $model->production_from_year) {
        return \rand($model->production_from_month, 12);
      }
      elseif ($year == $model->production_to_year) {
        return \rand(1, $model->production_to_month);
      }
      elseif (empty($model->production_to_year)) {
        return \rand(1, \intval(\date('m')));
      }
      else {
        return \rand(1, 12);
      }
    }
}
