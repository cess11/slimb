<?php

namespace Tests\Functional\Comments;

use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class AddCommentTest extends BaseTestCase
{

    use UseDatabaseTrait;

    /** @test */
    public function an_authenticated_user_may_comment_on_a_bug()
    {
        $bug = $this->createBug();
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];

        $payload = [
            'comment' => [
                'body' => 'His name was my name too.',
            ],
        ];

        $response = $this->request(
            'POST',
            "/api/bugs/$bug->slug/comments",
            $payload,
            $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('comments', ['body' => 'His name was my name too.']);
        $this->assertEquals(1, $bug->comments()->count());
        $this->assertEquals(1, $user->comments()->count());
    }


    /** @test */
    public function un_unauthenticated_may_not_post_new_comment()
    {
        $bug = $this->createBug();
        $response = $this->request('POST', "/api/bugs/$bug->slug/comments");

        $this->assertEquals(401, $response->getStatusCode());
    }

}
