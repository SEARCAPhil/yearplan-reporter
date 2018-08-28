<?php

use Faker\Generator as Faker;

$factory->define(App\BudgetaryRequirement::class, function (Faker $faker) {
    return [
        'line2id' => $faker->numberBetween(1,5),
        'lineitem' =>$faker->catchPhrase(), 
        'peso' => $faker->numberBetween(0, 999),
        'dollar' => $faker->numberBetween(0, 999),
        'remarks' =>"remarks: {$faker->catchPhrase()}", 
    ];
});
