<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Board;
use App\User;
use Faker\Generator as Faker;

$factory->define(Board::class, function (Faker $faker) {
    $creator = factory(User::class)->create();

    return [
        'board_uri' => $faker->unique()->word,
        'title' => $faker->words(3, true),
        'description' => $faker->sentence,
        'created_at' => now(),
        'created_by' => $creator->user_id,
        'operated_by' => $creator->user_id,
        'posts_total' => 0,
        'is_indexed' => true,
        'is_overboard' => true,
        'is_worksafe' => false,
        'last_post_at' => null,
        'featured_at' => null,
    ];
});
