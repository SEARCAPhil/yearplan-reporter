<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        // Create 10 records of users
        factory(App\User::class, 10)->create()->each(function ($user) {
            //objectives
            $objectives = factory(App\Objective::class, 4)->make();
            $user->objectives()->saveMany($objectives);

            $objectives->each(function($obj) {
                // operational bjectives
                $operationalObjectives = factory(App\OperationalObjective::class, 3)->make();
                $obj->operational_objectives()->saveMany($operationalObjectives);

                $operationalObjectives->each(function ($ob) {
                    // activities
                    $activities = factory(App\Activity::class, 2)->make();
                    $ob->activities()->saveMany($activities);

                    $activities->each(function ($act) {
                        $line_items = factory(App\BudgetaryRequirement::class, 2)->make();
                        $act->budgetary_requirements()->saveMany($line_items);
                    });

                });
            });
        });

        // fyp
        factory(App\Fyp::class, 3)->create()->each(function($fyp) {
            $yearplans = factory(App\YearPlan::class, 5)->make();
            $fyp->year_plans()->saveMany($yearplans);
        });

        // line item
        factory(App\LineItem::class, 5)->create();


    }
}
