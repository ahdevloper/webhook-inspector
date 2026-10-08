<?php
namespace App\Console\Commands;
use App\Services\GeoDataImporter;
use Illuminate\Console\Command;
class GeoUpdateCommand extends Command {
 protected $signature='geo:update {--countries-only} {--cities-only}';
 protected $description='Refresh global geographic data from the configured open-data sources.';
 public function handle(GeoDataImporter $importer): int {
  try {
   $report=$this->option('countries-only')?['countries'=>$importer->importCountries(true)]:($this->option('cities-only')?['cities'=>$importer->importCities(true)]:$importer->import(true));
   $this->line(json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); return self::SUCCESS;
  } catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
 }
}