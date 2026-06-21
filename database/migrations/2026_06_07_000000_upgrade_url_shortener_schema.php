<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            if (! Schema::hasColumn('urls', 'short_code')) {
                $table->string('short_code', 20)->nullable()->unique()->after('original_url');
            }

            if (! Schema::hasColumn('urls', 'title')) {
                $table->string('title')->nullable()->after('short_code');
            }

            if (! Schema::hasColumn('urls', 'clicks_count')) {
                $table->unsignedBigInteger('clicks_count')->default(0)->after('title');
            }

            if (! Schema::hasColumn('urls', 'last_clicked_at')) {
                $table->timestamp('last_clicked_at')->nullable()->after('clicks_count');
            }

            if (! Schema::hasColumn('urls', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_clicked_at');
            }

            if (! Schema::hasColumn('urls', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('expires_at');
            }
        });

        Schema::table('clicks', function (Blueprint $table) {
            if (! Schema::hasColumn('clicks', 'url_id')) {
                $table->foreignId('url_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('clicks', 'ip_hash')) {
                $table->string('ip_hash', 64)->nullable()->after('url_id');
            }

            if (! Schema::hasColumn('clicks', 'user_agent_hash')) {
                $table->string('user_agent_hash', 64)->nullable()->after('ip_hash');
            }

            if (! Schema::hasColumn('clicks', 'referer')) {
                $table->string('referer')->nullable()->after('user_agent_hash');
            }

            if (! Schema::hasColumn('clicks', 'country')) {
                $table->string('country', 2)->nullable()->after('referer');
            }

            $table->index(['url_id', 'created_at'], 'clicks_url_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            if (Schema::hasColumn('clicks', 'url_id')) {
                $table->dropConstrainedForeignId('url_id');
            }

            foreach (['country', 'referer', 'user_agent_hash', 'ip_hash'] as $column) {
                if (Schema::hasColumn('clicks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('urls', function (Blueprint $table) {
            foreach (['is_active', 'expires_at', 'last_clicked_at', 'clicks_count', 'title', 'short_code'] as $column) {
                if (Schema::hasColumn('urls', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
