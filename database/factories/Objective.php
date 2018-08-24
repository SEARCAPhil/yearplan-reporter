<?php

use Faker\Generator as Faker;

$factory->define(App\Objective::class, function (Faker $faker) {
    return [
        'objectives' => $faker->sentence(6),
        'fyid' => $faker->numberBetween(0,5),
    ];
});
