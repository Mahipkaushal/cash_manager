<?php
namespace App\Middleware;

class TokenCheckMiddleware extends Middleware {
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {

    	if($request->hasHeader('X-Auth-Token')) {
    		$token = $request->getHeader('X-Auth-Token')[0];
    		if($this->container->token->validateToken($token)) {
    			$request = $request->withAttribute('error', false);
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