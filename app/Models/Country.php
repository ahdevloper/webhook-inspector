<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Country extends Model {
    use HasFactory;
    protected $fillable = ['geoname_id','name','name_en','iso2','iso3','numeric_code','continent','region','subregion','capital','latitude','longitude','timezone','phone_code','currency','flag_code','source','source_version','imported_at','last_updated_at','license'];
    protected $casts = ['latitude'=>'float','longitude'=>'float','imported_at'=>'datetime','last_updated_at'=>'datetime'];
    public function cities(): HasMany { return $this->hasMany(City::class); }
}