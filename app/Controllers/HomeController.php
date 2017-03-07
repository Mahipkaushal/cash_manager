<?php

namespace App\Controllers;

use Slim\Views\Twig as View;

class HomeController extends Controller {
    
    public function index($request, $response) {
        $vars = [
            'page' => [
            'title'         => 'Cash Manager',
            'description'   => 'Welcome to the official Cash Manager',
            'author'        => 'Mahip Kaushal'
            ],
        ];        
        return $this->view->render($response, 'home.twig', $vars);
    }
    
}