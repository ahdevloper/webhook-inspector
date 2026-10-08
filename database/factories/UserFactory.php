<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;use Illuminate\Support\Str;
class UserFactory extends Factory {protected $model=\App\Models\User::class;public function definition():array{return ['name'=>fake()->name(),'email'=>fake()->unique()->safeEmail(),'email_verified_at'=>now(),'password'=>bcrypt('password'),'remember_token'=>Str::random(10)];}}