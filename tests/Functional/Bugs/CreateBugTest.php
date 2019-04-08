<?php

namespace Tests\Functional\Bugs;

use Slimbug\Models\Bug;
use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class CreateBugTest extends BaseTestCase
{

    use UseDatabaseTrait;

    /** @test */
    public function un_authenticated_user_may_post_new_bug()
    {
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];
        $payload = [
            'bug' => [
                'title'       => 'How to train your dragon',
                'description' => 'Ever wonder how?',
                'body'        => 'You have to believe',
            ],
        ];

        $response = $this->request('POST', '/api/bugs', $payload, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('bugs', ['description' => 'Ever wonder how?']);
    }


    /** @test */
    public function un_unauthenticated_may_not_post_bugs()
    {
        $response = $this->request('POST', '/api/bugs');

        $this->assertEquals(401, $response->getStatusCode());
    }

    /** @test */
    public function create_bug_require_title()
    {
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];
        $payload = [
            'bug' => [
                'description' => 'Ever wonder how?',
                'body'        => 'You have to believe',
            ],
        ];

        $response = $this->request('POST', '/api/bugs', $payload, $headers);
        $body = json_decode((string)$response->getBody(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('title', $body['errors']);
    }

    /** @test */
    public function create_bug_require_description()
    {
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];
        $payload = [
            'bug' => [
                'title' => 'How to train your dragon',
                'body'  => 'You have to believe',
            ],
        ];

        $response = $this->request('POST', '/api/bugs', $payload, $headers);
        $body = json_decode((string)$response->getBody(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('description', $body['errors']);
    }

    /** @test */
    public function create_bug_require_body()
    {
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];
        $payload = [
            'bug' => [
                'title'       => 'How to train your dragon',
                'description' => 'Ever wonder how?',
            ],
        ];

        $response = $this->request('POST', '/api/bugs', $payload, $headers);
        $body = json_decode((string)$response->getBody(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('body', $body['errors']);
    }

    /** @test */
    public function user_can_add_tags_when_creating_bugs()
    {
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];
        $payload = [
            'bug' => [
                'title'       => 'How to train your dragon',
                'description' => 'Ever wonder how?',
                'body'        => 'You have to believe',
                'tagList'     => ['reactjs', 'angularjs', 'dragons'],
            ],
        ];

        $this->request('POST', '/api/bugs', $payload, $headers);

        $this->assertDatabaseHas('tags', ['title' => 'reactjs']);
        $this->assertEquals(3, Bug::where('title', 'How to train your dragon')->first()->tags()->count());
    }


    /** @test */
    public function creating_new_bugs_does_not_duplicate_slugs()
    {
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];

        $existingBug = Bug::create([
            'title'       => $title = 'How to train your dragon',
            'slug'        => str_slug($title),
            'description' => 'Ever wonder how?',
            'body'        => 'You have to believe',
            'user_id'     => $user->id,
        ]);

        $payload = [
            'bug' => [
                'title'       => 'How to train your dragon',
                'description' => 'Ever wonder how?',
                'body'        => 'You have to believe',
            ],
        ];

        $response = $this->request('POST', '/api/bugs', $payload, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('bugs', ['description' => 'Ever wonder how?']);
    }

}
