<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use jeremykenedy\LaravelLogger\App\Models\Activity;

class CreateLaravelLoggerActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $activity = new Activity();
        $connection = $activity->getConnectionName();
        $table = $activity->getTableName();
        $tableCheck = Schema::connection($connection)->hasTable($table);

        if (!$tableCheck) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->longText('description');
                $table->string('userType');
                $table->integer('userId')->nullable();
                $table->longText('route')->nullable();
                $table->ipAddress('ipAddress')->nullable();
                $table->text('userAgent')->nullable();
                $table->string('locale')->nullable();
                $table->longText('referer')->nullable();
                $table->string('methodType')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $activity = new Activity();
        $connection = $activity->getConnectionName();
        $table = $activity->getTableName();

        Schema::connection($connection)->dropIfExists($table);
    }
}
