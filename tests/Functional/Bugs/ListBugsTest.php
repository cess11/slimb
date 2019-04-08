<?php

namespace Tests\Functional\Bugs;

use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class ListBugsTest extends BaseTestCase
{
    use UseDatabaseTrait;

    /** @test */
    public function returns_all_bugs()
    {
        $response = $this->runApp('GET', '/api/bugs');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
