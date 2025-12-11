<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use jeremykenedy\LaravelLogger\App\Models\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $activity = new Activity();
        $connection = $activity->getConnectionName();
        $tableName = $activity->getTableName();
        $schema = Schema::connection($connection ?? config('database.default'));

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $schema->table($tableName, function (Blueprint $table) use ($schema, $tableName) {
            if (! $schema->hasColumn($tableName, 'relId')) {
                $table->unsignedBigInteger('relId')->index()->nullable();
            }

            if (! $schema->hasColumn($tableName, 'relModel')) {
                $table->string('relModel')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $activity = new Activity();
        $connection = $activity->getConnectionName();
        $tableName = $activity->getTableName();
        $schema = Schema::connection($connection ?? config('database.default'));

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $schema->table($tableName, function (Blueprint $table) use ($schema, $tableName) {
            if ($schema->hasColumn($tableName, 'relId')) {
                $table->dropColumn('relId');
            }

            if ($schema->hasColumn($tableName, 'relModel')) {
                $table->dropColumn('relModel');
            }
        });
    }
};
