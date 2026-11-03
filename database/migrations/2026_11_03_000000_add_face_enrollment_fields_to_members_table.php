<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('face_api_member_id', 36)->nullable()->unique();
            $table->string('face_enrollment_session_id', 36)->nullable();
            $table->string('face_enrollment_status', 20)->default('NOT_ENROLLED');
            $table->timestamp('face_enrolled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['face_api_member_id']);
            $table->dropColumn([
                'face_api_member_id',
                'face_enrollment_session_id',
                'face_enrollment_status',
                'face_enrolled_at',
            ]);
        });
    }
};
