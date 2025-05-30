<?php

use Laravel\Passport\Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Migrations\Migration;

class CreatePassportPersonalClientCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the personal access client already exists
        if (!Client::where('personal_access_client', true)->exists()) {
            // Create the personal access client
            Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'Personal Access Client',
                '--no-interaction' => true
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Client::where('personal_access_client', true)->delete();
    }
}
