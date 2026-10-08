<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ReplayRequest extends FormRequest {public function authorize():bool{return $this->user()!==null;}public function rules():array{return ['target_url'=>['required','url','max:2048'],'headers'=>['sometimes','array'],'headers.*'=>['string','max:10000'],'payload'=>['nullable','array']];}}