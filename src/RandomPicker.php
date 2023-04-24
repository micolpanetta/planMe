<?php

namespace App;

class RandomPicker
{
    private $available_values;
    private $picked_values = [];
    
    public function __construct($available_values) {
        $this->available_values = $available_values;
        shuffle($this->available_values);
    }
  
    public function pick() {

        $picked = array_shift($this->available_values);
        $this->picked_values[] = $picked;

        if(empty($this->available_values)) {
            $this->available_values = $this->picked_values;
            $this->picked_values = [];
            shuffle($this->available_values);

            if($this->available_values[0] == $picked) {
                array_push($this->available_values, array_shift($this->available_values));
            }
        }

        return $picked;
    }
}