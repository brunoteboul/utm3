<?php
require_once __DIR__.'/../vendor/autoload.php';
$app = Utm\Core::instance();

// plugin registration
$app->registerPlugin('\Utm\Plugin\Db');
$app->registerPlugin('\Utm\Plugin\Twig');

// route declaration
$app->addRoute('GET', '/api/articles', ['ctrl' => 'toto', 'act' => 'index']);
$app->addRoute('GET', '/api/article/:id', ['ctrl' => 'index', 'act' => 'index']);
$app->addRoute('GET', '/api/article/:id/p/:page', ['ctrl' => 'index', 'act' => 'index']);
$app->addRoute('POST', '/api/article/:id', ['ctrl' => 'toto', 'act' => 'index']);
$app->addRoute('DELETE', '/api/article/:id', ['ctrl' => 'index', 'act' => 'delete']);
$app->addRoute('PATCH', '/api/article/:id', ['ctrl' => 'index', 'act' => 'patch']);
$app->addRoute('PUT', '/api/article/:id', ['ctrl' => 'index', 'act' => 'put']);

$app->run();