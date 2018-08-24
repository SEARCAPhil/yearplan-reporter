<?php

use Faker\Generator as Faker;

$factory->define(App\OperationalObjective::class, function (Faker $faker) {
    return [
        'operationalobjective' => $faker->catchPhrase(),
    ];
});
