<?php

use Faker\Generator as Faker;

$factory->define(App\Activity::class, function (Faker $faker) {
    return [
        'activitydesc' => $faker->sentence(20),
    ];
});
