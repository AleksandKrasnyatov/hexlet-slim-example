<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../validation/Validator.php';

use DI\Container;
use Slim\Factory\AppFactory;
use validation\Validator;

//$app = AppFactory::create();
$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);

$app->addErrorMiddleware(true, true, true);

$users = json_decode(file_get_contents(__DIR__ . '/../users/users.json'), true);

$app->get('/users', function ($request, $response) use ($users) {
    $name = $request->getQueryParam('name');
    if (!empty($name)) {
        $users = array_filter($users, function ($user) use ($name) {
            return str_contains($user['name'], $name);
        });
    }
    $params = ['users' => $users];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->get('/users/new', function ($request, $response, $args) use ($users) {
    $params = [
        'user' => ['name' => '', 'email' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    $validator = new Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $users = json_decode(file_get_contents(__DIR__ . '/../users/users.json'), true);
        $userId = uniqid();
        $user['id'] = $userId;
        $users[$userId] = $user;
        if (!file_put_contents(__DIR__ . '/../users/users.json', json_encode($users, JSON_PRETTY_PRINT))) {
            throw new Exception('Unable to write file.');
        }
        return $response->withRedirect('/users', 302);
    }
    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response, "/users/new.phtml", $params);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->run();