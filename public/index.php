<?php
/*
 * Полноценный работающий MVC проект.
 * Непортить и неменять. Копировать и использовать как эталон для тренировок.
 */
if( !session_id() ) @session_start();

require '../vendor/autoload.php';

use Aura\SqlQuery\QueryFactory;
use Delight\Auth\Auth;
use DI\ContainerBuilder;
use League\Plates\Engine;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    Engine::class => function() {
        return new Engine('../app/views');
    },

    QueryFactory::class => function() {
        return new QueryFactory('mysql');
    },

    PDO::class => function() {
        $driver = "mysql";
        $host = "localhost";
        $database_name = "marlin_own_comp";
        $charset = "utf8";
        $username = "root";
        $password = "root";

        return new PDO("$driver:host=$host;dbname=$database_name;charset=$charset", $username, $password);
    },

    Auth::class => function($container) {
        return new Auth($container->get('PDO'));
    },
]); //указываем исключения из правил
$container = $containerBuilder->build();

$templates = new League\Plates\Engine('../app/views');

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/about', ['App\controllers\PageController', 'about']);
    $r->addRoute('GET', '/register', ['App\controllers\PageController', 'register']);
    $r->addRoute('GET', '/email-verification/{selector}/{token}', ['App\controllers\PageController', 'email_verification']);
    $r->addRoute('GET', '/login/{email}/{password}', ['App\controllers\PageController', 'login']);
    $r->addRoute('GET', '/login/view', ['App\controllers\PageController', 'userView']);
    $r->addRoute('GET', '/sendemail', ['App\controllers\PageController', 'sendEmail']);

    $r->addRoute('GET', '/homepage[/{id:\d+}]', ['App\controllers\PageController', 'homepage']);
    $r->addRoute('GET', '/', ['App\controllers\PageController', 'homepage']);

    $r->addRoute('GET', '/users', 'get_all_users_handler');
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo $templates->render('404');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $container->call($handler, [$vars]);

        break;
}