<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Support\GeoText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
class GeoController extends Controller {
 public function countries(Request $request): JsonResponse {
  $q=trim((string)$request->query('q',''));
  $key='geo:countries:'.sha1($q.'|'.$request->query('page',1));
  return response()->json(Cache::remember($key,config('geo.search_cache_seconds',300),function()use($q){
   $query=Country::query()->orderBy('name_en');
   if($q!==''){ $query->where(fn($x)=>$x->where('name','like',"%$q%")->orWhere('name_en','like',"%$q%")->orWhere('iso2','like',strtoupper($q).'%')->orWhere('iso3','like',strtoupper($q).'%')); }
   return $query->paginate(50)->withQueryString();
  }));
 }
 public function country(Country $country): JsonResponse { return response()->json($country); }
 public function countryCities(Request $request,Country $country): JsonResponse { return response()->json($this->cityQuery($request)->where('country_id',$country->id)->paginate(50)->withQueryString()); }
 public function cities(Request $request): JsonResponse { return response()->json($this->cityQuery($request)->paginate(50)->withQueryString()); }
 public function search(Request $request): JsonResponse {
  $request->validate(['q'=>'required|string|min:1|max:200']); $q=trim($request->query('q')); $a=GeoText::ascii($q);
  return response()->json(City::query()->with('country:id,name,name_en,iso2,iso3')->where(fn($x)=>$x->where('name','like',"%$q%")->orWhere('name_en','like',"%$q%")->orWhere('ascii_name','like',"%$a%")->orWhere('alternate_names','like',"%$q%"))->orderByDesc('population')->paginate(50));
 }
 public function nearby(Request $request): JsonResponse {
  $data=$request->validate(['lat'=>'required|numeric|between:-90,90','lng'=>'required|numeric|between:-180,180','radius'=>'nullable|numeric|min:0.1|max:'.config('geo.nearby_max_radius_km',200)]);
  $lat=(float)$data['lat'];$lng=(float)$data['lng'];$radius=(float)($data['radius']??25);
  $formula='(6371 * acos(cos(radians(?))*cos(radians(latitude))*cos(radians(longitude)-radians(?))+sin(radians(?))*sin(radians(latitude))))';
  $query=City::query()->with('country:id,name,name_en,iso2,iso3')->select('cities.*')->selectRaw($formula.' as distance_km',[$lat,$lng,$lat])->whereRaw($formula.' <= ?',[$lat,$lng,$lat,$radius])->orderBy('distance_km');
  return response()->json($query->paginate(50));
 }
 private function cityQuery(Request $request) {
  $q=trim((string)$request->query('q',''));$a=GeoText::ascii($q);
  return City::query()->with('country:id,name,name_en,iso2,iso3')->when($q!=='',fn($x)=>$x->where(fn($w)=>$w->where('name','like',"%$q%")->orWhere('name_en','like',"%$q%")->orWhere('ascii_name','like',"%$a%")->orWhere('alternate_names','like',"%$q%")))->orderByDesc('population');
 }
}