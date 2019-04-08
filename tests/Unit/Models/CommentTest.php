<?php

namespace Tests\Unit\Models;

use Slimbug\Models\Bug;
use Slimbug\Models\Comment;
use Slimbug\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\BaseTestCase;
use Tests\UseDatabaseTrait;

class CommentTest extends BaseTestCase
{

    use UseDatabaseTrait;

    /** @test */
    public function a_comment_belongs_to_a_bug()
    {
        $comment = new Comment();

        $this->assertInstanceOf(BelongsTo::class, $comment->bug());
        $this->assertInstanceOf(Bug::class, $comment->bug()->getRelated());
    }

    /** @test */
    public function a_comment_belongs_to_a_user()
    {
        $comment = new Comment();

        $this->assertInstanceOf(BelongsTo::class, $comment->user());
        $this->assertInstanceOf(User::class, $comment->user()->getRelated());

    }
}
