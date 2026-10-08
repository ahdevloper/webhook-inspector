<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateEndpointRequest extends FormRequest {public function authorize():bool{return $this->user()!==null&&$this->user()->can('update',$this->route('endpoint'));}public function rules():array{return ['name'=>['sometimes','required','string','max:120'],'enabled'=>['sometimes','boolean']];}}