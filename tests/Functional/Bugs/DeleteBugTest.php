<?php

namespace Tests\Functional\Bugs;

use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class DeleteBugTest extends BaseTestCase
{

    use UseDatabaseTrait;

    /** @test */
    public function an_authenticated_user_may_delete_a_bug()
    {
        $user = $this->createUserWithValidToken();
        $bug = $this->createBug(['user_id' => $user->id]);
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];

        $response = $this->request('DELETE', "/api/bugs/$bug->slug", null, $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseDoesNotHave('bugs', ['title' => $bug->title]);
    }

    /** @test */
    public function unauthenticated_users_may_not_send_request_to_delete_bugs()
    {
        $bug = $this->createBug();

        $response = $this->request('DELETE', "/api/bugs/$bug->slug");

        $this->assertEquals(401, $response->getStatusCode());
    }

    /** @test */
    public function only_the_owner_of_the_bug_can_delete_the_bug()
    {
        $bug = $this->createBug();
        $unauthorizedUser = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $unauthorizedUser->token];

        $response = $this->request('PUT', "/api/bugs/$bug->slug", null, $headers);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertDatabaseHas('bugs', ['description' => $bug->description]);
    }

}
