<?php
namespace App\Console\Commands;
use App\Services\GeoDataImporter;
use Illuminate\Console\Command;
class GeoImportCommand extends Command {
 protected $signature='geo:import {--countries-only} {--cities-only} {--no-download}';
 protected $description='Import the global GeoNames country and populated-place datasets.';
 public function handle(GeoDataImporter $importer): int {
  $download=!$this->option('no-download');
  try {
   if($this->option('countries-only')) $report=['countries'=>$importer->importCountries($download)];
   elseif($this->option('cities-only')) $report=['cities'=>$importer->importCities($download)];
   else $report=$importer->import($download);
   $this->line(json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); return self::SUCCESS;
  } catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
 }
}