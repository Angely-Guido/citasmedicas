<?php

/*
Este archivo crea la conexion entre l aplicacion y la base de datos
, dejandola lista para usarse
*/

/*
Importa la interfaz estándar que permite inyectar 
y utilizar el contenedor de dependencias (el "archivero") 
dentro de esta función.
*/
use Psr\Container\ContainerInterface;


/*
Crea un nuevo espacio en el contenedor llamado
base_datos. Utiliza una función que recibe el 
propio contenedor ($c) para poder acceder a las 
otras gavetas ya creadas.
*/
$container->set('base_datos', function(ContainerInterface $c) {
    $conf = $c->get('config_bd'); //extrae las credenciales y las guarda en esa variable

    $opc = [ //opciones adicionales
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //en caso de fallar, lanza excepcion visible
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ //los resutados de las consultas se entreguen como objetos
    ];


    /*
    Construye el DSN (Data Source Name), una cadena de 
    texto estructurada que indica el motor (mysql), 
    dónde está el servidor ($conf->host), a qué base de 
    datos apuntar ($conf->database) y la codificación ($conf->charset).
    */
    $dsn = "mysql:host={$conf->host};dbname={$conf->database};charset={$conf->charset}";

    try {
        $conexion = new PDO($dsn, $conf->username, $conf->password, $opc); //realiza la conexion a la base de datps
    } catch (PDOException $e) {
        die("Error de conexion " . $e->getMessage()); //detiene inmediatamente la ejecucion de la aplicacion
    }

    return $conexion; //devuelve la conexion y lo deja guardado en el contenedor con la etiqueta base_datos
});