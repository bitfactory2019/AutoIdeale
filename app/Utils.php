<?php

namespace App;

class Utils
{
    private $presenter;
    private $db;

    public function __construct(\Nette\Application\UI\Presenter $presenter)
    {
        $this->presenter = $presenter;
        $this->db = $this->presenter->getDbService();
    }

    public function arrayKeyValue($array, $key, $value)
    {
        $a =  [];

        foreach ($array as $item) {
            $a[$item[$key]] = $item[$value];
        }

        return $a;
    }

    public function getHours($start_h = 8, $start_m = 0, $end_h = 20, $end_m = 0)
    {
        $hours = [];

        for ($h = $start_h; $h <= $end_h; $h++) {
            for ($m = 0; $m < 60; $m = $m + 15) {
                $hours[] = \str_pad($h, 2, "0", STR_PAD_LEFT).":".\str_pad($m, 2, "0", STR_PAD_LEFT);
            }
        }

        return $hours;
    }

    public function getDbOptions($table, $where = [], $showEmpty = false)
    {
        $results = $this->db->table($table);

        foreach ($where as $field => $value) {
            $results->where($field, $value);
        }

        $results->order('name');

        $options = $this->arrayKeyValue($results, 'id', 'name');

        if ($showEmpty === true) {
            $options = ["" => "-- Scegli --"] + $options;
        }

        return $options;
    }

    public function getKilometersOptions()
    {
        $kilometers = [];

        foreach ($this->db->table('kilometers') as $km) {
            $kilometers[$km->id] = $this->_formatKmLabel($km);
        }

        return $kilometers;
    }

    private function _formatKmLabel($km)
    {
        if ($km->from === null) {
            return 'Km '.$km->to;
        }

        if ($km->to === null) {
            return $km->from.'+';
        }

        return \sprintf('%s - %s km', $km->from, $km->to);
    }

    public function getSeatsOptions()
    {
        $seats = [];

        foreach ($this->db->table('seats') as $seat) {
            $seats[$seat->id] = $this->_formatSeatLabel($seat);
        }

        return $seats;
    }

    private function _formatSeatLabel($seat)
    {
        return $seat->seats_no.($seat->more ? '+' : '');
    }

    public function getModelTypesOptions($modelId)
    {
        $types = [];

        $model_types = $this->db->table('brands_models_types')->where('brands_models_id', $modelId);

        foreach ($model_types as $type) {
            $types[$type['id']] = $type->type." ".$type->cv."cv";
        }

        return $types;
    }

    public function getTypeYearsOptions($typeId)
    {
        $options = [];

        $type = $this->db->table('brands_models_types')->get($typeId);

        for ($year = $type->production_from_year; $year <= $type->production_to_year; $year++) {
            $options[$year] = $year;
        }

        return $options;
    }

    public function getTypeYearMonthsOptions($typeId, $year)
    {
        $options = [];

        $type = $this->db->table('brands_models_types')->get($typeId);
        $start = 1;
        $end = 12;

        if ($type->production_from_year == $year) {
            $start = $type->production_from_month;
        }

        if ($type->production_to_year == $year) {
            $end = $type->production_to_month;
        }

        for ($month = $start; $month <= $end; $month++) {
            $options[$month] = Library::MONTHS[$month];
        }

        return $options;
    }

    public function formatPostResult($post)
    {
        $thumbnail = $this->presenter->getDbService()->table('posts_images')->where('posts_id', $post->id)->limit(1)->fetch();

        return [
            "data" => $post,
            "thumbnail" => $thumbnail,
            "images" => $post->related('posts_images.posts_id'),
            "isNew" => $post->creation_time > strtotime("3 days ago"),
            "isNotAvailable" => !$post->approved
        ];
    }

    public function generatePassword()
    {
        $chars = "abcdefghijklmnopqrstuvwxyz1234567890";

        $password = "";

        for ($i = 0; $i < 8; $i++)
        {
            $password .= $chars[\rand(0, \strlen($chars) - 1)];
        }

        return $password;
    }
}
