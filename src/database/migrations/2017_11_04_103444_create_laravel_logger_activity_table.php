<?php

use jeremykenedy\LaravelLogger\App\Models\Activity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaravelLoggerActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $activity       = new Activity;
        $connection     = $activity->getConnectionName();
        $table          = $activity->getTableName();
        $tableCheck     = Schema::connection($connection)->hasTable($table);

        if (!$tableCheck) {
            Schema::connection($connection)->create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->longText('description');
                $table->string('userType');
                $table->integer('userId')->nullable();
                $table->string('route')->nullable();
                $table->ipAddress('ipAddress')->nullable();
                $table->text('userAgent')->nullable();
                $table->string('locale')->nullable();
                $table->string('referer')->nullable();
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
        $activity       = new Activity;
        $connection     = $activity->getConnectionName();
        $table          = $activity->getTableName();

        Schema::connection($connection)->dropIfExists($table);
    }
}
