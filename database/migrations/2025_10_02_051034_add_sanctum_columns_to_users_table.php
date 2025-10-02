<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add name column (generated from first_name + last_name for existing rows)
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            // Add email_verified_at column
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            // Add is_admin column (default to true for existing users, false for new)
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('password');
            }

            // Add is_approved column (default to true for existing users)
            if (!Schema::hasColumn('users', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('is_admin');
            }

            // Add timestamps if they don't exist
            if (!Schema::hasColumn('users', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Update existing rows with generated name and approve existing users
        DB::connection('vantripper_db')->statement("
            UPDATE users
            SET
                name = CONCAT(first_name, ' ', last_name),
                email_verified_at = NOW(),
                is_admin = IF(position LIKE '%admin%' OR position LIKE '%Admin%', 1, 0),
                is_approved = 1,
                created_at = COALESCE(created_at, date_of_joining),
                updated_at = COALESCE(updated_at, NOW())
            WHERE name IS NULL OR name = ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove Sanctum columns (only if they exist)
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
            if (Schema::hasColumn('users', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
};
