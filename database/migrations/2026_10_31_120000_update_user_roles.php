<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema role baru (perintah Nandz 31 Okt 2026):
     * admin -> super_admin, staf -> team_leader,
     * yogie@bankjakarta.co.id -> department_head.
     *
     * Catatan deploy (fork nakgamers): pernyataan ALTER ... MODIFY ... ENUM
     * hanya valid di MySQL. Di driver lain (SQLite) pelebaran/persempitan
     * enum dilakukan lewat Blueprint ->change() dengan hasil akhir setara.
     * Perilaku di MySQL tidak berubah sama sekali.
     */
    public function up(): void
    {
        // 1) Lebarkan enum: nilai lama & baru sama-sama sah sementara.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf','super_admin','department_head','team_leader') NOT NULL DEFAULT 'staf'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'staf', 'super_admin', 'department_head', 'team_leader'])->default('staf')->change();
            });
        }

        // 2) Petakan data lama ke role baru.
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'staf')->update(['role' => 'team_leader']);
        DB::table('users')->where('email', 'yogie@bankjakarta.co.id')->update(['role' => 'department_head']);

        // 3) Persempit ke tiga role final.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','department_head','team_leader') NOT NULL DEFAULT 'team_leader'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'department_head', 'team_leader'])->default('team_leader')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf','super_admin','department_head','team_leader') NOT NULL DEFAULT 'staf'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'staf', 'super_admin', 'department_head', 'team_leader'])->default('staf')->change();
            });
        }

        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', ['department_head', 'team_leader'])->update(['role' => 'staf']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf') NOT NULL DEFAULT 'staf'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'staf'])->default('staf')->change();
            });
        }
    }
};
