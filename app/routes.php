<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Controller\LoanController;
use App\Application\Models\DB;
use PharIo\Manifest\Application;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    // $container = $app->getContainer();

    // $container->set(DB::class, function (ContainerInterface $container) {
    //     return new DB(); // Atau sesuai dengan cara Anda membuat instance DB
    // });
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->group("/api", function (Group $group) {

        $group->get("/loans", LoanController::class . ':index');
        $group->post("/loan", LoanController::class . ':create');
    });

    // $app->get('/', LoanController::class . ':index');

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
