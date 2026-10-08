<?php
namespace Database\Seeders;
use App\Services\GeoDataImporter;
use Illuminate\Database\Seeder;
class CitySeeder extends Seeder { public function run(): void { app(GeoDataImporter::class)->importCities(true); } }