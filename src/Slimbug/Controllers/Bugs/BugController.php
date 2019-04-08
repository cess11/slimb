<?php

namespace Slimbug\Controllers\Bugs;

use Slimbug\Models\Bug;
use Slimbug\Models\Tag;
use Slimbug\Transformers\BugTransformer;
use Interop\Container\ContainerInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

class BugController
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
     * BugController constructor.
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
     * Return bugs
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $requestUserId = optional($requestUser = $this->auth->requestUser($request))->id;
        $builder = Bug::query()->latest()->with(['tags', 'user'])->limit(20);


        if ($request->getUri()->getPath() == '/api/bugs/feed') {
            if (is_null($requestUser)) {
                return $response->withJson([], 401);
            }
            $ids = $requestUser->followings->pluck('id');
            $builder->whereIn('user_id', $ids);
        }

        if ($author = $request->getParam('author')) {
            $builder->whereHas('user', function ($query) use ($author) {
                $query->where('username', $author);
            });
        }

        if ($tag = $request->getParam('tag')) {
            $builder->whereHas('tags', function ($query) use ($tag) {
                $query->where('title', $tag);
            });
        }

        if ($favouriteByUser = $request->getParam('favourited')) {
            $builder->whereHas('favourites', function ($query) use ($favouriteByUser) {
                $query->where('username', $favouriteByUser);
            });
        }

        if ($limit = $request->getParam('limit')) {
            $builder->limit($limit);
        }

        if ($offset = $request->getParam('offset')) {
            $builder->offset($offset);
        }

        $bugCount = $builder->count();
        $bug = $builder->get();

        $data = $this->fractal->createData(new Collection($bug,
            new BugTransformer($requestUserId)))->toArray();

        return $response->withJson(
            [
                'bug' => $data['data'], 
                'bugCount' => $bugCount
            ]);
    }

    /**
     * Return a single bug
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function show(Request $request, Response $response, array $args)
    {
        $requestUserId = optional($this->auth->requestUser($request))->id;

        $bug= Bug::query()->where('slug', $args['slug'])->firstOrFail();

        $data = $this->fractal->createData(
            new Item(
                $bug, 
                new BugTransformer($requestUserId)
            ))->toArray();

        return $response->withJson(['bug' => $data]);
    }

    /**
     * Create and store a new bug
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @return Response
     */
    public function store(Request $request, Response $response)
    {
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        $this->validator->validateArray($data = $request->getParam('bug'),
            [
                'title'       => v::notEmpty(),
                'description' => v::notEmpty(),
                'body'        => v::notEmpty(),
            ]);

        if ($this->validator->failed()) {
            return $response->withJson(['errors' => $this->validator->getErrors()], 422);
        }

        $bug = new Bug($request->getParam('bug'));
        $bug->slug = str_slug($bug->title);
        $bug->user_id = $requestUser->id;
        $bug->save();

        $tagsId = [];
        if (isset($data['tagList'])) {
            foreach ($data['tagList'] as $tag) {
                $tagsId[] = Tag::updateOrCreate(['title' => $tag], ['title' => $tag])->id;
            }
            $bug->tags()->sync($tagsId);
        }

        $data = $this->fractal->createData(new Item($bug, new BugTransformer()))->toArray();

        return $response->withJson(['bug' => $data]);

    }

    /**
     * Update Bug Endpoint
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function update(Request $request, Response $response, array $args)
    {
        $bug = Bug::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        if ($requestUser->id != $bug->user_id) {
            return $response->withJson(['message' => 'Forbidden'], 403);
        }

        $params = $request->getParam('bug', []);

        $bug->update([
            'title'       => isset($params['title']) ? $params['title'] : $bug->title,
            'description' => isset($params['description']) ? $params['description'] : $bug->description,
            'body'        => isset($params['body']) ? $params['body'] : $bug->body,
        ]);

        if (isset($params['title'])) {
            $bug->slug = str_slug($params['title']);
        }

        $data = $this->fractal->createData(new Item($bug, new BugTransformer()))->toArray();

        return $response->withJson(['bug' => $data]);
    }

    /**
     * Delete Bug Endpoint
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param array               $args
     *
     * @return \Slim\Http\Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        $bug = Bug::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }

        if ($requestUser->id != $bug->user_id) {
            return $response->withJson(['message' => 'Forbidden'], 403);
        }

        $bug->delete();

        return $response->withJson([], 200);
    }

}
