<?php

namespace Slimbug\Controllers\Bug;

use Slimbug\Models\Bug;
use Slimbug\Models\Comment;
use Slimbug\Transformers\BugTransformer;
use Slimbug\Transformers\CommentTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class CommentController
{

    /** @var \Slimbug\Validation\Validator */
    protected $validator;
    /** @var \Illuminate\Database\Capsule\Manager */
    protected $db;
    /** @var \Slimbug\Services\Auth\Auth */
    protected $auth;
    /** @var \League\Fractal\Manager */
    protected $fractal;

    /**
     * UserController constructor.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @internal param $auth
     */
    public function __construct(ContainerInterface $container)
    {
        $this->auth = $container->get('auth');
        $this->fractal = $container->get('fractal');
        $this->validator = $container->get('validator');
        $this->db = $container->get('db');
    }

    /**
     * Return all comments for bug
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $requestUserId = optional($this->auth->requestUser($request))->id;

        $bug = Bug::query()->with('comments')
                           ->where('slug', $args['slug'])
                           ->firstOrFail();

        $data = $this->fractal->createData(new Collection(
            $bug->comments,
            new CommentTransformer($requestUserId)
        ))->toArray();

        return $response->withJson(['comments' => $data['data']]);
    }

    /**
     * Create a new comment
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function store(Request $request, Response $response, array $args)
    {
        $bug = Bug::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        $this->validator->validateArray($data = $request->getParam('comment'),
            [
                'body' => v::notEmpty(),
            ]);

        if ($this->validator->failed()) {
            return $response->withJson(
                [
                    'errors' => $this->validator->getErrors()
                ], 
                422
            );
        }

        $comment = Comment::create([
            'body'       => $data['body'],
            'user_id'    => $requestUser->id,
            'bug_id'     => $bug->id,
        ]);

        $data = $this->fractal->createData(new Item(
            $comment, 
            new CommentTransformer()
        ))->toArray();

        return $response->withJson(['comment' => $data]);

    }

    /**
     * Delete A Comment Endpoint
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        $comment = Comment::query()->findOrFail($args['id']);
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        if ($requestUser->id != $comment->user_id) {
            return $response->withJson(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return $response->withJson([], 200);
    }

}
