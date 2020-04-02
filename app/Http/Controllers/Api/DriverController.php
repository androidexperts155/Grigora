<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Item;
use App\Setting;
use App\Order;
use App\OrderDetail;
use App\DriverRequest;
use App\RestaurantCuisine;
use App\UserToken;
use App\Notification;
use App\Transaction;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;

class DriverController extends Controller
{
    public function acceptOrder(Request $request){
    	try{
    		$rules = [
                    'order_id' => 'required',
                      //'status' => 'required', // 1 : accepted ,2 : rejected
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

        $driver = Auth::user();

        $order = Order::where("id",$request->order_id)->first();
        if($order->driver_id != ''){
          return response()->json([
                                      'status' => false,
                                      'message' => "Order Already accepted by driver.",
                                      'data' => $order
                                  ], 200);
        }
        //echo'<pre>';print_r($order);die;
        $customer = User::where('id', $order->user_id)->first();
        $restaurant = User::where('id', $order->restaurant_id)->first();
        $driver = User::where('id', $driver->id)->first();
        
      	$order->order_status = '3';
        $order->driver_id = $driver->id;
      	if($order->save()){
          //notification to customer, restaurant
          $orderId = $request->order_id;
          $driverName = $driver['name'];
          $message = "$driverName Driver has been assigned to your order #$orderId";
          $frenchMessage = $this->translation($message);
          if($customer->language == '1'){
            $msg = $message; 
          }else{
            $msg = $frenchMessage[0]; 
          }
          $deta = array('order_id' => $order->id, 'notification_type' => '303');
          if($restaurant['device_type'] == '0'){
              $sendNotification = $this->sendPushNotification($restaurant['device_token'],$message,$deta);    
          }
          if($restaurant['device_type'] == '1'){
              $sendNotification = $this->iosPushNotification($restaurant['device_token'],$message,$deta);    
          }
          if($customer->notification == '1'){
            $message1 = "Driver Is Reaching The Restaurant To Pick Up Your Order";
            $frenchMessage = $this->translation($message);
            if($customer->language == '1'){
              $msg1 = $message1;
            }else{
              $msg1 = $frenchMessage[0];
            }
            $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
            if($userTokens){
              foreach ($userTokens as $tokenKey => $userToken) {
                
                if($userToken['device_type'] == '0'){
                    $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg1,$deta = array('order_id' => $order->id, 'notification_type' => '3'));    
                }
                if($userToken['device_type'] == '1'){
                    $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg1,$deta = array('order_id' => $order->id, 'notification_type' => '3'));    
                }
              }
            }
          }

          $saveNotification = new Notification;
          $saveNotification->user_id = $customer->id;
          $saveNotification->order_id = $order->id;
          $saveNotification->restaurant_id = $order->restaurant_id;
          $saveNotification->notification = $message;
          $saveNotification->french_notification = $frenchMessage[0];
          $saveNotification->role = '2';
          $saveNotification->read = '0';
          $saveNotification->notification_type = '3';
          $saveNotification->image = $driver->image;          
          $saveNotification->save();

      		return response()->json([
      						                    'status' => true,
      						                    'message' => "Order accepted by driver.",
      						                    'data' => $order
      						                ], 200);
      	}else{
          return response()->json([
                                      'message' => "Something Went Wrong!",
                                      'status' => false,
                                  ], 422);
      	}
           	

    	}catch (Exception $e) {
        return response()->json([
                                    'message' => "Something Went Wrong!",
                                    'status' => false,
                                ], 422);
      }
    	
    }

    public function ongoingOrders(){
      try{
        $driver = Auth::user();
        $orders = Order::where('driver_id', $driver->id)
                        ->whereIn('order_status', ['3', '4', '7', '9'])
                        ->get()
                        ->toArray();
        //echo'<pre>';print_r($orders);die;
        
        if($orders){
          foreach($orders as $key => $order){
            $customer = User::where('id', $order['user_id'])->first();
            $restaurant = User::where('id', $order['restaurant_id'])->first();
            //if($order['order_status'] == '3' || $order['order_status'] == '7'){
              $orders[$key]['restaurant_name'] = $restaurant['name'];
              $orders[$key]['restaurant_image'] = $restaurant['image'];
              $orders[$key]['restaurant_address'] = $order['start_address'];
              $orders[$key]['restaurant_lat'] = $order['start_lat'];
              $orders[$key]['restaurant_long'] = $order['start_long'];
              $orders[$key]['restaurant_preparing_time'] = $restaurant['preparing_time'];
            //}else{
              $orders[$key]['user_name'] = $customer['name'];
              $orders[$key]['user_image'] = $customer['image'];
              $orders[$key]['user_phone'] = $customer['phone'];
              $orders[$key]['user_address'] = $order['delivery_address'];
              $orders[$key]['user_lat'] = $order['end_lat'];
              $orders[$key]['user_long'] = $order['end_long'];
            //}
            $orderDetails = OrderDetail::where('order_id', $order['id'])->get()->toArray();
            foreach ($orderDetails as $key1 => $details) {
              $item = Item::where('id', $details['item_id'])->first();
              //echo'<pre>';print_r($item);die;
              $orderDetails[$key1]['item_name'] = $item['name'];
              $orderDetails[$key1]['french_name'] = $item['french_name'];
            }
            $orders[$key]['order_details'] = $orderDetails;
          }
          return response()->json([
                                    'status' => true,
                                    'message' => "Order Successfully Found.",
                                    'data' => $orders
                                  ], 200);
        }else{
          return response()->json([
                                    'status' => true,
                                    'message' => "Order Not Found.",
                                    'data' => $orders
                                  ], 200);
        }
      }catch (Exception $e) {
        return response()->json([
                                    'message' => "Something Went Wrong!",
                                    'status' => false,
                                ], 422);
      }
    }

    public function allOrders(){
      try{
        $driver = Auth::user();
        $orders = Order::where('driver_id', $driver->id)
                        ->whereIn('order_status', ['3', '4', '5'])
                        // ->where('order_status', '<>', '1')
                        // ->where('order_status', '<>', '6')
                        // ->where('order_status', '<>', '0')
                        ->get()
                        ->toArray();
        //echo'<pre>';print_r($orders);die;

        if($orders){
          foreach ($orders as $key => $order) {
            $customer = User::where('id', $order['user_id'])->first();
            $restaurant = User::where('id', $order['restaurant_id'])->first();
            $orderDetails = OrderDetail::where('order_id', $order['id'])->get()->toArray(); 
            foreach ($orderDetails as $key1 => $details) {
              $item = Item::where('id', $details['item_id'])->first();
              //echo'<pre>';print_r($item);die;
              $orderDetails[$key1]['item_name'] = $item['name'];
              $orderDetails[$key1]['french_name'] = $item['french_name'];
            } 
            $orders[$key]['restaurant_name'] = $restaurant['name'];
            $orders[$key]['restaurant_image'] = $restaurant['image'];
            $orders[$key]['restaurant_address'] = $restaurant['address'];
            $orders[$key]['restaurant_lat'] = $restaurant['latitude'];
            $orders[$key]['restaurant_long'] = $restaurant['longitude'];
            $orders[$key]['user_name'] = $customer['name'];
            $orders[$key]['user_image'] = $customer['image'];
            $orders[$key]['user_address'] = $customer['address'];
            $orders[$key]['user_lat'] = $customer['latitude'];
            $orders[$key]['user_long'] = $customer['longitude'];
            $orders[$key]['order_details'] = $orderDetails;
          }
          
            return response()->json([
                                'status' => true,
                                'message' => "Order Successfully Found.",
                                'data' => $orders
                            ], 200);
        }else{
          return response()->json([
                                      'status' => true,
                                      'message' => "Order Not Found.",
                                      'data' => $orders
                                  ], 200);
        }
      }catch (Exception $e) {
        return response()->json([
                                    'message' => "Something Went Wrong!",
                                    'status' => false,
                                ], 422);
  }
    }

    public function availableOrders(Request $request){
      try{
        $rules = [
                    'latitude' => 'required',
                    'logitude' => 'required',
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
        //echo $user->id;die;
        if($user->busy_status == '1'){
          return response()->json([
                                      'status' => false,
                                      'message' => "Available Orders Not Found.",
                                      'data' => []
                                  ], 200);
        }
        $lat = $request->latitude;
        $long = $request->logitude;

        $setting = Setting::where('id', '1')->first();
        $distance = $setting->distance;

        $query = "SELECT *,ACOS( SIN( RADIANS( start_lat ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( start_lat ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( start_long ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM orders
                        WHERE
                        ACOS( SIN( RADIANS( start_lat ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( start_lat ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( start_long ) - RADIANS( $long )) ) * 6371  < $distance AND `order_status` In('2') 
                         AND `order_type` = '1' ORDER BY `distance`";
        $orders = DB::select(DB::raw($query));
        //echo'<pre>';print_r($orders);die;
        $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
        $userImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
        $userPhone = User::where('role', '<>', '1')->pluck('phone', 'id')->toArray();
        //echo'<pre>';print_r($userNames);die;
        if($orders){
          $allOrders = array();
          foreach ($orders as $key => $order) {
            if($order->is_schedule == '1'){
              $scheduleTime = $order->schedule_time;
              $beforeTime = date('Y-m-d h:i:s',strtotime('-55 minutes',strtotime($scheduleTime)));
              $currentTime =  date("Y-m-d H:i:s");     
              if(strtotime($beforeTime) <= strtotime($currentTime)){
                $allOrders[] = $order;
              }else{
                //unset($orders[$key]);
              }
            }else{
              $allOrders[] = $order;
            }
          }
          //return $allOrders;
          foreach ($allOrders as $key => $order) {
            
            //return $orders;
            $orderDetails = OrderDetail::where('order_id', $order->id)->get()->toArray();
            foreach ($orderDetails as $k => $orderDetail) {
              $item = Item::where('id', $orderDetail['item_id'])->first();
              $orderDetails[$k]['item_name'] = $item['name'];
              $orderDetails[$k]['item_french_name'] = $item['french_name'];
            }
            $customerinfo = User::where('id', $order->user_id)->first();
            $restaurantinfo = User::where('id', $order->restaurant_id)->first();
            $allOrders[$key]->restaurant_name = $restaurantinfo->name;
            $allOrders[$key]->restaurant_preparing_time = $restaurantinfo->preparing_time;
            $allOrders[$key]->restaurant_image = $restaurantinfo->image;
            $allOrders[$key]->customer_name = $customerinfo->name;
            $allOrders[$key]->customer_image = $customerinfo->image;
            $allOrders[$key]->customer_phone = $customerinfo->phone;
            $allOrders[$key]->order_details = $orderDetails;
          }
          return response()->json([
                                      'status' => true,
                                      'message' => "Available Orders Found.",
                                      'data' => $allOrders
                                  ], 200);
        }else{
          return response()->json([
                                      'status' => false,
                                      'message' => "Available Orders Not Found.",
                                      'data' => $orders
                                  ], 200);
        }

      }catch (Exception $e) {
        return response()->json([
                                  'message' => "Something Went Wrong!",
                                  'status' => false,
                                ], 422);
      }
    }

    public function orderPicked($orderId){
      try{
        $order = Order::where('id', $orderId)->first();
        $customer = User::where('id', $order->user_id)->first();
        $restaurant = User::where('id', $order->restaurant_id)->first();
        $driver = User::where('id', $order->driver_id)->first();
        //$restaurant = User::where('id', $order->restaurant_id)->first();
        $order->order_status = '4';//out of delivery or picked
        if($order->save()){
          $customer = User::where('id', $order['user_id'])->first();
          $restaurant = User::where('id', $order['restaurant_id'])->first();
          if($order){
            if($order->order_status == '3'){
              $order['user_name'] = $restaurant['name'];
              $order['user_image'] = $restaurant['image'];
              $order['user_address'] = $restaurant['address'];
              $order['user_lat'] = $restaurant['latitude'];
              $order['user_long'] = $restaurant['longitude'];
            }else{
              $order['user_name'] = $customer['name'];
              $order['user_image'] = $customer['image'];
              $order['user_address'] = $customer['address'];
              $order['user_lat'] = $customer['latitude'];
              $order['user_long'] = $customer['longitude'];
            }
          }
          $orderDetails = OrderDetail::where('order_id', $order->id)->get()->toArray();
          foreach ($orderDetails as $key => $details) {
            $item = Item::where('id', $details['item_id'])->first();
            //echo'<pre>';print_r($item);die;
            $orderDetails[$key]['item_name'] = $item['name'];
            $orderDetails[$key]['french_name'] = $item['french_name'];
          }
          $order['order_details'] = $orderDetails;
          //notification to customer for order picked
          $restaurantName = $restaurant['name'];
          $message = "#$orderId Order Is Picked From $restaurantName";
          $frenchMessage = $this->translation($message);
          if($customer['notification'] == '1'){
            if($customer['language'] == '1'){
              $msg = $message;
            }else{
              $msg = $frenchMessage[0];
            }
            $deta = array(  
                            "order_id" => $order->id,
                            //"restaurant_id" => $order->restaurant_id,
                            "restaurant_name" => $restaurant->name ,
                            "restaurant_lat" => $restaurant->latitude,
                            "restaurant_long" => $restaurant->longitude,
                            "restaurant_image" => $restaurant->image,
                            "restaurant_address" => $restaurant->address,
                            "notification_type" => '4'

                          );
            //$deta = array('notification_type' => '4');
            
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
          $saveNotification->user_id = $customer['id'];
          $saveNotification->order_id = $order->id;
          $saveNotification->restaurant_id = $order->restaurant_id;
          $saveNotification->notification = $message;
          $saveNotification->french_notification = $frenchMessage[0];
          $saveNotification->role = '2';
          $saveNotification->read = '0';
          $saveNotification->notification_type = '4';
          $saveNotification->image = $driver['image'];          
          $saveNotification->save();

          $restaurant = User::where('id', $order->restaurant_id)->first();
          $orderId = $order->id;
          $message1 = "Order #$orderId Is Picked";
          $frenchMessage = $this->translation($message1);
          if($restaurant['language'] == '1'){
            $msg1 = $message1;
          }else{
            $msg1 = $frenchMessage[0];
          }
          $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
          if($userTokens){
            foreach ($userTokens as $tokenKey => $userToken) {
              if($userToken['device_type'] == '0'){
                $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg1,$deta = array('notification_type' => '304'));    
              }
              if($userToken['device_type'] == '1'){
                $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg1,$deta = array('notification_type' => '304'));    
              }
            }
          }

          return response()->json([
                                      'status' => true,
                                      'message' => "Order Picked By Driver.",
                                      'data' => $order
                                  ], 200);
        }else{
          return response()->json([
                                      'message' => "Something Went Wrong!",
                                      'status' => false,
                                  ], 422);
        }
      }catch (Exception $e) {
        return response()->json([
                                    'message' => "Something Went Wrong!",
                                    'status' => false,
                                ], 422);
      }
    }


    // public function orderDeliveredByDriver($orderId){
    //   try{
    //     $order = Order::where('id', $orderId)->first();
    //     $customer = User::where('id', $order->user_id)->first();
    //     $order->order_status = '5';//Delieverd
    //     $setting = Setting::where('id', '1')->first();
    //     if($order->save()){
    //       //echo $order->driver_id;die;
          
    //       //notification to customer for order picked
    //       //add money to driver and restaurant wallet.
    //       $appAmount = $order->final_price*$setting['app_fee']/100;
    //       $restaurantAmount = $order->final_price-$appAmount;
    //       $restaurant = User::where('id', $order->restaurant_id)->update(['wallet', $restaurantAmount]);
    //       $driver = User::where('id', $order->driver_id)->update(['wallet', $order->delivery_fee]);
    //       if($customer->notification == '1'){
            
            
    //         $message = "Order Delivered";
    //         if($customer['device_type'] == '0'){
    //             $sendNotification = $this->sendPushNotification($customer['device_token'],$message,$deta = array());    
    //         }

    //         if($customer['device_type'] == '1'){
    //             $sendNotification = $this->iosPushNotification($customer['device_token'],$message,$deta = array());    
    //         }
    //       }

    //       return response()->json([
    //                                   'status' => true,
    //                                   'message' => "Order Delivered.",
    //                                   'data' => $order
    //                               ], 200);
    //     }else{//die('bye');
    //       return response()->json([
    //                                   'message' => "Something Went Wrong!",
    //                                   'status' => false,
    //                               ], 422); 
    //     }
    //   }catch (Exception $e) {
    //         return response()->json([
    //                                   'message' => "Something Went Wrong!",
    //                                   'status' => false,
    //                               ], 422);
    //   }
    // }


    public function checkDrive(Request $request){
    	try{
    		$rules = [
                    'order_id' => 'required',
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

        $order = Order::where("id",$request->order_id)->first();
        $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            //echo $order->order_status;die;
        if($order->order_status == '3'){ 
        	$user = User::where("id",$order->driver_id)->first();
        	return response()->json([
				                    'status' => true,
				                    'message' => "Driver Assigned.",
				                    'data' => $user
				                ], 200);
        }else if($order->order_status == 2){ 
        	$customerData = User::where("id",$order->user_id)->first();
    		//$customerData->latitude
    		$requestedDrivers = DriverRequest::where("order_id",$request->order_id)->pluck("driver_id","driver_id")->toArray();
    		

    		if(!empty($requestedDrivers)){
          $str = " and id Not IN (".implode(",", $requestedDrivers).") ";
        }else{
          $str = "";    
        }

        $setting = Setting::where('id', '1')->first();
        $distance = $setting->distance;

    		$query = "SELECT
                           `id`,`device_type`,`device_token`,
                           ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $customerData->latitude ) ) + COS( RADIANS( `latitude` ) )
                           * COS( RADIANS( $customerData->latitude )) * COS( RADIANS( `longitude` ) - RADIANS( $customerData->longitude )) ) * 3953 AS `distance`
                           FROM `users`
                           WHERE
                           ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $customerData->latitude ) ) + COS( RADIANS( `latitude` ) )
                           * COS( RADIANS( $customerData->latitude )) * COS( RADIANS( `longitude` ) - RADIANS( $customerData->longitude )) ) * 3959 < $distance And `role` IN('3') $str  ORDER BY `distance` DESC";
                           $nearByDriver = DB::select(DB::raw($query));

        	if($order->driver_id == ""){  // rejected
        		
               	$restaurantName = $userName[$order->restaurant_id];
               	if(!empty($nearByDriver)){
               			//echo "<pre>";print_r($nearByDriver);die;
               		$message = "New order for you from $restaurantName restaurant";
                  if($nearByDriver[0]->device_type == '0'){
                      $sendNotification = $this->sendPushNotification($nearByDriver[0]->device_type,$message);    
                  }

                  if($nearByDriver[0]->device_type == '1'){
                      $sendNotification = $this->iosPushNotification($nearByDriver[0]->device_type,$message);    
                  }
                   
                  $driverRequest = new DriverRequest();
                    $driverRequest->order_id = $order->id;
                    $driverRequest->driver_id = $nearByDriver[0]->id;


                    if($driverRequest->save()){
                		$order->driver_id = $nearByDriver[0]->id;
                		$currentTime = Carbon::now();
	          	   	$order->request_time = $currentTime;  

	          	   	if($order->save()){
	          	   		return response()->json([
                                    'status' => true,
                                    'message' => "Checking For Driver.",
                                    'data' => ""
                                ], 200); 		
	          	   	}else{
	          	   		return response()->json([
                                    'status' => true,
                                    'message' => "Somthing Went Wrong.",
                                    'data' => ""
                                ], 200); 		
	          	   	} 
                           
                    }else{
                        return response()->json([
                                            'message' => "Something Went Wrong!",
                                            'status' => false,
                                        ], 422);
                    }
               	}else{
               	 // no driver found
                 	return response()->json([
                                              'message' => "No Driver Found",
                                              'status' => true,
                                          ], 200);
               	}

        	}else{
        		//if request time is exeeded than not responded
        		
               	$requestedTime = $order->request_time;

                       //$selectedTime = "9:15:00";
               	$requestedTime = strtotime("+1 minutes", strtotime($requestedTime));
               

               	$currentTime = Carbon::now();
               	$currentTimeStr = strtotime($currentTime);
               	if($currentTimeStr >  $requestedTime){ // if 1 min exceed
                   // add user to checked list and send request to new user
               		$driverRequest = DriverRequest::where(["order_id" => $order->id,"driver_id" => $order->driver_id])->first();
                    $driverRequest->status = 2;

                    if($driverRequest->save()){

	          	   	$order->driver_id = null;
	          	   	$order->request_time = null;   
	          	   	if($order->save()){
		          	   	if(!empty($nearByDriver)){
		          	   		$restaurantName = $userName[$order->restaurant_id];
		          	   		$message = "New order for you from $restaurantName restaurant";
		                    if($nearByDriver[0]->device_type == '0'){
		                        $sendNotification = $this->sendPushNotification($nearByDriver[0]->device_type,$message);    
		                    }
		                    if($nearByDriver[0]->device_type == '1'){
		                        $sendNotification = $this->iosPushNotification($nearByDriver[0]->device_type,$message);    
		                    }

		                    $driverRequest = new DriverRequest();
	                        $driverRequest->order_id = $order->id;
	                        $driverRequest->driver_id = $nearByDriver[0]->id;
	                        if($driverRequest->save()){
	                        	return response()->json([
                                            'message' => "Checking For Driver",
                                            'status' => true,
                                        ], 200);
	                        }
		          	   	}else{
		          	   		return response()->json([
                                            'message' => "No Driver Found",
                                            'status' => true,
                                        ], 200);
		          	   	}
	          	   		
	          	   	}        	
                    }
               	}else{
               		return response()->json([
                                              'message' => "Driver Not Responded",
                                              'status' => true,
                                          ], 200);
       			}
        	}
        }else{
        	
        }

    	}catch (Exception $e) {
        return response()->json([
                                    'message' => "Something Went Wrong!",
                                    'status' => false,
                                ], 422);
      }
    }

    public function addTip(Request $request){
      try{
        $rules = [
                  'amount' => 'required',
                  'order_id' => 'required'
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

        $orderId = $request->order_id;
        $amount = $request->amount;
        if($user->wallet < $amount){
          return response()->json([
                                      'message' => "You Doesn't have sufficent amount in your wallet.",
                                      'status' => false,
                                  ], 200);
        }
        $order = Order::where('id', $orderId)->first();
        $driverId = $order['driver_id'];
        $driver = User::where('id', $driverId)->first();
        $wallet = $driver['wallet'] + $amount;
        $driver->wallet = $wallet;
        if($driver->save()){
            $user->wallet = $user->wallet-$amount;
            if($user->save()){
              $transaction = new Transaction;
              $transaction->user_id = $user->id;
              //$transaction->order_id = $order->id;
              $transaction->transaction_data = $driver['id'];
              //$transaction->reference = $request->receipt_id;
              $transaction->amount = $amount;
              $transaction->type = '5';
              //if($request->has('reason') && !empty($request->reason)){
                  $transaction->reason = "Tip paid";
              //}
              $transaction->save();

              $transaction = new Transaction;
              $transaction->user_id = $driver['id'];
              //$transaction->order_id = $order->id;
              $transaction->transaction_data = $user->id;
              //$transaction->reference = $request->receipt_id;
              $transaction->amount = $amount;
              //if($request->has('reason') && !empty($request->reason)){
                  $transaction->reason = "Tip received";
              //}
              $transaction->type = '6';
              $transaction->save();

              //if($driver->busy_status == '0'){
                  //$message = "New order from $restaurant->name";
                $message = "You Receive a tip of $amount from $user->name";
                $frenchMessage = $this->translation($message);
                if($driver->language == '1'){
                  $msg = $message;
                }else{
                  $msg = $frenchMessage[0];
                }
                if($driver->device_type == '0'){
                    $sendNotification = $this->sendPushNotification($driver->device_token,$msg,$deta = array('notification_type' => '312'));    
                }
                if($driver->device_type == '1'){
                    $sendNotification = $this->iosPushNotification($driver->device_token,$msg,$deta = array('notification_type' => '312'));    
                }

                return response()->json([
                                          'message' => "Tip Paid",
                                          'status' => true,
                                        ], 200);
              //}
            }else{
              return response()->json([
                                          'message' => "Something Went Wrong!",
                                          'status' => false,
                                      ], 422);
            }
        }else{
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
      }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
      }
    }

    public function orderDelivered($orderId){
      try{
        //$order = Order::where('id', $orderId)->update(['order_status' => '5']);
        $order = Order::where('id', $orderId)->first();
        $userInfo = User::where('id', $order['user_id'])->first();
        $restaurantInfo = User::where('id', $order['restaurant_id'])->first();
        $setting = Setting::where('id', '1')->first();
        $loyality = $setting['loyality'];
        $order->order_status = '5';
        //$order->loyality = $loyality;
        if($order->save()){
          $bsyStatus = User::where('id', $order->driver_id)->update(['busy_status' => '0']);
          $restaurantName = $restaurantInfo['name'];
          $message = "#$orderId Order From $restaurantName is Delivered";
          $frenchMessage = $this->translation($message);
          if($userInfo->language == '1'){
            $msg = $message;
          }else{
            $msg = $frenchMessage[0];
          }
          $appAmount = ($order->final_price-$order->delivery_fee)*$setting['app_fee']/100;
          $restaurantAmount = $order->final_price-$appAmount; // sourabh
          $resturentRes = User::where('id', $order->restaurant_id)->first();
          $restaurant = User::where('id', $order->restaurant_id)->update(['wallet' => $resturentRes->wallet + $restaurantAmount]);// sourabh
          //echo $order->delivery_fee;die;
          $transaction = new Transaction();
          $transaction->user_id = $order->restaurant_id; 
          $transaction->order_id = $orderId;
          $transaction->amount = $restaurantAmount;
          $transaction->reason = "given to restaurant";
          $transaction->type = "7";
          $transaction->save();

          $driverRes = User::where('id', $order->driver_id)->first();
          $driver = User::where('id', $order->driver_id)->update(['wallet' => $driverRes->wallet + $order->driver_fee]);

          $transaction = new Transaction();
          $transaction->user_id = $order->driver_id; 
          $transaction->order_id = $orderId;
          $transaction->amount = $order->driver_fee;
          $transaction->reason = "given to driver";
          $transaction->type = "8";
          $transaction->save();

          if($userInfo->notification == '1'){
            $deta = array(  
                            "order_id" => $order->id,
                            //"restaurant_id" => $order->restaurant_id,
                            "restaurant_name" => $restaurantInfo->name ,
                            "restaurant_lat" => $restaurantInfo->latitude,
                            "restaurant_long" => $restaurantInfo->longitude,
                            "restaurant_image" => $restaurantInfo->image,
                            "restaurant_address" => $restaurantInfo->address,
                            "notification_type" => '5'

                        );
            //$deta = array('notification_type' => '5');
            $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
            if($userTokens){
              foreach ($userTokens as $tokenKey => $userToken) {
                if($userInfo['device_type'] == '0'){
                    $sendNotification = $this->sendPushNotification($userInfo['device_token'],$msg,$deta);    
                }
                if($userInfo['device_type'] == '1'){
                    $sendNotification = $this->iosPushNotification($userInfo['device_token'],$msg,$deta);    
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
          $saveNotification->notification_type = '5';
          $saveNotification->image = $restaurantInfo->image;
          $saveNotification->save();
          
          //$message1 = "#$orderId Order Delivered";
          $message1 = "Order #$orderId was delivered successfully to $userInfo->name";
          $frenchMessage = $this->translation($message);
          if($resturentRes['language'] == '1'){
            $msg1 = $message1;
          }else{
            $msg1 = $frenchMessage[0];
          }
          $deta = array('order_id', $orderId, 'notification_type' => '305');
          $userTokens = UserToken::where('user_id', $order->restaurant_id)->get()->toArray();
          if($userTokens){
            foreach ($userTokens as $tokenKey => $userToken) {
              if($userToken['device_type'] == '0'){
                  $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg1,$deta);    
              }
              if($userToken['device_type'] == '1'){
                  $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg1,$deta);    
              }
            }
          }
          return response()->json([
                        'message' => "Order successfully delivered",
                        'status' => true,
                        'data' => $order
                    ], 200);
        }else{
          return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
                    ], 422);
        }
      }catch (Exception $e) {
            return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
                    ], 422);
      }
    }


    public function updateLatLong(Request $request){
      try{
        $rules = [
                    'latitude' => 'required',
                    'longitude' => 'required',              
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
        $longitude = $request->get('longitude');
        $userId = $user->id;
        $latitude = $request->get('latitude');
        $update = User::where('id', $userId)->update(['latitude' => $latitude, 'longitude' => $longitude]);
        if($update){
            return response()->json([
                                       "message" => "User's loaction updated successfully.",
                                       'status' => true,
                                   ]);   
        }else{
            return response()->json([
                                       "message" => "Something went wrong!",
                                       'status' => false,
                                   ]);
        } 
      }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
      }
    }

    public function driverStatus($status){
      try{
        $user = Auth::user();
        $driver = User::where('id', $user->id)->first();
        $driver->busy_status = $status;

        if($driver->save()){
          return response()->json([ 
                                     "message" => "Driver Status Is Updated.",
                                     'status' => true,
                                 ]);   
        }else{
          return response()->json([
                                     "message" => "Something went wrong.",
                                     'status' => false,
                                 ]);
        }

      }catch (Exception $e) {
            return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
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
              //     "data" => $deta
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
                  "data" => $deta,
                  "content-available" => 1,
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

    public function get_distance_matrxi_google($start_lat, $start_long, $end_lat, $end_long, $key="AIzaSyBWKzhdte7QcSwMh5Xm6UQv4CUx9_CZBYI") {
          $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$start_lat,$start_long&destinations=$end_lat,$end_long&units=imperial&key=$key";

          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          $data = curl_exec($ch);
          curl_close($ch);


            $response = json_decode($data, TRUE);
            //echo "<pre>";print_r($response);die;
            $data = array();
            
            if ($response['status'] == "OK") {

                $elements = $response['rows'][0]['elements'];
                if(array_key_exists('status', $elements[0]) && $elements[0]['status'] == "ZERO_RESULTS"){
                    $data['distance'] = array('value' => 0, 'units' => 'meters');
                    $data['duration'] = array('value' => 0, 'units' => 'seconds');
                    $data["error"] = 1;
                }else{
                    $distance = $elements[0]['distance']['value'];
                    $duration = $elements[0]['duration']['value'];
                    $data['distance'] = array('value' => $distance, 'units' => 'meters');
                    $data['duration'] = array('value' => $duration, 'units' => 'seconds');
                    $data["error"] = 1;
                }
                
            }else{
                $data["error"] = 0;
                $data["message"] = $response['status'];
            }

          return $data;
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

}
