<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Report;
use Faker\Generator as Faker;

$factory->define(Report::class, function (Faker $faker) {
    return [
        'reason' => $faker->sentence,
        'reporter_ip' => $faker->ip,
    ];
});
