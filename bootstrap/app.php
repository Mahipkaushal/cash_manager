<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../app/config.php';

$app = new \Slim\App([
    'settings'  =>  [
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails'   =>  true,
        'addContentLengthHeader' => false,
        'db'    =>  $dbConfig       
    ]
]);

$container = $app->getContainer();

/*$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();*/

$container['dbDriver'] = function($container) {
    $driver = '\App\Library\Database\\' . DB_DRIVER;
    return new $driver;  
};

$container['db'] = function($container) {
    return new \App\Library\Db($container->dbDriver);  
};

$container['auth'] = function($container) {
    return new \App\Auth\Auth($container);
};

$container['token'] = function($container) {
    return new \App\Auth\Token($container);
};

$container['flash'] = function($container) {
    return new \Slim\Flash\Messages;
};

$container['view'] = function($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache'     =>  false
    ]);
    
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    
    $view->getEnvironment()->addGlobal('auth', [
        'check'     =>  $container->auth->check(),
        'user'      =>  $container->auth->user(),
    ]);
    
    $view->getEnvironment()->addGlobal('flash', $container->flash);
    
    return $view;
};

$container['validator'] = function($container) {
    return new \App\Validation\Validator;  
};

$container['HomeController'] = function($container) {
    return new \App\Controllers\HomeController($container);  
};

$container['AuthController'] = function($container) {
    return new \App\Controllers\Auth\AuthController($container);  
};

$container['ExpenseController'] = function($container) {
    return new \App\Controllers\Expense\ExpenseController($container);  
};

$container['CashTransactionsController'] = function($container) {
    return new \App\Controllers\Expense\CashTransactionsController($container);  
};

$container['CashTransactionApprovalsController'] = function($container) {
    return new \App\Controllers\Expense\CashTransactionApprovalsController($container);  
};

$container['SearchController'] = function($container) {
    return new \App\Controllers\Common\SearchController($container);  
};

//$container['csrf'] = function($container) {
  //  return new \Slim\Csrf\Guard;
//};

//$app->add(new \App\Middleware\TokenMiddleware($container));

//$app->add(new \App\Middleware\CsrfViewMiddleware($container));

//$app->add($container->csrf);

require __DIR__ . '/../app/routes.php';