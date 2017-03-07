<?php

namespace App\Auth;

use App\Controllers\Controller;
use App\Models\User;

class Auth extends Controller {

    protected $user;
    protected $container;

    public function __construct($container) {
        $this->container = $container;
        $this->user = new User($this->container);
    }

    public function user() {
        return true;
    }

    public function check() {
        return true;
    }

    public function validateToken($request) {
        if($request) {
            return $this->token->validateToken($request);
        } else {
            return false;
        }
    }
    
    public function attempt($username, $password) {
        $user = array();
        
        $data = array(
            'username'      =>  $username,
            'password'      =>  $password
        );

        $userData = $this->user->login($data);

        if($userData) {
            $token = $this->token->generateToken($userData['user_id']);
            $user['username'] = $userData['username'];
            $user['token'] = $token;
            return $user;
        } else {
            return false;
        }
    }
    
}