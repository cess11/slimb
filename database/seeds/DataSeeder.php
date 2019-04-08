<?php

use Slimbug\Models\Bug;
use Slimbug\Models\Comment;
use Slimbug\Models\Tag;
use Slimbug\Models\User;

class DataSeeder extends BaseSeeder
{

    protected $usersCount = 20;

    protected $tags = [
        "core", 
        "staging", 
        "DB", 
        "controller", 
        "frontend", 
        "critical", 
        "trivial", 
        "confirmed",
    ];

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $users = $this->factory->of(User::class)->times($this->usersCount)->create();

        $tags = collect($this->tags)->map(function ($tag) {
            return Tag::create(['title' => $tag]);
        });

        $users->random($this->usersCount * 0.75)->each(function ($user) use ($tags) {
            $this->factory->of(Bug::class)->times(rand(1, 5))->create([
                'user_id' => $user->id,
            ])->each(function (Bug $bug) use ($tags) {
                $bug->tags()->sync($tags->random()->pluck('id')->toArray());
            });
        });

        $bugs = Bug::all();

        $bugs->each(function (Bug $bug) {
            $this->factory->of(Comment::class)->times(rand(0, 5))->create(
                ['bug_id' => $bug->id, 'user_id' => $this->faker->numberBetween(1, $this->usersCount)]);
        });

        $bugs->each(function (Bug $bug) use ($users) {
            $bug->favourites()->sync($users->random()->pluck('id')->toArray());
        });

        $users->each(function (User $user) use ($users) {
            $user->followings()->sync($users->random(rand(0, 10))->pluck('id')->toArray());
        });
    }
}
