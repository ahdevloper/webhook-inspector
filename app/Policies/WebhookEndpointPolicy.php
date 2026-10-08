<?php
namespace App\Policies;
use App\Models\User;use App\Models\WebhookEndpoint;
class WebhookEndpointPolicy {public function view(User $u,WebhookEndpoint $e):bool{return $e->user_id===$u->id;}public function update(User $u,WebhookEndpoint $e):bool{return $this->view($u,$e);}public function delete(User $u,WebhookEndpoint $e):bool{return $this->view($u,$e);}}