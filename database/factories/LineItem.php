<?php

use Faker\Generator as Faker;

$factory->define(App\LineItem::class, function (Faker $faker) {
    return [
        'code' => $faker->numberBetween(100,350),
        'line2desc' => $faker->catchPhrase(),

    ];
});
