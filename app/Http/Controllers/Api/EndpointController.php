<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;use App\Http\Requests\StoreEndpointRequest;use App\Http\Requests\UpdateEndpointRequest;use App\Models\WebhookEndpoint;use App\Services\WebhookReceiver;use Illuminate\Http\JsonResponse;use Illuminate\Http\Request;
class EndpointController extends Controller {
 public function index(Request $r){return $r->user()->webhookEndpoints()->latest()->get()->map(fn($e)=>['id'=>$e->id,'name'=>$e->name,'enabled'=>$e->enabled,'webhook_url'=>$e->url,'created_at'=>$e->created_at]);}
 public function store(StoreEndpointRequest $r,WebhookReceiver $receiver):JsonResponse{$e=$r->user()->webhookEndpoints()->create(['name'=>$r->string('name'),'enabled'=>$r->boolean('enabled',true),'token'=>$receiver->token()]);return response()->json(['data'=>$e,'webhook_url'=>$e->url],201);}
 public function show(WebhookEndpoint $e):JsonResponse{$this->authorize('view',$e);return response()->json(['data'=>$e,'webhook_url'=>$e->url]);}
 public function update(UpdateEndpointRequest $r,WebhookEndpoint $e):JsonResponse{$this->authorize('update',$e);$e->update($r->validated());return response()->json(['data'=>$e,'webhook_url'=>$e->url]);}
 public function destroy(WebhookEndpoint $e){$this->authorize('delete',$e);$e->delete();return response()->noContent();}
 public function regenerate(WebhookEndpoint $e,WebhookReceiver $receiver):JsonResponse{$this->authorize('update',$e);$e->update(['token'=>$receiver->token()]);return response()->json(['data'=>$e,'webhook_url'=>$e->url]);}
 public function requests(WebhookEndpoint $e){$this->authorize('view',$e);return response()->json($e->requests()->latest('received_at')->paginate(50));}
}