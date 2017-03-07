<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class AuthController extends Controller {
    
    public function getLogin($request, $response) {
        $json = array();
        $vars = [];
        $json['html'] = $this->view->fetch('auth/login.twig', $vars);
        return json_encode($json);
    }
    
    public function postLogin($request, $response) {
        $json = array();

        $auth = $this->auth->attempt(
            $request->getParam('username'),
            $request->getParam('password')
        );
        
        
        if(!$auth) {
            $json['error']['type'] = 'error';
            $json['error']['code'] = 401;
            $json['error']['message'] = 'Username or Password does not match!';
        } else {
            $json['user'] = $auth;
            $json['success'] = 'Login Successful!';
        }
        
        $response = $json;
    
        return json_encode($response);
    }
    
    public function validateToken($request, $response) {
        $json = array();

        if($request->hasHeader('X-Auth-Token')) {
            $token = $request->getHeader('X-Auth-Token')[0];
            $json['success'] = $this->token->validateToken($token);
        } else {
            $json['success'] = false;
        }

        $response = $json;
    
        return json_encode($response);
    }

    public function logout($request, $response) {
        $this->auth->logout($request);
        return $this->view->render($response, 'auth/login.twig');
    }
}