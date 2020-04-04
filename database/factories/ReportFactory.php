<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Report;
use Faker\Generator as Faker;

$factory->define(Report::class, function (Faker $faker) {
    return [
        'reason' => $faker->sentence,
        'reporter_ip' => $faker->ipv4,
    ];
});

$factory->state(Report::class, 'demoted', [
    'promoted_at' => now(),
    'global' => false,
]);

$factory->state(Report::class, 'dismissed', [
    'is_dismissed' => true,
]);

$factory->state(Report::class, 'global', [
    'global' => true,
]);

$factory->state(Report::class, 'promoted', [
    'promoted_at' => now(),
    'global' => true,
]);

$factory->state(Report::class, 'successful', [
    'is_successful' => true,
]);
