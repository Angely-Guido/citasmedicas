<?php
namespace App\controllers;

use Slim\Routing\RouteCollectorProxy; 

$app->group('/api', function(RouteCollectorProxy $api) {
    $api->group('/medicos', function(RouteCollectorProxy $endpoint) { 
        $endpoint->get('[/{id}]', Medico::class . ':read'); 
        $endpoint->get('/filter/{offset}/{limit}', Medico::class . ':filter');
        $endpoint->post('', Medico::class . ':create');
        $endpoint->put('/{id}', Medico::class . ':update');
        $endpoint->delete('/{id}', Medico::class . ':delete');
    });


    $api->group('/pacientes', function(RouteCollectorProxy $url) {
        $url->get('[/{id}]', Paciente::class . ':read');
        $url->get('/filter/{offset}/{limit}', Paciente::class . ':filter');
        $url->post('', Paciente::class . ':create');
        $url->put('/{id}', Paciente::class . ':update');
        $url->delete('/{id}', Paciente::class . ':delete');
    });

    $api->group('/citas', function(RouteCollectorProxy $url) {
        $url->get('/paciente[/{id}]', Citas::class . ':read');
        $url->post('', Citas::class . ':create');
    });

});