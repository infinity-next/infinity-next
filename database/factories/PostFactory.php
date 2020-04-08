<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'bumped_last' => now(),
        'created_at' => now(),
        'updated_at' => null,

        'author_ip' => $faker->ipv4,
        'subject' => $faker->sentence,
        'author' => $faker->name,
        'email' => $faker->email,
        'password' => $faker->password,

        'body' => $faker->paragraph(3, true),
    ];
});
