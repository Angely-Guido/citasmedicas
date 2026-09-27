<?php
namespace App\controllers;

use App\controllers\Paciente;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteCollectorProxy; //permite crear grupo de rutas

/*Cuando se realiza una peticion GET a la URL localhost:8080/, se ejecuta una 
funcion anonima, en donde en el cuerpo de la respuesta se escribe la palabra 
'Hello' y se retorna.
*/
$app->get('/', function (Request $request, Response $response, array $args) {
    
    $response->getBody()->write("Hello Slim");
    return $response;
});

/*
Cuando se realiza una peticion GET a la URL /hello/{name}, se ejecuta la funcion anonima, y lo que venga en 
{name} se va a guardar en $args. Despues accedemos a lo que esta en args y lo guadamos con $name. Y ya luego
en el cuerpo de la respuesta escribimos "Hello, $name", y se retorna la respuesta.
*/
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");//Interporalizacion en PHP ""
    return $response;
});

$app->group('/api', function(RouteCollectorProxy $api) { //Todo lo que entre aqui, va a empezar con api
    
    $api->group('/medicos', function(RouteCollectorProxy $endpoint) { //Todo lo que este aqui adentro, a /api se le va agregar /medico = /api/medico

        //       /api/medico - nombre de la clase - nombre del metodo 
        $endpoint->get('[/{id}]', Medico::class . ':read'); //Se registra un endpoind
        /*Medico::class devuelve el nombre de la clase con su namespace y lo concatena con . :nombre del metodo
        crea el objeto, le inyecta sus dependencias y luego ejecuta el metodo read.
        */
        $endpoint->get('/filter/{offset}/{limit}', Medico::class . ':filter');
        $endpoint->post('', Medico::class . ':create');
        $endpoint->put('/{id}', Medico::class . ':update');
        $endpoint->delete('/{id}', Medico::class . ':delete');
    });


    $api->group('/pacientes', function(RouteCollectorProxy $url) {
        $url->get('[/{id}]', Paciente::class . ':read');
        $url->post('', Paciente::class . ':create');
        $url->put('/{id}', Paciente::class . ':update');
        $url->delete('/{id}', Paciente::class . ':delete');
        $url->get('/filter/{offset}/{limit}', Paciente::class . ':filter');
    });

    $api->group('/citas', function(RouteCollectorProxy $url) {
        $url->get('/paciente[/{id}]', Citas::class . ':read');
        $url->post('', Citas::class . ':create');
    });

});