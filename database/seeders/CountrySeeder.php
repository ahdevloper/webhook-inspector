<?php
namespace Database\Seeders;
use App\Services\GeoDataImporter;
use Illuminate\Database\Seeder;
class CountrySeeder extends Seeder { public function run(): void { app(GeoDataImporter::class)->importCountries(true); } }