<?php

use Faker\Generator as Faker;

$factory->define(App\YearPlan::class, function (Faker $faker) {
    return [
        'yeardesc' => "{$faker->year()}-{$faker->year()}",
        'exchangerate' => (int) $faker->realText($faker->numberBetween(45,60)),
    ];
});
