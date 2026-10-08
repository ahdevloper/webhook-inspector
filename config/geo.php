<?php
return [
 'data_dir'=>storage_path('app/geo'),
 'sources'=>[
  'geonames'=>[
   'license'=>'Creative Commons Attribution 4.0',
   'countries'=>'https://download.geonames.org/export/dump/countryInfo.txt',
   'all_countries'=>'https://download.geonames.org/export/dump/allCountries.zip',
  ],
  'un_m49'=>['url'=>'https://unstats.un.org/unsd/methodology/m49/overview'],
 ],
 'search_cache_seconds'=>(int)env('GEO_SEARCH_CACHE_SECONDS',300),
 'nearby_max_radius_km'=>(float)env('GEO_NEARBY_MAX_RADIUS_KM',200),
];