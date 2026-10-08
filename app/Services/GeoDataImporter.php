<?php
namespace App\Services;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;
use DOMDocument;
use DOMXPath;
use RuntimeException;
class GeoDataImporter
{
    public const GEONAMES_LICENSE = 'Creative Commons Attribution 4.0';
    public const GEONAMES_COUNTRIES = 'https://download.geonames.org/export/dump/countryInfo.txt';
    public const GEONAMES_ALL = 'https://download.geonames.org/export/dump/allCountries.zip';
    public const M49 = 'https://unstats.un.org/unsd/methodology/m49/overview';

    public function __construct(private readonly string $dataDir)
    {
        if (!is_dir($this->dataDir)) mkdir($this->dataDir, 0775, true);
    }

    public function importCountries(bool $download = true): array
    {
        $countryFile = $this->dataDir.'/countryInfo.txt';
        if ($download || !is_file($countryFile)) $this->download(self::GEONAMES_COUNTRIES, $countryFile);
        $m49 = $this->fetchM49();
        $rows = $this->readCountryInfo($countryFile);
        $now = now();
        $seen = [];
        $payload = [];
        foreach ($rows as $row) {
            $iso2 = strtoupper($row[0] ?? '');
            if (!preg_match('/^[A-Z]{2}$/', $iso2) || isset($seen[$iso2])) continue;
            $seen[$iso2] = true;
            $iso3 = strtoupper($row[1] ?? '');
            $numeric = str_pad((string)($row[2] ?? ''), 3, '0', STR_PAD_LEFT);
            $geoId = (int)($row[16] ?? 0);
            $meta = $m49[$iso3] ?? [];
            $payload[] = [
                'geoname_id'=>$geoId ?: crc32($iso2),
                'name'=>$row[4] ?? $iso3,
                'name_en'=>$row[4] ?? $iso3,
                'iso2'=>$iso2,'iso3'=>$iso3,'numeric_code'=>$numeric,
                'continent'=>$row[8] ?? null,'region'=>$meta['region'] ?? null,'subregion'=>$meta['subregion'] ?? null,
                'capital'=>$row[5] ?? null,'phone_code'=>$row[12] ?? null,'currency'=>$row[10] ?? null,
                'flag_code'=>strtolower($iso2),'source'=>'GeoNames','source_version'=>date('Y-m-d'),
                'imported_at'=>$now,'last_updated_at'=>$now,'license'=>self::GEONAMES_LICENSE,
                'created_at'=>$now,'updated_at'=>$now,
            ];
        }
        foreach (array_chunk($payload, 500) as $chunk) {
            Country::upsert($chunk, ['iso2'], array_keys($chunk[0]));
        }
        return ['source_rows'=>count($rows),'countries_imported'=>Country::count(),'duplicate_countries'=>count($rows)-count($seen)];
    }

    public function importCities(bool $download = true): array
    {
        $zipPath = $this->dataDir.'/allCountries.zip';
        if ($download || !is_file($zipPath)) $this->download(self::GEONAMES_ALL, $zipPath);
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) throw new RuntimeException('Unable to open GeoNames allCountries.zip');
        $stream = $zip->getStream('allCountries.txt');
        if (!$stream) { $zip->close(); throw new RuntimeException('allCountries.txt is missing from GeoNames archive.'); }
        $countries = Country::query()->pluck('id','iso2')->mapWithKeys(fn($id,$iso)=>[strtoupper($iso)=>$id])->all();
        if (!$countries) throw new RuntimeException('Import countries before cities.');
        $chunk=[]; $seen=0; $invalid=0; $missingCountries=[]; $now=now(); $countryPoints=[];
        while (($line=fgets($stream)) !== false) {
            $c = str_getcsv(rtrim($line, "\r\n"), "\t");
            if (count($c)<19 || ($c[6] ?? '') !== 'P') continue;
            $geoId=(int)$c[0]; $iso=strtoupper($c[8] ?? '');
            if (!$geoId || !isset($countries[$iso])) { if ($iso) $missingCountries[$iso]=true; continue; }
            $lat=(float)($c[4] ?? 0); $lng=(float)($c[5] ?? 0);
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) { $invalid++; continue; }
            $chunk[]=[
                'geoname_id'=>$geoId,'country_id'=>$countries[$iso],'name'=>$c[1] ?? '',
                'name_en'=>$c[1] ?? '','ascii_name'=>$c[2] ?? '',
                'latitude'=>$lat,'longitude'=>$lng,'population'=>max(0,(int)($c[14] ?? 0)),
                'feature_code'=>$c[7] ?? null,'admin1'=>$c[10] ?? null,'admin2'=>$c[11] ?? null,
                'timezone'=>$c[17] ?? null,'elevation'=>($c[15] ?? '') === '' ? null : (int)$c[15],
                'alternate_names'=>$c[3] ?? null,'source'=>'GeoNames','source_version'=>date('Y-m-d'),
                'imported_at'=>$now,'last_updated_at'=>$now,'license'=>self::GEONAMES_LICENSE,
                'created_at'=>$now,'updated_at'=>$now,
            ];
            if (($c[7] ?? '') === 'PPLC') $countryPoints[$iso]=[$lat,$lng,$c[17] ?? null];
            $seen++;
            if (count($chunk)>=1000) { City::upsert($chunk,['geoname_id'],array_keys($chunk[0])); $chunk=[]; }
        }
        if ($chunk) City::upsert($chunk,['geoname_id'],array_keys($chunk[0]));
        fclose($stream); $zip->close();
        foreach ($countryPoints as $iso=>$point) {
            Country::where('iso2',$iso)->update(['latitude'=>$point[0],'longitude'=>$point[1],'timezone'=>$point[2],'updated_at'=>$now]);
        }
        return [
            'source_rows'=>$seen,'cities_imported'=>City::count(),'invalid_coordinates'=>$invalid,
            'countries_missing_from_mapping'=>array_keys($missingCountries),'duplicate_cities'=>0,
        ];
    }

    public function import(bool $download = true): array
    {
        $countries=$this->importCountries($download);
        $cities=$this->importCities($download);
        return [
            'countries'=>$countries,'cities'=>$cities,
            'countries_missing'=>max(0,$countries['source_rows']-$countries['countries_imported']),
            'completed_at'=>now()->toIso8601String(),
        ];
    }

    private function download(string $url,string $path): void
    {
        Http::timeout(600)->retry(3,1000)->withOptions(['sink'=>$path])->get($url)->throw();
    }

    private function readCountryInfo(string $path): array
    {
        $rows=[];
        $handle=fopen($path,'rb');
        while (($line=fgets($handle))!==false) {
            if ($line==='' || str_starts_with($line,'#')) continue;
            $rows[]=str_getcsv(rtrim($line,"\r\n"),"\t");
        }
        fclose($handle); return $rows;
    }

    private function fetchM49(): array
    {
        try {
            $html=Http::timeout(60)->get(self::M49)->throw()->body();
            $dom=new DOMDocument(); @$dom->loadHTML($html);
            $xpath=new DOMXPath($dom); $map=[];
            foreach ($xpath->query('//table//tr') as $tr) {
                $cells=[];
                foreach ($xpath->query('./th|./td',$tr) as $cell) $cells[]=trim(preg_replace('/\s+/u',' ',$cell->textContent));
                if (count($cells)<12) continue;
                $iso3=strtoupper($cells[11] ?? '');
                if (!preg_match('/^[A-Z]{3}$/',$iso3)) continue;
                $map[$iso3]=['region'=>$cells[3] ?? null,'subregion'=>$cells[5] ?? null];
            }
            return $map;
        } catch (\Throwable) {
            return [];
        }
    }
}