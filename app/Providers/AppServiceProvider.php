<?php
namespace App\Providers;
use App\Models\WebhookEndpoint;use App\Models\WebhookRequest;use App\Policies\WebhookEndpointPolicy;use App\Policies\WebhookRequestPolicy;use App\Services\GeoDataImporter;use Illuminate\Cache\RateLimiting\Limit;use Illuminate\Http\Request;use Illuminate\Support\Facades\Gate;use Illuminate\Support\Facades\RateLimiter;use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
 public function register():void { $this->app->singleton(GeoDataImporter::class,fn()=>new GeoDataImporter(config('geo.data_dir'))); }
 public function boot():void { Gate::policy(WebhookEndpoint::class,WebhookEndpointPolicy::class);Gate::policy(WebhookRequest::class,WebhookRequestPolicy::class);RateLimiter::for('webhook',fn(Request $r)=>Limit::perMinute((int)config('webhook.rate_limit',120))->by(($r->route('endpoint')?->id??'unknown').'|'.$r->ip())); }
}