<?php

use Faker\Generator as Faker;

$factory->define(App\Fyp::class, function (Faker $faker) {
    return [
        'fyp_desc' => "{$faker->year()}-{$faker->year()}",
    ];
});
