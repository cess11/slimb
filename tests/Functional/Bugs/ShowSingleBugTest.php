<?php

namespace Tests\Functional\Bugs;

use Slimbug\Models\Bug;
use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class ShowSingleBugTest extends BaseTestCase
{
    use UseDatabaseTrait;

    /** @test */
    public function it_return_a_single_bug()
    {
        $user = $this->createUser();
        $bug = Bug::create([
            'slug'           => 'how-to-train-your-dragon',
            'title'          => 'How to train your dragon',
            'description'    => 'Ever wonder how?',
            'body'           => 'It takes a Jacobian',
            'user_id'      => $user->id,
        ]);

        $response = $this->request('GET', "/api/bugs/$bug->slug");
        $body = json_decode((string)$response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('bug', $body);
    }

    /** @test */
    public function it_return_the_correct_author_following_status()
    {
        $user = $this->createUser();
        $requestUser = $this->createUserWithValidToken();
        $requestUser->follow($user->id);
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $requestUser->token];

        $bug = $this->createBug(['user_id' => $user->id]);

        $response = $this->request(
            'GET',
            "/api/bugs/$bug->slug",
            null,
            $headers
        );

        $body = json_decode((string)$response->getBody(), true);
        $this->assertTrue($body['bug']['author']['following']);
    }
}
