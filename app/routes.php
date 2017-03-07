<?php

$app->get('/', 'HomeController:index')->setName('home');

$app->group('/auth', function() use ($app) {

	$app->get('/login', 'AuthController:getLogin')->setName('auth.login');

	$app->post('/login', 'AuthController:postLogin');

	$app->get('/validateToken', 'AuthController:validateToken');

});


$app->group('/expense', function() use ($app) {

	$app->get('/home', 'ExpenseController:getHome')->setName('expense.home');

	$app->get('/cashManager', 'ExpenseController:getCashManager')->setName('expense.cashManager');

	$app->post('/submitTransaction', 'ExpenseController:insertTransaction');

	$app->get('/cashTransactions', 'CashTransactionsController:getCashTransactions')->setName('expense.cashTransactions');

	$app->post('/cashTransactions', 'CashTransactionsController:getTransactions');

	$app->get('/cashTransactionApprovals', 'CashTransactionApprovalsController:getCashTransactionApprovalPage')->setName('expense.cashTransactionApprovals');

	$app->post('/cashTransactionApprovals', 'CashTransactionApprovalsController:getCashTransactionApprovals');

	$app->get('/logout', 'ExpenseController:logout')->setName('expense.logout');

})->add(new \App\Middleware\TokenValidationMiddleware($app->getContainer()));


$app->group('/common', function() use ($app) {

	$app->get('/subSearch', 'SearchController:subSearch');

})->add(new \App\Middleware\TokenCheckMiddleware($app->getContainer()));