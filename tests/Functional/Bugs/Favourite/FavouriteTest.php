<?php

namespace Tests\Functional\Comments;

use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class FavouriteTest extends BaseTestCase
{

    use UseDatabaseTrait;

    /** @test */
    public function an_authenticated_user_may_favourite_a_bug()
    {
        $bug = $this->createBug();
        $user = $this->createUserWithValidToken();
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];

        $response = $this->request(
            'POST',
            "/api/bugs/$bug->slug/favourite",
            null,
            $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('user_favourite', ['user_id' => $user->id, 'bug_id' => $bug->id]);
        $this->assertEquals(1, $user->favouriteBugs()->count());
        $this->assertEquals(1, $bug->favourites()->count());
    }
    
    /** @test */
    public function unauthenticated_may_not_favourite_bugs()
    {
        $bug = $this->createBug();
        $response = $this->request('POST', "/api/bugs/$bug->slug/favourite");

        $this->assertEquals(401, $response->getStatusCode());
    }

    /** @test */
    public function an_authenticated_user_may_unfavourite_a_bug()
    {
        $user = $this->createUserWithValidToken();
        $bug = $this->createBug();
        $user->favouriteBugs()->sync($bug->id, false);
        $this->assertEquals(1, $bug->favourites()->count());
        $headers = ['HTTP_AUTHORIZATION' => 'Token ' . $user->token];

        $response = $this->request('DELETE',
            "/api/bugs/$bug->slug/favourite",
            null,
            $headers);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseDoesNotHave('user_favourite', ['user_id' => $user->id, 'bug_id' => $bug->id]);
    }

    /** @test */
    public function unauthenticated_users_may_not_send_request_to_unfavourite_bugs()
    {
        $bug = $this->createBug();

        $response = $this->request('DELETE',
            "/api/bugs/$bug->slug/favourite"
        );

        $this->assertEquals(401, $response->getStatusCode());
    }
}
