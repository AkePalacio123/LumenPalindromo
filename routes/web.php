<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () {
    return redirect('/dashboard');
});

$router->get('/login', 'SessionController@loginForm');
$router->post('/login', 'SessionController@login');
$router->get('/registro', 'SessionController@registerForm');
$router->post('/registro', 'SessionController@register');

$router->get('/palindromo', 'PalindromoController@index');
$router->post('/palindromo', 'PalindromoController@verificar');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/logout', 'SessionController@logout');

    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/dashboard-demo', 'DashboardController@index');

    $router->get('/operadores', 'UserInterfacesController@operators');

    $router->get('/perfil/editar', 'UserInterfacesController@edit');
    $router->post('/perfil/editar', 'UserInterfacesController@updateProfile');

    $router->get('/flotilla', 'FleetController@index');
    $router->post('/flotilla', 'FleetController@store');
    $router->post('/flotilla/{id}/estado', 'FleetController@updateStatus');
    $router->post('/flotilla/{id}/eliminar', 'FleetController@destroy');

    $router->get('/viajes', 'TravelController@index');
    $router->get('/viajes/crear', 'TravelController@create');
    $router->post('/viajes', 'TravelController@store');
    $router->post('/viajes/{id}/estado', 'TravelController@updateStatus');
    $router->post('/viajes/{id}/eliminar', 'TravelController@destroy');

    $router->get('/diesel', 'FuelController@index');
    $router->post('/diesel', 'FuelController@store');

    $router->group(['middleware' => 'admin'], function () use ($router) {
        $router->get('/usuarios', 'UserInterfacesController@index');
        $router->get('/usuarios/crear', 'UserInterfacesController@create');
        $router->post('/usuarios', 'UserInterfacesController@store');
        $router->post('/usuarios/{id}/estado', 'UserInterfacesController@setStatus');
    });
});
