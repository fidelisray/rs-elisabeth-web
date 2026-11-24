<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glossary_term_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100);
            $table->date('stat_date');
            $table->unsignedInteger('search_clicks')->default(0);
            $table->unsignedInteger('directory_views')->default(0);
            $table->unsignedInteger('direct_views')->default(0);
            $table->unsignedInteger('internal_nav_views')->default(0);
            $table->unsignedInteger('popular_clicks')->default(0);
            $table->timestamps();

            $table->unique(['slug', 'stat_date'], 'glossary_term_daily_stats_slug_date_unique');
            $table->index('stat_date', 'glossary_term_daily_stats_stat_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary_term_daily_stats');
    }
};
