<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class City extends Model {
    use HasFactory;
    protected $fillable = ['geoname_id','country_id','name','name_en','ascii_name','latitude','longitude','population','feature_code','admin1','admin2','timezone','elevation','alternate_names','source','source_version','imported_at','last_updated_at','license'];
    protected $casts = ['latitude'=>'float','longitude'=>'float','population'=>'integer','elevation'=>'integer','imported_at'=>'datetime','last_updated_at'=>'datetime'];
    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
}