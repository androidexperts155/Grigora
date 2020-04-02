<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Brand;
use App\User;
use Validator;
use Auth;


class BrandsController extends Controller
{
    
	public function getAllBrands(){
		try{
			$brands = Brand::where('status', '1')->get()->toArray();
            if($brands){
            	return response()->json([
	                                        'status' => true,
	                                        'message' => "Brands Found Successfuly.",
	                                        'data' => $brands
	                                    ], 200);	
            }else{
            	return response()->json([
	                                        'status' => false,
	                                        'message' => "Brands Not Found."
	                                    ], 200);	
            }
		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

    public function searchBrand(Request $request){
    	try{
    		$rules = [
                        'search' => 'required'
                    ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
                                            'status' => false,
                                            "message" => $validator->errors()->first(),
                                           //'errors' => $validator->errors()->toArray(),
                                       ], 422);              
            }   

            $search = $request->search;
            $check = Brand::where('name', 'like', '%' . $search . '%')->get()->toArray();
            if($check){
            	return response()->json([
	                                        'status' => true,
	                                        'message' => "Brands Found Successfuly.",
	                                        'data' => $check
	                                    ], 200);	
            }else{
            	return response()->json([
	                                        'status' => false,
	                                        'message' => "Brands Not Found."
	                                    ], 200);	
            }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }
}
