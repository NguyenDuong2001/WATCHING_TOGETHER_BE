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
            $table->string('name');
            $table->string('video')->nullable();
            $table->integer('time')->nullable();
            $table->string('poster')->nullable();
            $table->string('traller')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_series')->default(false);
            $table->string('status')->default(MovieStatus::Draft);
            $table->foreignId('year_id')->constrained('years');
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('director_id')->constrained('directors');
            $table->foreignId('company_id')->nullable()->constrained('companies');
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
