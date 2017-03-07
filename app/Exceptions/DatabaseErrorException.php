<?php
namespace App\Exceptions;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class DatabaseErrorException extends Exception {
	
	protected $error;
	
	public function __construct($error) {
		$this->error = $error;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next) {
		$json = array();
		$json['error']['code'] = 501;
		$json['error']['message'] = $this->error;
		
		$response = json_encode($json);

		return $response;
	}

}