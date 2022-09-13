<?php

use App\Enums\MovieStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('view');
            $table->string('name')->unique();
            $table->date('publication_time');
            $table->string('company')->nullable();
            $table->json('url_video')->nullable();
            $table->integer('limit_age')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_series')->default(false);
            $table->integer('movie_duration')->nullable();
            $table->string('status')->default(MovieStatus::Draft);
            $table->foreignId('director_id')->constrained('directors');
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
};
