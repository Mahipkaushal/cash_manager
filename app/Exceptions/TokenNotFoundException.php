<?php
namespace App\Exceptions;

use Exception;

class TokenNotFoundException extends Exception {
	public function __invoke($request, $response, $next) {
		$json = array();
		$json['error']['code'] = 401;
		$json['error']['message'] = 'Token Mismatch!';
		
		$response = json_encode($json);

		return $response;
	}
}