<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherCard;
use App\VoucherCardCode;
use App\User;

class BarcodegeneratorController extends Controller
{
     public function barcode($id)
    {
        $voucher = VoucherCard::where('id',$id)->first();
		$voucher_codes1 =VoucherCardCode::where('voucher_id',$id)->get();
		$voucher_code = array();
		 foreach ($voucher_codes1 as $key => $value) {
      		

	      $user =  User::where('id',$value->user_id)->first();
	      if($user){
	        $username = $user->name;
	      }else{
	        $username = '';
	      }
	        $voucher_code[$key]['id'] = $value->id;
	        $voucher_code[$key]['voucher_code'] = $value->voucher_code;
	        $voucher_code[$key]['voucher_id'] = $value->voucher_id;
	        $voucher_code[$key]['status'] =  $value->status;
	        $voucher_code[$key]['valid'] = $value->valid;
	        $voucher_code[$key]['amount'] = $value->amount;
	        $voucher_code[$key]['username'] = $username;
	      # code...
	    }
    	return view('admin.voucher.view')->with(compact('voucher','voucher_code'));
    }
}
