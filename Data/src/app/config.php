<?php

/*
Este archivo funciona como un almacen de credenciales y parametros
de conexion en el proyecto
*/

$container->set('config_bd', function() { //crea una nueva entrada en el contenedor llamandolo 'config_bd'
//Los datos se devolveran agrupados en formato de objeto, en ves de un arreglo.
    return (object) [
        'host' => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSW'],
        'database' => $_ENV['DB_NAME'],
        'charset' => 'utf8mb4'
    ];
});
