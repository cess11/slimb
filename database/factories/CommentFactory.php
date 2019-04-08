<?php

use Slimbug\Models\Bug;
use Slimbug\Models\Comment;
use Slimbug\Models\User;

$this->factory->define(Comment::class, function (\Faker\Generator $faker) {
        return [
            'body'        => $faker->sentences(rand(1,5), true),
            'bug_id'   => function () {
                return $this->factory->of(Bug::class)->create()->id;
            },
            'user_id'   => function () {
                return $this->factory->of(User::class)->create()->id;
            },
        ];
    });

