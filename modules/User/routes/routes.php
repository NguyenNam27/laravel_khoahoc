<?php

use Illuminate\Support\Facades\Route;

Route::middleware('demo')->get('/users',function (){
   return config('config.test');
});
