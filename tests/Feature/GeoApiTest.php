<?php
namespace Tests\Feature;
use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class GeoApiTest extends TestCase {
 use RefreshDatabase;
 public function test_country_and_city_endpoints_are_paginated(): void {
  $country=Country::create(['geoname_id'=>100,'name'=>'المملكة العربية السعودية','name_en'=>'Saudi Arabia','iso2'=>'SA','iso3'=>'SAU','numeric_code'=>'682','continent'=>'AS','source'=>'test']);
  City::create(['geoname_id'=>101,'country_id'=>$country->id,'name'=>'الرياض','name_en'=>'Riyadh','ascii_name'=>'Riyadh','latitude'=>24.7136,'longitude'=>46.6753,'population'=>7000000,'feature_code'=>'PPLC','source'=>'test']);
  $this->getJson('/api/countries')->assertOk()->assertJsonPath('data.0.iso2','SA');
  $this->getJson('/api/countries/'.$country->id.'/cities')->assertOk()->assertJsonPath('data.0.name','الرياض');
  $this->getJson('/api/cities/search?q=riyad')->assertOk()->assertJsonPath('data.0.name_en','Riyadh');
  $this->getJson('/api/cities/search?q=الرياض')->assertOk()->assertJsonPath('data.0.name','الرياض');
 }
 public function test_nearby_search_returns_distance(): void {
  $country=Country::create(['geoname_id'=>200,'name'=>'Saudi Arabia','name_en'=>'Saudi Arabia','iso2'=>'SB','iso3'=>'SXX','source'=>'test']);
  City::create(['geoname_id'=>201,'country_id'=>$country->id,'name'=>'Riyadh','name_en'=>'Riyadh','ascii_name'=>'Riyadh','latitude'=>24.7136,'longitude'=>46.6753,'population'=>1,'source'=>'test']);
  $this->getJson('/api/cities/nearby?lat=24.7136&lng=46.6753&radius=5')->assertOk()->assertJsonPath('data.0.name_en','Riyadh')->assertJsonStructure(['data'=>[['distance_km']]]);
 }
}