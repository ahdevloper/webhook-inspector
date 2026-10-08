<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('geoname_id')->unique();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('name_en', 200);
            $table->string('ascii_name', 200);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedBigInteger('population')->default(0);
            $table->string('feature_code', 20)->nullable()->index();
            $table->string('admin1', 20)->nullable()->index();
            $table->string('admin2', 80)->nullable()->index();
            $table->string('timezone', 64)->nullable();
            $table->integer('elevation')->nullable();
            $table->longText('alternate_names')->nullable();
            $table->string('source', 100)->default('geonames');
            $table->string('source_version', 64)->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->string('license', 255)->nullable();
            $table->timestamps();
            $table->index(['country_id','name']);
            $table->index(['country_id','name_en']);
            $table->index(['country_id','ascii_name']);
            $table->index('population');
            $table->index(['latitude','longitude']);
            $table->index(['name_en','ascii_name']);
        });
    }
    public function down(): void { Schema::dropIfExists('cities'); }
};