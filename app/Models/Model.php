<?php

namespace App\Models;

class Model {
    protected $container;
    
    public function __construct($container) {
        $this->container = $container;
    }
}