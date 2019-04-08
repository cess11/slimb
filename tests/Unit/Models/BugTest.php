<?php

namespace Tests\Unit\Models;

use Slimbug\Models\Bug;
use Slimbug\Models\Comment;
use Slimbug\Models\Tag;
use Slimbug\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class BugTest extends BaseTestCase
{

    use UseDatabaseTrait;

    /** @test */
    public function a_bug_can_have_many_tags()
    {
        $bug = new Bug();

        $this->assertInstanceOf(BelongsToMany::class, $bug->tags());
        $this->assertInstanceOf(Tag::class, $bug->tags()->getRelated());
    }

    /** @test */
    public function a_bug_may_have_many_comments()
    {
        $bug = new Bug();

        $this->assertInstanceOf(HasMany::class, $bug->comments());
        $this->assertInstanceOf(Comment::class, $bug->comments()->getRelated());
    }

    /** @test */
    public function a_bug_has_an_author()
    {
        $bug = new Bug();

        $this->assertInstanceOf(BelongsTo::class, $bug->user());
        $this->assertInstanceOf(User::class, $bug->user()->getRelated());
    }

    /** @test */
    public function it_can_be_favourited_by_users()
    {
        $bug = new Bug();

        $this->assertInstanceOf(BelongsToMany::class, $bug->favourites());
        $this->assertInstanceOf(User::class, $bug->favourites()->getRelated());
    }
}
