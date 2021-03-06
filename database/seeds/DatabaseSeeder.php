<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SeniorDesignTitlesSeeder::class);
        $this->call(SeniorDesignTermsSeeder::class);
    }
}
