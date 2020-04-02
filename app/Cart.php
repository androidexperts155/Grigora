<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function cart_details(){
    	return $this->hasMany('App\CartItemsDetail','cart_id','id');
    }
}
