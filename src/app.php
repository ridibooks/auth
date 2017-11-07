<?php
declare(strict_types=1);

use Ridibooks\Auth\Controller\AuthControllerProvider;
use Ridibooks\Auth\Services\OAuth2ServiceProvider;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;

$app = new Application([
    'debug' => $_ENV['DEBUG'],
]);

$app->register(new TwigServiceProvider(), [
    'twig.options' => [
        'cache' => __DIR__ . '/../var/cache',
        'auto_reload' => true,
    ],
    'twig.path' => [
        __DIR__ . '/../views',
    ],
]);

$app->register(new OAuth2ServiceProvider(), [
    'oauth2.db' => [
        'default' => [
            'host' => $_ENV['OAUTH_DB_HOST'],
            'dbname' => $_ENV['OAUTH_DB_DBNAME'],
            'user' => $_ENV['OAUTH_DB_USER'],
            'password' => $_ENV['OAUTH_DB_PASSWORD'],
            'driver' => 'pdo_mysql',
            'charset' => 'utf8',
        ],
        'user_credential' => [
            'host' => $_ENV['USER_DB_HOST'],
            'dbname' => $_ENV['USER_DB_DBNAME'],
            'user' => $_ENV['USER_DB_USER'],
            'password' => $_ENV['USER_DB_PASSWORD'],
            'driver' => 'pdo_mysql',
            'charset' => 'utf8',
        ]
    ]
]);

$app->register(new SessionServiceProvider(), [
    'session.storage.save_path' => __DIR__ . '/../var/sessions',
    'session.storage.options' => [
        'cookie_lifttime' => 60 * 60 * 24 * 90,
        'cookie_path' => '/',
        'cookie_domain' => $_ENV['DOMAIN'],
    ],
]);

$app->get('/', function (Application $app) {
    $user_idx = $app['session']->get('user_idx');
    $user_id = $app['session']->get('user_id');
    $user_name = $app['session']->get('user_name');
    return isset($user_idx) ? "user_idx=$user_idx user_id=$user_id user_name=$user_name" : 'Not logined.';
});

$app->mount('/auth', new AuthControllerProvider());

return $app;