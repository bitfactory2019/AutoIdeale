<?php

namespace App;

class Utils
{
    private $db;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->db = $database;
    }

    public function arrayKeyValue($array, $key, $value)
    {
        $a =  [];

        foreach ($array as $item) {
            $a[$item[$key]] = $item[$value];
        }

        return $a;
    }

    public function getDbOptions($table)
    {
        return $this->arrayKeyValue($this->db->table($table), 'id', 'name');
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
}