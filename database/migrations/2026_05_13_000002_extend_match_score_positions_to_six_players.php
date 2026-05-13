<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE match_scores MODIFY posisi ENUM('juara','runner_up','ketiga','keempat','kelima','keenam','none') NOT NULL DEFAULT 'none'");
    }

    public function down(): void
    {
        DB::statement("UPDATE match_scores SET posisi = 'none' WHERE posisi IN ('kelima','keenam')");
        DB::statement("ALTER TABLE match_scores MODIFY posisi ENUM('juara','runner_up','ketiga','keempat','none') NOT NULL DEFAULT 'none'");
    }
};
