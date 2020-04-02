<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\User;
use App\Cuisine;
use App\Cart;
use App\RatingReview;
use App\CartItemsDetail;
use App\RestaurantCuisine;
use App\Order;
use App\OrderDetail;
use App\Item;
use App\ItemCategory;
use App\Notification;
use App\Transaction;
use App\CartUser;
use App\ItemSubCategory;
use App\Promocode;
use App\Setting;
use App\UserToken;
use App\RestaurantPromo;
use App\TableBooking;
use App\UserOrderType;
use Auth;
use DB;
use Carbon\Carbon;
use Braintree_Transaction;
use Braintree_Customer;
use Braintree_WebhookNotification;
use Braintree_Subscription;
use Braintree_CreditCard;
use Braintree_ClientToken;

class OrderController extends Controller
{

	public function promoCodeList($id){
		try{
			$promocodes = Promocode::all()->toArray();
			//echo'<pre>';print_r($promocodes);die;
			foreach ($promocodes as $key => $promocode) {
				$restaurantPromo = RestaurantPromo::where('restaurant_id', $id)->where('promo_id', $promocode['id'])->first();
				//echo'<pre>';print_r($restaurantPromo);die;
				if($restaurantPromo){
					$promocodes[$key]['applied'] = true;
				}else{
					$promocodes[$key]['applied'] = false;
				}
			}
			if($promocodes){
				return response()->json([
                                            'status' => true,
                                            'message' => "Promocodes Found Successfully.",
                                            'data' => $promocodes
                                        ], 200);
			}else{
				return response()->json([
                                            'status' => true,
                                            'message' => "Promocodes Not Found Successfully.",
                                            'data' => $promocodes
                                        ], 200);
			}

		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

	public function createGroupCart(Request $request){
		try{
			$rules = [
                        //'share_link' => 'required',
                        'max_per_person' => 'required',
                        'restaurant_id' => 'required',
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

            $user = Auth::user();
            $restaurantId = $request->restaurant_id;
            $cart = Cart::where('user_id', $user->id)
        				->where('status', '1')
        				//->where('restaurant_id', $restaurantId)
        				->first();
            $restaurant = User::where('id', $restaurantId)->first();
     //        if($cart){
     //        	//$cart->share_link = $request->share_link;
     //        	$cart->max_per_person = $request->max_per_person;
     //        	$cart->group_order = "1";

     //        	if($cart->save()){
     //        		$cartUser = CartUser::where('cart_id', $cart->id)
     //        							->where('user_id', $user->id)
     //        							->first();
					// if($cartUser){

					// }else{
					// 	$addCartUser = new CartUser;
					// 	$addCartUser->cart_id = $cart->id;
					// 	$addCartUser->user_id = $user->id;
					// 	$addCartUser->save();
					// }
     //        		$cart->restaurant_name = $restaurant->name;
     //        		return response()->json([
		   //                                      'status' => true,
		   //                                      'message' => "Group Cart Created Successfully.",
		   //                                      'data' => $cart
		   //                                  ], 200);
     //        	}else{
     //        		return response()->json([
		   //                                      'status' => false,
		   //                                      'message' => "Something Went Wrong!"
		   //                                  ], 422);		
     //        	}
     //        }else{
            	$cart = new Cart;
            	$cart->user_id = $user->id;
            	$cart->restaurant_id = $restaurantId;
            	//$cart->share_link = $request->share_link;
            	$cart->max_per_person = $request->max_per_person;
            	$cart->group_order = "1";
            	if($cart->save()){
            		$cart->restaurant_name = $restaurant['name'];
            		$cartUser = CartUser::where('cart_id', $cart->id)
            							->where('user_id', $user->id)
            							->first();
					if($cartUser){

					}else{
						$addCartUser = new CartUser;
						$addCartUser->cart_id = $cart->id;
						$addCartUser->user_id = $user->id;
						$addCartUser->save();
					}
            		return response()->json([
		                                        'status' => true,
		                                        'message' => "Group Cart Created Successfully.",
		                                        'data' => $cart
		                                    ], 200);
            	}else{
        			return response()->json([
		                                        'status' => false,
		                                        'message' => "Something Went Wrong!"
		                                    ], 422);			
            	}
            //}

		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

	public function saveCartShareLink(Request $request){
		try{
			$rules = [
                        'share_link' => 'required',
                        'cart_id' => 'required',
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

            $cartId = $request->cart_id;
            $shareLink = $request->share_link;

            $cart = Cart::where('id', $cartId)->first();
            $restaurant = User::where('id', $cart['restaurant_id'])->first();
            if($cart){
	            $cart->share_link = $shareLink;
	            if($cart->save()){
	            	$cart->restaurant_name = $restaurant['name'];
	            	return response()->json([
		                                        'status' => true,
		                                        'message' => "Share Link Saved Successfully.",
		                                        'data' => $cart
		                                    ], 200);
	            }else{
	            	return response()->json([
		                                        'status' => false,
		                                        'message' => "Something Went Wrong!"
		                                    ], 422);	
	            }
	        }else{
	        	return response()->json([
	                                        'status' => false,
	                                        'message' => "Cart Not Found."
	                                    ], 200);	
	        }
		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

	public function settings(){
		try{
			$settings = Setting::where('id', '1')->first();
			if($settings){
				return response()->json([
                                            'status' => true,
                                            'message' => "Settings Found Successfully.",
                                            'data' => $settings
                                        ], 200);
			}else{
				return response()->json([
                                            'status' => true,
                                            'message' => "Settings Not Found Successfully.",
                                            'data' => $settings
                                        ], 200);
			}
		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

    // public function addCart(Request $request){
    // 	//echo'<pre>';print_r($request->all());die;
    // 	try{
    // 		$rules = [
    //                     'restaurant_id' => 'required',
    //                     'item_id' => 'required',
    //                     //'item_choices' => 'required',
    //                     'quantity' => 'required',
    //                     'price' => 'required',
    //                	];
    //         $validator = Validator::make($request->all(), $rules);

    //         if ($validator->fails()) {
    //             $errors = $validator->errors()->first();
    //             return response()->json([
    //                                         'status' => false,
    //                                         'message' => $errors,
    //                                         //'errors' => $errors
    //                                     ], 400);
    //         }

    //         $user = Auth::user();
    //         //echo'<pre>';print_r($user);die;
    //         //echo $request->item_choices;die;
    //         //$itemChoices = json_decode($request->item_choices);
    //         //echo '<pre>';print_r($itemChoices);die;
    //         $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
    //         $userImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
    //         $settings = Setting::where('id', '1')->first();
    //         $check = Cart::where('user_id', $user->id)->first();
    //         if($check){

    //         	if($check->restaurant_id != $request->restaurant_id){
    //         		return response()->json([
		  //                                       'status' => false,
		  //                                       'message' => 'You can only order from one menu at a time.',
		  //                                   ], 422);
    //         	}else{
    //         		$checkItemDetails = CartItemsDetail::where('cart_id', $check->id)
    //         											->where('item_id', $request->item_id)
    //         											->first();
				// 	if($checkItemDetails){
				// 		//$quantity = $checkItemDetails->quantity+$request->quantity;
				// 		//cart table updation is pending
				// 		if($request->has('item_choices') && !empty($request->item_choices)){
				// 			//echo $request->item_choices;
				// 			//$CartItemsDetail = CartItemsDetail::where('cart_id', $check->id)->first();
				// 			$CartItemsDetail = CartItemsDetail::where('cart_id', $check->id)->where('item_choices', $request->item_choices)->first();

				// 			$databaseItemCart = json_decode($CartItemsDetail['item_choices']);
				// 			$requestChocies = json_decode($request->item_choices);

				// 			$countRes = array();$hasSameItem =  false;
				// 			if(!empty($databaseItemCart) && !empty($requestChocies)){
				// 				foreach ($databaseItemCart as $databaseCartKey => $databaseCartValue) {
				// 					foreach ($requestChocies as $requestKey => $requestValue) {
				// 						if($databaseCartValue->id == $requestValue->id){
				// 							$countRes[$databaseCartValue->id] = $databaseCartValue->id;
				// 						}
				// 					}
				// 				}
				// 				if(count($countRes) == count($databaseItemCart)){
				// 					$hasSameItem =  true;
				// 				}else{
				// 					$hasSameItem =  false;
				// 				}
				// 			}else{
				// 				// set false;
				// 				$hasSameItem =  false;
				// 			}
							


				// 			if($CartItemsDetail && $hasSameItem){
				// 				$CartItemsDetail = CartItemsDetail::where('cart_id', $check->id)->first();
				// 				//echo'<pre>';print_r($CartItemsDetail);die;
				// 				$CartItemsDetail['quantity'] = $CartItemsDetail['quantity'] + $request->quantity;
				// 				if($CartItemsDetail->save()){
				// 					$check->quantity = $check->quantity+$request->quantity;
				// 					$check->total_price = $check->total_price+($request->quantity * $request->price);
				// 					if($check->save()){
				// 						$cart= Cart::where("id",$check->id)->with("cart_details")->first();
				// 						foreach ($cart['cart_details'] as $key => $cartDetails) {
				// 							$items = Item::where('id', $cartDetails->item_id)->first();
				// 							$cart['cart_details'][$key]->item_name = $items['name'];
				// 							$cart['cart_details'][$key]->item_french_name = $items['french_name'];
				// 						}
		  //                               $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
		  //                               $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
		  //                               $cart['app_fee'] = $settings['app_fee'];
		  //                               $cart['delivery_fee'] = $settings['delivery_fee'];
				// 						return response()->json([
				// 		                                            'status' => true,
				// 		                                            'message' => "Cart Updated Successfully.",
				// 		                                            'data' => $cart
				// 		                                        ], 200);	
				// 					}else{
				// 						return response()->json([
				// 			                                        'status' => false,
				// 			                                        'message' => 'Something went wrong.',
				// 			                                    ], 422);	
				// 					}
									
				// 				}else{
				// 					return response()->json([
				// 		                                        'status' => false,
				// 		                                        'message' => 'Something went wrong.',
				// 		                                    ], 422);
				// 				}
				// 			}else{
				// 				$cartItemDetails = new CartItemsDetail;
				//             	$cartItemDetails->cart_id = $check->id;
				//             	$cartItemDetails->item_id = $request->item_id;
				//             	$cartItemDetails->quantity = $request->quantity;
				//             	$cartItemDetails->price = $request->price;
				//             	if($request->has('item_choices') && !empty($request->item_choices)){
				//             		$cartItemDetails->item_choices = $request->item_choices;
				//             	}
				//             	if($cartItemDetails->save()){
				//             		$check->quantity = $request->quantity + $check->quantity;
				// 					$check->total_price = $check->total_price + ($request->quantity*$request->price);
				// 					if($check->save()){
				// 						$cart= Cart::where("id",$check->id)->with("cart_details")->first();
				// 						foreach ($cart['cart_details'] as $key => $cartDetails) {
				// 							$items = Item::where('id', $cartDetails->item_id)->first();
				// 							$cart['cart_details'][$key]->item_name = $items->name;
				// 							$cart['cart_details'][$key]->item_french_name = $items->french_name;
				// 						}
		  //                               $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
		  //                               $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
		  //                               $cart['app_fee'] = $settings['app_fee'];
		  //                               $cart['delivery_fee'] = $settings['delivery_fee'];
				// 						return response()->json([
				// 		                                            'status' => true,
				// 		                                            'message' => "Cart Created Successfully.",
				// 		                                            'data' => $cart
				// 		                                        ], 200);
				// 					}else{
				// 						return response()->json([
				// 			                                        'status' => false,
				// 			                                        'message' => 'Something went wrong.',
				// 			                                    ], 422);
				// 					}
				//             	}else{
				//             		//$delete = Cart::where('id', $cart->id)->delete();
				//             		return response()->json([
				// 		                                        'status' => false,
				// 		                                        'message' => 'Something went wrong.',
				// 		                                    ], 422);
				//             	}
			 //            	}
				// 		}
				// 		$CartItemsDetail = CartItemsDetail::where('cart_id', $check->id)->first();
				// 		//echo'<pre>';print_r($CartItemsDetail);die;
				// 		$CartItemsDetail->quantity = $CartItemsDetail->quantity + $request->quantity;
				// 		if($request->has('item_choices') && !empty($request->item_choices)){
				// 			$CartItemsDetail->item_choices = $request->item_choices;
				// 		}else{
				// 			$CartItemsDetail->item_choices = "";
				// 		}
				// 		//$CartItemsDetail->save();
				// 		//update(['quantity' => 'quantity' + $request->quantity]);

				// 		if($CartItemsDetail->save()){
				// 			$check->quantity = $check->quantity+$request->quantity;
				// 			$check->total_price = $check->total_price+($request->quantity * $request->price);
				// 			if($check->save()){
				// 				$cart= Cart::where("id",$check->id)->with("cart_details")->first();
				// 				foreach ($cart['cart_details'] as $key => $cartDetails) {
				// 					$items = Item::where('id', $cartDetails->item_id)->first();
				// 					$cart['cart_details'][$key]->item_name = $items['name'];
				// 					$cart['cart_details'][$key]->item_french_name = $items['french_name'];
				// 				}
    //                             $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
    //                             $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
    //                             $cart['app_fee'] = $settings['app_fee'];
    //                             $cart['delivery_fee'] = $settings['delivery_fee'];
				// 				return response()->json([
				//                                             'status' => true,
				//                                             'message' => "Cart Updated Successfully.",
				//                                             'data' => $cart
				//                                         ], 200);	
				// 			}else{
				// 				return response()->json([
				// 	                                        'status' => false,
				// 	                                        'message' => 'Something went wrong.',
				// 	                                    ], 422);	
				// 			}
							
				// 		}else{
				// 			return response()->json([
				//                                         'status' => false,
				//                                         'message' => 'Something went wrong.',
				//                                     ], 422);
				// 		}
				// 	}else{

				// 		$cartItemDetails = new CartItemsDetail;
		  //           	$cartItemDetails->cart_id = $check->id;
		  //           	$cartItemDetails->item_id = $request->item_id;
		  //           	$cartItemDetails->quantity = $request->quantity;
		  //           	$cartItemDetails->price = $request->price;
		  //           	if($request->has('item_choices') && !empty($request->item_choices)){
		  //           		$cartItemDetails->item_choices = $request->item_choices;
		  //           	}
		  //           	if($cartItemDetails->save()){
		  //           		$check->quantity = $request->quantity + $check->quantity;
				// 			$check->total_price = $check->total_price + ($request->quantity*$request->price);
				// 			if($check->save()){
				// 				$cart= Cart::where("id",$check->id)->with("cart_details")->first();
				// 				foreach ($cart['cart_details'] as $key => $cartDetails) {
				// 					$items = Item::where('id', $cartDetails->item_id)->first();
				// 					$cart['cart_details'][$key]->item_name = $items->name;
				// 					$cart['cart_details'][$key]->item_french_name = $items->french_name;
				// 				}
    //                             $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
    //                             $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
    //                             $cart['app_fee'] = $settings['app_fee'];
    //                             $cart['delivery_fee'] = $settings['delivery_fee'];
				// 				return response()->json([
				//                                             'status' => true,
				//                                             'message' => "Cart Created Successfully.",
				//                                             'data' => $cart
				//                                         ], 200);
				// 			}else{
				// 				return response()->json([
				// 	                                        'status' => false,
				// 	                                        'message' => 'Something went wrong.',
				// 	                                    ], 422);
				// 			}
		  //           	}else{
		  //           		//$delete = Cart::where('id', $cart->id)->delete();
		  //           		return response()->json([
				//                                         'status' => false,
				//                                         'message' => 'Something went wrong.',
				//                                     ], 422);
		  //           	}

				// 	}
    //         	}

    //         }else{

	   //          $cart = new Cart;
	   //          $cart->user_id = $user->id;
	   //          $cart->restaurant_id = $request->restaurant_id;
	   //          $cart->quantity = $request->quantity;
	   //          $price = $request->quantity*$request->price;
	   //          $cart->total_price = $price;
	   //          if($cart->save()){
	   //          	$cartItemDetails = new CartItemsDetail;
	   //          	$cartItemDetails->cart_id = $cart->id;
	   //          	$cartItemDetails->item_id = $request->item_id;
	   //          	$cartItemDetails->quantity = $request->quantity;
	   //          	$cartItemDetails->price = $request->price;
	   //          	$cartItemDetails->item_choices = $request->item_choices;
	   //          	if($cartItemDetails->save()){
	   //          		$cart['cart_details'] = $cartItemDetails;
				// 		$items = Item::where('id', $cartItemDetails['item_id'])->first();
				// 		$cart['cart_details']['item_name'] = $items['name'];
				// 		$cart['cart_details']['item_french_name'] = $items['french_name'];
	   //          		return response()->json([
		  //                                           'status' => true,
		  //                                           'message' => "Cart Created Successfully.",
		  //                                           'data' => $cart
		  //                                       ], 200);
	   //          	}else{
	   //          		$delete = Cart::where('id', $cart->id)->delete();
	   //          		return response()->json([
			 //                                        'status' => false,
			 //                                        'message' => 'Something went wrong.',
			 //                                    ], 422);
	   //          	}

	   //          }else{
	   //          	return response()->json([
		  //                                       'status' => false,
		  //                                       'message' => 'Something went wrong.',
		  //                                   ], 422);
	   //          }
	   //      }

    // 	}catch (Exception $e) {
    //         return response()->json([
    //                                     'status' => false,
    //                                     'message' => "Something Went Wrong!"
    //                                 ], 422);
    //     }
    // }

    public function addInGroupCart(Request $request){
    	//echo'<pre>';print_r($request->all());die;
    	try{
    		$rules = [
                        'cart_id' => 'required',
                        'restaurant_id' => 'required',
                        'item_id' => 'required',
                        //'item_choices' => 'required',
                        'quantity' => 'required',
                        'price' => 'required',
                        //'group_order' => 'required',
                        //'cart_type' => 'required', //1:delivery, 2:pickup
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $user = Auth::user();
            $cartId = $request->cart_id;
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $userImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
            $settings = Setting::where('id', '1')->first();
            $check = Cart::where('id', $cartId)->where('status','1')->first();
            if($check){
            	$userOrderType = UserOrderType::where('restaurant_id', $request->restaurant_id)
	            							->where('user_id', $user->id)
	            							->first();

	            if($userOrderType){
	            	$check->cart_type = $userOrderType['order_type'];
	            }else{
	            	$check->cart_type = '1';
	            }
	            $check->save();
            	$cartUser = CartUser::where('cart_id', $check->id)
            						->where('user_id', $user->id)
            						->first();
				if($cartUser){

				}else{
					$newCartUser = new CartUser;
					$newCartUser->cart_id = $check->id;
					$newCartUser->user_id = $user->id;
					$newCartUser->save();
				}
            	if($check->restaurant_id != $request->restaurant_id){
            		return response()->json([
		                                        'status' => false,
		                                        'message' => 'You can only order from one menu at a time.',
		                                    ], 422);
            	}else{
            		$checkCartUser = CartUser::where('cart_id', $check->id)->where('user_id', $user->id);
            		if($checkCartUser){

            		}else{
            			$cartUser = new CartUser;
	        			$cartUser->cart_id = $cart->id;
	        			$cartuser->user_id = $user_id;
	        			$cartuser->save();
	        		}
            		if($request->has('item_choices') && !empty($request->item_choices)){
            			$cartItemDetails = new CartItemsDetail;
		            	$cartItemDetails->cart_id = $check->id;
		            	$cartItemDetails->user_id = $user->id;
		            	$cartItemDetails->item_id = $request->item_id;
		            	$cartItemDetails->quantity = $request->quantity;
		            	$cartItemDetails->price = $request->price;
		            	//if($request->has('item_choices') && !empty($request->item_choices)){
		            		$cartItemDetails->item_choices = $request->item_choices;
		            	//}
		            	if($cartItemDetails->save()){
		            		$check->quantity = $request->quantity + $check->quantity;
							$check->total_price = $check->total_price + ($request->quantity*$request->price);
							if($check->save()){
								$cart= Cart::where("id",$check->id)->with("cart_details")->first();
								$cart['cart_id'] = $cart['id'];
								foreach ($cart['cart_details'] as $key => $cartDetails) {
									$items = Item::where('id', $cartDetails->item_id)->first();
									$cart['cart_details'][$key]->item_name = $items->name;
									$cart['cart_details'][$key]->item_french_name = $items->french_name;
								}
                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
                                $cart['app_fee'] = $settings['app_fee'];
                                $cart['delivery_fee'] = $settings['delivery_fee'];
								return response()->json([
				                                            'status' => true,
				                                            'message' => "Cart Created Successfully.",
				                                            'data' => $cart
				                                        ], 200);
							}else{
								return response()->json([
					                                        'status' => false,
					                                        'message' => 'Something went wrong.',
					                                    ], 422);
							}
		            	}else{
		            		//$delete = Cart::where('id', $cart->id)->delete();
		            		return response()->json([
				                                        'status' => false,
				                                        'message' => 'Something went wrong.',
				                                    ], 422);
		            	}
            		}else{
						$checkItemDetails = CartItemsDetail::where('cart_id', $check->id)
            											->where('item_id', $request->item_id)
            											->where('user_id', $user->id)
            											//->where('item_choices', '')
            											->first();
						//return $checkItemDetails;
						if($checkItemDetails){
							if($checkItemDetails->item_choices != ''){
								$cartItemDetails = new CartItemsDetail;
			            		$cartItemDetails->cart_id = $check->id;
			            		$cartItemDetails->user_id = $user->id;
			            		$cartItemDetails->item_id = $request->item_id;
			            		$cartItemDetails->quantity = $request->quantity;
			            		$cartItemDetails->price = $request->price;
				            	//if($request->has('item_choices') && !empty($request->item_choices)){
				            		//$cartItemDetails->item_choices = " ";
				            	//}
				            	if($cartItemDetails->save()){
				            		$check->quantity = $request->quantity + $check->quantity;
									$check->total_price = $check->total_price + ($request->quantity*$request->price);
									if($check->save()){
										$cart= Cart::where("id",$check->id)->with("cart_details")->first();
										foreach ($cart['cart_details'] as $key => $cartDetails) {
											$items = Item::where('id', $cartDetails->item_id)->first();
											$cart['cart_details'][$key]->item_name = $items->name;
											$cart['cart_details'][$key]->item_french_name = $items->french_name;
										}
		                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
		                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
		                                $cart['cart_id'] = $cart['id'];
		                                $cart['app_fee'] = $settings['app_fee'];
		                                $cart['delivery_fee'] = $settings['delivery_fee'];
										return response()->json([
						                                            'status' => true,
						                                            'message' => "Cart Created Successfully.",
						                                            'data' => $cart
						                                        ], 200);
									}else{
										return response()->json([
							                                        'status' => false,
							                                        'message' => 'Something went wrong.',
							                                    ], 422);
									}
								}
							}
							$checkItemDetails->quantity = $checkItemDetails['quantity'] + $request->quantity;
							if($checkItemDetails->save()){
								$check->quantity = $request->quantity + $check->quantity;
								$check->total_price = $check->total_price + ($request->quantity*$request->price);
								if($check->save()){
									$cart= Cart::where("id",$check->id)->with("cart_details")->first();
									$cart['cart_id'] = $cart['id'];
									foreach ($cart['cart_details'] as $key => $cartDetails) {
										$items = Item::where('id', $cartDetails->item_id)->first();
										$cart['cart_details'][$key]->item_name = $items->name;
										$cart['cart_details'][$key]->item_french_name = $items->french_name;
									}
	                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
	                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
	                                $cart['app_fee'] = $settings['app_fee'];
	                                $cart['cart_id'] = $cart['id'];
	                                $cart['delivery_fee'] = $settings['delivery_fee'];
									return response()->json([
					                                            'status' => true,
					                                            'message' => "Cart Created Successfully.",
					                                            'data' => $cart
					                                        ], 200);
								}else{
									return response()->json([
						                                        'status' => false,
						                                        'message' => 'Something went wrong.',
						                                    ], 422);
								}
								return response()->json([
				                                            'status' => true,
				                                            'message' => "Cart Updated Successfully.",
				                                            'data' => $cart
				                                        ], 200);	
							}else{
								return response()->json([
					                                        'status' => false,
					                                        'message' => 'Something went wrong.',
					                                    ], 422);	
							}
						}else{
							$cartItemDetails = new CartItemsDetail;
		            		$cartItemDetails->cart_id = $check->id;
		            		$cartItemDetails->user_id = $user->id;
		            		$cartItemDetails->item_id = $request->item_id;
		            		$cartItemDetails->quantity = $request->quantity;
		            		$cartItemDetails->price = $request->price;
			            	//if($request->has('item_choices') && !empty($request->item_choices)){
			            		//$cartItemDetails->item_choices = " ";
			            	//}
			            	if($cartItemDetails->save()){
			            		$check->quantity = $request->quantity + $check->quantity;
								$check->total_price = $check->total_price + ($request->quantity*$request->price);
								if($check->save()){
									$cart= Cart::where("id",$check->id)->with("cart_details")->first();
									$cart['cart_id'] = $cart['id'];
									foreach ($cart['cart_details'] as $key => $cartDetails) {
										$items = Item::where('id', $cartDetails->item_id)->first();
										$cart['cart_details'][$key]->item_name = $items->name;
										$cart['cart_details'][$key]->item_french_name = $items->french_name;
									}
	                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
	                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
	                                
	                                $cart['app_fee'] = $settings['app_fee'];
	                                $cart['delivery_fee'] = $settings['delivery_fee'];
									return response()->json([
					                                            'status' => true,
					                                            'message' => "Cart Created Successfully.",
					                                            'data' => $cart
					                                        ], 200);
								}else{
									return response()->json([
						                                        'status' => false,
						                                        'message' => 'Something went wrong.',
						                                    ], 422);
								}
							}
						}
            		}
            	}


            }else{

	            $cart = new Cart;
	            $cart->user_id = $user->id;
	            $cart->restaurant_id = $request->restaurant_id;
	            $cart->quantity = $request->quantity;
	            $price = $request->quantity*$request->price;
	            $cart->total_price = $price;
	            if($request->has('group_order') && !empty($request->group_order)){
	            	if($request->group_order == '1'){
	            		$cart->group_order = '1';
	            	}
	            }

	            $userOrderType = UserOrderType::where('restaurant_id', $request->restaurant_id)
	            							->where('user_id', $user->id)
	            							->first();

	            if($userOrderType){
	            	$cart->cart_type = $userOrderType['order_type'];
	            }else{
	            	$cart->cart_type = '1';
	            }
	            if($cart->save()){
	            	$cartUser = CartUser::where('cart_id', $cart->id)
            						->where('user_id', $user->id)
            						->first();
					if($cartUser){

					}else{
						$newCartUser = new CartUser;
						$newCartUser->cart_id = $cart->id;
						$newCartUser->user_id = $user->id;
						$newCartUser->save();

					}
	            	if($request->has('group_order') && !empty($request->group_order)){
	            		if($request->group_order == '1'){
	            			$cartUser = new CartUser;
	            			$cartUser->cart_id = $cart->id;
	            			$cartuser->user_id = $user_id;
	            			$cartuser->save();
	            		}
	            	}
	            	$cartItemDetails = new CartItemsDetail;
	            	$cartItemDetails->cart_id = $cart->id;
	            	$cartItemDetails->user_id = $user->id;
	            	$cartItemDetails->item_id = $request->item_id;
	            	$cartItemDetails->quantity = $request->quantity;
	            	$cartItemDetails->price = $request->price;
	            	if($request->has('item_choices') && !empty($request->item_choices)){
	            		$cartItemDetails->item_choices = $request->item_choices;
	            	}
	            	if($cartItemDetails->save()){
	            		$cart['cart_id'] = $cart['id'];
	            		//changes made by dilpreet added [0](array) for cart details 
	            		//added cart id outside cart detail in cart_id
	            		$cart['cart_details'][0] = $cartItemDetails;
						$items = Item::where('id', $cartItemDetails['item_id'])->first();
						$cart['cart_details'][0]['item_name'] = $items['name'];
						$cart['cart_details'][0]['item_french_name'] = $items['french_name'];
						$cart['restaurant_name'] = $userName[$cart['restaurant_id']];
                        $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
                        
                        $cart['app_fee'] = $settings['app_fee'];
                        $cart['delivery_fee'] = $settings['delivery_fee'];
	            		return response()->json([
		                                            'status' => true,
		                                            'message' => "Cart Created Successfully.",
		                                            'data' => $cart
		                                        ], 200);
	            	}else{
	            		$delete = CartItemsDetail::where('cart_id', $cart->id)->delete();
	            		$delete = Cart::where('id', $cart->id)->delete();
	            		return response()->json([
			                                        'status' => false,
			                                        'message' => 'Something went wrong.',
			                                    ], 422);
	            	}

	            }else{
	            	return response()->json([
		                                        'status' => false,
		                                        'message' => 'Something went wrong.',
		                                    ], 422);
	            }
	        }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function checkLoginLogoutCart(Request $request){
    	try{
    		$rules = [
                        'device_id' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $user = Auth::user();

            $deviceId = $request->device_id;
            $logoutCart = Cart::where('user_id', $deviceId)->where('group_order', '<>', '1')->where('status', '1')->first();
            $loginCart = Cart::where('user_id', $user->id)->where('group_order', '<>', '1')->where('status', '1')->first();

            $restaurantLogin = User::where('id', $loginCart['restaurant_id'])->first();
            $restaurantLogout = User::where('id', $logoutCart['restaurant_id'])->first();

            if($logoutCart){
            	$logoutCart['restaurant_name'] = $restaurantLogout['name'];
            }

            if($loginCart){
            	$loginCart['restaurant_name'] = $restaurantLogin['name'];
            }

            $carts = array();
            $carts['logout_cart'] = $logoutCart;
            $carts['login_cart'] = $loginCart;
            if($carts){
            	if(!empty($logoutCart) && empty($loginCart)){
            		CartItemsDetail::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
	            	UserOrderType::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
	            	$updatedCart = Cart::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
	            	$cart = Cart::where('user_id', $user->id)->first();
	            	//Cart::where('user_id', $user->id)->where('status', '1')->update(['status' => '0']);
	            	$carts['login_cart'] = $cart;
	            	$carts['logout_cart'] = null;
            	}
            	// if(empty($logoutCart) && !empty($loginCart)){
            		
            	// }
            	return response()->json([
	                                        'status' => true,
	                                        'message' => "Cart Found.",
	                                        'data' => $carts
	                                    ], 200);	
            }else{
            	return response()->json([
	                                        'status' => false,
	                                        'message' => "Cart Not Found.",
	                                        'data' => $carts
	                                    ], 200);	
            }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function changeUserInCart(request $request){
    	try{
    		$rules = [
                        'device_id' => 'required',
                        //'selected_cart_id' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $deviceId = $request->device_id;
            
            $user = Auth::user();
            $logoutCart = Cart::where('user_id', $deviceId)->where('group_order', '<>', '1')->where('status', '1')->first();
            $loginCart = Cart::where('user_id', $user->id)->where('group_order', '<>', '1')->where('status', '1')->first();
            //$carts = array_merge($logoutCart, $loginCart);
            if(!empty($logoutCart) && !empty($loginCart)){
            	//if both cart is there
            	$rules = [
	                        'selected_cart_id' => 'required',
	                   	];
	            $validator = Validator::make($request->all(), $rules);

	            if ($validator->fails()) {
	                $errors = $validator->errors()->first();
	                return response()->json([
	                                            'status' => false,
	                                            'message' => $errors,
	                                            //'errors' => $errors
	                                        ], 400);
	            }
	            $selectedCartId = $request->selected_cart_id;
	            if($selectedCartId == $logoutCart['id']){
	            	CartItemsDetail::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
	            	UserOrderType::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
	            	Cart::where('user_id', $user->id)->where('status', '1')->update(['status' => '0']);
	            	Cart::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
	            	//	
	            	return response()->json([
	                                            'status' => true,
	                                            'message' => "updated.",
	                                            'data' => $logoutCart
	                                        ], 200);
	            }elseif($selectedCartId == $loginCart['id']){
	            	CartItemsDetail::where('cart_id', $logoutCart['id'])->delete();
	            	UserOrderType::where('user_id', $deviceId)->delete();
	            	Cart::where('user_id', $deviceId)->where('status', '1')->delete();
	            	return response()->json([
	                                            'status' => true,
	                                            'message' => "updated.",
	                                            'data' => $loginCart
	                                        ], 200);
	            }
	            
            }elseif(!empty($logoutCart) && empty($loginCart)){
            	//if only logout cart is there
            	CartItemsDetail::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
            	UserOrderType::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
            	Cart::where('user_id', $deviceId)->update(["user_id"=>$user->id]);
            	Cart::where('user_id', $user->id)->where('status', '1')->update(['status' => '0']);	
            	return response()->json([
                                            'status' => true,
                                            'message' => "updated.",
                                            'data' => $logoutCart
                                        ], 200);
            }elseif(empty($logoutCart) && !empty($loginCart)){
            	//if only login cart is there
            	return response()->json([
                                            'status' => true,
                                            'message' => "updated.",
                                            'data' => $loginCart
                                        ], 200);
            }else{
            	//Cart::where('id', $request->cart_id)->update(["user_id"=>$request->user()->id]);
	            return response()->json([
                                            'status' => false,
                                            'message' => "No Cart found with this id",
                                        ], 200);
            }
            

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

   public function addCart(Request $request){
    	//echo'<pre>';print_r($request->all());die;
    	try{
    		$rules = [
                        'restaurant_id' => 'required',
                        'item_id' => 'required',
                        //'item_choices' => 'required',
                        'quantity' => 'required',
                        'price' => 'required',
                        //'group_order' => 'required',
                        //'cart_type' => 'required', //1:delivery, 2:pickup
                        'login_type' => 'required',//1:with login ,2:without login
                        'user_id' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            
            $user_id = $request->user_id;
            
            

            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $userImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
            $settings = Setting::where('id', '1')->first();

            $checkitem = Item::where('id', $request->item_id)->first();
            if($checkitem['status'] == '0'){
            	return response()->json([
	                                        'status' => false,
	                                        'message' => 'This Item is Currently Unavailable.',
	                                    ], 200);
            }

            $check = Cart::where('user_id', $user_id)->where('group_order', '<>', '1')->where('status','1')->first();
            if($check){

            	if($check->restaurant_id != $request->restaurant_id){
            		return response()->json([
		                                        'status' => false,
		                                        'message' => 'You can only order from one menu at a time.',
		                                    ], 422);
            	}else{
            		$checkCartUser = CartUser::where('cart_id', $check->id)->where('user_id', $user_id);
            		if($checkCartUser){

            		}else{
            			$cartUser = new CartUser;
	        			$cartUser->cart_id = $cart->id;
	        			$cartuser->user_id = $user_id;
	        			$cartuser->save();
	        		}
            		if($request->has('item_choices') && !empty($request->item_choices)){
            			$cartItemDetails = new CartItemsDetail;
		            	$cartItemDetails->cart_id = $check->id;
		            	$cartItemDetails->user_id = $user_id;
		            	$cartItemDetails->item_id = $request->item_id;
		            	$cartItemDetails->quantity = $request->quantity;
		            	$cartItemDetails->price = $request->price;
		            	//if($request->has('item_choices') && !empty($request->item_choices)){
		            		$cartItemDetails->item_choices = $request->item_choices;
		            	//}
		            	if($cartItemDetails->save()){
		            		$check->quantity = $request->quantity + $check->quantity;
							$check->total_price = $check->total_price + ($request->quantity*$request->price);
							if($check->save()){
								$cart= Cart::where("id",$check->id)->with("cart_details")->first();
								$cart['cart_id'] = $cart['id'];
								foreach ($cart['cart_details'] as $key => $cartDetails) {
									$items = Item::where('id', $cartDetails->item_id)->first();
									//echo $cartDetails->item_id;
									//echo "<pre>";print_r($items);die;
									$cart['cart_details'][$key]->item_name = $items['name'];
									$cart['cart_details'][$key]->item_french_name = $items['french_name'];
								}
                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
                                $cart['app_fee'] = $settings['app_fee'];
                                $cart['delivery_fee'] = $settings['delivery_fee'];
								return response()->json([
				                                            'status' => true,
				                                            'message' => "Cart Created Successfully.",
				                                            'data' => $cart
				                                        ], 200);
							}else{
								return response()->json([
					                                        'status' => false,
					                                        'message' => 'Something went wrong.',
					                                    ], 422);
							}
		            	}else{
		            		//$delete = Cart::where('id', $cart->id)->delete();
		            		return response()->json([
				                                        'status' => false,
				                                        'message' => 'Something went wrong.',
				                                    ], 422);
		            	}
            		}else{
						$checkItemDetails = CartItemsDetail::where('cart_id', $check->id)
            											->where('item_id', $request->item_id)
            											//->where('item_choices', '')
            											->first();
						//return $checkItemDetails;
						if($checkItemDetails){
							if($checkItemDetails->item_choices != ''){
								$cartItemDetails = new CartItemsDetail;
			            		$cartItemDetails->cart_id = $check->id;
			            		$cartItemDetails->user_id = $user_id;
			            		$cartItemDetails->item_id = $request->item_id;
			            		$cartItemDetails->quantity = $request->quantity;
			            		$cartItemDetails->price = $request->price;
				            	//if($request->has('item_choices') && !empty($request->item_choices)){
				            		//$cartItemDetails->item_choices = " ";
				            	//}
				            	if($cartItemDetails->save()){
				            		$check->quantity = $request->quantity + $check->quantity;
									$check->total_price = $check->total_price + ($request->quantity*$request->price);
									if($check->save()){
										$cart= Cart::where("id",$check->id)->with("cart_details")->first();
										foreach ($cart['cart_details'] as $key => $cartDetails) {
											$items = Item::where('id', $cartDetails->item_id)->first();
											$cart['cart_details'][$key]->item_name = $items->name;
											$cart['cart_details'][$key]->item_french_name = $items->french_name;
										}
		                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
		                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
		                                $cart['cart_id'] = $cart['id'];
		                                $cart['app_fee'] = $settings['app_fee'];
		                                $cart['delivery_fee'] = $settings['delivery_fee'];
										return response()->json([
						                                            'status' => true,
						                                            'message' => "Cart Created Successfully.",
						                                            'data' => $cart
						                                        ], 200);
									}else{
										return response()->json([
							                                        'status' => false,
							                                        'message' => 'Something went wrong.',
							                                    ], 422);
									}
								}
							}
							$checkItemDetails->quantity = $checkItemDetails['quantity'] + $request->quantity;
							if($checkItemDetails->save()){
								$check->quantity = $request->quantity + $check->quantity;
								$check->total_price = $check->total_price + ($request->quantity*$request->price);
								if($check->save()){
									$cart= Cart::where("id",$check->id)->with("cart_details")->first();
									$cart['cart_id'] = $cart['id'];
									foreach ($cart['cart_details'] as $key => $cartDetails) {
										$items = Item::where('id', $cartDetails->item_id)->first();
										$cart['cart_details'][$key]->item_name = $items->name;
										$cart['cart_details'][$key]->item_french_name = $items->french_name;
									}
	                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
	                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
	                                $cart['app_fee'] = $settings['app_fee'];
	                                $cart['cart_id'] = $cart['id'];
	                                $cart['delivery_fee'] = $settings['delivery_fee'];
									return response()->json([
					                                            'status' => true,
					                                            'message' => "Cart Created Successfully.",
					                                            'data' => $cart
					                                        ], 200);
								}else{
									return response()->json([
						                                        'status' => false,
						                                        'message' => 'Something went wrong.',
						                                    ], 422);
								}
								return response()->json([
				                                            'status' => true,
				                                            'message' => "Cart Updated Successfully.",
				                                            'data' => $cart
				                                        ], 200);	
							}else{
								return response()->json([
					                                        'status' => false,
					                                        'message' => 'Something went wrong.',
					                                    ], 422);	
							}
						}else{
							$cartItemDetails = new CartItemsDetail;
		            		$cartItemDetails->cart_id = $check->id;
		            		$cartItemDetails->user_id = $user_id;
		            		$cartItemDetails->item_id = $request->item_id;
		            		$cartItemDetails->quantity = $request->quantity;
		            		$cartItemDetails->price = $request->price;
			            	//if($request->has('item_choices') && !empty($request->item_choices)){
			            		//$cartItemDetails->item_choices = " ";
			            	//}
			            	if($cartItemDetails->save()){
			            		$check->quantity = $request->quantity + $check->quantity;
								$check->total_price = $check->total_price + ($request->quantity*$request->price);
								if($check->save()){
									$cart= Cart::where("id",$check->id)->with("cart_details")->first();
									$cart['cart_id'] = $cart['id'];
									foreach ($cart['cart_details'] as $key => $cartDetails) {
										$items = Item::where('id', $cartDetails->item_id)->first();
										$cart['cart_details'][$key]->item_name = $items->name;
										$cart['cart_details'][$key]->item_french_name = $items->french_name;
									}
	                                $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
	                                $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
	                                
	                                $cart['app_fee'] = $settings['app_fee'];
	                                $cart['delivery_fee'] = $settings['delivery_fee'];
									return response()->json([
					                                            'status' => true,
					                                            'message' => "Cart Created Successfully.",
					                                            'data' => $cart
					                                        ], 200);
								}else{
									return response()->json([
						                                        'status' => false,
						                                        'message' => 'Something went wrong.',
						                                    ], 422);
								}
							}
						}
            		}
            	}

            }else{

	            $cart = new Cart;
	            $cart->user_id = $user_id;
	            $cart->restaurant_id = $request->restaurant_id;
	            $cart->quantity = $request->quantity;
	            $cart->login_type = $request->login_type;
	            $price = $request->quantity*$request->price;
	            $cart->total_price = $price;
	            if($request->has('group_order') && !empty($request->group_order)){
	            	if($request->group_order == '1'){
	            		$cart->group_order = '1';
	            	}
	            }

	            $userOrderType = UserOrderType::where('restaurant_id', $request->restaurant_id)
	            							->where('user_id', $user_id)
	            							->first();

	            if($userOrderType){
	            	$cart->cart_type = $userOrderType['order_type'];
	            }else{
	            	$cart->cart_type = '1';
	            }
	            if($cart->save()){
	            	
	            	if($request->has('group_order') && !empty($request->group_order)){
	            		if($request->group_order == '1'){
	            			$cartUser = new CartUser;
	            			$cartUser->cart_id = $cart->id;
	            			$cartuser->user_id = $user_id;
	            			$cartuser->save();
	            		}
	            	}
	            	$cartItemDetails = new CartItemsDetail;
	            	$cartItemDetails->cart_id = $cart->id;
	            	$cartItemDetails->user_id = $user_id;
	            	$cartItemDetails->item_id = $request->item_id;
	            	$cartItemDetails->quantity = $request->quantity;
	            	$cartItemDetails->price = $request->price;
	            	if($request->has('item_choices') && !empty($request->item_choices)){
	            		$cartItemDetails->item_choices = $request->item_choices;
	            	}
	            	if($cartItemDetails->save()){
	            		$cart['cart_id'] = $cart['id'];
	            		//changes made by dilpreet added [0](array) for cart details 
	            		//added cart id outside cart detail in cart_id
	            		$cart['cart_details'][0] = $cartItemDetails;
						$items = Item::where('id', $cartItemDetails['item_id'])->first();
						$cart['cart_details'][0]['item_name'] = $items['name'];
						$cart['cart_details'][0]['item_french_name'] = $items['french_name'];
						$cart['restaurant_name'] = $userName[$cart['restaurant_id']];
                        $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
                        
                        $cart['app_fee'] = $settings['app_fee'];
                        $cart['delivery_fee'] = $settings['delivery_fee'];
	            		return response()->json([
		                                            'status' => true,
		                                            'message' => "Cart Created Successfully.",
		                                            'data' => $cart
		                                        ], 200);
	            	}else{
	            		$delete = CartItemsDetail::where('cart_id', $cart->id)->delete();
	            		$delete = Cart::where('id', $cart->id)->delete();
	            		return response()->json([
			                                        'status' => false,
			                                        'message' => 'Something went wrong.',
			                                    ], 422);
	            	}

	            }else{
	            	return response()->json([
		                                        'status' => false,
		                                        'message' => 'Something went wrong.',
		                                    ], 422);
	            }
	        }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function updateCartItemQty(Request $request){
    	try{
			$rules = [
	                    'cart_item_id' => 'required',
	                    'quantity' => 'required',
	                    'cart_id' => 'required',
	                    'user_id' => 'required',
	                    "login_type" => 'required'
	               	];
	        $validator = Validator::make($request->all(), $rules);

	        if ($validator->fails()) {
	            $errors = $validator->errors()->first();
	            return response()->json([
	                                        'status' => false,
	                                        'message' => $errors,
	                                        //'errors' => $errors
	                                    ], 400);
	        }

	        $user_id = $request->user_id;
	        $cartId = $request->cart_id;
	        $cartItemId = $request->cart_item_id;

	        $cart = Cart::where('id', $cartId)->first();
	        if($cart){
	        	$checkCartUser = CartUser::where('cart_id', $cartId)->where('user_id', $user_id);
        		if($checkCartUser){

        		}else{
        			if($request->login_type == 1){
        				$cartUser = new CartUser;
	        			$cartUser->cart_id = $cart->id;
	        			$cartuser->user_id = $user_id;
	        			$cartuser->save();	
        			}
        			
        		}
		        $cartItem = CartItemsDetail::where('id', $cartItemId)->first();
		        //echo'<pre>';print_r($cartItem);die;
		        $totalQty = $cartItem['quantity'] + $request->quantity;
		        //echo $totalQty;die;
		        if($totalQty == 0){
		        	$cartItem = CartItemsDetail::where('id', $cartItemId)->delete();
		        	$checkCartItems = CartItemsDetail::where('cart_id', $cartId)->get()->toArray();
		        	if($checkCartItems){
		        		$quantitySum = CartItemsDetail::where('cart_id', $cartId)->sum('quantity');
		        		$priceSum = CartItemsDetail::where('cart_id', $cartId)->sum(\DB::raw('price * quantity'));
		        		//$totalQuantity = $cart['quantity'] + $request->quantity;
		        		$cart = Cart::where('id', $cartId)->update(['quantity' => $quantitySum, 'total_price' => $priceSum]);
		        		return response()->json([
		                                            'status' => true,
		                                            'message' => "Cart Updated Successfully.",
		                                            'data' => $cart
		                                        ], 200);
		        	}else{
		        		if($cart['group_order'] == '0'){
		        			$cart = Cart::where('id', $cartId)->delete();
		        		}
		        		return response()->json([
			                                        'status' => true,
			                                        'message' => "Cart is empty. Add items to cart.",
			                                        'data' => $cart,
			                                        //'wallet' => $user->wallet
			                                    ], 200);
		        	}
		        }else{
		        	$cartItem->quantity = $totalQty;
		        	if($cartItem->save()){
		        		$quantitySum = CartItemsDetail::where('cart_id', $cartId)->sum('quantity');
		        		$priceSum = CartItemsDetail::where('cart_id', $cartId)->sum(\DB::raw('price * quantity'));
		        		//$totalQuantity = $cart['quantity'] + $request->quantity;
		        		$cart = Cart::where('id', $cartId)->update(['quantity' => $quantitySum, 'total_price' => $priceSum]);
		        		return response()->json([
		                                            'status' => true,
		                                            'message' => "Cart Updated Successfully.",
		                                            'data' => $cart
		                                        ], 200);	
					}else{
						return response()->json([
			                                        'status' => false,
			                                        'message' => 'Something went wrong.',
			                                    ], 422);	
					}
		        }
		    }else{
		    	return response()->json([
	                                        'status' => false,
	                                        'message' => "Cart is empty. Add items to cart.",
	                                        'data' => $cart,
	                                        //'wallet' => $user->wallet
	                                    ], 200);
		    }
	    }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function cartItemList(request $request){
    	try{
    		$rules = [
	                    'item_id' => 'required',
	                    'user_id' => 'required',
	                    "login_type" => 'required'
	               	];
	        $validator = Validator::make($request->all(), $rules);

	        if ($validator->fails()) {
	            $errors = $validator->errors()->first();
	            return response()->json([
	                                        'status' => false,
	                                        'message' => $errors,
	                                        //'errors' => $errors
	                                    ], 400);
	        }

	        if($request->login_type == 2){
	        	$user_id = $request->user_id;
	        	//$user = User::where("id",$user_id)->first();
	        }else{
	        	$user_id = $request->user_id;
	        	$user = User::where("id",$user_id)->first();
	        }

    		
    		$cart= Cart::where("user_id",$user_id)->where('group_order', '<>', '1')->where('status', '1')->first();
    		//return $cart;
    		$cartItems = CartItemsDetail::where('cart_id', $cart['id'])->where('item_id', $request->item_id)->get()->toArray();
    		
    		if($cartItems){
    			foreach ($cartItems as $key => $cartItem) {
    				$items = Item::where('id', $cartItem['item_id'])->first();
    				if($cartItem['item_choices'] != ""){
						$itemChoices = json_decode($cartItem['item_choices']);
						if($itemChoices){
							foreach ($itemChoices as $key1 => $itemChoice) {
								$finalResultToAppend = array();
								$itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
								//echo'<pre>';print_r($itemCategory);die;
								$itemChoices[$key1]->name = $itemCategory['name'];
								$itemChoices[$key1]->french_name = $itemCategory['french_name'];
								$itemChoices[$key1]->selection = $itemCategory['selection'];
								$itemChoices[$key1]->veg = $items['pure_veg'];
								$itemSubCats = explode(',', $itemChoice->item_sub_category);
								//echo'<pre>';print_r($itemSubCats);die;
								foreach ($itemSubCats as $key3 => $itemSubCat) { 
									$itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

									$finalResultToAppend[] = array("id" => $itemSubCategory['id'],
												"name" => $itemSubCategory['name'],
												"french_name" => $itemSubCategory['french_name'], 
												"add_on_price" => $itemSubCategory['add_on_price']
														);		
									
								}
								$itemChoices[$key1]->item_sub_category = $finalResultToAppend;
							}
						}

					}else{
						$itemChoices = array();
					}
					
					$cartItems[$key]['item_choices'] = $itemChoices;
    				$cartItems[$key]['item_name'] = $items['name'];
    				$cartItems[$key]['item_french_name'] = $items['french_name'];
    				$cartItems[$key]['veg'] = $items['pure_veg'];

    				if($request->login_type == 2){
    					//$usersInfo = User::where('id', $cartItem['user_id'])->first();
	    				$cartItems[$key]['user_name'] = "";
	    				$cartItems[$key]['user_french_name'] = "";
    				}else{
    					$usersInfo = User::where('id', $cartItem['user_id'])->first();
    					$cartItems[$key]['user_name'] = $usersInfo->name;
    					$cartItems[$key]['user_french_name'] = $usersInfo->french_name;
    				}

    				
    			}

    			return response()->json([
                                            'status' => true,
                                            'message' => "Items Found Successfully.",
                                            'data' => $cartItems,
                                            //'wallet' => $user->wallet
                                        ], 200);
    		}else{
    			return response()->json([
                                            'status' => false,
                                            'message' => "Items Not Found.",
                                            'data' => $cartItems,
                                            //'wallet' => $user->wallet
                                        ], 200);
    		}

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function groupCartItemList(Request $request){
    	try{

    		$rules = [
	                    'item_id' => 'required',
	                    'cart_id' => 'required'
	               	];
	        $validator = Validator::make($request->all(), $rules);

	        if ($validator->fails()) {
	            $errors = $validator->errors()->first();
	            return response()->json([
	                                        'status' => false,
	                                        'message' => $errors,
	                                        //'errors' => $errors
	                                    ], 400);
	        }

    		$user = Auth::user();
    		$cartId = $request->cart_id;
    		$itemId = $request->item_id;
    		//$cart= Cart::where("user_id", $user->id)->where('status', '1')->first();
    		//return $cart;
    		$cartItems = CartItemsDetail::where('cart_id', $cartId)
										->where('user_id', $user->id)
										->where('item_id', $itemId)
										->get()
										->toArray();
    		
    		if($cartItems){
    			foreach ($cartItems as $key => $cartItem) {
    				$items = Item::where('id', $cartItem['item_id'])->first();
    				if($cartItem['item_choices'] != ""){
						$itemChoices = json_decode($cartItem['item_choices']);
						if($itemChoices){
							foreach ($itemChoices as $key1 => $itemChoice) {
								$finalResultToAppend = array();
								$itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
								//echo'<pre>';print_r($itemCategory);die;
								$itemChoices[$key1]->name = $itemCategory['name'];
								$itemChoices[$key1]->french_name = $itemCategory['french_name'];
								$itemChoices[$key1]->selection = $itemCategory['selection'];
								$itemChoices[$key1]->veg = $items['pure_veg'];
								$itemSubCats = explode(',', $itemChoice->item_sub_category);
								//echo'<pre>';print_r($itemSubCats);die;
								foreach ($itemSubCats as $key3 => $itemSubCat) { 
									$itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

									$finalResultToAppend[] = array("id" => $itemSubCategory['id'],
												"name" => $itemSubCategory['name'],
												"french_name" => $itemSubCategory['french_name'], 
												"add_on_price" => $itemSubCategory['add_on_price']
														);		
									
								}
								$itemChoices[$key1]->item_sub_category = $finalResultToAppend;
							}
						}

					}else{
						$itemChoices = array();
					}
					
					$cartItems[$key]['item_choices'] = $itemChoices;
    				$cartItems[$key]['item_name'] = $items['name'];
    				$cartItems[$key]['item_french_name'] = $items['french_name'];
    				$cartItems[$key]['veg'] = $items['pure_veg'];
    				$usersInfo = User::where('id', $cartItem['user_id'])->first();
    				$cartItems[$key]['user_name'] = $usersInfo->name;
    				$cartItems[$key]['user_french_name'] = $usersInfo->french_name;
    			}

    			return response()->json([
                                            'status' => true,
                                            'message' => "Items Found Successfully.",
                                            'data' => $cartItems,
                                            //'wallet' => $user->wallet
                                        ], 200);
    		}else{
    			return response()->json([
                                            'status' => false,
                                            'message' => "Items Not Found.",
                                            'data' => $cartItems,
                                            //'wallet' => $user->wallet
                                        ], 200);
    		}

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function viewGroupcart(Request $request){
    	try{
    		$rules = [
                        'cart_id' => 'required',
                        'latitude' => 'required',
                        'longitude' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }
            
            $user = Auth::user();
            $cartId = $request->cart_id;
            $settings = Setting::where('id', '1')->first();
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $userImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
            $cart = Cart::where("id", $cartId)->where('status', '1')->first();
            $itemIds = array();
            //return $cart;
            if($cart){
            	$cart['user_name'] = $userName[$cart['user_id']];
	            $cartUsers = CartUser::where('cart_id', $cart['id'])->get()->toArray();
	            //echo'<pre>';print_r($cartUsers);die;
	            $usersCartItemsDetail = array();
	            foreach ($cartUsers as $key => $cartUsers) {
	            	//ECHO $cartItemsDetail['user_id'];DIE;
	            	$userInfo = User::where('id', $cartUsers['user_id'])
	            						->select('id', 'name', 'french_name')
	            						->first()
	            						->toArray();
					//echo'<pre>';print_r($userInfo);die;
					$cartInfo = CartItemsDetail::where('cart_id', $cart['id'])
												->where('user_id', $userInfo['id'])
												->get()
												->toArray();

					foreach ($cartInfo as $cartK => $info) {    	
	                    if($info['quantity'] == '0'){
	                        $deleteCartItem = CartItemsDetail::where('id', $info['id'])->delete();
	                    }
	                    $itemIds[] = $info['item_id'];
	                    $items = Item::where('id', $info['item_id'])->first();
	                    if($info['item_choices'] != ""){
	                        //echo $cartDetails->item_choices;die;
	                        $itemChoices = json_decode($info['item_choices']);
	                        //echo'<pre>';print_r($itemChoices);die;
	                        if($itemChoices){
	                            foreach ($itemChoices as $key1 => $itemChoice) {
	                                $finalResultToAppend = array();
	                                $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
	                                //echo'<pre>';print_r($itemCategory);die;
	                                $itemChoices[$key1]->name = $itemCategory['name'];
	                                $itemChoices[$key1]->french_name = $itemCategory['french_name'];
	                                $itemChoices[$key1]->selection = $itemCategory['selection'];
	                                $itemChoices[$key1]->veg = $items['pure_veg'];
	                                $itemSubCats = explode(',', $itemChoice->item_sub_category);
	                                //echo'<pre>';print_r($itemSubCats);die;
	                                foreach ($itemSubCats as $key3 => $itemSubCat) { 
	                                    $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

	                                    $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
	                                                "name" => $itemSubCategory['name'],
	                                                "french_name" => $itemSubCategory['french_name'], 
	                                                "add_on_price" => $itemSubCategory['add_on_price']
	                                                        );   
	                                }
	                                $itemChoices[$key1]->item_sub_category = $finalResultToAppend;
	                            }
	                        }

	                    }else{
	                        $itemChoices = array();
	                    }
	                    //echo "<pre>";print_r($cart['cart_details'][0]);die;
	                    //return $cart['cart_details'][$key2]['cart'][$uk]['item_choices'];
	                    //$userInfo = User::where('id', $info['user_id'])->first();
	                    $cartInfo[$cartK]['item_choices'] = $itemChoices;
	                    $cartInfo[$cartK]['item_name'] = $items['name'];
	                    $cartInfo[$cartK]['item_french_name'] = $items['french_name'];
	                    $cartInfo[$cartK]['veg'] = $items['pure_veg'];
	                    $cartInfo[$cartK]['approx_prep_time'] = $items['approx_prep_time'];
					}
					$userInfo['cart'] = $cartInfo;
	            	$usersCartItemsDetail[] = $userInfo;
	            }
	            //$usersCartItemsDetail = array_unique($usersCartItemsDetail);
	            //echo'<pre>';print_r($usersCartItemsDetail);die;
	            $cart['cart_details'] = $usersCartItemsDetail;
	            //echo'<pre>';print_r($cart);die;
	            $restaurantInfo = User::where('id', $cart['restaurant_id'])->first();

	            $destinationLat = $request->latitude;
	            $destinationLong = $request->logitude;
	            $originLat = $restaurantInfo['latitude'];
	            $originLong = $restaurantInfo['longitude'];
	            
	            //echo'<pre>';print_r($estimatedDeliveryTime);die;
	            $ratings = RatingReview::where('receiver_type', '2')
	                                    ->where('receiver_id', $cart['restaurant_id'])
	                                    ->get()
	                                    ->toArray();
	            $avergeRating = "0.0";
	            if($ratings){
	                $ratingArr = array();
	                foreach ($ratings as $key1 => $rating) {
	                    $ratingArr[] = $rating['rating'];
	                }
	                $totalRating = count($ratings);
	                $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
	            }else{
	                $totalRating = "0";
	            }
	        }
            // $resultRestaurants[$key]['average_rating'] = $avergeRating;
            // $resultRestaurants[$key]['total_rating'] = $totalRating;
            
            if($cart){
                if($originLat && $originLong && $destinationLat && $destinationLong){
                    $estimatedDeliveryTime = $this->deliveryTime($originLat, $originLong, $destinationLat, $destinationLong);
                }else{
                    $estimatedDeliveryTime = "0";
                }
                $lat = $request->latitude;
                $long = $request->longitude;

                $setting = Setting::where('id', '1')->first();
                $distance = $setting->distance;

                $query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`";
                $result = DB::select(DB::raw($query));

                if($result){
                    //$itemIds = array();
                    //echo'<pre>';print_r($cart['cart_details']);die;
                    // foreach ($cart['cart_details'] as $key2 => $cartDetailss) {
                    // 	//return $cartDetailss;
                    // 	foreach ($cartDetailss['cart'] as $uk => $cartDetails) {
	                    	
	                   //      if($cartDetails['quantity'] == '0'){
	                   //          $deleteCartItem = CartItemsDetail::where('id', $cartDetails['id'])->delete();
	                   //      }
	                   //      $itemIds[] = $cartDetails['item_id'];
	                   //      $items = Item::where('id', $cartDetails['item_id'])->first();
	                   //      if($cartDetails['item_choices'] != ""){
	                   //          //echo $cartDetails->item_choices;die;
	                   //          $itemChoices = json_decode($cartDetails['item_choices']);
	                   //          //echo'<pre>';print_r($itemChoices);die;
	                   //          if($itemChoices){
	                   //              foreach ($itemChoices as $key => $itemChoice) {
	                   //                  $finalResultToAppend = array();
	                   //                  $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
	                   //                  //echo'<pre>';print_r($itemCategory);die;
	                   //                  $itemChoices[$key]->name = $itemCategory['name'];
	                   //                  $itemChoices[$key]->french_name = $itemCategory['french_name'];
	                   //                  $itemChoices[$key]->selection = $itemCategory['selection'];
	                   //                  $itemChoices[$key]->veg = $items['pure_veg'];
	                   //                  $itemSubCats = explode(',', $itemChoice->item_sub_category);
	                   //                  //echo'<pre>';print_r($itemSubCats);die;
	                   //                  foreach ($itemSubCats as $key3 => $itemSubCat) { 
	                   //                      $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

	                   //                      $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
	                   //                                  "name" => $itemSubCategory['name'],
	                   //                                  "french_name" => $itemSubCategory['french_name'], 
	                   //                                  "add_on_price" => $itemSubCategory['add_on_price']
	                   //                                          );   
	                   //                  }
	                   //                  $itemChoices[$key]->item_sub_category = $finalResultToAppend;
	                   //              }
	                   //          }

	                   //      }else{
	                   //          $itemChoices = array();
	                   //      }
	                   //      //echo "<pre>";print_r($cart['cart_details'][0]);die;
	                   //      //return $cart['cart_details'][$key2]['cart'][$uk]['item_choices'];
	                   //      $userInfo = User::where('id', $cartDetails['user_id'])->first();
	                   //      $cart['cart_details'][$key2]['cart'][$uk]['item_choices'] = $itemChoices;
	                   //      $cart['cart_details'][$key2]['cart'][$uk]['item_name'] = $items['name'];
	                   //       $cart['cart_details'][$key2]['cart'][$uk]['item_french_name'] = $items['french_name'];
	                   //      $cart['cart_details'][$key2]['cart'][$uk]['veg'] = $items['pure_veg'];
	                   //      $cart['cart_details'][$key2]['cart'][$uk]['approx_prep_time'] = $items['approx_prep_time'];
	                   //      // $cart['cart_details'][$key2]->user_name = $userInfo['name'];
	                   //      // $cart['cart_details'][$key2]->user_french_name = $userInfo['french_name'];
	                   //  }

                    // }
                    //return $itemIds;
                    $itemsPrepTime = Item::whereIn('id', $itemIds)->pluck('approx_prep_time', 'id')->toArray();
                    //echo'<pre>';print_r($itemsPrepTime);die;
                    //echo max($itemsPrepTime);die;
                    $userOrderType = UserOrderType::where('restaurant_id', $cart['restaurant_id'])
													->where('user_id', $cart['user_id'])
													->first();
					if($userOrderType){
						$cart['cart_type'] = $userOrderType['order_type'];	
					}
                    $cart['restaurant_name'] = $userName[$cart['restaurant_id']];
                    $cart['restaurant_image'] = $userImage[$cart['restaurant_id']];
                    $cart['restaurant_address'] = $restaurantInfo->address;
	                $cart['restaurant_french_address'] = $restaurantInfo->french_address;
	                $cart['restaurant_latitude'] = $restaurantInfo->latitude;
	                $cart['restaurant_longitude'] = $restaurantInfo->longitude;
                    $cart['app_fee'] = $settings['app_fee'];
                    $cart['delivery_fee'] = $settings['delivery_fee'];
                    $cart['average_rating'] = $avergeRating;
                    $cart['wallet'] = $user->wallet;
                    if(is_array($itemsPrepTime) && !empty($itemsPrepTime)){
                    	$cart['estimated_preparing_time'] = max($itemsPrepTime);
                    }else{
                    	$cart['estimated_preparing_time'] = 0;
                    }
                    $cart['estimated_delivery_time'] = (int)$estimatedDeliveryTime;
                    $cart['pickup'] = $restaurantInfo->pickup;
                    $cart['table_booking'] = $restaurantInfo->table_booking;
                    $cart['no_of_seats'] = $restaurantInfo->no_of_seats;
                    $cart['closingTime'] = $restaurantInfo->closingTime;
                    $cart['openingTime'] = $restaurantInfo->openingTime;
                    $cart['fullTime'] = $restaurantInfo->full_time;
                    $cart['busy_status'] = $restaurantInfo->busy_status;

                    $setting = Setting::where('id', '1')->first();
                    $appFee = $setting->app_fee;

                    $moreItems = Item::whereNotIn('id', $itemIds)
                                        ->where('restaurant_id', $cart['restaurant_id'])
                                        ->inRandomOrder()
                                        ->limit(4)
                                        ->get()
                                        ->toArray();
                    $cuisine = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();
                    foreach ($moreItems as $key => $list) {
                        $moreItems[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
                        $oldprice = $list['price'];
                        $appPrice = $oldprice*$appFee/100;
                        $moreItems[$key]['price'] = round($oldprice+$appPrice, 2);
                        $oldOfferPrice = $list['offer_price'];
                        $appofferPrice = $oldOfferPrice*$appFee/100;
                        $moreItems[$key]['offer_price'] = round($oldOfferPrice+$appofferPrice, 2);

                        $moreItems[$key]['restaurant_name'] = $restaurantInfo['name'];
                        $itemCategories = ItemCategory::where('item_id', $list['id'])->get()->toArray();
                        if($itemCategories){
                            foreach ($itemCategories as $key1 => $itemCategorie) {
                                $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                                $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                            }
                            $moreItems[$key]['item_categories'] = $itemCategories;
                        }else{
                            $moreItems[$key]['item_categories'] = array();
                        }

                        $ratings = RatingReview::where('receiver_type', '1')
                                                ->where('receiver_id', $list['id'])
                                                ->get()
                                                ->toArray();
                        $avergeRating = "0.0";
                        if($ratings){
                            $ratingArr = array();
                            foreach ($ratings as $key2 => $rating) {
                                $ratingArr[] = $rating['rating'];
                            }
                            $totalRating = count($ratings);
                            $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
                        }else{
                            $totalRating = "0";
                        }
                        $moreItems[$key]['avg_ratings'] = $avergeRating;
                        $moreItems[$key]['total_rating'] = $totalRating;
                        //$userId = $user->id;
                        $cart1 = Cart::where('user_id', $cartId)->first();
                        if($cart1){
                            $cartItemsDetail = CartItemsDetail::where('cart_id', $cart1['id'])->where('item_id', $list['id'])->sum('quantity');
                            $moreItems[$key]['item_count_in_cart'] = $cartItemsDetail;
                        }else{
                            $moreItems[$key]['item_count_in_cart'] = '0';
                        }
                    }
                    $cart['add_more_items'] = $moreItems;
                    return response()->json([
                                                'status' => true,
                                                'message' => "Cart Found Successfully.",
                                                'data' => $cart,
                                                //'add_more_items' => $moreItems
                                                //'wallet' => $user->wallet
                                            ], 200);
                }else{
                    $cartItemDetails = CartItemsDetail::where('cart_id', $cart['id'])->delete();
                    $cart = Cart::where('id', $cart['id'])->delete();
                    return response()->json([
                                                'status' => false,
                                                'message' => "The items in your basket can't be delivered to your new address.",
                                                //'data' => $cart,
                                                //'wallet' => $user->wallet
                                            ], 200);
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Cart is empty. Add items to cart.",
                                            'data' => $cart,
                                            //'add_more_items' => []
                                            //'wallet' => $user->wallet
                                        ], 200);
            }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }


    public function viewCart(Request $request){
    	try{
    		$rules = [
                        'latitude' => 'required',
                        'logitude' => 'required',
                        'login_type' => 'required',//1:with login ,2:without login
                        "user_id" => 'required'
                        //'address' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $user_id = $request->user_id;
            if($request->login_type == 1){
            	$user = User::where("id",$request->user_id)->first();
            }
    		
            $settings = Setting::where('id', '1')->first();
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $userImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
    		$cart = Cart::where("user_id",$user_id)->where('group_order', '<>', '1')->where('status', '1')->with("cart_details")->first();
    		//return $cart;
    		$restaurantInfo = User::where('id', $cart['restaurant_id'])->first();
    		//echo $restaurantInfo->fullTime;die;
    		//echo "<pre>";print_r($restaurantInfo);die;
    		$customerInfo = User::where('id', $cart['user_id'])->first();
//return $restaurantInfo;
    		$destinationLat = $request->latitude;
    		$destinationLong = $request->logitude;
    		$originLat = $restaurantInfo['latitude'];
    		$originLong = $restaurantInfo['longitude'];
    		
    		//echo'<pre>';print_r($estimatedDeliveryTime);die;
    		$ratings = RatingReview::where('receiver_type', '2')
                                                    ->where('receiver_id', $cart['restaurant_id'])
                                                    ->get()
                                                    ->toArray();
            $avergeRating = "0.0";
            if($ratings){
                $ratingArr = array();
                foreach ($ratings as $key1 => $rating) {
                    $ratingArr[] = $rating['rating'];
                }
                $totalRating = count($ratings);
                $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
            }else{
                $totalRating = "0";
            }
            // $resultRestaurants[$key]['average_rating'] = $avergeRating;
            // $resultRestaurants[$key]['total_rating'] = $totalRating;
			
			if($cart){
				if($originLat && $originLong && $destinationLat && $destinationLong){
					$estimatedDeliveryTime = $this->deliveryTime($originLat, $originLong, $destinationLat, $destinationLong);
				}else{
					$estimatedDeliveryTime = "0";
				}
				$lat = $request->latitude;
				$long = $request->logitude;

				$setting = Setting::where('id', '1')->first();
                $distance = $setting->distance;

				$query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`";
            	$result = DB::select(DB::raw($query));

            	if($result){
            		$itemIds = array();
            		//echo'<pre>';print_r($cart['cart_details']);die;
					foreach ($cart['cart_details'] as $key2 => $cartDetails) {
						if($cartDetails->quantity == '0'){
							$deleteCartItem = CartItemsDetail::where('id', $cartDetails->id)->delete();
						}
						$itemIds[] = $cartDetails->item_id;
						$items = Item::where('id', $cartDetails->item_id)->first();
						if($cartDetails->item_choices != ""){
							//echo $cartDetails->item_choices;die;
							$itemChoices = json_decode($cartDetails->item_choices);
							//echo'<pre>';print_r($itemChoices);die;
							if($itemChoices){
								foreach ($itemChoices as $key => $itemChoice) {
									$finalResultToAppend = array();
									$itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
									//echo'<pre>';print_r($itemCategory);die;
									$itemChoices[$key]->name = $itemCategory['name'];
									$itemChoices[$key]->french_name = $itemCategory['french_name'];
									$itemChoices[$key]->selection = $itemCategory['selection'];
									$itemChoices[$key]->veg = $items['pure_veg'];
									$itemSubCats = explode(',', $itemChoice->item_sub_category);
									//echo'<pre>';print_r($itemSubCats);die;
									foreach ($itemSubCats as $key3 => $itemSubCat) { 
										$itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

										$finalResultToAppend[] = array("id" => $itemSubCategory['id'],
													"name" => $itemSubCategory['name'],
													"french_name" => $itemSubCategory['french_name'], 
													"add_on_price" => $itemSubCategory['add_on_price']
															);		
										//echo "<pre>";print_r($itemSubCategory);
											//foreach ($itemSubCategory as $k => $itemSubCatData) {
												/*$itemChoices[$key]->item_sub_category = array($key1 => array("id" => $itemSubCategory['id'],
													"name" => $itemSubCategory['name'],
													"french_name" => $itemSubCategory['french_name'], 
													"add_on_price" => $itemSubCategory['add_on_price']
															));		*/
											//}
										
										
										//die;

										// $itemChoices[$key]['item_sub_category'][$key1]['name'] = $itemSubCategory['name'];
										// $itemChoices[$key]['item_sub_category'][$key1]['french_name'] = $itemSubCategory['french_name'];
										// $itemChoices[$key]['item_sub_category'][$key1]['add_on_price'] = $itemSubCategory['add_on_price'];
									}
									$itemChoices[$key]->item_sub_category = $finalResultToAppend;
								}
							}

						}else{
							$itemChoices = array();
						}
						//echo "<pre>";print_r($cart['cart_details'][0]);die;
						$userInfo = User::where('id', $cartDetails->user_id)->first();
						$cart['cart_details'][$key2]->item_choices = $itemChoices;
						$cart['cart_details'][$key2]->item_name = $items['name'];
						$cart['cart_details'][$key2]->item_french_name = $items['french_name'];
						$cart['cart_details'][$key2]->veg = $items['pure_veg'];
						$cart['cart_details'][$key2]->approx_prep_time = $items['approx_prep_time'];
						$cart['cart_details'][$key2]->user_name = $userInfo['name'];
						$cart['cart_details'][$key2]->user_french_name = $userInfo['french_name'];
					}
					//return $itemIds;
					$itemsPrepTime = Item::whereIn('id', $itemIds)->pluck('approx_prep_time', 'id')->toArray();
					//echo'<pre>';print_r($itemsPrepTime);die;
					//echo max($itemsPrepTime);die;
					$userOrderType = UserOrderType::where('restaurant_id', $cart['restaurant_id'])
													->where('user_id', $cart['user_id'])
													->first();
					if($userOrderType){
						$cart['cart_type'] = $userOrderType['order_type'];	
					}
					$cart['user_name'] = $customerInfo['name'];
	                $cart['restaurant_name'] = $restaurantInfo['name'];
	                $cart['restaurant_image'] = $restaurantInfo['image'];
	                $cart['restaurant_address'] = $restaurantInfo['address'];
	                $cart['restaurant_french_address'] = $restaurantInfo['french_address'];
	                $cart['restaurant_latitude'] = $restaurantInfo['latitude'];
	                $cart['restaurant_longitude'] = $restaurantInfo['longitude'];
	                $cart['busy_status'] = $restaurantInfo['busy_status'];
	                $cart['app_fee'] = $settings['app_fee'];
	                $cart['delivery_fee'] = $settings['delivery_fee'];
	                $cart['average_rating'] = $avergeRating;
	                if($request->login_type == 2){
	                	$cart['wallet'] = "0.00";
	                }else{
	                	$cart['wallet'] = $user->wallet;	
	                }
	                
	                if(is_array($itemsPrepTime) && !empty($itemsPrepTime)){
	                	$cart['estimated_preparing_time'] = max($itemsPrepTime);
	                }else{
	                	$cart['estimated_preparing_time'] = 0;
	                }
	                $cart['estimated_delivery_time'] = (int)$estimatedDeliveryTime;
	                $cart['pickup'] = $restaurantInfo->pickup;
	                $cart['table_booking'] = $restaurantInfo->table_booking;
	                $cart['no_of_seats'] = $restaurantInfo->no_of_seats;
	                $cart['closingTime'] = $restaurantInfo->closing_time;
	                $cart['openingTime'] = $restaurantInfo->opening_time;
	                $cart['fullTime'] = $restaurantInfo->full_time;
	                
	                $setting = Setting::where('id', '1')->first();
                	$appFee = $setting->app_fee;

	                $moreItems = Item::whereNotIn('id', $itemIds)
	                					->where('restaurant_id', $cart['restaurant_id'])
	                					->inRandomOrder()
									    ->limit(4)
									    ->get()
									    ->toArray();
				    $cuisine = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();
	                foreach ($moreItems as $key => $list) {
                        $moreItems[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
                        $oldprice = $list['price'];
                        $appPrice = $oldprice*$appFee/100;
                        $moreItems[$key]['price'] = round($oldprice+$appPrice, 2);
                        $oldOfferPrice = $list['offer_price'];
                        $appofferPrice = $oldOfferPrice*$appFee/100;
                        $moreItems[$key]['offer_price'] = round($oldOfferPrice+$appofferPrice, 2);

                        $moreItems[$key]['restaurant_name'] = $restaurantInfo['name'];
                        $itemCategories = ItemCategory::where('item_id', $list['id'])->get()->toArray();
                        if($itemCategories){
                            foreach ($itemCategories as $key1 => $itemCategorie) {
                                $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                                $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                            }
                            $moreItems[$key]['item_categories'] = $itemCategories;
                        }else{
                            $moreItems[$key]['item_categories'] = array();
                        }

                        $ratings = RatingReview::where('receiver_type', '1')
                                                ->where('receiver_id', $list['id'])
                                                ->get()
                                                ->toArray();
                        $avergeRating = "0.0";
                        if($ratings){
                            $ratingArr = array();
                            foreach ($ratings as $key2 => $rating) {
                                $ratingArr[] = $rating['rating'];
                            }
                            $totalRating = count($ratings);
                            $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
                        }else{
                            $totalRating = "0";
                        }
                        $moreItems[$key]['avg_ratings'] = $avergeRating;
                        $moreItems[$key]['total_rating'] = $totalRating;
                        $userId = $user_id;
                        $cart1 = Cart::where('user_id', $userId)->first();
                        if($cart1){
                            $cartItemsDetail = CartItemsDetail::where('cart_id', $cart1['id'])->where('item_id', $list['id'])->sum('quantity');
                            $moreItems[$key]['item_count_in_cart'] = $cartItemsDetail;
                        }else{
                            $moreItems[$key]['item_count_in_cart'] = '0';
                        }
                    }
				    $cart['add_more_items'] = $moreItems;
					return response()->json([
	                                            'status' => true,
	                                            'message' => "Cart Found Successfully.",
	                                            'data' => $cart,
	                                            //'add_more_items' => $moreItems
	                                            //'wallet' => $user->wallet
	                                        ], 200);
				}else{
					$cartItemDetails = CartItemsDetail::where('cart_id', $cart['id'])->delete();
					$cart = Cart::where('id', $cart['id'])->delete();
					return response()->json([
	                                            'status' => false,
	                                            'message' => "The items in your basket can't be delivered to your new address.",
	                                            //'data' => $cart,
	                                            //'wallet' => $user->wallet
	                                        ], 200);
				}
			}else{
				return response()->json([
                                            'status' => false,
                                            'message' => "Cart is empty. Add items to cart.",
                                            'data' => $cart,
                                            //'add_more_items' => []
                                            //'wallet' => $user->wallet
                                        ], 200);
			}

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    function deliveryTime($originLat, $originLong, $destinationLat, $destinationLong){
    	// echo "ol".$originLat;
    	// echo "olo".$originLong;
    	// echo "dl".$destinationLat;
    	// echo "dlo".$destinationLong;die;
    	$key = "AIzaSyC2aWp0P-YzltErSlsHn7-AJcQV7iTzN5E";
    	$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$originLat.",".$originLong."&destinations=".$destinationLat.",".$destinationLong."&mode=driving&language=en&key=$key";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    $result = curl_exec($ch);
	    curl_close($ch);
	    $response = json_decode($result, true);
	    //echo'<pre>';print_r($response);die;
	    if($response != "" && array_key_exists('distance', $response))
	    	$distance = $response['rows'][0]['elements'][0]['distance']['text'];
		else
			$distance = 0;
		if($response != "" && array_key_exists('duration', $response))
	    	$time = $response['rows'][0]['elements'][0]['duration']['value']/60;
		else
			$time = 0;

	    //return array('distance' => $distance, 'time' => $time);die;
	    return $time;
    }

    public function removeItem(Request $request){
    	try{

    		$rules = [
                        'item_id' => 'required',
                        'quantity' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $carRes = Cart::where("user_id",$request->user()->id)->where("status",'1')->first();
            //echo $carRes->id;die;
			$res =  CartItemsDetail::where("item_id",$request->item_id)->where("cart_id",$carRes->id)->first();    		
			//echo "<pre>";print_r($res);die;
						
			$finalQuantity = $carRes->quantity = $carRes->quantity - $request->quantity;
			$carRes->total_price = $carRes->total_price - ($request->quantity * $res->price);


			$cartItemQuantity = $res->quantity = $res->quantity - $request->quantity;
			//echo $cartItemQuantity;die;
			if($cartItemQuantity == 0){
				$res->delete();
			}else{
				$res->save();
			}

			//if(true){ //$carRes->save()
				if($finalQuantity == 0){
					$carRes->delete();
				}else{
					$carRes->save();
				}
				
				return response()->json([
	                                        'status' => true,
	                                        'message' => "Item Deleted from cart!"
	                                    ], 200);
			/*}else{
				return response()->json([
	                                        'status' => false,
	                                        'message' => "Something Went Wrong!"
	                                    ], 404);
			}*/
    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function removeCart(Request $request){
    	try{

    		$rules = [
                        'cart_id' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $cartId = $request->cart_id; 
    		//$user = Auth::user();
    		$check = Cart::where('id', $cartId)->first();
    		$check->status = "0";
   //  		$removecartDetails = CartItemsDetail::where('cart_id', $cartId)->delete();
   //  		$removeCartUsers = CartUser::where('cart_id', $cartId)->delete();
			// $removeCart = Cart::where('id', $cartId)->delete();    		

			if($check->save()){
				return response()->json([
	                                        'status' => true,
	                                        'message' => "Cart Successfully Removed!"
	                                    ], 200);
			}else{
				return response()->json([
	                                        'status' => false,
	                                        'message' => "Something Went Wrong!"
	                                    ], 422);
			}

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function createToken(Request $request)
    {
        $userid = Auth::id();

        try{
           /* $rules = [
                'firstName' => 'required',
                'email' => 'required',
                //'phone'  => 'required'               
            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
                   "message" => "Something went wrong!",
                   'errors' => $validator->errors()->toArray(),
               ], 422);               
            }       */    
            /*$result = Braintree_Customer::create(array(
                'firstName' =>$request->firstName, 
                'lastName'=>$request->lastName,      
                'email' => $request->email,
               // 'phone' => $request->phone
            ));
*/
           /* if ($result->success) {
            $cust_id = $result->customer->id;
            } */
            

            //User::where('id',$userid)->update(['customer_id'=>$cust_id]);
            $result = Braintree_ClientToken::generate();
            // return $result;
            return response()->json([
                'status' => true,
                'message' => "Token created Successfully.",
                'data' => $result
            ], 200);
            //$result['tokenval'] = $token; 
        }     

        catch(Exception $e){
            $result = [
                'error'=> $e->getMessage(). ' Line No '. $e->getLine() . ' In File'. $e->getFile()
            ];
            Log::error($e->getTraceAsString());
            $result['success'] = 0;
        }
        return $result;
    }

    public function payment(Request $request)
    {
        $user_id = Auth::id(); 
       
        try{  
            $rules = [                
		                'nounce' => 'required',
		                'amount' => 'required',           
		            ];
            $validator = Validator::make($request->all(), $rules);

                if($validator->fails())
                {
                    return response()->json([
						                       "message" => "Something went wrong!",
						                       'errors' => $validator->errors()->toArray(),
						                   	], 422);               
                }

            $charge = Braintree_Transaction::sale([
										              	'amount' => $request->amount,
										              	'paymentMethodNonce' => 'fake-valid-nonce'       
										            ]);

            if ($charge->success || !is_null($charge->transaction)) {


                $result['message'] = "payment done successfully";
                //User::where('id',$user_id)->update(['subscription_type'=>$request->subscription_type]);
            }
            else
            {
             $result['message'] = "Something went wrong";
            }
        }
        catch(Exception $e){
            $result = [
                'error'=> $e->getMessage(). ' Line No '. $e->getLine() . ' In File'. $e->getFile()
            ];
            Log::error($e->getTraceAsString());
            $result['success'] = 0;
        }
        return $result;
   	}

   	public function cancelOrder($orderId){
   		try{
   			$user = Auth::user();
   			$order = Order::where('id', $orderId)->first();
   			$user = User::where('id', $order['user_id'])->first();
   			$order->order_status = '8';
   			$driver = User::where('id', $order['driver_id'])->update(['busy_status' => '0']);
   			if($order->payment_method == '2' || $order->payment_method == '3'){
   				$wallet = $user['wallet']+$order['final_price'];
   				$user->wallet = $wallet;
   				$user->save();
   				$transaction = new Transaction();
   				$transaction->user_id = $order['user_id']; 
   				$transaction->order_id = $orderId;
   				$transaction->transaction_data = $order['restaurant_id'];
   				$transaction->amount = $order['final_price'];
   				$transaction->reason = "refund back";
   				$transaction->type = "9";
   				$transaction->save();


   				if($user->notification == '1'){
					$amount = $order['final_price'];
					$message = "$amount Added to your wallet";
	                $userTokens = UserToken::where('user_id', $user->id)->get()->toArray();
	                if($userTokens){
	                    foreach ($userTokens as $tokenKey => $userToken) {
	                        if($userToken['device_type'] == '0'){
	                            $sendNotification = $this->sendPushNotification($userToken['device_token'],$message,$deta = array("notification_type" => '8'));    
	                        }
	                        if($userToken['device_type'] == '1'){
	                            $sendNotification = $this->iosPushNotification($userToken['device_token'],$message,$deta = array("notification_type" => '8'));    
	                        }
	                    }
	                }
	            }
   			}

   			

            //$restaurantName = $userName[$order->restaurant_id];
            $restaurant = User::where('id', $order->restaurant_id)->first();
            $orderId = $order->id;
            $message = "#$orderId Order is Cancelled.";
            $frenchMessage = $this->translation($message);
            if($restaurant['language'] == '1'){
            	$msg = $message;
            }else{
            	$msg = $frenchMessage[0];
            }
            $deta = $order;
            $deta->notification_type = "307";
            $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
            if($userTokens){
            	foreach ($userTokens as $tokenKey => $userToken) {
                    if($userToken['device_type'] == '0'){
                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
                    }
                    if($userToken['device_type'] == '1'){
                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
                    }
                }
            }
            $cart = Cart::where('id', $order->cart_id)->update(['status' => '0']);
            unset($order->notification_type);
   			if($order->save()){
   				// $cart = Cart::where('user_id', $user->id)->first();
   				// $cartDetails = CartItemsDetail::where('cart_id', $cart['id'])->delete();
   				// $cart = Cart::where('user_id', $user->id)->delete();
   				return response()->json([
	                                        'status' => true,
	                                        'message' => "Order Cancelled.If their is any deduction in amount it will be refunded to your Grigora wallet",
	                                    ], 200);	
   			}else{
   				return response()->json([
	                                        'status' => false,
	                                        'message' => "Something Went Wrong!"
	                                    ], 422);
   			}
   		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
   	}

   	public function changeDeliveryToPickup(Request $request){
   		try{
			$rules = [
		                'order_id' => 'required',
		                'status' => 'required',//1:accept, 2:reject, 3:waiting for driver
		            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
						                   "message" => "Something went wrong!",
						                   'errors' => $validator->errors()->toArray(),
						               	], 422);               
            }
            $status = $request->status;
            $orderId = $request->order_id;
            $order = Order::where('id', $orderId)->first();
            $user = User::where('id', $order['user_id'])->first();
            $restaurant = User::where('id', $order['restaurant_id'])->first();
            if($status == '1'){
            	//pickup request accepted by customer
	            
	            //$customer = User::where('id', $order['user_id'])->first();
	            $order->order_status = "0";
	            $order->order_type = "2";
	            if($order->save()){
	            	if($user->notification == '1'){
	            		$message = "Your order is placed waiting for restaurant confirmation";
	                    $frenchMessage = $this->translation($message);
	                    if($user->notification == '1'){
		                    $customerName = $user['name'];
		                    if($user->language == '1'){
		                    	$msg = $message;
		                    }else{
		                    	$msg = $frenchMessage[0];
		                    }
		                    //$deta = $order;
		                    $deta = array(  
	                                        "order_id" => $order->id,
	                                        //"restaurant_id" => $order->restaurant_id,
	                                        "restaurant_name" => $restaurant->name ,
	                                        "restaurant_lat" => $restaurant->latitude,
	                                        "restaurant_long" => $restaurant->longitude,
	                                        "restaurant_image" => $restaurant->image,
	                                        "restaurant_address" => $restaurant->address,
	                                        "notification_type" => '0'

	                                    );
		                    //$deta = array('notification_type' => '0');
		                    $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
		                    if($userTokens){
		                    	foreach ($userTokens as $tokenKey => $userToken) {
		                    		if($userToken['device_type'] == '0'){
				                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
				                    }
				                    if($userToken['device_type'] == '1'){
				                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
				                    }	
		                    	}    
			                }

			                
		                }
		                $saveNotification = new Notification;
		                $saveNotification->user_id = $order->user_id;
		                $saveNotification->notification = $message;
		                $saveNotification->french_notification = $frenchMessage[0];
		                $saveNotification->role = '2';
		                $saveNotification->read = '0';
		                $saveNotification->image = $restaurant->image;
		                $saveNotification->notification_type = '0';
		                $saveNotification->save();
	            	}	
	            	$orderId = $order->id;
	            	$message = "Customer Change #$orderId From Delivery To Pickup.";
                    $frenchMessage = $this->translation($message);
                    //if($user->notification == '1'){
	                    //$customerName = $user['name'];
                    if($user->language == '1'){
                    	$msg = $message;
                    }else{
                    	$msg = $frenchMessage[0];
                    }
                    //$deta = $order;
                    $deta = array(  
                                    "order_id" => $order->id,
                                    //"restaurant_id" => $order->restaurant_id,
                                    "restaurant_name" => $restaurant->name ,
                                    "restaurant_lat" => $restaurant->latitude,
                                    "restaurant_long" => $restaurant->longitude,
                                    "restaurant_image" => $restaurant->image,
                                    "restaurant_address" => $restaurant->address,
                                    "notification_type" => '315'

                                );
                    //$deta = array('notification_type' => '0');
                    $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
                    if($userTokens){
                    	foreach ($userTokens as $tokenKey => $userToken) {
                    		if($userToken['device_type'] == '0'){
		                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
		                    }
		                    if($userToken['device_type'] == '1'){
		                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
		                    }	
                    	}    
	                }

	            	return response()->json([
		                                        'status' => true,
		                                        'message' => "Your pickup request successfully placed. Waiting for restaurant confirmation."
		                                    ], 200);
	            }else{
	            	return response()->json([
		                                        'status' => false,
		                                        'message' => "Something Went Wrong!"
		                                    ], 422);
	            }
            }elseif($status == '2'){
            	//pickup request rejected by customer
            	$order->cancel_accepted = "1";
            	$order->save();
            	if($order->payment_method == '2' || $order->payment_method == '3'){
            		//refund
            		$wallet = $user['wallet']+$order['final_price'];
	   				$user->wallet = $wallet;
	   				$user->save();

	   				if($user->notification == '1'){
						$amount = $order['final_price'];
						$message = "$amount Added to your wallet";
						$frenchMessage = $this->translation($message);
						if($user->language == '1'){
							$msg = $message;
						}else{
							$msg = $frenchMessage[0];
						}
						
		                $userTokens = UserToken::where('user_id', $user->id)->get()->toArray();
		                if($userTokens){
		                    foreach ($userTokens as $tokenKey => $userToken) {
		                        if($userToken['device_type'] == '0'){
		                            $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array("notification_type" => '18'));    
		                        }
		                        if($userToken['device_type'] == '1'){
		                            $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array("notification_type" => '18'));    
		                        }
		                    }
		                }

		                $saveNotification = new Notification;
	                    $saveNotification->user_id = $user->id;
	                    $saveNotification->notification = $message;
	                    $saveNotification->french_notification = $frenchMessage[0];
	                    $saveNotification->role = '2';
	                    $saveNotification->read = '0';
	                    $saveNotification->image = $restaurant->image;
	                    $saveNotification->notification_type = '18';
	                    $saveNotification->save();
		            }
            	}

            	//notification to restaurant
            	$customerName = $user->name;
            	$message = "$customerName rejected pickup request for order #$orderId";
                $frenchMessage = $this->translation($message);
                if($restaurant->language == '1'){
                	$msg = $message;
                }else{
                	$msg = $frenchMessage[0];
                }
                $deta = $order;
                $deta->notification_type = "309";
                $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
                if($userTokens){
                	foreach ($userTokens as $tokenKey => $userToken) {
	                    if($userToken['device_type'] == '0'){
	                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
	                    }
	                    if($userToken['device_type'] == '1'){
	                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
	                    }
	                }
	            }

            	return response()->json([
	                                        'status' => true,
	                                        'message' => "Pickup request rejected by customer."
	                                    ], 200);
            }else{
            	//customer wants to wait for driver
            	$order->order_status = "2";
            	if($order->save()){
            		//notification to restaurant
        			$orderId = $order->id;
            		$message = "Customer Wants to Wait For Driver For Order #$orderId.";
                    $frenchMessage = $this->translation($message);
	                    //$customerName = $user['name'];
                    if($user->language == '1'){
                    	$msg = $message;
                    }else{
                    	$msg = $frenchMessage[0];
                    }
                    //$deta = $order;
                    $deta = array(  
                                    "order_id" => $order->id,
                                    //"restaurant_id" => $order->restaurant_id,
                                    // "restaurant_name" => $restaurant->name ,
                                    // "restaurant_lat" => $restaurant->latitude,
                                    // "restaurant_long" => $restaurant->longitude,
                                    // "restaurant_image" => $restaurant->image,
                                    // "restaurant_address" => $restaurant->address,
                                    "notification_type" => '310'

                                );
                    //$deta = array('notification_type' => '0');
                    $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
                    if($userTokens){
                    	foreach ($userTokens as $tokenKey => $userToken) {
                    		if($userToken['device_type'] == '0'){
		                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
		                    }
		                    if($userToken['device_type'] == '1'){
		                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
		                    }	
                    	}    
	                }	
	            	return response()->json([
		                                        'status' => true,
		                                        'message' => "Your waiting for driver request successfully placed. Waiting for restaurant confirmation."
		                                    ], 200);
            	}else{
            		return response()->json([
		                                        'status' => false,
		                                        'message' => "Something Went Wrong!"
		                                    ], 422);		
            	}
            }

   		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
   	}

   	public function bookingAvailable(Request $request){
   		try{
   			$rules = [
		                'no_of_seats' => 'required',
		            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
						                   "message" => "Something went wrong!",
						                   'errors' => $validator->errors()->toArray(),
						               	], 422);               
            }

            $noOfSeats = $request->no_of_seats;

            $user = Auth::user();

            $update = User::where('id', $user->id)->update(['table_booking' => '1', 'no_of_seats' => $noOfSeats]);

            if($update){
            	return response()->json([
	                                        'status' => true,
	                                        'message' => "Table Booking Available on your Restaurant."
	                                    ], 200);
            }else{
            	return response()->json([
	                                        'status' => false,
	                                        'message' => "Something Went Wrong!"
	                                    ], 422);
            }

   		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
   	}

   	public function bookTable(Request $request){
   		try{
   			$rules = [
		                'restaurant_id' => 'required',
		                'no_of_seats' => 'required',
		                'date' => 'required',
		                'start_time_from' => 'required',
		                'start_time_to' => 'required',
		            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
						                   "message" => "Something went wrong!",
						                   'errors' => $validator->errors()->toArray(),
						               	], 422);               
            }

            $user = Auth::user();
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $restaurant = User::where('id', $request->restaurant_id)->first();
            $bookTable = new TableBooking;	
            $bookTable->user_id = $user->id;
            $bookTable->restaurant_id = $request->restaurant_id;
            $bookTable->no_of_seats = $request->no_of_seats;
            $bookTable->date = $request->date;
            $bookTable->start_time_from = $request->start_time_from;
            $bookTable->start_time_to = $request->start_time_to;
            $bookTable->booking_status = '1';
            if($bookTable->save()){
            	//notification to restaurant
            	$restaurantName = $userName[$bookTable->restaurant_id];
                $message = "You got a new table booking request.";
                $frenchMessage = $this->translation($message);
                if($restaurant['language'] == '1'){
                	$msg = $message;
                }else{
                	$msg = $frenchMessage[0];
                }
                $deta = $bookTable;
                $deta->notification_type = "311";
                $userTokens = UserToken::where('user_id', $bookTable->restaurant_id)->get()->toArray();
                if($userTokens){
                	foreach ($userTokens as $tokenKey => $userToken) {
	                    if($userToken['device_type'] == '0'){
	                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
	                    }
	                    if($userToken['device_type'] == '1'){
	                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
	                    }
	                }
	            }
            	return response()->json([
	                                        'status' => true,
	                                        'message' => "Table Booking Request Created Successfully.",
	                                        'data' => $bookTable
	                                    ], 200);
            }else{
            	return response()->json([
	                                        'status' => false,
	                                        'message' => "Something Went Wrong!"
	                                    ], 422);
            }
   		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
   	}

    public function placeOrder(Request $request){
    	try{
    		$rules = [
		                'cart_id' => 'required',
		                //'promo_id' => 'required',
		                'app_fee' => 'required',
		                'payment_method' => 'required',//1=>cash, 2=>card, 3=>grigora wallet
		                //'delivery_fee' => 'required',
		                //'delivery_note' => 'required',
		                //'delivery_address' => 'required',
		                //'delivery_lat' => 'required',
		                //'delivery_long' => 'required',
		                'price_before_promo' => 'required',
		                'price_after_promo' => 'required',
		                'final_price' => 'required',
		                'order_type' => 'required',//1:delivery, 2:pickup
		            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
						                   "message" => "Something went wrong!",
						                   'errors' => $validator->errors()->toArray(),
						               	], 422);               
            }	

            if($request->payment_method == "2"){
            	//card payment
            	$rules = [
	                		//'payment_data' => 'required',
                      		'reference' => 'required'
	            		];

	            $validator = Validator::make($request->all(), $rules);

	            if($validator->fails())
	            {
	                return response()->json([
							                   "message" => "Something went wrong!",
							                   'errors' => $validator->errors()->toArray(),
							               	], 422);               
	            }	

	            $chargeData = "";
	            $reference = $request->reference;

	            // $transaction = new Transaction;
             //    $transaction->user_id = $user->id;
             //    $transaction->order_id = $user->id;
             //    //$transaction->transaction_data = json_encode($response);
             //    $transaction->reference = $request->receipt_id;
             //    $transaction->type = '2';

            }else{
            	$chargeData = "";
            	$reference = "";
            }

            $cartId = $request->get('cart_id');
            $cart = Cart::where('id', $cartId)->first();
            //echo'<pre>';print_r($cart);die;
            $restaurant = User::where('id', $cart['restaurant_id'])->first();
            $customer = User::where('id', $cart['user_id'])->first();
            $setting = Setting::where('id', '1')->first();
            if($request->payment_method == "3"){
        		//deduct payment from wallet
        		//$walletMoney = $customer['wallet']/$setting['naira_to_points'];
        		$walletMoney = $customer['wallet'];
        		if($walletMoney > $request->final_price){
	        		//$walletAmount = $customer['wallet']-($request->final_price*$setting['naira_to_points']);
	        		$walletAmount = $customer['wallet']-$request->final_price;
	        		$deduct = User::where('id', $customer['id'])->update(['wallet' => $walletAmount]);
	        	}else{
	        		return response()->json([
		                                        'status' => false,
		                                        'message' => "User's Doesn't Have Sufficient Amount.",
		                                        //'data' => $transaction
		                                    ], 200);
	        	}
        	}
            //check driver is available or not
        	//echo '<pre>';print_r($restaurant);die;
            

            
            //echo $cartId;die;

            
            if(empty($cart)){
                return response()->json([
                                            'status' => false,
                                            'message' => "Your cart is empty.",
                                            //'data' => $cart
                                        ], 200);
            }
            //echo'<pre>';print_r($cart);die;
            if($request->has('promo_id') && !empty($request->promo_id)){
            	$promo = $request->promo_id;
        	}else{
        		$promo = "";
        	}
        	if(!empty($promo)){
	            if($restaurant['promo_id'] != $promo){
	            	return response()->json([
	                                            'status' => false,
	                                            'message' => "This Restaurant does not provide this promo code.",
	                                            //'data' => $cart
	                                        ], 200);
	            }
	        }
            	// if($request->get('promo_id') != "0"){
            	// 	$promocode = Promocode::where('id', $request->get('promo_id'))->first();
            	// 	$promo = $promocode->code;
            	// }else{
            	// 	$promo = "";
            	// }
            	//echo'<pre>';print_r($cart);die;
            	$restaurant = User::where('id', $cart['restaurant_id'])->first();
            	$order = new Order;
            	$order->cart_id = $cart['id'];
            	$order->user_id = $cart['user_id'];
            	$order->restaurant_id = $cart['restaurant_id'];
            	$order->quantity = $cart['quantity'];
            	$order->payment_method = $request->payment_method;
            	$order->promocode = $promo;
            	$order->app_fee = $request->app_fee;
            	if($request->has('delivery_address')){
            		$order->delivery_address = $request->delivery_address;
            	}
            	$order->start_lat = $restaurant->latitude;
            	$order->start_long = $restaurant->longitude;
            	$order->start_address = $restaurant->address;
            	if($request->has('delivery_lat')){
            		$order->end_lat = $request->delivery_lat;
            	}
            	if($request->has('delivery_long')){
            		$order->end_long = $request->delivery_long;
            	}
            	if($request->has('delivery_note')){
	            	$order->delivery_note = $request->delivery_note;
	            }
	            if($request->has('delivery_fee')){
            		$order->delivery_fee = $request->delivery_fee;
	            }
	            $settings = Setting::where('id', '1')->first();
	            $order->driver_fee = $settings->delivery_fee;
	            $order->order_type = $request->order_type;
            	$order->price_before_promo = $request->price_before_promo;
            	$order->price_after_promo = $request->price_after_promo;
            	$order->final_price = $request->final_price;
            	$order->payment_data = $chargeData;
            	$order->reference = $reference;
            	$order->order_status = '0'; 
            	if($order->save()){
            		$cartDetails = CartItemsDetail::where('cart_id', $cartId)->get()->toArray();
            		foreach ($cartDetails as $key => $cartDetail) {
            			$orderDetail = new OrderDetail;
            			$orderDetail->order_id = $order->id;
            			$orderDetail->user_id = $order->user_id;
            			$orderDetail->item_id = $cartDetail['item_id'];
            			$orderDetail->quantity = $cartDetail['quantity'];
            			$orderDetail->price = $cartDetail['price'];
            			$orderDetail->item_choices = $cartDetail['item_choices'];
            			if($orderDetail->save()){
                            
            			}else{
            				$delete = Order::where('id', $order->id)->delete();
            				return response()->json([
				                                        'status' => false,
				                                        'message' => "Something Went Wrong!"
				                                    ], 422);
            			}
                    }
                    // $delete = Cart::where('id', $cartId)->delete();
                    // $delete = CartItemsDetail::where('cart_id', $cartId)->delete();
                    $updateCart = Cart::where('id', $cartId)->update(['status' => '0']);
                    //notification to restaurant
                    $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();

                    //------notification for restaurant-----------
                    $restaurantName = $userName[$order->restaurant_id];

                    $message = "You got a new order";
                    //$message = "You have an order from $customer['name']";
                    $frenchMessage = $this->translation($message);
                    if($restaurant->language == '1'){
                    	$msg = $message;
                    }else{
                    	$msg = $frenchMessage[0];
                    }
                    $deta = $order;
                    $deta['notification_type'] = '300';
                    $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
                    if($userTokens){
                    	foreach ($userTokens as $tokenKey => $userToken) {
		                    if($userToken['device_type'] == '0'){
		                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
		                    }
		                    if($userToken['device_type'] == '1'){
		                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
		                    }
		                }
		            }
                    //------notification for restaurant-----------

                    //------notification for customer-----------
                    $message = "Your order is placed waiting for restaurant confirmation";
                    $frenchMessage = $this->translation($message);
                    if($customer->notification == '1'){
	                    //$customerName = $userName[$order->user_id];
	                    if($customer->language == '1'){
	                    	$msg = $message;
	                    }else{
	                    	$msg = $frenchMessage[0];
	                    }
	                    //$deta = $order;
	                    $deta = array(  
                                        "order_id" => $order->id,
                                        //"restaurant_id" => $order->restaurant_id,
                                        "restaurant_name" => $restaurant->name ,
                                        "restaurant_lat" => $restaurant->latitude,
                                        "restaurant_long" => $restaurant->longitude,
                                        "restaurant_image" => $restaurant->image,
                                        "restaurant_address" => $restaurant->address,
                                        "notification_type" => '0'

                                    );
	                    //$deta = array('notification_type' => '0');
	                    $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
	                    if($userTokens){
	                    	foreach ($userTokens as $tokenKey => $userToken) {
	                    		if($userToken['device_type'] == '0'){
			                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
			                    }
			                    if($userToken['device_type'] == '1'){
			                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
			                    }	
	                    	}    
		                }

		                
	                }
	                $saveNotification = new Notification;
	                $saveNotification->user_id = $order->user_id;
	                $saveNotification->order_id = $order->id;
	                $saveNotification->restaurant_id = $order->restaurant_id;
	                $saveNotification->notification = $message;
	                $saveNotification->french_notification = $frenchMessage[0];
	                $saveNotification->role = '2';
	                $saveNotification->read = '0';
	                $saveNotification->image = $restaurant->image;
	                $saveNotification->notification_type = '0';
	                $saveNotification->save();
                    //------notification for customer-----------

                    $order['order_details'] = $orderDetail;
                    if($request->payment_method == "2"){
                    	$transaction = new Transaction;
		                $transaction->user_id = $cart['user_id'];
		                $transaction->order_id = $order->id;
		               //$transaction->transaction_data = json_encode($response);
		                //$transaction->reference = $request->receipt_id;
		                $transaction->amount = $order['final_price'];
		                $transaction->type = '2';
		                $transaction->save();
                    }

                    if($request->payment_method == "3"){
	                    $transaction = new Transaction;
		                $transaction->user_id = $cart['user_id'];
		                $transaction->order_id = $order->id;
		                //$transaction->transaction_data = json_encode($response);
		                //$transaction->reference = $request->receipt_id;
		                $transaction->amount = $order['final_price'];
		                $transaction->type = '4';
		                $transaction->save();
		            }
                    return response()->json([
                                                'status' => true,
                                                'message' => "Order Placed Successfully.",
                                                'data' => $order
                                            ], 200);
            		//}
            		
            	}else{
            		return response()->json([
		                                        'status' => false,
		                                        'message' => "Something Went Wrong!"
		                                    ], 422);
            	}
            // }else{
            // 	return response()->json([
            //                                 'status' => false,
            //                                 'message' => "This Restaurant does not provide this promo code.",
            //                                 //'data' => $cart
            //                             ], 200);
            // }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function scheduleOrder(Request $request){
    	try{
    		$rules = [
		                'cart_id' => 'required',
		                //'promo_id' => 'required',
		                'app_fee' => 'required',
		                'payment_method' => 'required',//1=>cash, 2=>card, 3=>grigora wallet
		                //'delivery_fee' => 'required',
		                //'delivery_note' => 'required',
		                //'delivery_address' => 'required',
		                //'delivery_lat' => 'required',
		                //'delivery_long' => 'required',
		                'price_before_promo' => 'required',
		                'price_after_promo' => 'required',
		                'final_price' => 'required',
		                'order_type' => 'required',//1:delivery, 2:pickup
		                'schedule_time' => 'required'
		            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
						                   "message" => "Something went wrong!",
						                   'errors' => $validator->errors()->toArray(),
						               	], 422);               
            }	

            if($request->payment_method == "2"){
            	//card payment
            	$rules = [
	                		//'payment_data' => 'required',
                      		'reference' => 'required'
	            		];

	            $validator = Validator::make($request->all(), $rules);

	            if($validator->fails())
	            {
	                return response()->json([
							                   "message" => "Something went wrong!",
							                   'errors' => $validator->errors()->toArray(),
							               	], 422);               
	            }	

	            $chargeData = "";
	            $reference = $request->reference;

	            // $transaction = new Transaction;
             //    $transaction->user_id = $user->id;
             //    $transaction->order_id = $user->id;
             //    //$transaction->transaction_data = json_encode($response);
             //    $transaction->reference = $request->receipt_id;
             //    $transaction->type = '2';

            }else{
            	$chargeData = "";
            	$reference = "";
            }

            $cartId = $request->get('cart_id');
            $cart = Cart::where('id', $cartId)->first();
            //echo'<pre>';print_r($cart);die;
            $restaurant = User::where('id', $cart['restaurant_id'])->first();
            $customer = User::where('id', $cart['user_id'])->first();
            if($request->payment_method == "3"){
        		//deduct payment from wallet
        		if($customer['wallet'] > $request->final_price){
	        		$walletAmount = $customer['wallet']-$request->final_price;
	        		$deduct = User::where('id', $customer['id'])->update(['wallet' => $walletAmount]);
	        	}else{
	        		return response()->json([
		                                        'status' => false,
		                                        'message' => "User's Doesn't Have Sufficient Amount.",
		                                        //'data' => $transaction
		                                    ], 200);
	        	}
        	}
            //check driver is available or not
        	//echo '<pre>';print_r($restaurant);die;
            

            
            //echo $cartId;die;

            
            if(empty($cart)){
                return response()->json([
                                            'status' => false,
                                            'message' => "Your cart is empty.",
                                            //'data' => $cart
                                        ], 200);
            }
            //echo'<pre>';print_r($cart);die;
            if($request->has('promo_id') && !empty($request->promo_id)){
            	$promo = $request->promo_id;
        	}else{
        		$promo = "";
        	}
        	if(!empty($promo)){
	            if($restaurant['promo_id'] != $promo){
	            	return response()->json([
	                                            'status' => false,
	                                            'message' => "This Restaurant does not provide this promo code.",
	                                            //'data' => $cart
	                                        ], 200);
	            }
	        }
            	// if($request->get('promo_id') != "0"){
            	// 	$promocode = Promocode::where('id', $request->get('promo_id'))->first();
            	// 	$promo = $promocode->code;
            	// }else{
            	// 	$promo = "";
            	// }
            	//echo'<pre>';print_r($cart);die;
            	$restaurant = User::where('id', $cart['restaurant_id'])->first();
            	$order = new Order;
            	$order->cart_id = $cart['id'];
            	$order->user_id = $cart['user_id'];
            	$order->restaurant_id = $cart['restaurant_id'];
            	$order->quantity = $cart['quantity'];
            	$order->payment_method = $request->payment_method;
            	$order->promocode = $promo;
            	$order->app_fee = $request->app_fee;
            	if($request->has('delivery_address')){
            		$order->delivery_address = $request->delivery_address;
            	}
            	$order->start_lat = $restaurant->latitude;
            	$order->start_long = $restaurant->longitude;
            	$order->start_address = $restaurant->address;
            	if($request->has('delivery_lat')){
            		$order->end_lat = $request->delivery_lat;
            	}
            	if($request->has('delivery_long')){
            		$order->end_long = $request->delivery_long;
            	}
            	if($request->has('delivery_note')){
	            	$order->delivery_note = $request->delivery_note;
	            }
	            if($request->has('delivery_fee')){
            		$order->delivery_fee = $request->delivery_fee;
	            }
	            $settings = Setting::where('id', '1')->first();
	            $order->driver_fee = $settings->delivery_fee;
	            $order->order_type = $request->order_type;
            	$order->price_before_promo = $request->price_before_promo;
            	$order->price_after_promo = $request->price_after_promo;
            	$order->final_price = $request->final_price;
            	$order->payment_data = $chargeData;
            	$order->reference = $reference;
            	$order->schedule_time = $request->schedule_time;
                $order->order_status = '1';
                $order->is_schedule = '1';
            	if($order->save()){
            		$cartDetails = CartItemsDetail::where('cart_id', $cartId)->get()->toArray();
            		foreach ($cartDetails as $key => $cartDetail) {
            			$orderDetail = new OrderDetail;
            			$orderDetail->order_id = $order->id;
            			$orderDetail->user_id = $order->user_id;
            			$orderDetail->item_id = $cartDetail['item_id'];
            			$orderDetail->quantity = $cartDetail['quantity'];
            			$orderDetail->price = $cartDetail['price'];
            			$orderDetail->item_choices = $cartDetail['item_choices'];
            			if($orderDetail->save()){
                            
            			}else{
            				$delete = Order::where('id', $order->id)->delete();
            				return response()->json([
				                                        'status' => false,
				                                        'message' => "Something Went Wrong!"
				                                    ], 422);
            			}
                    }
                    // $delete = Cart::where('id', $cartId)->delete();
                    // $delete = CartItemsDetail::where('cart_id', $cartId)->delete();
                    $updateCart = Cart::where('id', $cartId)->update(['status' => '0']);
                    //notification to restaurant
                    $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();

                    //------notification for restaurant-----------
                    $restaurantName = $userName[$order->restaurant_id];

                    $message = "New order Scheduled At $request->schedule_time";
                    $frenchMessage = $this->translation($message);
                    if($restaurant->language == '1'){
                    	$msg = $message;
                    }else{
                    	$msg = $frenchMessage[0];
                    }
                    $deta = $order;
                    $deta['notification_type'] = "301";
                    $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
                    if($userTokens){
                    	foreach ($userTokens as $tokenKey => $userToken) {
		                    if($userToken['device_type'] == '0'){
		                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
		                    }
		                    if($userToken['device_type'] == '1'){
		                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
		                    }
		                }
		            }
                    //------notification for restaurant-----------

                    //------notification for customer-----------
                    $message = "Your Schedule order is placed waiting for restaurant confirmation";
                    $frenchMessage = $this->translation($message);
                    if($customer->notification == '1'){
	                    $customerName = $userName[$order->user_id];
	                    if($customer->language == '1'){
	                    	$msg = $message;
	                    }else{
	                    	$msg = $frenchMessage[0];
	                    }
	                    //$deta = $order;
	                    $deta = array(  
                                        "order_id" => $order->id,
                                        //"restaurant_id" => $order->restaurant_id,
                                        "restaurant_name" => $restaurant->name ,
                                        "restaurant_lat" => $restaurant->latitude,
                                        "restaurant_long" => $restaurant->longitude,
                                        "restaurant_image" => $restaurant->image,
                                        "restaurant_address" => $restaurant->address,
                                        "notification_type" => '1'

                                    );
	                    //$deta = array('notification_type' => '0');
	                    $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
	                    if($userTokens){
	                    	foreach ($userTokens as $tokenKey => $userToken) {
	                    		if($userToken['device_type'] == '0'){
			                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
			                    }
			                    if($userToken['device_type'] == '1'){
			                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
			                    }	
	                    	}    
		                }

		                
	                }
	                $saveNotification = new Notification;
	                $saveNotification->user_id = $order->user_id;
	                $saveNotification->notification = $message;
	                $saveNotification->french_notification = $frenchMessage[0];
	                $saveNotification->role = '2';
	                $saveNotification->read = '0';
	                $saveNotification->image = $restaurant->image;
	                $saveNotification->notification_type = '1';
	                $saveNotification->save();
                    //------notification for customer-----------
	                if(!$orderDetail){
	                	$orderDetail = [];
	                }
                    $order['order_details'] = $orderDetail;
                    if($request->payment_method == "2"){
                    	$transaction = new Transaction;
		                $transaction->user_id = $cart['user_id'];
		                $transaction->order_id = $order->id;
		               //$transaction->transaction_data = json_encode($response);
		                //$transaction->reference = $request->receipt_id;
		                $transaction->amount = $order['final_price'];
		                $transaction->type = '2';
		                $transaction->save();
                    }

                    if($request->payment_method == "3"){
	                    $transaction = new Transaction;
		                $transaction->user_id = $cart['user_id'];
		                $transaction->order_id = $order->id;
		                //$transaction->transaction_data = json_encode($response);
		                //$transaction->reference = $request->receipt_id;
		                $transaction->amount = $order['final_price'];
		                $transaction->type = '4';
		                $transaction->save();
		            }
                    return response()->json([
                                                'status' => true,
                                                'message' => "Order Scheduled Successfully.",
                                                'data' => $order
                                            ], 200);
            		//}
            		
            	}else{
            		return response()->json([
		                                        'status' => false,
		                                        'message' => "Something Went Wrong!"
		                                    ], 422);
            	}
            // }else{
            // 	return response()->json([
            //                                 'status' => false,
            //                                 'message' => "This Restaurant does not provide this promo code.",
            //                                 //'data' => $cart
            //                             ], 200);
            // }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function reOrder(Request $request){
    	try{
		 	$rules = [
		                'order_id' => 'required',
		            ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
						                   "message" => "Something went wrong!",
						                   'errors' => $validator->errors()->toArray(),
						               	], 422);               
            }

            $orderId = $request->order_id;
            $user = Auth::user();

            $order = Order::where('id', $orderId)->first();
            if($order){
            	$cartCheck = Cart::where('user_id', $user->id)->where('status', '1')->first();
            	if($cartCheck){
            		$cartCheck->status = "0";
            		$cartCheck->save();
            	}
            	$cartId = $order['cart_id'];
            	$cart = Cart::where('id', $cartId)->first();
            	if($cart){
            		$cart->status = "1";
            		if($cart->save()){

            			$cart['details'] = CartItemsDetail::where('cart_id', $cartId)->get()->toArray();
            			return response()->json([
			                                        'status' => true,
			                                        'message' => "Cart Found.",
			                                        'data' => $cart
			                                    ], 200);			
            		}
            	}else{
            		return response()->json([
		                                        'status' => false,
		                                        'message' => "Cart Not Found."
		                                    ], 200);		
            	}
            }else{
            	return response()->json([
	                                        'status' => false,
	                                        'message' => "Order Not Found."
	                                    ], 200);	
            }

    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function getGroupCarts(){
    	try{
    		$user = Auth::user();
    		$carts = Cart::where('user_id', $user->id)
    						->where('status', '1')
    						->get()
    						->toArray();

			if($carts){
				foreach ($carts as $key => $cart) {
					$customer = User::where('id', $cart['user_id'])->first();
					$restaurant = User::where('id', $cart['restaurant_id'])->first();
					$cartDetails = CartItemsDetail::where('cart_id', $cart['id'])->get()->toArray();
					$cartItemsCount = CartItemsDetail::where('cart_id', $cart['id'])->count();
					$cartusersCount = CartItemsDetail::where('cart_id', $cart['id'])
														->groupBy('user_id')
														->count();
					$carts[$key]['user_name'] = $customer->name;
					$carts[$key]['user_french_name'] = $customer->french_name;
					$carts[$key]['restaurant_name'] = $restaurant->name;
					$carts[$key]['restaurant_french_name'] = $restaurant->french_name;
					$carts[$key]['cart_items_count'] = $cartItemsCount;
					$carts[$key]['cart_users_count'] = $cartusersCount;
					//$carts[$key]['cart_details'] = $cartDetails;
				}
				return response()->json([
	                                        'status' => true,
	                                        'message' => "Carts Found.",
	                                        'data' => $carts
	                                    ], 200);	
			}else{
				return response()->json([
	                                        'status' => false,
	                                        'message' => "Cart Not Found.",
	                                        'data' => []
	                                    ], 200);	
			}
    	}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function sendPushNotification($token,$msg="", $deta) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
              "registration_ids" => array(
                  $token
              ),
              // "notification" => array(
              //     "title" => "Grigora",
              //     "body" => $msg,
              //     "sendby" => "Grigora",
              //     "establishment_detail" => "Grigora",
              //     "type" => "Grigora",
              //     "content-available" => 1,
              //     "badge" => 0,
              //     "sound" => "default",
              // ),
              "data" => array(
                  "body" => $msg,
                  //'notification_type' => $notificationType,
                  "sendby" => "Grigora",
                  "establishment_detail" => "Grigora",
                  "type" => "Grigora",
                  "content-available" => 1,
                  "data" => $deta,
                  "badge" => 0,
                  "sound" => "default",
              ),
              "priority" => 10
          );

          $fields = json_encode($fields);
         
          $headers = array(
              'Authorization: key=' . "AAAAZXkf9Lg:APA91bHIpFpj5jp7zdlnLrVd7YqaGJD1KPvv0B48rYe5UHkyHgAuJZTtAa4wgJLjJNGkoIqPRr43GeRZ1bPei1KPSBALWDLy7Oq23uBMfZ84BMPU3OmUOi5mchDBaIHwJGYvVIkEL8pc",
              'Content-Type: application/json'
          );
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

          $result = curl_exec($ch);
          curl_close($ch);

        return $result;
    }

    public function iosPushNotification($token,$msg="",$deta) {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
                        'sound' => 'Default',
                        "type"=> "test",
                        "data"=>"test",
                        "base_url"=>url("/"),
                        "body" => $msg,
                        "title" => "Grigora",
                        //"type" => $type,
                        "deta" => $deta,
                        ];
        $fields = array(
                          'to' => $token,
                          'notification' => $notification,
                          
                        );
        $fields = json_encode($fields);
        $headers = array(
              'Authorization: key=' . "AAAAl9ypsSw:APA91bE9HbQD0KBUJUngyC1GotYZaWyYbGxDs3zib6ePE-F1Mx67ii4C3DVSIxUVYjz3o7i6JTcGIws8-sdlsfa3JM0VKKqTLTVSgeB-DMJ9gdp7qmIMBJ_ilZRIzc5QqjlsxL1GGyut",
              'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
          // print_r($result);die;
        return $result;
    }

    function translation($englishWords){
        //echo'<pre>';print_r($englishWords);die; 
        //$englishWords = array('NY dish');
        /*$englishWords = array(array('a'),array('b'));
        $apiKey = 'AIzaSyAcOkbLZTZ4M684I0qIm6k998N1MltJiFQ';
        $englishWords = implode(' , ', $englishWords);
        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $apiKey . '&q=' . rawurlencode($englishWords) . '&source=en&target=fr';

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);                 
        $responseDecoded = json_decode($response, true);
        curl_close($handle);
        $frenchWords =  $responseDecoded['data']['translations'][0]['translatedText'];
        $frenchWords = explode(',', $frenchWords);
        echo'<pre>';print_r($frenchWords);die;
        return $frenchWords;*/


         $handle = curl_init();

            if (FALSE === $handle)
               throw new Exception('failed to initialize');

            curl_setopt($handle, CURLOPT_URL,'https://www.googleapis.com/language/translate/v2');
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            $data = array('key' => "AIzaSyAcOkbLZTZ4M684I0qIm6k998N1MltJiFQ",
                            'q' => $englishWords,
                            'source' => "en",
                            'target' => "fr");
            curl_setopt($handle, CURLOPT_POSTFIELDS, preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($data)));
            curl_setopt($handle,CURLOPT_HTTPHEADER,array('X-HTTP-Method-Override: GET'));
            $response = curl_exec($handle);
            $responseDecoded = json_decode($response, true);
            $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            if($responseCode != 200) {
               return array();
            }else{
                $res = array();
                if(!empty($responseDecoded['data']['translations'])){
                    foreach ($responseDecoded['data']['translations'] as $key => $value) {
                        $res[] = $value["translatedText"];
                    }
                }
               return $res;
            }
    }

    public function payStack(request $request){
	    try{
	    	$rules = [
	                    'order_id' =>'required',  
                      	'amount' => 'required',
                      	'payment_data' => 'required',
                      	'reference' => 'required'
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
	        
	    	$paystack = new \Yabacon\Paystack("sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e");
	      	$tranx = $paystack->transaction->initialize([
													        'amount'=> $request->amount,       // in kobo
													        'email'=> $request->email,         // unique to customers
													        'reference'=> $request->reference, // unique to transactions
												      	]);

	      //echo "<pre>";print_r($tranx);die;
	      return response()->json($tranx);
	    } catch(\Yabacon\Paystack\Exception\ApiException $e){
	      	print_r($e->getResponseObject());
      		die($e->getMessage());
	    }
    }
    
}
