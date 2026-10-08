<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('geoname_id')->unique();
            $table->string('name', 200);
            $table->string('name_en', 200);
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->unique();
            $table->string('numeric_code', 3)->nullable()->index();
            $table->string('continent', 2)->nullable()->index();
            $table->string('region', 100)->nullable()->index();
            $table->string('subregion', 100)->nullable()->index();
            $table->string('capital', 200)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->string('phone_code', 32)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('flag_code', 10)->nullable();
            $table->string('source', 100)->default('geonames');
            $table->string('source_version', 64)->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->string('license', 255)->nullable();
            $table->timestamps();
            $table->index(['continent','region','subregion']);
        });
    }
    public function down(): void { Schema::dropIfExists('countries'); }
};