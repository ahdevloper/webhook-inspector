<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable; use Illuminate\Notifications\Notifiable; use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable {use Notifiable;protected $fillable=['name','email','password'];protected $hidden=['password','remember_token'];public function webhookEndpoints():HasMany{return $this->hasMany(WebhookEndpoint::class);}}