<?php

use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Url\Url as UrlProvider;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Loader;

$di = new FactoryDefault();
$di->setShared(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();

        return $session;
    }
);

$di->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => getenv('DB_HOST'),
                'port'     => getenv('DB_PORT'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'dbname'   => getenv('DB_NAME'),
            ]
        );
    }
);

$di->set('models-finder', function() use($di) {
    return new \Phalcon\Mvc\ModelFinder($di);
});

$di->setShared('modelsManager', function() use ($di) {
    $manager = new \Phalcon\Mvc\Model\SqlManager();
    $manager->setDI($di);
    return $manager;
});

$di->setShared('modelsMetadata', function() use ($di) {
    $helper = new \Phalcon\Support\HelperFactory();
    
    $fact = new \Phalcon\Cache\AdapterFactory(
            $helper,
            new \Phalcon\Storage\SerializerFactory()
            );
    $md = new \Phalcon\Mvc\Model\MetaData\Apcu($fact);
    $md->setDI($di);
    return $md;
});

/**
 * Setting the View
 */
$di->setShared('view', function () use ($di) {
    $view = new View();
    $view->setViewsDir(BASE_PATH . '/tests/_data/App/Views/');
    $view->registerEngines(
        [
            ".volt"  => "voltService"
        ]
    );
    $eventsManager = $di->get('eventsManager');
    $eventsManager->attach('view', function ($event, $view) use ($di) {
        /**
         * @var \Phalcon\Events\Event $event
         * @var \Phalcon\Mvc\View $view
         */
        if ($event->getType() == 'notFoundView') {
            $message = sprintf('View not found - %s', $view->getActiveRenderPath());
            throw new Exception($message);
        }
    });
    $view->setEventsManager($eventsManager);
    return $view;
});

/**
 * Volt Service
 */
$di->set(
    'voltService',
    function ($view) use ($di) {
        $volt = new Volt($view, $di);

        $volt->setOptions(
            [
                'compiledPath'      => BASE_PATH . '/_output/compiled-templates/',
                'compiledExtension' => '.compiled',
            ]
        );

        return $volt;
    }
);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $url = new UrlProvider();
    $url->setBaseUri('/');
    return $url;
});


$router = $di->getRouter();

$router->add('/', [
    'controller' => 'App\Controllers\Index',
    'action'     => 'index'
])->setName('front.index');

return new Application($di);
