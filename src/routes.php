<?php

use Slimbug\Controllers\Bugs\BugController;
use Slimbug\Controllers\Bugs\CommentController;
use Slimbug\Controllers\Bugs\FavouriteController;
use Slimbug\Controllers\Auth\LoginController;
use Slimbug\Controllers\Auth\RegisterController;
use Slimbug\Controllers\User\ProfileController;
use Slimbug\Controllers\User\UserController;
use Slimbug\Middleware\OptionalAuth;
use Slimbug\Models\Tag;
use Slim\Http\Request;
use Slim\Http\Response;


// Api Routes
$app->group('/api',
    function () {
        $jwtMiddleware = $this->getContainer()->get('jwt');
        $optionalAuth = $this->getContainer()->get('optionalAuth');
        /** @var \Slim\App $this */

        // Auth Routes
        $this->post('/users', RegisterController::class . ':register')->setName('auth.register');
        $this->post('/users/login', LoginController::class . ':login')->setName('auth.login');

        // User Routes
        $this->get('/user', UserController::class . ':show')->add($jwtMiddleware)->setName('user.show');
        $this->put('/user', UserController::class . ':update')->add($jwtMiddleware)->setName('user.update');

        // Profile Routes
        $this->get('/profiles/{username}', ProfileController::class . ':show')
            ->add($optionalAuth)
            ->setName('profile.show');
        $this->post('/profiles/{username}/follow', ProfileController::class . ':follow')
            ->add($jwtMiddleware)
            ->setName('profile.follow');
        $this->delete('/profiles/{username}/follow', ProfileController::class . ':unfollow')
            ->add($jwtMiddleware)
            ->setName('profile.unfollow');


        // Bugs routes
        $this->get('/bugs/feed', BugController::class . ':index')->add($optionalAuth)->setName('bug.index');
        $this->get('/bugs/{slug}', BugController::class . ':show')->add($optionalAuth)->setName('bug.show');
        $this->put('/bugs/{slug}',
            BugController::class . ':update')->add($jwtMiddleware)->setName('bug.update');
        $this->delete('/bugs/{slug}',
            BugController::class . ':destroy')->add($jwtMiddleware)->setName('bug.destroy');
        $this->post('/bugs', BugController::class . ':store')->add($jwtMiddleware)->setName('bug.store');
        $this->get('/bugs', BugController::class . ':index')->add($optionalAuth)->setName('bug.index');

        // Comments
        $this->get('/bugs/{slug}/comments',
            CommentController::class . ':index')
            ->add($optionalAuth)
            ->setName('comment.index');
        $this->post('/bugs/{slug}/comments',
            CommentController::class . ':store')
            ->add($jwtMiddleware)
            ->setName('comment.store');
        $this->delete('/bugs/{slug}/comments/{id}',
            CommentController::class . ':destroy')
            ->add($jwtMiddleware)
            ->setName('comment.destroy');

        // Favourite Bug Routes
        $this->post('/bugs/{slug}/favourite',
            FavouriteController::class . ':store')
            ->add($jwtMiddleware)
            ->setName('favourite.store');
        $this->delete('/bugs/{slug}/favourite',
            FavouriteController::class . ':destroy')
            ->add($jwtMiddleware)
            ->setName('favourite.destroy');

        // Tags Route
        $this->get('/tags', function (Request $request, Response $response) {
            return $response->withJson([
                'tags' => Tag::all('title')->pluck('title'),
            ]);
        });
    });


// Routes

$app->get('/[{name}]',
    function (Request $request, Response $response, array $args) {
        // Sample log message
        $this->logger->info("Slim-Skeleton '/' route");

        // Render index view
        return $this->renderer->render($response, 'index.phtml', $args);
    });
