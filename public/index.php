<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

//$app = AppFactory::create();
$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->addErrorMiddleware(true, true, true);

$app->get('/users', function ($request, $response) use ($users) {
    $name = $request->getQueryParam('name');
    if (!empty($name)) {
        $users = array_filter($users, function ($user) use ($name) {
            return str_contains($user, $name);
        });
    }
    $params = ['users' => $users];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    return $response->write('POST /users');
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->run();