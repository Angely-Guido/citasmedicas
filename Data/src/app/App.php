<?php
/*
Importa la herramienta principal de Slim Framework 
necesaria para inicializar y construir la aplicación web.
*/
use Slim\Factory\AppFactory;

/*
Importa la clase encargada de crear el 
"contenedor" de inyección de dependencias.
*/
use DI\Container;

/*
Carga el archivo generado por Composer, el cual
importa automáticamente todas las librerías 
descargadas para el proyecto (Slim, PHP-DI).
 */
require __DIR__ . '/../../vendor/autoload.php';

/*
Crea el contenedor, inicializando una estructura 
donde más adelante se guardarán los ajustes globales 
como las credenciales de la base de datos.
*/
$container = new Container();

$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->load();

/*
Le asigna el contenedor recién creado al gestor de 
Slim para que la aplicación reconozca y pueda utilizar 
las dependencias almacenadas en él.
*/
AppFactory::setContainer($container);

/*
Crea y asigna a la variable $app el objeto central 
de la aplicación, el cual procesará las entradas y 
salidas de la web.
*/
$app = AppFactory::create();

$app->addBodyParsingMiddleware(); 

//carga los archivo, y si ya estaban cargados, no los vuelve a cargar
require_once "routes.php"; //carga el archivo
require_once "config.php";
require_once "conexion.php";
require_once "eloquent.php";

$app->run(); //enciende la aplicacion