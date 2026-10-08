<?php
namespace App\Policies;
use App\Models\User;use App\Models\WebhookRequest;
class WebhookRequestPolicy {public function view(User $u,WebhookRequest $r):bool{return $r->endpoint->user_id===$u->id;}}