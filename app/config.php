<?php

/**
 * Routes
 * 
 */
$router = $container->singleton('\Core\Router');

$router->error404('Main', 'error404');
$router->error500('Main', 'error500');

$router->get('homepage', '', 'Main', 'index');
$router->get('page1', 'page1', 'Main', 'page1');
