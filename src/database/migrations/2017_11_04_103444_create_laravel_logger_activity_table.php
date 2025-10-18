<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use jeremykenedy\LaravelLogger\App\Models\Activity;

class CreateLaravelLoggerActivityTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $activity = new Activity();
        $connection = $activity->getConnectionName();
        $table = $activity->getTableName();
        $tableCheck = Schema::connection($connection)->hasTable($table);

        if (!$tableCheck) {
            Schema::connection($connection)->create($table, function (Blueprint $table): void {
                $table->id();
                $table->longText('description');
                $table->longText('details')->nullable();
                $table->string('userType');
                $table->unsignedBigInteger('userId')->nullable();
                $table->longText('route')->nullable();
                $table->ipAddress('ipAddress')->nullable();
                $table->text('userAgent')->nullable();
                $table->string('locale')->nullable();
                $table->longText('referer')->nullable();
                $table->string('methodType')->nullable();
                $table->unsignedBigInteger('relId')->index()->nullable();
                $table->string('relModel')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $activity = new Activity();
        $connection = $activity->getConnectionName();
        $table = $activity->getTableName();

        Schema::connection($connection)->dropIfExists($table);
    }
}
