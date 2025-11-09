<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('/', 'Home::index', ['filter' => 'auth']);
$routes->get('/home', 'Home::index', ['filter' => 'auth']);
$routes->get('/auth/login', 'Auth::login');
$routes->post('/auth/login', 'Auth::attemptLogin');
$routes->get('/auth/register', 'Auth::register');
$routes->post('/auth/register', 'Auth::attemptRegister');
$routes->get('/logout', 'Auth::logout');
$routes->post('/auth/refresh', 'Auth::refresh');
$routes->get('chat/fetch/(:num)', 'Chat::fetch/$1', ['filter' => 'auth']);
$routes->post('chat/send', 'Chat::send', ['filter' => 'auth']);

$routes->get('/tes', 'Home::tes');