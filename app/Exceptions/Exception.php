<?php
namespace App\Exceptions;

class Exception {
    protected $container;
    
    public function __construct($container) {
        $this->container = $container;
    }
}