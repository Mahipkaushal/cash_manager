<?php

namespace App\Auth;

use App\Controllers\Controller;
use App\Models\User;

class Token extends Controller {
	protected $length = 32;

	protected $user;
    protected $container;

    public function __construct($container) {
        $this->container = $container;
        $this->user = new User($this->container);
    }

	public function validateToken($token) {
		$result = $this->user->validateToken($token);
		if($result) {
			return true;
		} else {
			return false;
		}
	}

	public function generateToken($user_id = 0) {
		$token = bin2hex(openssl_random_pseudo_bytes($this->length));
		$this->updateToken($token, $user_id);
		return $token;
	}

	public function getUserByToken($token) {
		return $this->user->getUserByToken($token);
	}

	protected function updateToken($token, $user_id) {
		$this->user->updateToken($token, $user_id);
	}

	public function deleteToken($token) {
		$this->user->deleteToken($token);
	}
}