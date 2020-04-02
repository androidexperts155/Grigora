<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherCard;
use App\VoucherCardCode;
use DNS1DFacade;
use App\GiftCard;
use App\User;
use Auth;
use App\Mail\AccountVerification;
use Mail;

class VoucherController extends Controller
{
    //
      public function list(){
		$voucher = VoucherCard::all();
    	return view('admin.voucher.index')->with(compact('voucher'));
    }
     public function voucher_codes($id){
		$voucher = VoucherCard::where('id',$id)->first();
		$voucher_codes =VoucherCardCode::where('voucher_id',$id)->get();
    $voucher_code = array();
    foreach ($voucher_codes as $key => $value) {
      

      $user =  User::where('id',$value->user_id)->first();
      if($user){
        $username = $user->name;
      }else{
        $username = '';
      }
        $voucher_code[$key]['id'] = $value->id;
        $voucher_code[$key]['voucher_code'] = $value->code;
        $voucher_code[$key]['voucher_id'] = $value->voucher_id;
        $voucher_code[$key]['status'] =  $value->status;
        $voucher_code[$key]['valid'] = $value->valid;
        $voucher_code[$key]['amount'] = $value->amount;
        $voucher_code[$key]['username'] = $username;
      # code...
    }
    	return view('admin.voucher.view')->with(compact('voucher','voucher_code'));
    }
    public function viewback($code){
      
    	return view('admin.voucher.viewback')->with(compact('code'));
    }
    
    public function RedeemeData($code){ 
      $redemedcard = GiftCard::where('voucher_code',$code)->where('redemed','1')->first();
      if($redemedcard){
         $user = User::where('id',$redemedcard->user_id)->first();
      }else{
        $user = "";
      }
      $voucher_codedata = VoucherCardCode::where('voucher_code',$code)->first();
      $voucher = VoucherCard::where('id',$voucher_codedata->voucher_id)->first();

        return view('admin.voucher.redeem')->with(compact('redemedcard','user','voucher','code'));
    }

      public function add(Request $request){
       $user =  Auth::user();
        $voucher_data = VoucherCard::where('id',$request->id)->first();
        for ($x = 0; $x < $request->number; $x++) {
            $voucher_code = 'VC'.$request->id.$this->generateRandomString('6');
            $new_code =   new VoucherCardCode;
            $new_code->voucher_id = $request->id;
            $new_code->valid = '1';
            $new_code->amount = $voucher_data->amount;
            $new_code->voucher_code = $voucher_code;
            $new_code->user_id = $user->id;

            $new_code->save();
        } 
       
      		return response()->json([
                  "status" => true,
                  "message" => 'Code Generated succesfully',

          ], 200);

      	

    	
    }

     function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    } 
}
