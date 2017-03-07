<?php
namespace App\Middleware;

use App\Models\User;

class TokenValidationMiddleware extends Middleware {
	protected $user;
    protected $container;

    public function __construct($container) {
        $this->container = $container;
        $this->user = new User($this->container);
    }

    public function __invoke($request, $response, $next) {
    	
    	$json = array();

    	if($request->hasHeader('X-Auth-Token')) {
    		$token = $request->getHeader('X-Auth-Token')[0];
    		$user = false;
    		$user = $this->container->token->getUserByToken($token);
    		if($this->container->token->validateToken($token) && $user) {
    			$request = $request->withAttribute('error', false);
    			$request = $request->withAttribute('token', $token);
    			$request = $request->withAttribute('user', $user);
                $this->container['user'] = $user;
                $this->container['cashbox_id'] = 1000000;
    		} else {
    			$request = $request->withAttribute('error', true);
				$request = $request->withAttribute('message', 'Token Mismatch');
    		}
    	} else {
    		$request = $request->withAttribute('error', true);
    		$request = $request->withAttribute('message', 'Token Missing');
    	}
    	
        $response = $next($request, $response);

        return $response;
		
    }
}