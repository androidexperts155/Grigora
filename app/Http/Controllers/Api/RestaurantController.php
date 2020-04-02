<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\User;
use App\Cart;
use App\CartItemsDetail;
use App\Item;
use App\ItemCategory;
use App\ItemSubCategory;
use App\Setting;
use App\Order;
use App\ShortListedUser;
use App\Cuisine;
use App\TableBooking;
use App\Notification;
use App\Category;
use App\QuizQuestion;
use App\OrderDetail;
use App\DriverRequest;
use App\RestaurantCuisine;
use App\Favourite;
use App\UserToken;
use App\RatingReview;
use App\Promocode;
use App\RestaurantPromo;
use App\Transaction;
use App\RestaurantApprovedQuiz;
use App\OrderCancelReason;
use App\UserOrderType;
use App\RestaurantParentCuisine;
//use App\UserOrderType;
use Auth;
use DB;
use Carbon\Carbon;

class RestaurantController extends Controller
{

    public function ChangeOrderType(Request $request){
        try{
            $rules = [
                        'cart_type' => 'required',//1:delivery, 2:pickup
                        'restaurant_id' => 'required',
                        'user_id' => 'required',
                        'login_type' =>'required'
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

            //$user = Auth::user();
            $userId = $request->user_id;
            //return $user;
            $restaurantId = $request->restaurant_id;
            $restaurant = User::where('id', $restaurantId)->first();
            $carttype = $request->cart_type;

            $userOrderType = UserOrderType::where('user_id', $userId)
                                            ->where('restaurant_id', $restaurantId)
                                            ->first();

            if($userOrderType){
                if($carttype == '2' && $restaurant['pickup'] != '1'){
                    
                }else{
                    $userOrderType->order_type = $carttype;
                }
                if($userOrderType->save()){
                    return response()->json([
                                            'status' => true,
                                            'message' => "Cart type Updated Successfully.",
                                            //'data' => $data
                                        ], 200);
                }else{
                    return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);    
                }
            }else{
                $userOrderType = new UserOrderType;
                $userOrderType->user_id = $userId;
                $userOrderType->restaurant_id = $restaurantId;
                if($userOrderType->save()){
                    return response()->json([
                                            'status' => true,
                                            'message' => "Cart type Created Successfully.",
                                            //'data' => $data
                                        ], 200);
                }else{
                    return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);    
                }
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }
    // work_sat
    public function updatePreparingTime(Request $request){
        try{
            $rules = [
                        'order_id' => 'required',
                        "time" => 'required',
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

            $currennt_time = Carbon::now();
            $order = Order::where('id', $request->order_id)->first();
            $order->preparing_time =  $request->time;
            $order->request_time = $currennt_time;
            $order->update_preparing_time = '1';
            if($order->save()){
                $user = User::where('id', $order->user_id)->first();
                $restaurant = User::where('id', $order->restaurant_id)->first();
                $orderId = $order->id;
                $message = "Preparation time for order Id #$orderId has been increased due to some challenges. We apologize for the delay.";
                $frenchMessage = $this->translation($message);
                if($user->language == '1'){
                    $msg = $message;
                }else{
                    $msg = $frenchMessage[0];
                }
                if($user->notification == '1'){
                    $deta = $order;
                    $deta['notification_type'] = '11';
                    $deta['order_id'] = $order->id;
                    $userTokens = UserToken::where('user_id', $user->id)->get()->toArray();
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
                $saveNotification->notification_type = '11';
                $saveNotification->save();

                return response()->json([
                                            'message' => "Updated!",
                                            'status' => true,
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
    // work_sat

    public function list(Request $request){
    	try{
    		$rules = [
                        'sort' => 'required',//1=>rating,2=>nearby,3=>all
		                'lat' => 'required',
		                'long' => 'required',
                        //'search' => 'required',
                        //'user_id' => 'required',
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

             //echo $userId = $request->user_id;die;
            //echo'<pre>';print_r($user);die;
            $lat = $request->lat;
            $long = $request->long;

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
            $resIds = array();
            if($result){
                foreach ($result as $keyRes => $valueRes) {
                    $resIds[] = $valueRes->id;
                }
            }
//echo'<pre>';print_r($resIds);die;
            if($resIds){
                if($request->sort == "1"){
                    
                    $restaurants = User::whereIn('id', $resIds)
                                        ->where('role', '4')
                                        ->where('approved', '1')
                                        ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                        ->get()
                                        ->toArray();  
                    //$restaurants = User::where(['role' =>  '4'])->get()->toArray();
                    // $restaurants = User::where('role', '2')->withCount(['rest_rating' => function($query) {
                    //                             $query->select(DB::raw('coalesce(avg(rating),0)'));
                    //                         }])->orderByDesc('rest_rating')->get();
                    // $restaurants = User::where(['role' =>  '4'])
                    //                     ->leftJoin('rating_reviews','users.id','=','rating_reviews.receiver_id') ->select('users.*',DB::raw('avg(rating_reviews.rating) as rest_rating')) 
                    //                     ->GroupBy('rating_reviews.receiver_id')
                    //                     ->orderBy("rest_rating","DESC")
                    //                     ->get()
                    //                     ->toArray();


                    //echo "<pre>";print_r($restaurants);die;
                    //die;
                }elseif($request->sort == "2"){
                    //echo'<pre>';print_r($resIds);die;
                    $restaurants = User::whereIn('id', $resIds)
                                        ->where('role', '4')
                                        ->where('approved', '1')
                                        //->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                        ->get()
                                        ->toArray();    
                    //echo'<pre>';print_r($restaurants);die;
                }else{
                    $restaurants = User::whereIn('id', $resIds)
                                        ->where('role', '4')
                                        ->where('approved', '1')
                                        ->orderBy('id', 'DESC')
                                        ->get()
                                        ->toArray();    
                }
            }else{
                $restaurants = array();
            }

            if($restaurants){
                //echo '<pre>';print_r($restaurants);die;
                $resultRestaurants = array();
                if($request->has('search') && $request->search){
                    $content = $request->search;
                    
                    foreach ($restaurants as $key => $restaurant) {
                        //if(array_search($content, $restaurant)){
                        if (stripos($restaurant['name'],$content) !== false) {
                            //echo'hi';
                            $resultRestaurants[] = $restaurant;
                        }
                    }

                }else{
                    $resultRestaurants = $restaurants;
                }
                if($resultRestaurants){
                    foreach ($resultRestaurants as $key => $resultRestaurant) {
                        //echo'<pre>';print_r($resultRestaurant);die;

                        if($request->has('user_id') && !empty($request->user_id)){
                            //echo $request->user_id;die;
                            $favourite = Favourite::where(['user_id' => $request->user_id, 'restaurant_id' => $resultRestaurant['id']])->first();
                            //echo'<pre>';print_r($favourite);die;
                            if($favourite){
                                $resultRestaurants[$key]['favourite'] = true;    
                            }else{
                                $resultRestaurants[$key]['favourite'] = false;    
                            }
                        }else{
                            $resultRestaurants[$key]['favourite'] = false;    
                        }

                        $ratings = RatingReview::where('receiver_type', '2')
                                                    ->where('receiver_id', $resultRestaurant['id'])
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
                        $resultRestaurants[$key]['average_rating'] = $avergeRating;
                        $resultRestaurants[$key]['total_rating'] = $totalRating;

                    }

                    if($request->sort == "1"){
                        //$resultRestaurants = array_multisort(array_column($resultRestaurants, 'average_rating'), SORT_DESC, $resultRestaurants);
                        usort($resultRestaurants, function($a, $b) {
                            return $a['average_rating'] <=> $b['average_rating'];
                        });
                        $resultRestaurants = array_reverse($resultRestaurants);

                    }
                    //echo'<pre>';print_r($resultRestaurants);die;

                    $finalRestaurant = array();
                    foreach ($resultRestaurants as $k => $restaurant) {
                        //if restaurant does't have items than skip
                        $items = Item::where('restaurant_id', $restaurant['id'])->where('approved', '1')->get()->toArray();
                        //echo'<pre>';print_r($items);die;
                        if(!$items){
                            continue;
                        }
                        $finalRestaurant[] = $restaurant;
                    }
                    if($finalRestaurant){
                    	return response()->json([
        						                    'status' => true,
        						                    'message' => "Restaurants Found Successfully.",
        						                    'data' => $finalRestaurant
        						                ], 200);
                    }else{
                        return response()->json([
                                                    'status' => false,
                                                    'message' => "Restaurants Not Found.",
                                                    'data' => $resultRestaurants
                                                ], 404);    
                    }
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Restaurants Not Found.",
                                                'data' => $resultRestaurants
                                            ], 404);
                }
            }else{
            	return response()->json([
						                    'status' => false,
						                    'message' => "Restaurants Not Found.",
						                    'data' => $restaurants
						                ], 404);
            }

    	}catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    // public function trendingItems(Request $request){
    //     try{
    //         $rules = [
    //                     'latitude' => 'required',
    //                     'longitude' => 'required',
    //                 ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if($validator->fails())
    //         {
    //             return response()->json([
    //                                         'status' => false,
    //                                         "message" => $validator->errors()->first(),
    //                                        //'errors' => $validator->errors()->toArray(),
    //                                     ], 422);              
    //         }

    //         $lat = $request->latitude;
    //         $long = $request->longitude;

    //         $setting = Setting::where('id', '1')->first();
    //         $user = Auth::user();

    //         $distance = $setting->distance;
    //         $restaurantsUnderLocation = DB::select(DB::raw("SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
    //                     * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
    //                     FROM users
    //                     WHERE
    //                     ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
    //                     * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
    //                     ORDER BY `distance`"));
    //         $restaurantsUnderLocationIds = array();
    //         if($restaurantsUnderLocation){
    //             foreach ($restaurantsUnderLocation as $k => $restaurantsUnderLoc) {
    //                 $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
    //             }
    //         }

    //         $itemids = OrderDetail::orderBy('id', 'Desc')
    //                                 ->groupBy('item_id')
    //                                 ->pluck('item_id', 'item_id')
    //                                 ->toArray();

    //         $trendingItems = Item::whereIn('id', $itemids)
    //                                 ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                 ->limit(10)
    //                                 ->get()
    //                                 ->toArray();
    //                                 //echo $user->id;die;
    //         $checkcart = Cart::where('user_id', $user->id)->where('group_order', '<>', '1')->where('status', '1')->first();
    //         if($checkcart){
    //             $restaurantInfo = User::where('id', $checkcart['restaurant_id'])->first();
    //             $checkcart['restaurant_name'] = $restaurantInfo['name'];
    //         }

    //         if($trendingItems){
    //             $appFee = $setting['app_fee'];
    //             foreach ($trendingItems as $keyT => $trendingItem) {
    //                 $cuisins = Cuisine::where('id', $trendingItem['cuisine_id'])->first();
    //                 $oldprice = $trendingItem['price'];
    //                 $appPrice = $oldprice*$appFee/100;
    //                 $trendingItems[$keyT]['price'] = round($oldprice+$appPrice, 2);
    //                 $trendingItems[$keyT]['cuisine_name'] = $cuisins['name'];

    //                 $restaurantInfo = User::where('id', $trendingItem['restaurant_id'])->first();
    //                 $trendingItems[$keyT]['restaurant_name'] = $restaurantInfo['name'];
    //                 $trendingItems[$keyT]['restaurant_french_name'] = $restaurantInfo['french_name'];

    //                 $itemCategories = ItemCategory::where('item_id', $trendingItem['id'])->get()->toArray();
    //                 if($itemCategories){
    //                     foreach ($itemCategories as $keyC => $itemCategorie) {
    //                         $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
    //                         $itemCategories[$keyC]['item_sub_category'] = $itemSubCat;
    //                     }
    //                     $trendingItems[$keyT]['item_categories'] = $itemCategories;
    //                 }else{
    //                     $trendingItems[$keyT]['item_categories'] = array();
    //                 }

    //                 $ratings = RatingReview::where('receiver_type', '1')
    //                                         ->where('receiver_id', $trendingItem['id'])
    //                                         ->get()
    //                                         ->toArray();
    //                 $avergeRating = "0.0";
    //                 if($ratings){
    //                     $ratingArr = array();
    //                     foreach ($ratings as $key2 => $rating) {
    //                         $ratingArr[] = $rating['rating'];
    //                     }
    //                     $totalRating = count($ratings);
    //                     $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
    //                 }else{
    //                     $totalRating = "0";
    //                 }
    //                 $trendingItems[$keyT]['avg_ratings'] = $avergeRating;
    //                 $trendingItems[$keyT]['total_rating'] = $totalRating;

                    

    //                 if($checkcart){
    //                     $cartItemsDetailCount = CartItemsDetail::where('cart_id', $checkcart['id'])
    //                                                     ->where('user_id', $user->id)
    //                                                     ->where('item_id', $trendingItem['id'])
    //                                                     ->sum('quantity');
    //                     $trendingItems[$keyT]['item_count_in_cart'] = $cartItemsDetailCount;
    //                     //$trendingItems[$keyT]['cart'] = $checkcart;
    //                 }else{
    //                     $trendingItems[$keyT]['item_count_in_cart'] = 0;
    //                 }
    //             }

    //             return response()->json([
    //                                         'message' => "Trending Items Found.",
    //                                         'status' => true,
    //                                         'data' => array('cart' => $checkcart , 'trending' => $trendingItems )
    //                                     ], 200);
    //         }else{
    //             $trendingItems = array();
    //             return response()->json([
    //                                         'message' => "Trending Items Not Found.",
    //                                         'status' => false,
    //                                         'data' => $trendingItems 
    //                                     ], 200);
    //         }
            


    //     }catch (Exception $e) {
    //         return response()->json([
    //                                     'message' => "Something Went Wrong!",
    //                                     'status' => false,
    //                                 ], 422);
    //     }
    // }

     public function trendingItems(Request $request){
        try{
            $rules = [
                        'latitude' => 'required',
                        'longitude' => 'required',
                        'user_id' => 'required'
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

            $lat = $request->latitude;
            $long = $request->longitude;

            $setting = Setting::where('id', '1')->first();
            $userId = $request->user_id;

            $distance = $setting->distance;
            $restaurantsUnderLocation = DB::select(DB::raw("SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`"));
           /// return $restaurantsUnderLocation; 
            $restaurantsUnderLocationIds = array();
            if($restaurantsUnderLocation){
                foreach ($restaurantsUnderLocation as $k => $restaurantsUnderLoc) {
                    $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
                }
            }

            $itemids = OrderDetail::orderBy('id', 'Desc')
                                    ->groupBy('item_id')
                                    //->select('item_id', DB::raw('count(*) as total'))
                                    ->pluck('item_id', DB::raw('count(*) as total'))
                                    //->orderBy('total', 'Asc')
                                    //->get()
                                    ->toArray();
                                    //return $itemids; 
         //  echo'<pre>';print_r($itemids);
            krsort($itemids);
            //echo'<pre>';print_r($itemids);die;

            $trendingItems = Item::whereIn('id', $itemids)
                                    ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                    ->pluck('id')
                                    ->toArray();
                                    //echo $user->id;die;
           // return $trendingItems; 

                //24 hr

            $trendingItemsid_24hr = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                   ->whereIn('item_id', $trendingItems)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(1440))
                                   ->first();
            
              //48 hr


            if($trendingItemsid_24hr == ""){
               
                $trendingItemsid_48hr = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                   ->whereIn('item_id', $trendingItems)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(2880))
                                   ->first();
            }else{
                $trendingItemsid_48hr = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                   ->whereIn('item_id', $trendingItems)
                                   ->where('item_id','<>',$trendingItemsid_24hr->item_id)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(2880))
                                   ->first();

            
            }

            // 1 week 

            if(empty($trendingItemsid_48hr) && empty($trendingItemsid_24hr)){
                    
                $trendingItemsid_1wk = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                   ->whereIn('item_id', $trendingItems)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(10080))
                                   ->first();
            }elseif(empty($trendingItemsid_48hr) && !empty($trendingItemsid_24hr)){

                $trendingItemsid_1wk = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                    ->where('item_id','<>',$trendingItemsid_24hr->item_id)
                                    ->whereIn('item_id', $trendingItems)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(10080))
                                   ->first();

            
            }elseif(!empty($trendingItemsid_48hr) && empty($trendingItemsid_24hr)){
 
                $trendingItemsid_1wk = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                   ->whereIn('item_id', $trendingItems)
                                   ->where('item_id','<>',$trendingItemsid_48hr->item_id)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(10080))
                                   ->first();

            
            }else{
 
                  $trendingItemsid_1wk = OrderDetail::orderBy('total', 'Desc')
                                   ->groupBy('item_id')
                                   ->select('item_id', DB::raw('count(*) as total'))
                                   ->whereIn('item_id', $trendingItems)
                                    ->where('item_id','<>',$trendingItemsid_24hr->item_id)
                                   ->where('item_id','<>',$trendingItemsid_48hr->item_id)
                                   ->where('created_at', '>', Carbon::now()->subMinutes(10080))
                                   ->first();
            }



            if($userId == '0'){
                $checkcart = null;
            }else{
                $checkcart = Cart::where('user_id', $userId)->where('group_order', '<>', '1')->where('status', '1')->first();
            }
            if($checkcart){
                $restaurantInfo = User::where('id', $checkcart['restaurant_id'])->first();
                $checkcart['restaurant_name'] = $restaurantInfo['name'];
            }

        if($trendingItemsid_24hr){
              $trendingItems24hrdata = Item::where('id', $trendingItemsid_24hr->item_id)->get()->toArray();
        }else{
            $trendingItems24hrdata = array();
        }
        if($trendingItemsid_48hr){
              $trendingItems48hrdata = Item::where('id', $trendingItemsid_48hr->item_id)->get()->toArray();
        }else{
            $trendingItems48hrdata = array();
        }
        if($trendingItemsid_1wk){
              $trendingItems1wkdata = Item::where('id', $trendingItemsid_1wk->item_id)->get()->toArray();
        }else{
            $trendingItems1wkdata = array();
        }

          if($trendingItems24hrdata){
                $appFee = $setting['app_fee'];
                foreach ($trendingItems24hrdata as $keyT24 => $trendingItem24) {

                    $totalorder_inlast24hr = OrderDetail::where('item_id', $trendingItem24['id'])->select('item_id',DB::raw('count(*) as total_orders') ,DB::raw('count(DISTINCT user_id) as customers'))->get();

                   // return $totalorder_inlast24hr;
                    $cuisins = Cuisine::where('id', $trendingItem24['cuisine_id'])->first();
                    $oldprice = $trendingItem24['price'];
                    $appPrice = $oldprice*$appFee/100;
                    $trendingItems24hrdata[$keyT24]['time'] = '24 hr';
                    $trendingItems24hrdata[$keyT24]['customers'] =$totalorder_inlast24hr[0]['customers'];
                    $trendingItems24hrdata[$keyT24]['total_orders'] =$totalorder_inlast24hr[0]['total_orders'];
                    $trendingItems24hrdata[$keyT24]['price'] = round($oldprice+$appPrice, 2);
                    $trendingItems24hrdata[$keyT24]['cuisine_name'] = $cuisins['name'];

                    $restaurantInfo = User::where('id', $trendingItem24['restaurant_id'])->first();
                    $trendingItems24hrdata[$keyT24]['restaurant_name'] = $restaurantInfo['name'];
                    $trendingItems24hrdata[$keyT24]['restaurant_french_name'] = $restaurantInfo['french_name'];

                    $itemCategories = ItemCategory::where('item_id', $trendingItem24['id'])->get()->toArray();
                    if($itemCategories){
                        foreach ($itemCategories as $keyC => $itemCategorie) {
                            $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                            $itemCategories[$keyC]['item_sub_category'] = $itemSubCat;
                        }
                        $trendingItems24hrdata[$keyT24]['item_categories'] = $itemCategories;
                    }else{
                        $trendingItems24hrdata[$keyT24]['item_categories'] = array();
                    }

                    $ratings = RatingReview::where('receiver_type', '1')
                                            ->where('receiver_id', $trendingItem24['id'])
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
                    $trendingItems24hrdata[$keyT24]['avg_ratings'] = $avergeRating;
                    $trendingItems24hrdata[$keyT24]['total_rating'] = $totalRating;

                    

                    if($checkcart){
                        $cartItemsDetailCount = CartItemsDetail::where('cart_id', $checkcart['id'])
                                                        ->where('user_id', $userId)
                                                        ->where('item_id', $trendingItem24['id'])
                                                        ->sum('quantity');
                        $trendingItems24hrdata[$keyT24]['item_count_in_cart'] = $cartItemsDetailCount;
                        //$trendingItems[$keyT24]['cart'] = $checkcart;
                    }else{
                        $trendingItems24hrdata[$keyT24]['item_count_in_cart'] = 0;
                    }
                }

               
            }else{
                $trendingItems24hrdata = array();
               
            }
              if($trendingItems48hrdata){
                $appFee = $setting['app_fee'];
                foreach ($trendingItems48hrdata as $keyT48 => $trendingItem48) {

                    $totalorder_inlast48hr = OrderDetail::where('item_id', $trendingItem48['id'])->select('item_id',DB::raw('count(*) as total_orders') ,DB::raw('count(DISTINCT user_id) as customers'))->get();

                   // return $totalorder_inlast24hr;
                    $cuisins = Cuisine::where('id', $trendingItem48['cuisine_id'])->first();
                    $oldprice = $trendingItem48['price'];
                    $appPrice = $oldprice*$appFee/100;

                    $trendingItems48hrdata[$keyT48]['time'] = '48 hr';
                    $trendingItems48hrdata[$keyT48]['customers'] =$totalorder_inlast48hr[0]['customers'];
                    $trendingItems48hrdata[$keyT48]['total_orders'] =$totalorder_inlast48hr[0]['total_orders'];
                    $trendingItems48hrdata[$keyT48]['price'] = round($oldprice+$appPrice, 2);
                    $trendingItems48hrdata[$keyT48]['cuisine_name'] = $cuisins['name'];

                    $restaurantInfo = User::where('id', $trendingItem48['restaurant_id'])->first();
                    $trendingItems48hrdata[$keyT48]['restaurant_name'] = $restaurantInfo['name'];
                    $trendingItems48hrdata[$keyT48]['restaurant_french_name'] = $restaurantInfo['french_name'];

                    $itemCategories = ItemCategory::where('item_id', $trendingItem48['id'])->get()->toArray();
                    if($itemCategories){
                        foreach ($itemCategories as $keyC => $itemCategorie) {
                            $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                            $itemCategories[$keyC]['item_sub_category'] = $itemSubCat;
                        }
                        $trendingItems48hrdata[$keyT48]['item_categories'] = $itemCategories;
                    }else{
                        $trendingItems48hrdata[$keyT48]['item_categories'] = array();
                    }

                    $ratings = RatingReview::where('receiver_type', '1')
                                            ->where('receiver_id', $trendingItem48['id'])
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
                    $trendingItems48hrdata[$keyT48]['avg_ratings'] = $avergeRating;
                    $trendingItems48hrdata[$keyT48]['total_rating'] = $totalRating;

                    

                    if($checkcart){
                        $cartItemsDetailCount = CartItemsDetail::where('cart_id', $checkcart['id'])
                                                        ->where('user_id', $userId)
                                                        ->where('item_id', $trendingItem48['id'])
                                                        ->sum('quantity');
                        $trendingItems48hrdata[$keyT48]['item_count_in_cart'] = $cartItemsDetailCount;
                        //$trendingItems[$keyT48]['cart'] = $checkcart;
                    }else{
                        $trendingItems48hrdata[$keyT48]['item_count_in_cart'] = 0;
                    }
                }

               
            }else{
                $trendingItems48hrdata = array();
               
            }



            if($trendingItems1wkdata){
                $appFee = $setting['app_fee'];
                foreach ($trendingItems1wkdata as $keyT1wk => $trendingItem1wk) {

                    $totalorder_inlast1wk = OrderDetail::where('item_id', $trendingItem1wk['id'])->select('item_id',DB::raw('count(*) as total_orders') ,DB::raw('count(DISTINCT user_id) as customers'))->get();

                   // return $totalorder_inlast24hr;
                    $cuisins = Cuisine::where('id', $trendingItem1wk['cuisine_id'])->first();
                    $oldprice = $trendingItem1wk['price'];
                    $appPrice = $oldprice*$appFee/100;
                    $trendingItems1wkdata[$keyT1wk]['time'] = '1 week'; 

                    $trendingItems1wkdata[$keyT1wk]['customers'] =$totalorder_inlast1wk[0]['customers'];
                    $trendingItems1wkdata[$keyT1wk]['total_orders'] =$totalorder_inlast1wk[0]['total_orders'];
                    $trendingItems1wkdata[$keyT1wk]['price'] = round($oldprice+$appPrice, 2);
                    $trendingItems1wkdata[$keyT1wk]['cuisine_name'] = $cuisins['name'];

                    $restaurantInfo = User::where('id', $trendingItem1wk['restaurant_id'])->first();
                    $trendingItems1wkdata[$keyT1wk]['restaurant_name'] = $restaurantInfo['name'];
                    $trendingItems1wkdata[$keyT1wk]['restaurant_french_name'] = $restaurantInfo['french_name'];

                    $itemCategories = ItemCategory::where('item_id', $trendingItem1wk['id'])->get()->toArray();
                    if($itemCategories){
                        foreach ($itemCategories as $keyC => $itemCategorie) {
                            $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                            $itemCategories[$keyC]['item_sub_category'] = $itemSubCat;
                        }
                        $trendingItems1wkdata[$keyT1wk]['item_categories'] = $itemCategories;
                    }else{
                        $trendingItems1wkdata[$keyT1wk]['item_categories'] = array();
                    }

                    $ratings = RatingReview::where('receiver_type', '1')
                                            ->where('receiver_id', $trendingItem1wk['id'])
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
                    $trendingItems1wkdata[$keyT1wk]['avg_ratings'] = $avergeRating;
                    $trendingItems1wkdata[$keyT1wk]['total_rating'] = $totalRating;

                    

                    if($checkcart){
                        $cartItemsDetailCount = CartItemsDetail::where('cart_id', $checkcart['id'])
                                                        ->where('user_id', $userId)
                                                        ->where('item_id', $trendingItem1wk['id'])
                                                        ->sum('quantity');
                        $trendingItems1wkdata[$keyT1wk]['item_count_in_cart'] = $cartItemsDetailCount;
                        //$trendingItems[$keyT1wk]['cart'] = $checkcart;
                    }else{
                        $trendingItems1wkdata[$keyT1wk]['item_count_in_cart'] = 0;
                    }
                }

               
            }else{
                $trendingItems1wkdata = array();
               
            }

           $datafinal = array_merge($trendingItems24hrdata,$trendingItems48hrdata,$trendingItems1wkdata);
           

            if($trendingItems24hrdata || $trendingItems48hrdata || $trendingItems1wkdata ){
                 return response()->json([
                                            'message' => "Trending Items Found.",
                                            'status' => true,
                                            'data' => array('cart' => $checkcart , 'trendingItems' =>   $datafinal  )
                                        ], 200);
            
            }else{
                $trendingItems = array();
                return response()->json([
                                            'message' => "Trending Items Not Found.",
                                            'status' => false,
                                            'data' => array()
                                        ], 200);
            }


        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    // public function pickupRestaurants1(Request $request){
    //     try{
    //         $rules = [
    //                     'latitude' => 'required',
    //                     'longitude' => 'required',
    //                 ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if($validator->fails())
    //         {
    //             return response()->json([
    //                                         'status' => false,
    //                                         "message" => $validator->errors()->first(),
    //                                        //'errors' => $validator->errors()->toArray(),
    //                                     ], 422);              
    //         }

    //         $lat = $request->latitude;
    //         $long = $request->longitude;

    //         $setting = Setting::where('id', '1')->first();
    //         $distance = $setting->distance;

    //         $query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
    //                     * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
    //                     FROM users
    //                     WHERE
    //                     ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
    //                     * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
    //                     ORDER BY `distance`";
    //         $result = DB::select(DB::raw($query));
    //         $resIds = array();
    //         if($result){
    //             foreach ($result as $keyRes => $valueRes) {
    //                 $item = Item::where('restaurant_id', $valueRes->id)->where('approved', '1')->first();
    //                 if($item){
    //                     $resIds[] = $valueRes->id;
    //                 }
    //             }
    //         }

    //         $setting = Setting::where('id', '1')->first();
    //         $extraInfo = array();
    //         $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
    //         $extraInfo['min_order_vale'] = $setting['min_order'];
    //         $extraInfo['min_kilo_meter'] = $setting['min_km'];

    //         if($resIds){
    //             $restaurants = User::whereIn('id', $resIds)
    //                                     ->where('role', '4')
    //                                     ->where('approved', '1')
    //                                     ->where('pickup', '1')
    //                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                     ->orderBy('id', 'DESC')
    //                                     ->get()
    //                                     ->toArray();    
    //             if($restaurants){
    //                 foreach($restaurants as $k2 => $restaurant){
                        
    //                     $ratingReview = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->get()->toArray();
    //                     if($ratingReview){
    //                         $ratings = 0.0;
    //                         foreach ($ratingReview as $k => $ratreviw) {
    //                             $ratings = $ratings+$ratreviw['rating'];
    //                         }
    //                         $avergeRatings = round($ratings/count($ratingReview), 1);
                            
    //                     }else{
    //                         $avergeRatings = 0.0;
    //                     }
    //                     //echo'<pre>';print_r($customizedData[$k1]['restaurants'][$k2]);die;
    //                     $items = Item::where('restaurant_id', $restaurant['id'])
    //                                     ->where('approved', '1')
    //                                     ->select('id', 'image')
    //                                     ->limit(5)
    //                                     ->get()
    //                                     ->toArray();
    //                     //return $items;
    //                     //if($items){
    //                         $restaurants[$k2]['average_rating'] = $avergeRatings;
    //                         $restaurants[$k2]['total_rating'] = count($ratingReview);
    //                         $restaurants[$k2]['items'] = $items;
    //                         //$final = $restaurants;
    //                     // }else{
    //                     //     $restaurants = [];
    //                     // }
    //                 }
                    
    //                 if($restaurants){
    //                     return response()->json([
    //                                                 'message' => "Restaurants Found.",
    //                                                 'status' => true,
    //                                                 'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
    //                                             ], 200);    
    //                 }else{
    //                     return response()->json([
    //                                                 'message' => "Restaurants Not Found.",
    //                                                 'status' => false,
    //                                                 'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
    //                                             ], 200);   
    //                 }
    //             }else{
    //                 return response()->json([
    //                                             'message' => "Restaurants Not Found.",
    //                                             'status' => false,
    //                                             'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
    //                                         ], 200);        
    //             }
    //         }else{
    //             return response()->json([
    //                                         'message' => "Restaurants Not Found.",
    //                                         'status' => false,
    //                                         'data' => array('main_info' => null, 'extra_info' => $extraInfo)
    //                                     ], 200);    
    //         }

    //     }catch (Exception $e) {
    //         return response()->json([
    //                                     'message' => "Something Went Wrong!",
    //                                     'status' => false,
    //                                 ], 422);
    //     }
    // }

    public function pickupRestaurants(Request $request){
        try{
            $rules = [
                        //'latitude' => 'required',
                        //'longitude' => 'required',
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

            // $lat = $request->latitude;
            // $long = $request->longitude;

            // $setting = Setting::where('id', '1')->first();
            // $distance = $setting->distance;

            // $query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
            //             * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
            //             FROM users
            //             WHERE
            //             ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
            //             * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
            //             ORDER BY `distance`";
            // $result = DB::select(DB::raw($query));
            // $resIds = array();
            // if($result){
            //     foreach ($result as $keyRes => $valueRes) {
            //         $item = Item::where('restaurant_id', $valueRes->id)->where('approved', '1')->first();
            //         if($item){
            //             $resIds[] = $valueRes->id;
            //         }
            //     }
            // }

            $setting = Setting::where('id', '1')->first();
            $extraInfo = array();
            $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
            $extraInfo['min_order_vale'] = $setting['min_order'];
            $extraInfo['min_kilo_meter'] = $setting['min_km'];

            //if($resIds){
                $restaurants = User::where('role', '4')
                                        ->where('approved', '1')
                                        ->where('pickup', '1')
                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                        ->orderBy('id', 'DESC')
                                        ->get()
                                        ->toArray();    
                if($restaurants){
                    foreach($restaurants as $k2 => $restaurant){
                        
                        $ratingReview = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->get()->toArray();
                        if($ratingReview){
                            $ratings = 0.0;
                            foreach ($ratingReview as $k => $ratreviw) {
                                $ratings = $ratings+$ratreviw['rating'];
                            }
                            $avergeRatings = round($ratings/count($ratingReview), 1);
                            
                        }else{
                            $avergeRatings = 0.0;
                        }
                        //echo'<pre>';print_r($customizedData[$k1]['restaurants'][$k2]);die;
                        $items = Item::where('restaurant_id', $restaurant['id'])
                                        ->where('approved', '1')
                                        ->select('id', 'image', 'approx_prep_time')
                                        ->limit(5)
                                        ->get()
                                        ->toArray();
                        //return $items;
                        //if($items){
                            $restaurants[$k2]['average_rating'] = $avergeRatings;
                            $restaurants[$k2]['total_rating'] = count($ratingReview);
                            $restaurants[$k2]['items'] = $items;
                            //$final = $restaurants;
                        // }else{
                        //     $restaurants = [];
                        // }
                    }
                    
                    if($restaurants){
                        return response()->json([
                                                    'message' => "Restaurants Found.",
                                                    'status' => true,
                                                    'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
                                                ], 200);    
                    }else{
                        return response()->json([
                                                    'message' => "Restaurants Not Found.",
                                                    'status' => false,
                                                    'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
                                                ], 200);   
                    }
                }else{
                    return response()->json([
                                                'message' => "Restaurants Not Found.",
                                                'status' => false,
                                                'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
                                            ], 200);        
                }
            // }else{
            //     return response()->json([
            //                                 'message' => "Restaurants Not Found.",
            //                                 'status' => false,
            //                                 'data' => array('main_info' => null, 'extra_info' => $extraInfo)
            //                             ], 200);    
            // }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function checkUnderLocation(Request $request){
        try{
            $rules = [
                        'lat' => 'required',
                        'long' => 'required',
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

            $cart = Cart::where('user_id', $user->id)->first();
            $lat = $request->lat;
            $long = $request->long;

            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;

            if($cart){
                $query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`";
                $result = DB::select(DB::raw($query));
                $resIds = array();
                if($result){
                    foreach ($result as $keyRes => $valueRes) {
                        $resIds[] = $valueRes->id;
                    }
                }
                //echo'<pre>';print_r($resIds);die;
                if(in_array($cart['restaurant_id'], $resIds)){
                    return response()->json([
                                            'status' => true,
                                            'message' => "Restaurant is in proximity.",
                                            //'data' => $cart
                                        ], 200);    
                }else{
                    $deleteDetails = CartItemsDetail::where('cart_id', $cart['id'])->delete();
                    $deleteCart = Cart::where('id', $cart['id'])->delete();
                    return response()->json([
                                            'status' => false,
                                            'message' => "Kindly create your cart again.",
                                            //'data' => $cart
                                        ], 200);
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Cart is empty. Add items to cart.",
                                            'data' => $cart
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    // public function search(Request $request){
    //     try{

    //         $rules = [
    //                     'latitude' => 'required',
    //                     'longitude' => 'required',
    //                     //'search' => 'required',
    //                     'filter_id' => 'required',//1=all,2=restaurant,3=cuisine
    //                 ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if($validator->fails())
    //         {
    //             return response()->json([
    //                                         'status' => false,
    //                                         "message" => $validator->errors()->first(),
    //                                        //'errors' => $validator->errors()->toArray(),
    //                                     ], 422);              
    //         }

    //         $user = Auth::user();

    //         $filterId = $request->filter_id;
    //         $lat = $request->latitude;
    //         $long = $request->longitude;

    //         if($request->has('search')){
    //             $content = $request->search;
    //         }else{
    //             $content = "";
    //         }

    //         $setting = Setting::where('id', '1')->first();
    //         $distance = $setting->distance;
    //         $restaurantsUnderLocation = DB::select(DB::raw("SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
    //                     * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
    //                     FROM users
    //                     WHERE
    //                     ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
    //                     * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
    //                     ORDER BY `distance`"));

    //         $restaurantsUnderLocationIds = array();
    //         if($restaurantsUnderLocation){
    //             foreach ($restaurantsUnderLocation as $k => $restaurantsUnderLoc) {
    //                 $item = Item::where('restaurant_id', $restaurantsUnderLoc->id)->where('approved', '1')->first();
    //                 if($item){
    //                     $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
    //                 }
    //             }
    //         }

    //         if($filterId == '1'){
    //             //all
    //             if($user->language == '1'){
    //                 $cuisines = Cuisine::where('name', 'like', '%' . $content . '%')
    //                                     ->pluck('id', 'id')
    //                                     ->toArray();
    //             }else{
    //                 $cuisines = Cuisine::where('french_name', 'like', '%' . $content . '%')
    //                                     ->pluck('id', 'id')
    //                                     ->toArray();                   
    //             }

    //             $items = Item::whereIn('cuisine_id', $cuisines)
    //                             ->where('approved', '1')
    //                             ->pluck('restaurant_id', 'restaurant_id')
    //                             ->toArray();

    //             $restaurants1 = User::whereIn('id', $items)
    //                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->get()
    //                                     ->toArray();

    //             //return $restaurants1;
    //             if($user->language == '1'){
    //                 $restaurants2 = User::where('name', 'like', '%' . $content . '%')
    //                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->get()
    //                                     ->toArray();
    //             }else{
    //                 $restaurants2 = User::where('french_name', 'like', '%' . $content . '%')
    //                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->get()
    //                                     ->toArray();
    //             }

    //             $restaurants = array_merge($restaurants1, $restaurants2);

    //         }elseif($filterId == '2'){
    //             //restaurants
    //             if($user->language == '1'){
    //                 $restaurants = User::where('name', 'like', '%' . $content . '%')
    //                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->get()
    //                                     ->toArray();
    //             }else{
    //                 $restaurants = User::where('french_name', 'like', '%' . $content . '%')
    //                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->get()
    //                                     ->toArray();
    //             }
    //         }else{
    //             //cuisines
    //             if($user->language == '1'){
    //                 $cuisines = Cuisine::where('name', 'like', '%' . $content . '%')
    //                                     ->pluck('id', 'id')
    //                                     ->toArray();
    //             }else{
    //                 $cuisines = Cuisine::where('french_name', 'like', '%' . $content . '%')
    //                                     ->pluck('id', 'id')
    //                                     ->toArray();                   
    //             }

    //             $items = Item::whereIn('cuisine_id', $cuisines)
    //                             ->where('approved', '1')
    //                             ->pluck('restaurant_id', 'restaurant_id')
    //                             ->toArray();

    //             $restaurants = User::whereIn('id', $items)
    //                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->get()
    //                                     ->toArray();

    //         }
    //         //echo'<pre>';print_r($restaurants);die;
    //         if($restaurants){
    //             $finalRestaurant = array();
    //             foreach ($restaurants as $k => $restaurant) {
    //                 $ratingReview = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->get()->toArray();
    //                 if($ratingReview){
    //                     $ratings = 0.0;
    //                     foreach ($ratingReview as $key => $ratreviw) {
    //                         $ratings = $ratings+$ratreviw['rating'];
    //                     }
    //                     $avergeRatings = round($ratings/count($ratingReview), 1);
                        
    //                 }else{
    //                     $avergeRatings = 0.0;
    //                 }

    //                 $items = Item::where('restaurant_id', $restaurant['id'])
    //                                 ->where('approved', '1')
    //                                 ->select('id', 'image')
    //                                 ->limit(5)
    //                                 ->get()
    //                                 ->toArray();

    //                 //echo'<pre>';print_r($customizedData[$k1]['restaurants'][$k2]);die;
    //                 $restaurants[$k]['average_rating'] = $avergeRatings;
    //                 $restaurants[$k]['total_rating'] = count($ratingReview);
    //                 $restaurants[$k]['items'] = $items;
    //             }
    //             //return $restaurants;
    //             $setting = Setting::where('id', '1')->first();
    //             $extraInfo = array();
    //             $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
    //             $extraInfo['min_order_vale'] = $setting['min_order'];
    //             $extraInfo['min_kilo_meter'] = $setting['min_km'];

    //             return response()->json([
    //                                         'status' => true,
    //                                         'message' => "Restaurants Found Successfully.",
    //                                         'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
    //                                     ], 200);
    //         }else{
    //             return response()->json([
    //                                         'status' => false,
    //                                         'message' => "Restaurants Not Found.",
    //                                         'data' => $restaurants
    //                                     ], 404);
    //         }

    //     }catch (Exception $e) {
    //         return response()->json([
    //                                     'message' => "Something Went Wrong!",
    //                                     'status' => false,
    //                                 ], 422);
    //     }
    // }

    public function search(Request $request){
        try{

            $rules = [
                        'latitude' => 'required',
                        'longitude' => 'required',
                        //'search' => 'required',
                        'filter_id' => 'required',//1=all,2=restaurant,3=cuisine
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

            $has_user = 0;
            if($request->has("user_id") && $request->user_id != 0){
                $user = User::where("id",$request->user_id)->first();
                $has_user = 1;   
            }else{
                $has_user = 0;   
            }
            //$user = Auth::user();

            $filterId = $request->filter_id;
            $lat = $request->latitude;
            $long = $request->longitude;

            if($request->has('search')){
                $content = $request->search;
            }else{
                $content = "";
            }

            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;
            $restaurantsUnderLocation = DB::select(DB::raw("SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`"));

            $restaurantsUnderLocationIds = array();
            if($restaurantsUnderLocation){
                foreach ($restaurantsUnderLocation as $k => $restaurantsUnderLoc) {
                    $item = Item::where('restaurant_id', $restaurantsUnderLoc->id)->where('approved', '1')->first();
                    if($item){
                        $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
                    }
                }
            }
            $data = array();

            if($filterId == '1'){
                //all
                if($has_user){
                    if($user->language == '1'){
                        $cuisines = Cuisine::where('name', 'like', '%' . $content . '%')
                                            //->pluck('id', 'id')
                                            ->get()
                                            ->toArray();
                    }else{
                        $cuisines = Cuisine::where('french_name', 'like', '%' . $content . '%')
                                            //->pluck('id', 'id')
                                            ->get()
                                            ->toArray();                   
                    }  
                }else{
                      $cuisines = Cuisine::where('name', 'like', '%' . $content . '%')
                                            //->pluck('id', 'id')
                                            ->get()
                                            ->toArray();
                }
                

                $data['cuisines'] = $cuisines;

                if($has_user){
                        if($user->language == '1'){
                            $restaurants2 = User::where('name', 'like', '%' . $content . '%')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->get()
                                                ->toArray();
                        }else{
                            $restaurants2 = User::where('french_name', 'like', '%' . $content . '%')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->get()
                                                ->toArray();
                        }
                }else{
                    $restaurants2 = User::where('name', 'like', '%' . $content . '%')
                                            ->whereIn('id', $restaurantsUnderLocationIds)
                                            ->get()
                                            ->toArray();
                }                
                

                $data['restaurants'] = $restaurants2;

                //$restaurants = array_merge($restaurants1, $restaurants2);

            }elseif($filterId == '2'){
                //restaurants

                if($has_user){
                    if($user->language == '1'){
                        $restaurants = User::where('name', 'like', '%' . $content . '%')
                                            ->whereIn('id', $restaurantsUnderLocationIds)
                                            ->get()
                                            ->toArray();
                    }else{
                        $restaurants = User::where('french_name', 'like', '%' . $content . '%')
                                            ->whereIn('id', $restaurantsUnderLocationIds)
                                            ->get()
                                            ->toArray();
                    }
                }else{
                    $restaurants = User::where('name', 'like', '%' . $content . '%')
                                            ->whereIn('id', $restaurantsUnderLocationIds)
                                            ->get()
                                            ->toArray();
                }
                
                $data['restaurants'] = $restaurants;
            }else{
                //cuisines
                if($has_user){
                    if($user->language == '1'){
                        $cuisines = Cuisine::where('name', 'like', '%' . $content . '%')
                                            //->pluck('id', 'id')
                                            ->get()
                                            ->toArray();
                    }else{
                        $cuisines = Cuisine::where('french_name', 'like', '%' . $content . '%')
                                            //->pluck('id', 'id')
                                            ->get()
                                            ->toArray();                   
                    }
                }else{
                    $cuisines = Cuisine::where('name', 'like', '%' . $content . '%')
                                            //->pluck('id', 'id')
                                            ->get()
                                            ->toArray();    
                }
                
                $data['cuisines'] = $cuisines;
                $setting = Setting::where('id', '1')->first();
                $extraInfo = array();
                $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
                $extraInfo['min_order_vale'] = $setting['min_order'];
                $extraInfo['min_kilo_meter'] = $setting['min_km'];

                return response()->json([
                                            'status' => true,
                                            'message' => "Data Found Successfully.",
                                            'data' => array('main_info' => $data, 'extra_info' => $extraInfo)
                                        ], 200);

            }
            //echo'<pre>';print_r($restaurants);die;
            if($data['restaurants']){
                $finalRestaurant = array();
                foreach ($data['restaurants'] as $k => $restaurant) {
                    $ratingReview = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->get()->toArray();
                    if($ratingReview){
                        $ratings = 0.0;
                        foreach ($ratingReview as $key => $ratreviw) {
                            $ratings = $ratings+$ratreviw['rating'];
                        }
                        $avergeRatings = round($ratings/count($ratingReview), 1);
                        
                    }else{
                        $avergeRatings = 0.0;
                    }

                    $items = Item::where('restaurant_id', $restaurant['id'])
                                    ->where('approved', '1')
                                    ->select('id', 'image', 'approx_prep_time')
                                    ->limit(5)
                                    ->get()
                                    ->toArray();

                    //echo'<pre>';print_r($customizedData[$k1]['restaurants'][$k2]);die;
                    $data['restaurants'][$k]['average_rating'] = $avergeRatings;
                    $data['restaurants'][$k]['total_rating'] = count($ratingReview);
                    $data['restaurants'][$k]['items'] = $items;
                }
                //return $restaurants;
                $setting = Setting::where('id', '1')->first();
                $extraInfo = array();
                $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
                $extraInfo['min_order_vale'] = $setting['min_order'];
                $extraInfo['min_kilo_meter'] = $setting['min_km'];

                return response()->json([
                                            'status' => true,
                                            'message' => "Data Found Successfully.",
                                            'data' => array('main_info' => $data, 'extra_info' => $extraInfo)
                                        ], 200);
            }else{
                if($data['cuisines']){
                    $setting = Setting::where('id', '1')->first();
                    $extraInfo = array();
                    $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
                    $extraInfo['min_order_vale'] = $setting['min_order'];
                    $extraInfo['min_kilo_meter'] = $setting['min_km'];
                    return response()->json([
                                                'status' => true,
                                                'message' => "Data Found.",
                                                'data' => array('main_info' => $data, 'extra_info' => $extraInfo)
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Data Not Found.",
                                                'data' => $data
                                            ], 200);
                }
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function userInfo($username){
        try{
            $user = User::where('username', $username)->select('id','name','french_name','email','image')->first();
            if($user){
                return response()->json([
                                            'status' => true,
                                            'message' => "User Found Successfully.",
                                            'data' => $user
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "User Not Found Successfully.",
                                            'data' => $user
                                        ], 404);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function almostPrepared(Request $request){
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

            $order = Order::where('id', $request->order_id)->first();
            $order->dispatch = '1';
            $order->order_status = '7';
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            
            if($order->save()){ 
                $customer = User::where('id', $order->user_id)->first();
                $restaurant = User::where('id', $order->restaurant_id)->first();
                $driver = User::where('id', $order->driver_id)->first();

                
                $customerName = $userName[$order->user_id];
                $orderId = $order->id;
                $message = "Your Order #$orderId is prepared and now ready to be dispatched.";
                $frenchMessage = $this->translation($message);
                if($customer->language == '1'){
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
                                "notification_type" => '7'

                            );
                    //$deta = array('notification_type' => '7');
                // if($customer->notification == '1'){
                //     $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
                //         if($userTokens){
                //             foreach ($userTokens as $tokenKey => $userToken) {
                //                 if($userToken['device_type'] == '0'){
                //                     $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta);    
                //                 }
                //                 if($userToken['device_type'] == '1'){
                //                     $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta);    
                //                 }
                //             }
                //         }
                // }

                $saveNotification = new Notification;
                $saveNotification->user_id = $order->user_id;
                $saveNotification->order_id = $order->id;
                $saveNotification->restaurant_id = $order->restaurant_id;
                $saveNotification->notification = $message;
                $saveNotification->french_notification = $frenchMessage[0];
                $saveNotification->role = '2';
                $saveNotification->read = '0';
                $saveNotification->notification_type = '7';
                $saveNotification->image = $restaurant->image;
                $saveNotification->save();

                $deta = array(  
                                "order_id" => $order->id,
                                //"restaurant_id" => $order->restaurant_id,
                                "restaurant_name" => $restaurant->name ,
                                "restaurant_lat" => $restaurant->latitude,
                                "restaurant_long" => $restaurant->longitude,
                                "restaurant_image" => $restaurant->image,
                                "restaurant_address" => $restaurant->address,
                                "notification_type" => '313'

                            );
                $orderId = $order->id;
                $message1 = "Order #$orderId is ready For Dispatch, Hurry and pick it up now!.";
                $frenchMessage1 = $this->translation($message1);
                if($driver['language'] == '1'){
                    $msg1 = $message1;
                }else{
                    $msg1 = $frenchMessage1[0];
                }
                if($driver['device_type'] == '0'){
                    $sendNotification = $this->sendPushNotification($driver['device_token'],$msg1,$deta);    
                }
                if($driver['device_type'] == '1'){
                    $sendNotification = $this->iosPushNotification($driver['device_token'],$msg1,$deta);    
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Order Is Almost Ready.",
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

    public function itemlist(Request $request){
        try{
            $rules = [
                        'restaurant_id' => 'required',
                        //'user_id' => 'required',
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

            $restaurantId = $request->restaurant_id;
            $restaurantInfo = User::where('id', $restaurantId)->first();
            $cuisine = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();
            $itemlist = Item::where('restaurant_id', $restaurantId)->where('approved', '1')->get()->toArray();

            $setting = Setting::where('id', '1')->first();
            $appFee = $setting->app_fee;
            //return $itemlist;
            foreach ($itemlist as $key => $list) {
                $itemlist[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
                $price = $list['price']*$appFee/100;
                $itemlist[$key]['price'] = round($list['price']+$price, 2);
                $offerPrice = $list['offer_price']*$appFee/100;
                $itemlist[$key]['offer_price'] = $list['offer_price']+$offerPrice;
            }
            if($itemlist){
                foreach ($itemlist as $key => $item) {
                    $itemlist[$key]['restaurant_name'] = $restaurantInfo['name'];
                    $itemCategories = ItemCategory::where('item_id', $item['id'])->get()->toArray();
                    if($itemCategories){
                        foreach ($itemCategories as $key1 => $itemCategorie) {
                            $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                            $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                        }
                        $itemlist[$key]['item_categories'] = $itemCategories;
                    }else{
                        $itemlist[$key]['item_categories'] = array();
                    }

                    $ratings = RatingReview::where('receiver_type', '1')
                                            ->where('receiver_id', $item['id'])
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
                    $itemlist[$key]['avg_ratings'] = $avergeRating;
                    $itemlist[$key]['total_rating'] = $totalRating;

                    if($request->has('user_id') && !empty($request->user_id)){
                        $userId = $request->user_id;
                        $cart = Cart::where('user_id', $userId)->first();
                        if($cart){
                            $cartItemsDetail = CartItemsDetail::where('cart_id', $cart['id'])->where('item_id', $item['id'])->sum('quantity');
                            $itemlist[$key]['item_count_in_cart'] = $cartItemsDetail;
                        }else{
                            $itemlist[$key]['item_count_in_cart'] = '0';
                        }
                    }else{
                        $itemlist[$key]['item_count_in_cart'] = '0';
                    }
                }

                return response()->json([
                                            'status' => true,
                                            'message' => "Restaurants Items Found Successfully.",
                                            'data' => $itemlist
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Restaurants Items Not Found.",
                                            'data' => $itemlist
                                        ], 404);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function getItemByCuisine(Request $request){
        try{
            $rules = [
                        'cuisine_id' => 'required',
                        //'price_range' => 'required',//1:below 10, 2:below 100, 3:below 1000
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

            $cuisineId = $request->cuisine_id;

            $items = Item::where('cuisine_id', $cuisineId)->where('approved', '1')->get()->toArray();
            if($items){
                foreach ($items as $key => $item) {
                        
                }
                return response()->json([
                                            'message' => "Items Found.",
                                            'status' => true,
                                            'data' => $items
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => "Items Not Found.",
                                            'status' => false,
                                            'data' => []
                                        ], 200);    
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    // public function restaurantDetails(Request $request){
    //     try{
    //         $rules = [
    //                     'restaurant_id' => 'required',
    //                     //'price_range' => 'required',//1:below 10, 2:below 100, 3:below 1000
    //                     //'cart_id' => 'required',
    //                     //'user_id' => 'required'
    //                 ];
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
    //         //echo $user->id;die;
    //         $restaurantId = $request->restaurant_id;
    //         $checkItems = Item::where('restaurant_id', $restaurantId)->where('approved', '1')->first();
    //         //return $checkItems;
    //         if($checkItems){
    //             if($request->has('price_range') && !empty($request->price_range)){
    //                 $priceRange = $request->price_range;
    //             }else{
    //                 $priceRange = '0';
    //             }
    //             if($priceRange == '1'){
    //                 $priceR = '10';
    //             }elseif($priceRange == '2'){
    //                 $priceR = '100';
    //             }elseif($priceRange == '3'){
    //                 $priceR = '1000';
    //             }else{
    //                 $priceR = '100000';
    //             }
    //             $restaurantInfo = User::where('id', $restaurantId)->first();
    //             $cuisine = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();

    //             $setting = Setting::where('id', '1')->first();
    //             $appFee = $setting->app_fee;
    //             $userId = $user->id;
    //             $cart = Cart::where('user_id', $userId)->where('status','1')->first();
    //             //return $cart;
    //             $items = array();
    //             $popularItems = array();
    //             $ratings = RatingReview::where('receiver_type', '2')
    //                                             ->where('receiver_id', $restaurantId)
    //                                             ->get()
    //                                             ->toArray();
    //             $avergeRating = "0.0";
    //             if($ratings){
    //                 $reviewArr = $ratingArr = array();
    //                 foreach ($ratings as $key1 => $rating) {
    //                     $ratingArr[] = $rating['rating'];
    //                     $reviewArr[] = $rating['review'];
    //                 }
    //                 $totalRating = count($ratings);
    //                 $totalReview = count($reviewArr);
    //                 $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
    //             }else{
    //                 $totalRating = "0";
    //                 $totalReview = "0";
    //             }

    //             //return $restaurantId;
    //             $restaurantCuisinesIds = Item::where('restaurant_id', $restaurantId)
    //                                             ->where('approved', '1')
    //                                             ->groupBy('cuisine_id')
    //                                             ->pluck('cuisine_id')
    //                                             ->toArray();
    //             //return $restaurantCuisinesIds;
    //             $cuisinNames = Cuisine::whereIn('id', $restaurantCuisinesIds)->pluck('name')->toArray();
    //             if($cuisinNames){
    //                 $cuisinName = implode(',', $cuisinNames);
    //             }else{
    //                 $cuisinName = "";
    //             }

    //             $itemsPrepTime = Item::where('restaurant_id', $restaurantId)->where('approved', '1')->pluck('approx_prep_time', 'id')->toArray();
    //             $getcart_id =  Cart::where('user_id', $user->id)
    //                                 ->where('group_order', '<>', '1')
    //                                 ->where('status','1')
    //                                 ->first();
    //             if($getcart_id){
    //                 $cart_id = $getcart_id->id;
    //             }else{
    //                 $cart_id = "";
    //             }

    //             $userOrderType = UserOrderType::where('user_id', $user->id)
    //                                             ->where('restaurant_id', $restaurantId)
    //                                             ->first();
    //             if($userOrderType){
    //                 $items['order_type'] = $userOrderType['order_type'];
    //             }else{
    //                 $items['order_type'] = "";
    //             }
    //             $items['restaurant_id'] = $restaurantId;
    //             $items['restaurant_name'] = $restaurantInfo->name;
    //             $items['cuisines'] = $cuisinName;
    //             $items['total_rating'] = $totalRating;
    //             $items['total_review'] = $totalReview;
    //             if($request->has('cart_id') && !empty($request->cart_id)){
    //                 $items['cart_id'] = $request->cart_id;
    //             }else{
    //                 $items['cart_id'] = $cart_id;
    //             }
    //             $items['veg'] = $restaurantInfo->pure_veg;
    //             $items['pickup'] = $restaurantInfo->pickup;
    //             $items['table_booking'] = $restaurantInfo->table_booking;
    //             $items['no_of_seats'] = $restaurantInfo->no_of_seats;
    //             $items['full_time'] = $restaurantInfo->full_time;
    //             $items['opening_time'] = $restaurantInfo->opening_time;
    //             $items['closing_time'] = $restaurantInfo->closing_time;
    //             $items['estimated_preparing_time'] = max($itemsPrepTime);
    //             //$items['estimated_delivery_time'] = (int)$estimatedDeliveryTime;
    //             $items['popluar_items'] = $popularItems;
    //             $previousOrderIds = Order::where('user_id', $user->id)
    //                                     ->where('restaurant_id', $restaurantId)
    //                                     ->where('order_status', '5')
    //                                     ->pluck('id')
    //                                     ->toArray();
    //             if($previousOrderIds){

    //                 $cartItemsDetails = CartItemsDetail::where('cart_id', $cart['id'])->pluck('item_id', 'item_id')->toArray();

    //                 $previousOrderItemIds = OrderDetail::whereIn('order_id', $previousOrderIds)
    //                                                 ->pluck('item_id')
    //                                                 ->toArray();
    //                 $previousOrderedItems = Item::whereIn('id', $previousOrderItemIds)
    //                                             ->whereNotIn('id', $cartItemsDetails)
    //                                             ->where('price', '<', $priceR)
    //                                             ->where('approved', '1')
    //                                             ->get()
    //                                             ->toArray();

    //                 foreach ($previousOrderedItems as $key => $list) {
    //                     $previousOrderedItems[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
    //                     $price = $list['price']*$appFee/100;
    //                     $previousOrderedItems[$key]['price'] = round($list['price']+$price, 2);
    //                     $offerPrice = $list['offer_price']*$appFee/100;
    //                     $previousOrderedItems[$key]['offer_price'] = $list['offer_price']+$offerPrice;

    //                     $previousOrderedItems[$key]['restaurant_name'] = $restaurantInfo['name'];
    //                     $itemCategories = ItemCategory::where('item_id', $list['id'])->get()->toArray();
    //                     if($itemCategories){
    //                         foreach ($itemCategories as $key1 => $itemCategorie) {
    //                             $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
    //                             $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
    //                         }
    //                         $previousOrderedItems[$key]['item_categories'] = $itemCategories;
    //                     }else{
    //                         $previousOrderedItems[$key]['item_categories'] = array();
    //                     }

    //                     $ratings = RatingReview::where('receiver_type', '1')
    //                                             ->where('receiver_id', $list['id'])
    //                                             ->get()
    //                                             ->toArray();
    //                     $avergeRating = "0.0";
    //                     if($ratings){
    //                         $ratingArr = array();
    //                         foreach ($ratings as $key1 => $rating) {
    //                             $ratingArr[] = $rating['rating'];
    //                         }
    //                         $totalRating = count($ratings);
    //                         $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
    //                     }else{
    //                         $totalRating = "0";
    //                     }
    //                     $previousOrderedItems[$key]['avg_ratings'] = $avergeRating;
    //                     $previousOrderedItems[$key]['total_rating'] = $totalRating;

    //                     //if($request->has('user_id') && !empty($request->user_id)){
                        
    //                     //changes by dilpreet
                        
    //                     // if($cart){
    //                     //     $cartItemsDetail = CartItemsDetail::where('cart_id', $cart['id'])->sum('quantity');
    //                     //     $previousOrderedItems[$key]['item_count_in_cart'] = $cartItemsDetail;
    //                     // }else{
    //                     //     $previousOrderedItems[$key]['item_count_in_cart'] = '0';
    //                     // }
    //                     // }else{
    //                     //     $itemlist[$key]['item_count_in_cart'] = '0';
    //                     // }
    //                 }

    //             }else{
    //                 $previousOrderedItems = array();
    //             }

    //             $userId = $user->id;
    //             if($request->has('cart_id') && !empty($request->cart_id)){
    //                 $cart = Cart::where('id', $request->cart_id)->where('status','1')->first();
    //                 if($cart['group_order'] == '1'){
    //                     $cart->user_name = $user->name;
    //                     $cart->user_french_name = $user->french_name;
    //                     $items['cart'] = $cart;
    //                 }
    //             }else{
    //                 $cart = Cart::where('user_id', $user->id)->where('group_order', '<>', '1')->where('status','1')->first();
    //                 $restaurantName = User::where('id', $cart['restaurant_id'])->first();
    //                 if($cart){
    //                     $cart['restaurant_name'] = $restaurantName['name'];
    //                 }
    //                 $items['normal_cart'] = $cart;
    //             }

    //             $items['previous_ordered_items'] = $previousOrderedItems;

                

    //             //$cuisinesitems = array();
    //             //return $restaurantCuisinesIds;
    //             foreach ($restaurantCuisinesIds as $k => $restaurantCuisinesId) {
    //                 //echo $restaurantCuisinesId;die;
    //                 //echo $price;die;
    //                 $cuisnitem = Item::where('cuisine_id', $restaurantCuisinesId)
    //                                     ->where('restaurant_id', $restaurantId)
    //                                     ->where('price', '<=', $priceR)
    //                                     ->where('approved', '1')
    //                                     ->get()
    //                                     ->toArray();
                    
    //                 //return $cuisnitem;
    //                 //echo'<pre>';print_r($cuisnitem);
    //                 foreach ($cuisnitem as $key => $list) {
    //                     $cuisnitem[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
    //                     $oldprice = $list['price'];
    //                     $appPrice = $oldprice*$appFee/100;
    //                     $cuisnitem[$key]['price'] = round($oldprice+$appPrice, 2);
    //                     $oldOfferPrice = $list['offer_price'];
    //                     $appofferPrice = $oldOfferPrice*$appFee/100;
    //                     $cuisnitem[$key]['offer_price'] = round($oldOfferPrice+$appofferPrice, 2);

    //                     $cuisnitem[$key]['restaurant_name'] = $restaurantInfo['name'];
    //                     $itemCategories = ItemCategory::where('item_id', $list['id'])->get()->toArray();
    //                     if($itemCategories){
    //                         foreach ($itemCategories as $key1 => $itemCategorie) {
    //                             $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
    //                             $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
    //                         }
    //                         $cuisnitem[$key]['item_categories'] = $itemCategories;
    //                     }else{
    //                         $cuisnitem[$key]['item_categories'] = array();
    //                     }

    //                     $ratings = RatingReview::where('receiver_type', '1')
    //                                             ->where('receiver_id', $list['id'])
    //                                             ->get()
    //                                             ->toArray();
    //                     $avergeRating = "0.0";
    //                     if($ratings){
    //                         $ratingArr = array();
    //                         foreach ($ratings as $key2 => $rating) {
    //                             $ratingArr[] = $rating['rating'];
    //                         }
    //                         $totalRating = count($ratings);
    //                         $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
    //                     }else{
    //                         $totalRating = "0";
    //                     }
    //                     $cuisnitem[$key]['avg_ratings'] = $avergeRating;
    //                     $cuisnitem[$key]['total_rating'] = $totalRating;
    //                     //return $cart;
    //                     //return $list;
    //                     if($cart){
    //                         $cartItemsDetailCount = CartItemsDetail::where('cart_id', $cart['id'])
    //                                                                 ->where('user_id', $user->id)
    //                                                                 ->where('item_id', $list['id'])
    //                                                                 ->sum('quantity');
    //                         //return $cartItemsDetailCount;
    //                         $cartItemsDetail = CartItemsDetail::where('cart_id', $cart['id'])->where('item_id', $list['id'])->get()->toArray();
    //                         $cuisnitem[$key]['item_cart'] = $cartItemsDetail;
    //                         $cuisnitem[$key]['item_count_in_cart'] = $cartItemsDetailCount;

    //                     }else{
    //                         $cuisnitem[$key]['item_count_in_cart'] = '0';
    //                     }
    //                 }
                    
    //                 $items['all_data'][$k]['name'] = $cuisine[$restaurantCuisinesId];
    //                 $items['all_data'][$k]['items'] = $cuisnitem;
    //             }
    //             if($cart){
    //                 $items['total_cart_item'] = $cart->quantity;
    //             }else{
    //                 $items['total_cart_item'] = 0;
    //             }
    //             //echo'<pre>';print_r($items);die;
                    
    //             return response()->json([
    //                                         'status' => true,
    //                                         'message' => "Restaurants Items Found Successfully.",
    //                                         'data' => $items
    //                                     ], 200);
    //         }else{
    //             return response()->json([
    //                                         'status' => false,
    //                                         'message' => "Restaurants Items Not Found.",
    //                                         'data' => []
    //                                     ], 200);
    //         }

    //     }catch (Exception $e) {
    //         return response()->json([
    //                                     'message' => "Something Went Wrong!",
    //                                     'status' => false,
    //                                 ], 422);
    //     }
    // }

    public function restaurantDetails(Request $request){
        try{
            $rules = [
                        'restaurant_id' => 'required',
                        //'price_range' => 'required',//1:below 10, 2:below 100, 3:below 1000
                        //'cart_id' => 'required',
                        'user_id' => 'required', 
                        'login_type' => 'required'
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
                $device_id = $request->user_id;
                $userId = 0;

            }else{
                $userId = $request->user_id;
            }

            
            //echo $user->id;die;
            $restaurantId = $request->restaurant_id;
            $checkItems = Item::where('restaurant_id', $restaurantId)->where('approved', '1')->first();
            //return $checkItems;
            if($checkItems){
                if($request->has('price_range') && !empty($request->price_range)){
                    $priceRange = $request->price_range;
                }else{
                    $priceRange = '0';
                }
                if($priceRange == '1'){
                    $priceR = '10';
                }elseif($priceRange == '2'){
                    $priceR = '100';
                }elseif($priceRange == '3'){
                    $priceR = '1000';
                }else{
                    $priceR = '100000';
                }
                $restaurantInfo = User::where('id', $restaurantId)->first();
                $cuisine = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();

                $setting = Setting::where('id', '1')->first();
                $appFee = $setting->app_fee;
                
                //$userId = $user->id;
                if($userId == '0'){
                    $cart = array();
                }else{
                    if($request->login_type == 2 && !empty($device_id)){
                        $cart = Cart::where('user_id', $device_id)->where('status','1')->first();
                    }else{
                        $cart = Cart::where('user_id', $userId)->where('status','1')->first();
                    }
                    
                }
                //return $cart;
                $items = array();
                $popularItems = array();
                $ratings = RatingReview::where('receiver_type', '2')
                                                ->where('receiver_id', $restaurantId)
                                                ->get()
                                                ->toArray();
                $avergeRating = "0.0";
                if($ratings){
                    $reviewArr = $ratingArr = array();
                    foreach ($ratings as $key1 => $rating) {
                        $ratingArr[] = $rating['rating'];
                        $reviewArr[] = $rating['review'];
                    }
                    $totalRating = count($ratings);
                    $totalReview = count($reviewArr);
                    $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
                }else{
                    $totalRating = "0";
                    $totalReview = "0";
                }

                //return $restaurantId;
                $restaurantCuisinesIds = Item::where('restaurant_id', $restaurantId)
                                                ->where('approved', '1')
                                                ->groupBy('cuisine_id')
                                                ->pluck('cuisine_id')
                                                ->toArray();
                //return $restaurantCuisinesIds;
                $cuisinNames = Cuisine::whereIn('id', $restaurantCuisinesIds)->pluck('name')->toArray();
                if($cuisinNames){
                    $cuisinName = implode(',', $cuisinNames);
                }else{
                    $cuisinName = "";
                }

                $itemsPrepTime = Item::where('restaurant_id', $restaurantId)->where('approved', '1')->pluck('approx_prep_time', 'id')->toArray();
                if($userId == '0'){
                    $cart_id = "";
                }else{
                    if($request->login_type == 2 && !empty($device_id)){
                        $getcart_id =  Cart::where('user_id', $device_id)
                                        ->where('group_order', '<>', '1')
                                        ->where('status','1')
                                        ->first();
                    }else{
                        $getcart_id =  Cart::where('user_id', $userId)
                                        ->where('group_order', '<>', '1')
                                        ->where('status','1')
                                        ->first();
                    }
                    
                    if($getcart_id){
                        $cart_id = $getcart_id->id;
                    }else{
                        $cart_id = "";
                    }
                }

                // if($userId == '0'){
                //     $items['order_type'] = "";
                // }else{
                    if($request->login_type == 2 && !empty($device_id)){
                        $userOrderType = UserOrderType::where('user_id', $device_id)
                                                    ->where('restaurant_id', $restaurantId)
                                                    ->first();
                    }else{
                        $userOrderType = UserOrderType::where('user_id', $userId)
                                                    ->where('restaurant_id', $restaurantId)
                                                    ->first();
                    }
                    
                    if($userOrderType){
                        $items['order_type'] = $userOrderType['order_type'];
                    }else{
                        $items['order_type'] = "";
                    }
                //}
                $items['restaurant_id'] = $restaurantId;
                $items['restaurant_name'] = $restaurantInfo->name;
                $items['cuisines'] = $cuisinName;
                $items['total_rating'] = $totalRating;
                $items['total_review'] = $totalReview;
                if($request->has('cart_id') && !empty($request->cart_id)){
                    $items['cart_id'] = $request->cart_id;
                }else{
                    $items['cart_id'] = $cart_id;
                }
                $items['veg'] = $restaurantInfo->pure_veg;
                $items['pickup'] = $restaurantInfo->pickup;
                $items['table_booking'] = $restaurantInfo->table_booking;
                $items['no_of_seats'] = $restaurantInfo->no_of_seats;
                $items['full_time'] = $restaurantInfo->full_time;
                $items['opening_time'] = $restaurantInfo->opening_time;
                $items['latitude'] = $restaurantInfo->latitude;
                $items['longitude'] = $restaurantInfo->longitude;
                $items['address'] = $restaurantInfo->address;
                $items['french_address'] = $restaurantInfo->french_address;
                $items['closing_time'] = $restaurantInfo->closing_time;
                $items['estimated_preparing_time'] = max($itemsPrepTime);
                $items['busy_status'] = $restaurantInfo->busy_status;
                //$items['estimated_delivery_time'] = (int)$estimatedDeliveryTime;
                $items['popluar_items'] = $popularItems;
                if($userId == '0'){
                    $previousOrderIds = array();
                }else{
                    if($request->login_type == 2 && !empty($device_id)){
                        $previousOrderIds = Order::where('user_id', $device_id)
                                            ->where('restaurant_id', $restaurantId)
                                            ->where('order_status', '5')
                                            ->pluck('id')
                                            ->toArray();
                    }else{
                        $previousOrderIds = Order::where('user_id', $userId)
                                            ->where('restaurant_id', $restaurantId)
                                            ->where('order_status', '5')
                                            ->pluck('id')
                                            ->toArray();
                    }
                    
                }
                if($previousOrderIds && $request->login_type == 1){

                    $cartItemsDetails = CartItemsDetail::where('cart_id', $cart['id'])->pluck('item_id', 'item_id')->toArray();

                    $previousOrderItemIds = OrderDetail::whereIn('order_id', $previousOrderIds)
                                                    ->pluck('item_id')
                                                    ->toArray();
                    $previousOrderedItems = Item::whereIn('id', $previousOrderItemIds)
                                                ->whereNotIn('id', $cartItemsDetails)
                                                ->where('price', '<', $priceR)
                                                ->where('approved', '1')
                                                ->get()
                                                ->toArray();

                    foreach ($previousOrderedItems as $key => $list) {
                        $previousOrderedItems[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
                        $price = $list['price']*$appFee/100;
                        $previousOrderedItems[$key]['price'] = round($list['price']+$price, 2);
                        $offerPrice = $list['offer_price']*$appFee/100;
                        $previousOrderedItems[$key]['offer_price'] = $list['offer_price']+$offerPrice;

                        $previousOrderedItems[$key]['restaurant_name'] = $restaurantInfo['name'];
                        $itemCategories = ItemCategory::where('item_id', $list['id'])->get()->toArray();
                        if($itemCategories){
                            foreach ($itemCategories as $key1 => $itemCategorie) {
                                $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                                $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                            }
                            $previousOrderedItems[$key]['item_categories'] = $itemCategories;
                        }else{
                            $previousOrderedItems[$key]['item_categories'] = array();
                        }

                        $ratings = RatingReview::where('receiver_type', '1')
                                                ->where('receiver_id', $list['id'])
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
                        $previousOrderedItems[$key]['avg_ratings'] = $avergeRating;
                        $previousOrderedItems[$key]['total_rating'] = $totalRating;

                        //if($request->has('user_id') && !empty($request->user_id)){
                        
                        //changes by dilpreet
                        
                        // if($cart){
                        //     $cartItemsDetail = CartItemsDetail::where('cart_id', $cart['id'])->sum('quantity');
                        //     $previousOrderedItems[$key]['item_count_in_cart'] = $cartItemsDetail;
                        // }else{
                        //     $previousOrderedItems[$key]['item_count_in_cart'] = '0';
                        // }
                        // }else{
                        //     $itemlist[$key]['item_count_in_cart'] = '0';
                        // }
                    }

                }else{
                    $previousOrderedItems = array();
                }

                if(($request->login_type == 2 && !empty($device_id)) || $userId != '0'){
                    $user = User::where('id', $userId)->first();
                
                    if($request->has('cart_id') && !empty($request->cart_id)){
                        $cart = Cart::where('id', $request->cart_id)->where('status','1')->first();
                        if($cart['group_order'] == '1'){
                            $cart->user_name = $user->name;
                            $cart->user_french_name = $user->french_name;
                            $items['cart'] = $cart;
                        }
                    }else{
                        if($request->login_type == 2){
                            $cart = Cart::where('user_id', $device_id)->where('group_order', '<>', '1')->where('status','1')->first();
                            
                        }else{
                            $cart = Cart::where('user_id', $user->id)->where('group_order', '<>', '1')->where('status','1')->first();    
                        }
                        
                        $restaurantName = User::where('id', $cart['restaurant_id'])->first();
                        if($cart){
                            $cart['restaurant_name'] = $restaurantName['name'];
                        }
                        $items['normal_cart'] = $cart;
                    }                   
                }

                $items['previous_ordered_items'] = $previousOrderedItems;

                

                //$cuisinesitems = array();
                //return $restaurantCuisinesIds;
                foreach ($restaurantCuisinesIds as $k => $restaurantCuisinesId) {
                    //echo $restaurantCuisinesId;die;
                    //echo $price;die;
                    $cuisnitem = Item::where('cuisine_id', $restaurantCuisinesId)
                                        ->where('restaurant_id', $restaurantId)
                                        ->where('price', '<=', $priceR)
                                        ->where('approved', '1')
                                        ->get()
                                        ->toArray();
                    
                    //return $cuisnitem;
                    //echo'<pre>';print_r($cuisnitem);
                    foreach ($cuisnitem as $key => $list) {
                        $cuisnitem[$key]['cuisine_name'] = $cuisine[$list['cuisine_id']];
                        $oldprice = $list['price'];
                        $appPrice = $oldprice*$appFee/100;
                        $cuisnitem[$key]['price'] = round($oldprice+$appPrice, 2);
                        $oldOfferPrice = $list['offer_price'];
                        $appofferPrice = $oldOfferPrice*$appFee/100;
                        $cuisnitem[$key]['offer_price'] = round($oldOfferPrice+$appofferPrice, 2);

                        $cuisnitem[$key]['restaurant_name'] = $restaurantInfo['name'];
                        $itemCategories = ItemCategory::where('item_id', $list['id'])->get()->toArray();
                        if($itemCategories){
                            foreach ($itemCategories as $key1 => $itemCategorie) {
                                $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                                $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                            }
                            $cuisnitem[$key]['item_categories'] = $itemCategories;
                        }else{
                            $cuisnitem[$key]['item_categories'] = array();
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
                        $cuisnitem[$key]['avg_ratings'] = $avergeRating;
                        $cuisnitem[$key]['total_rating'] = $totalRating;
                        //return $cart;
                        //return $list;
                        if($cart){
                            if($request->login_type == 2){
                                $cartItemsDetailCount = CartItemsDetail::where('cart_id', $cart['id'])
                                                                    ->where('user_id', $device_id)
                                                                    ->where('item_id', $list['id'])
                                                                    ->sum('quantity');
                            }else{
                                $cartItemsDetailCount = CartItemsDetail::where('cart_id', $cart['id'])
                                                                    ->where('user_id', $user->id)
                                                                    ->where('item_id', $list['id'])
                                                                    ->sum('quantity');
                            }
                            
                            //return $cartItemsDetailCount;
                            $cartItemsDetail = CartItemsDetail::where('cart_id', $cart['id'])->where('item_id', $list['id'])->get()->toArray();
                            $cuisnitem[$key]['item_cart'] = $cartItemsDetail;
                            $cuisnitem[$key]['item_count_in_cart'] = $cartItemsDetailCount;

                        }else{
                            $cuisnitem[$key]['item_count_in_cart'] = '0';
                        }
                    }
                    
                    $items['all_data'][$k]['name'] = $cuisine[$restaurantCuisinesId];
                    $items['all_data'][$k]['items'] = $cuisnitem;
                }
                if($cart){
                    $items['total_cart_item'] = $cart->quantity;
                }else{
                    $items['total_cart_item'] = 0;
                }
                //echo'<pre>';print_r($items);die;
                    
                return response()->json([
                                            'status' => true,
                                            'message' => "Restaurants Items Found Successfully.",
                                            'data' => $items
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Restaurants Items Not Found.",
                                            'data' => []
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function categoryItemList($categoryId){
        try{
            $user = Auth::user();
            $itemlist = Item::where(['restaurant_id' => $user->id, 'category_id' => $categoryId])->where('approved', '1')->get()->toArray();

            if($itemlist){
                return response()->json([
                                            'status' => true,
                                            'message' => "Category Items Found Successfully.",
                                            'data' => $itemlist
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Category Items Not Found.",
                                            'data' => $itemlist
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function quizQuestion(){
        try{
            $user = Auth::user();
            $quizQuestion = QuizQuestion::where('status', '1')->first();
            if($quizQuestion){
                $check = RestaurantApprovedQuiz::where('restaurant_id', $user->id)->where('question_id', $quizQuestion['id'])->first();
                if($check){
                    $quizQuestion['join_quiz'] = '1';
                }else{
                    $quizQuestion['join_quiz'] = '0';
                }
                $quizQuestion['options'] = array($quizQuestion['option1'], $quizQuestion['option2'], $quizQuestion['option3'], $quizQuestion['option4']);
                return response()->json([
                                            'message' => 'Question Found.',
                                            'status' => true,
                                            'data' => $quizQuestion
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => 'Question Not Found.',
                                            'status' => false,
                                            'data' => $quizQuestion
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function joinQuiz(Request $request){
        try{
            $rules = [
                        'question_id' => 'required',
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

            $questionId = $request->question_id;
            $user = Auth::user();
            $check = RestaurantApprovedQuiz::where('question_id', $questionId)->where('restaurant_id', $user->id)->first();
            if($check){
                return response()->json([
                                            'message' => "Restaurant already join quiz.",
                                            'status' => false,
                                        ], 200);
            }else{
                $joinQuiz = new RestaurantApprovedQuiz;
                $joinQuiz->question_id = $questionId;
                $joinQuiz->restaurant_id = $user->id;
                if($joinQuiz->save()){
                    return response()->json([
                                                'message' => "Restaurant join quiz successfully.",
                                                'status' => true,
                                            ], 200);
                }else{
                    return response()->json([
                                                'message' => "Something Went Wrong!",
                                                'status' => false,
                                            ], 422);
                }
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function checkQuizAnswer(Request $request){
        try{
            $rules = [
                        'question_id' => 'required',
                        'answer' => 'required',
                        //'time_taken' => 'required'
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
            $questionId = $request->question_id;
            $answer = $request->answer;
            //$timeTaken = $request->time_taken;
            $quizQuestion = QuizQuestion::where('id', $questionId)->first();
            if($answer == $quizQuestion['answer']){
                //correct answer
                //echo $timeTaken;die;
                $shortListed = new ShortListedUser;
                $shortListed->user_id = $user->id;
                //$shortListed->time_taken = $timeTaken;
                if($shortListed->save()){
                    return response()->json([
                                                'message' => "You are shortlisted for quiz.",
                                                'status' => true,
                                            ], 200);
                }else{
                    return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
                }
            }else{
                //incorrect answer
                return response()->json([
                                            'message' => "Incorect Answer.",
                                            'status' => true,
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function assignPromoToRestaurant(Request $request){
        try{
            $rules = [
                        'promo_id' => 'required',
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
            $check = RestaurantPromo::where('restaurant_id', $user->id)->first();
            $promoId = $request->promo_id;
            if($promoId == '0'){
                if($check){
                    $update1 = RestaurantPromo::where('restaurant_id', $user->id)->delete();
                }

                $update = User::where('id', $user->id)->update(['promo_id' => ""]);
                
                $user = User::where('id', $user->id)->first();
                if($update){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Promocode Successfully Assigned to Restaurant.",
                                                'data' => $user
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Something went wrong.",
                                                //'data' => $user
                                            ], 404);
                }

            }else{
                if($check){
                    $update1 = RestaurantPromo::where('restaurant_id', $user->id)->update(['promo_id' => $promoId]);
                }else{
                    $restaurantPromo = new RestaurantPromo;
                    $restaurantPromo->restaurant_id = $user->id;
                    $restaurantPromo->promo_id = $promoId;
                    $restaurantPromo->save();
                }
                $update = User::where('id', $user->id)->update(['promo_id' => $promoId]);
                
                $user = User::where('id', $user->id)->first();
                if($update){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Promocode Successfully Assigned to Restaurant.",
                                                'data' => $user
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Something went wrong.",
                                                //'data' => $user
                                            ], 404);
                }
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function getRestaurantByCuisine(Request $request){
        try{
            $rules = [
                        'id' => 'required',
                        'latitude' => 'required',
                        'longitude' => 'required',
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
            $lat = $request->latitude;
            $long = $request->longitude;

            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;

            $restaurantIds = Item::where('cuisine_id', $request->id)->where('approved', '1')->pluck('restaurant_id', 'restaurant_id')->toArray();

            $query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`";
            $result = DB::select(DB::raw($query));
            $resIds = array();
            if($result){
                foreach ($result as $keyRes => $valueRes) {
                    $item = Item::where('restaurant_id', $valueRes->id)->where('approved', '1')->first();
                    if($item){
                        $resIds[] = $valueRes->id;
                    }
                }
            }

            $setting = Setting::where('id', '1')->first();
            $extraInfo = array();
            $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
            $extraInfo['min_order_vale'] = $setting['min_order'];
            $extraInfo['min_kilo_meter'] = $setting['min_km'];

            if($resIds){
                $restaurants = User::whereIn('id', $resIds)
                                        ->whereIn('id', $restaurantIds)
                                        ->where('role', '4')
                                        ->where('approved', '1')
                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                        ->orderBy('id', 'DESC')
                                        ->get()
                                        ->toArray();    
                if($restaurants){
                    foreach($restaurants as $k2 => $restaurant){
                        
                        $ratingReview = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->get()->toArray();
                        if($ratingReview){
                            $ratings = 0.0;
                            foreach ($ratingReview as $k => $ratreviw) {
                                $ratings = $ratings+$ratreviw['rating'];
                            }
                            $avergeRatings = round($ratings/count($ratingReview), 1);
                            
                        }else{
                            $avergeRatings = 0.0;
                        }
                        //echo'<pre>';print_r($customizedData[$k1]['restaurants'][$k2]);die;
                        $items = Item::where('restaurant_id', $restaurant['id'])
                                        ->where('approved', '1')
                                        ->select('id', 'image', 'approx_prep_time')
                                        ->limit(5)
                                        ->get()
                                        ->toArray();
                        //return $items;
                        //if($items){
                            $restaurants[$k2]['average_rating'] = $avergeRatings;
                            $restaurants[$k2]['total_rating'] = count($ratingReview);
                            $restaurants[$k2]['items'] = $items;
                            //$final = $restaurants;
                        // }else{
                        //     $restaurants = [];
                        // }
                    }
                    
                    if($restaurants){
                        return response()->json([
                                                    'message' => "Restaurants Found.",
                                                    'status' => true,
                                                    'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
                                                ], 200);    
                    }else{
                        return response()->json([
                                                    'message' => "Restaurants Not Found.",
                                                    'status' => false,
                                                    'data' => array('main_info' => [], 'extra_info' => $extraInfo)
                                                ], 200);   
                    }
                }else{
                    return response()->json([
                                                'message' => "Restaurants Not Found.",
                                                'status' => false,
                                                'data' => array('main_info' => [], 'extra_info' => $extraInfo)
                                            ], 200);        
                }
            }else{
                return response()->json([
                                            'message' => "Restaurants Not Found.",
                                            'status' => false,
                                            'data' => array('main_info' => [], 'extra_info' => $extraInfo)
                                        ], 200);    
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function changeRestaurantBookingStatus(Request $request){
        try{
            $rules = [
                        'status' => 'required',
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
            $status = $request->status;
            $restaurant = User::where('id', $user->id)->first();
            $restaurant->table_booking = $status;
            if($restaurant->save()){
                return response()->json([
                                            'message' => "Table Booking Status Updated",
                                            'status' => true,
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

    public function newBookings(){
        try{
            $user = Auth::user();
            $bookings = TableBooking::where('restaurant_id', $user->id)
                                    ->whereIn('booking_status', ['1', '2'])
                                    ->get()
                                    ->toArray();

            if($bookings){
                foreach ($bookings as $key => $booking) {
                    $customer = User::where('id', $booking['user_id'])->first();
                    $bookings[$key]['customer_name'] = $customer['name'];
                    $bookings[$key]['customer_french_name'] = $customer['french_name'];
                    $bookings[$key]['customer_phone'] = $customer['phone'];
                    $bookings[$key]['customer_image'] = $customer['image'];
                }
                return response()->json([
                                            'message' => "Available Bookings Found.",
                                            'status' => true,
                                            'table_booking' => $user->table_booking,
                                            'no_of_seats' => $user->no_of_seats,
                                            'data' => $bookings,
                                            
                                        ], 200);    
            }else{
                return response()->json([
                                            'message' => "Available Bookings Not Found.",
                                            'status' => true,
                                            'table_booking' => $user->table_booking,
                                            'no_of_seats' => $user->no_of_seats,
                                            'data' => $bookings,
                                        ], 200);    
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function newOrders(){
        try{
            $user = Auth::user();
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $orders = Order::where('restaurant_id', $user->id)
                            ->whereIn('order_status', ['0','1','2','3','4','7','9'])
                            ->orderBy('id', 'Desc')
                            ->get()
                            ->toArray();
            $itemNames = Item::where('status', '1')->where('approved', '1')->pluck('name', 'id')->toArray();
            //echo'<pre>';print_r($itemNames);
            foreach ($orders as $key2 => $order) {
                // sat_work
                    $requestedTime = $order['request_time'];
                    $preparingTime = $order['preparing_time'];
                    if($requestedTime != ""){
                        //echo "+$preparingTime minutes";die;

                        $endPreparingTime = date('Y-m-d H:i:s',strtotime("+$preparingTime minutes",strtotime($requestedTime)));
                        //return $endPreparingTime;
                        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
                        if($order["id"] == 49){
                           // echo $currentTime;
                            // echo"<br>";
                           // echo $endPreparingTime;die;   
                        }
                         
                        if($currentTime >= $endPreparingTime){
                            $timeRemaining = 0;
                        }else{
                            $timeRemaining = strtotime($endPreparingTime) - strtotime($currentTime);
                        }
                    }else{
                        $timeRemaining = 0;
                    }
                    $orders[$key2]["timeRemaining"] = $timeRemaining;
                // sat_work

                $userInfo = User::where('id', $order['user_id'])->first();
                //$orders[$key]['user_name'] = $userName[$order['user_id']];
                $orderdetails = OrderDetail::where('order_id', $order['id'])->get()->toArray();
                //echo'<pre>';print_r($orderdetails);die;
                foreach ($orderdetails as $key1 => $orderdetail) {
                    $items = Item::where('id', $orderdetail['item_id'])->first();
                    if($orderdetail['item_choices'] != ""){
                        $itemChoices = json_decode($orderdetail['item_choices']);
                        foreach ($itemChoices as $key => $itemChoice) {
                           // echo $itemChoice->id;
                            $finalResultToAppend = array();
                            $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                            //echo'<pre>';print_r($itemCategory);die;
                            $itemChoices[$key]->name = $itemCategory['name'];
                            $itemChoices[$key]->french_name = $itemCategory['french_name'];
                            $itemChoices[$key]->selection = $itemCategory['selection'];
                            $itemSubCats = explode(',', $itemChoice->item_sub_category);
                            //echo "<pre>";print_r($itemSubCats);
                            foreach ($itemSubCats as $key3 => $itemSubCat) {
                                $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();
                               
                                $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price'],
                                            "item_choice_name" => $itemCategory['name']
                                                    );  
                                
                            }
                            $itemChoices[$key]->item_sub_category = $finalResultToAppend;
                        }

                    }else{
                        $itemChoices = [];
                    }
                    //echo "<pre>";print_r($itemChoices);die;
                    $orderdetails[$key1]['item_choices'] = $itemChoices;
                    $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                    $orderdetails[$key1]['item_name'] = $items['name'];
                }
                $orders[$key2]['user_name'] = $userInfo['name'];
                $orders[$key2]['user_image'] = $userInfo['image'];
                $orders[$key2]['user_email'] = $userInfo['email'];
                $orders[$key2]['user_phone'] = $userInfo['phone'];
                $orders[$key2]['user_address'] = $userInfo['address'];
                $orders[$key2]['order_details'] = $orderdetails;
            }//die;
            //echo'<pre>';print_r($orders);die;
            if($orders){
                return response()->json([
                                            'status' => true,
                                            'message' => "Orders Found.",
                                            'data' => $orders
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "No Orders Found.",
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

    public function currentOrdersLocation(){
        try{
            $user = Auth::user();
            
            $orders = Order::where('restaurant_id', $user->id)
                            ->whereIn('order_status', ['0','2','3','4','7','9'])
                            ->select('id','user_id','delivery_address', 'delivery_note', 'end_lat', 'end_long', 'order_status','order_type','preparing_time','update_preparing_time', 'request_time', 'created_at')
                            ->orderBy('id', 'Desc')
                            ->get()
                            ->toArray();
            if($orders){
                foreach ($orders as $key => $order) {

                    $requestedTime = $order['request_time'];
                    if($requestedTime != ""){
                        $preparingTime = $order['preparing_time'];
                        //echo "+$preparingTime minutes";die;

                        $endPreparingTime = date('Y-m-d H:i:s',strtotime("+$preparingTime minutes",strtotime($requestedTime)));
                        //return $endPreparingTime;
                        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
                        
                         
                        if($currentTime >= $endPreparingTime){
                            $timeRemaining = 0;
                        }else{
                            $timeRemaining = strtotime($endPreparingTime) - strtotime($currentTime);
                        }
                    }else{
                        $timeRemaining = 0;
                    }
                    $orders[$key]["timeRemaining"] = $timeRemaining;

                    //return $order;
                    $orderDetails = OrderDetail::where('order_id', $order['id'])->get()->toArray();
                    foreach ($orderDetails as $key1 => $orderDetail) {
                        $item = Item::where('id', $orderDetail['item_id'])->first();
                        $orderDetails[$key1]['item_name'] = $item['name'];
                        $orderDetails[$key1]['item_french_name'] = $item['french_name'];
                        $itemChoices = json_decode($orderDetail['item_choices']);
                        if($itemChoices){
                            foreach ($itemChoices as $key3 => $itemChoice) {
                               // echo $itemChoice->id;
                                $finalResultToAppend = array();
                                $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                                //echo'<pre>';print_r($itemCategory);die;
                                $itemChoices[$key3]->name = $itemCategory['name'];
                                $itemChoices[$key3]->french_name = $itemCategory['french_name'];
                                $itemChoices[$key3]->selection = $itemCategory['selection'];
                                $itemSubCats = explode(',', $itemChoice->item_sub_category);
                                //echo "<pre>";print_r($itemSubCats);
                                foreach ($itemSubCats as $key4 => $itemSubCat) {
                                    $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();
                                   
                                    $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                                "name" => $itemSubCategory['name'],
                                                "french_name" => $itemSubCategory['french_name'], 
                                                "add_on_price" => $itemSubCategory['add_on_price'],
                                                "item_choice_name" => $itemCategory['name']
                                                        );  
                                }
                                $itemChoices[$key3]->item_sub_category = $finalResultToAppend;
                            }
                        }else{
                            $itemChoices = [];
                        }
                        $orderDetails[$key1]['item_choices'] = $itemChoices;
                    }
                    $customername = User::where('id', $order['user_id'])->first();
                    if($customername['image']){
                        $customernameimage = $customername['image'];
                    }else{
                        $customernameimage = '';
                    }
                    $orders[$key]['user_name'] = $customername['name'];
                    $orders[$key]['user_french_name'] = $customername['french_name'];
                    $orders[$key]['user_email'] = $customername['email'];
                    $orders[$key]['user_phone'] = $customername['phone'];
                    $orders[$key]['user_image'] = $customernameimage;
                    $orders[$key]['order_details'] = $orderDetails;
                }
                return response()->json([
                                            'message' => "Order Location Found.",
                                            'status' => true,
                                            'latitude' => $user->latitude,
                                            'longitude' => $user->longitude,
                                            'data' => $orders
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => "Order Location Not Found.",
                                            'status' => true,
                                            'latitude' => $user->latitude,
                                            'longitude' => $user->longitude,
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

    public function onGoingOrders(){
        try{
            $user = Auth::user();
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $orders = Order::where('restaurant_id', $user->id)->whereIn('order_status', ['2','3','4','7','9'])->orderBy('id', 'Desc')->get()->toArray();
            $itemNames = Item::where('status', '1')->pluck('name', 'id')->toArray();
            //echo'<pre>';print_r($itemNames);
            foreach ($orders as $key2 => $order) {
                $userInfo = User::where('id', $order['user_id'])->first();
                $driverInfo = User::where('id', $order['driver_id'])->first();
                //$orders[$key]['user_name'] = $userName[$order['user_id']];
                $orderdetails = OrderDetail::where('order_id', $order['id'])->get()->toArray();
                //echo'<pre>';print_r($orderdetails);die;
                foreach ($orderdetails as $key1 => $orderdetail) {
                    $items = Item::where('id', $orderdetail['item_id'])->first();
                    if($orderdetail['item_choices'] != ""){
                        $itemChoices = json_decode($orderdetail['item_choices']);
                        //echo'<pre>';print_r($itemChoices);die;
                        foreach ($itemChoices as $key => $itemChoice) {
                           // echo $itemChoice->id;
                            $finalResultToAppend = array();
                            $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                            //echo'<pre>';print_r($itemCategory);die;
                            $itemChoices[$key]->name = $itemCategory['name'];
                            $itemChoices[$key]->french_name = $itemCategory['french_name'];
                            $itemChoices[$key]->selection = $itemCategory['selection'];
                            $itemSubCats = explode(',', $itemChoice->item_sub_category);
                            //echo "<pre>";print_r($itemSubCats);
                            foreach ($itemSubCats as $key3 => $itemSubCat) {
                                $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();
                                //echo '<pre>';print_r($itemSubCategory);
                               // echo $itemChoices[$key]->item_sub_category;
                                //echo $itemSubCat;
                               
                                $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price'],
                                            "item_choice_name" => $itemCategory['name']
                                                    );  
                                //echo "<pre>";print_r($itemChoices[$key]->item_sub_category);
                                //die;

                                // $itemChoices[$key]['item_sub_category'][$key1]['name'] = $itemSubCategory['name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['french_name'] = $itemSubCategory['french_name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['add_on_price'] = $itemSubCategory['add_on_price'];
                            }
                            $itemChoices[$key]->item_sub_category = $finalResultToAppend;
                        }

                    }else{
                        $itemChoices = [];
                    }
                    //echo "<pre>";print_r($itemChoices);die;
                    $orderdetails[$key1]['item_choices'] = $itemChoices;
                    $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                    $orderdetails[$key1]['item_name'] = $items['name'];
                }
                $orders[$key2]['user_name'] = $userInfo['name'];
                $orders[$key2]['user_image'] = $userInfo['image'];
                $orders[$key2]['user_email'] = $userInfo['email'];
                $orders[$key2]['user_phone'] = $userInfo['phone'];
                $orders[$key2]['user_address'] = $userInfo['address'];
                $orders[$key2]['driver_name'] = $driverInfo['name'];
                $orders[$key2]['driver_image'] = $driverInfo['image'];
                $orders[$key2]['driver_email'] = $driverInfo['email'];
                $orders[$key2]['driver_phone'] = $driverInfo['phone'];
                $orders[$key2]['order_details'] = $orderdetails;
                //
            }//die;
            //echo'<pre>';print_r($orders);die;
            if($orders){
                return response()->json([
                                            'status' => true,
                                            'message' => "Orders Found.",
                                            'data' => $orders
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "No Orders Found.",
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

    public function pastOrders(){
        try{
            $user = Auth::user();
            $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $orders = Order::where('restaurant_id', $user->id)->whereIn('order_status', ['5','6','8'])->orderBy('id', 'Desc')->get()->toArray();
            $itemNames = Item::where('status', '1')->pluck('name', 'id')->toArray();
            //echo'<pre>';print_r($itemNames);
            foreach ($orders as $key2 => $order) {
                $userInfo = User::where('id', $order['user_id'])->first();
                //$orders[$key]['user_name'] = $userName[$order['user_id']];
                $orderdetails = OrderDetail::where('order_id', $order['id'])->get()->toArray();
                //echo'<pre>';print_r($orderdetails);die;
                foreach ($orderdetails as $key1 => $orderdetail) {
                    $items = Item::where('id', $orderdetail['item_id'])->first();
                    if($orderdetail['item_choices'] != ""){
                        $itemChoices = json_decode($orderdetail['item_choices']);
                        //echo'<pre>';print_r($itemChoices);die;
                        foreach ($itemChoices as $key => $itemChoice) {
                           // echo $itemChoice->id;
                            $finalResultToAppend = array();
                            $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                            //echo'<pre>';print_r($itemCategory);die;
                            $itemChoices[$key]->name = $itemCategory['name'];
                            $itemChoices[$key]->french_name = $itemCategory['french_name'];
                            $itemChoices[$key]->selection = $itemCategory['selection'];
                            $itemSubCats = explode(',', $itemChoice->item_sub_category);
                            //echo "<pre>";print_r($itemSubCats);
                            foreach ($itemSubCats as $key3 => $itemSubCat) {
                                $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();
                                //echo '<pre>';print_r($itemSubCategory);
                               // echo $itemChoices[$key]->item_sub_category;
                                //echo $itemSubCat;
                               
                                $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price'],
                                            "item_choice_name" => $itemCategory['name']
                                                    );  
                                //echo "<pre>";print_r($itemChoices[$key]->item_sub_category);
                                //die;

                                // $itemChoices[$key]['item_sub_category'][$key1]['name'] = $itemSubCategory['name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['french_name'] = $itemSubCategory['french_name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['add_on_price'] = $itemSubCategory['add_on_price'];
                            }
                            $itemChoices[$key]->item_sub_category = $finalResultToAppend;
                        }

                    }else{
                        $itemChoices = [];
                    }
                    //echo "<pre>";print_r($itemChoices);die;
                    $orderdetails[$key1]['item_choices'] = $itemChoices;
                    $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                    $orderdetails[$key1]['item_name'] = $items['name'];
                }
                $orders[$key2]['user_name'] = $userInfo['name'];
                $orders[$key2]['user_image'] = $userInfo['image'];
                $orders[$key2]['user_email'] = $userInfo['email'];
                $orders[$key2]['user_phone'] = $userInfo['phone'];
                $orders[$key2]['user_address'] = $userInfo['address'];
                $orders[$key2]['order_details'] = $orderdetails;
            }//die;
            //echo'<pre>';print_r($orders);die;
            if($orders){
                return response()->json([
                                            'status' => true,
                                            'message' => "Orders Found.",
                                            'data' => $orders
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "No Orders Found.",
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

    public function restaurantOffer(Request $request){
        try{
            $rules = [
                        'offer' => 'required',
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
            $offerPercentage = $request->offer;
            $restaurantOffer = User::where('id', $user->id)->update(['offer' => $offerPercentage]);
            if($restaurantOffer){
                $items = Item::where('restaurant_id', $user->id)->where('approved', '1')->get()->toArray();
                if($items){
                    foreach ($items as $key => $item) {
                        $itemOffer = $item['price']*$offerPercentage/100;
                        $itemOfferPrice = $item['price']-$itemOffer;
                        $updateitem = Item::where('id', $item['id'])->update(['offer_price' => $itemOfferPrice]);
                    }
                    return response()->json([
                                                'message' => "Offer Applied To All Items.",
                                                'status' => true,
                                                'offer' => $offerPercentage
                                            ], 200);        
                }else{
                    return response()->json([
                                                'message' => "Items Not Found To Apply Offer.",
                                                'status' => false,
                                            ], 200);        
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

    public function restaurantPromo($id){
        try{
            $promos = Promocode::where('status', '1')->get()->toArray();
            if($promos){
                foreach ($promos as $key => $promo) {
                    $checkIsValid = RestaurantPromo::where('restaurant_id', $id)->where('promo_id', $promo['id'])->first();
                    if($checkIsValid){
                        $promos[$key]['is_valid'] = true;
                    }else{
                        $promos[$key]['is_valid'] = false;
                    }
                }
                // $promo =  DB::table('restaurant_promos')
                //                                     ->join('promocodes', 'restaurant_promos.promo_id', '=', 'promocodes.id')
                //                                     ->where('restaurant_promos.restaurant_id', $id)->first();
                //if($promo){
                    return response()->json([
                                                'message' => "Restaurant Promocode Found.",
                                                'status' => true,
                                                'data' => $promos
                                            ], 200);        
                //}
            }else{
                return response()->json([
                                            'message' => "Restaurant doesn't have promocode.",
                                            'status' => false,
                                            'data' => $promo
                                        ], 200);        
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function popularRestaurants(Request $request){
        try{
            $rules = [
                        //'user_id' => 'required',
                        'lat' => 'required',
                        'long' => 'required',
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

            $userId = $request->user_id;
            $lat = $request->lat;
            $long = $request->long;

            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;

            //$restaurants = User::where('role', '4')->get()->toArray();
            $query = "SELECT id,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                        ORDER BY `distance`";
            $result = DB::select(DB::raw($query));
            $resIds = array();
            if($result){
                foreach ($result as $keyRes => $valueRes) {
                    //$resIds[] = $valueRes->id;
                    $item = Item::where('restaurant_id', $valueRes->id)->first();
                    if($item){
                        $resIds[] = $valueRes->id;
                    }
                }
            }
            //echo "<pre>";print_r($resIds);die;
            $restaurants = User::whereIn('id', $resIds)
                                                ->where('role', '4')
                                                ->where('approved', '1')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->get()
                                                ->toArray();    
            $cuisines = Cuisine::all()->pluck('name', 'id')->toArray();
            $user = Auth::user();
            //echo'<pre>';print_r($user);die;
            if($restaurants){
                foreach ($restaurants as $key => $restaurant) {
                    
                    $cuisineRes = RestaurantCuisine::where('restaurant_id', $restaurant['id'])->get()->toArray();
                    //if($userId){
                        $favourite = Favourite::where(['user_id' => $user->id, 'restaurant_id' => $restaurant['id']])->first();
                        if($favourite){
                            $restaurants[$key]['favourite'] = true;    
                        }else{
                            $restaurants[$key]['favourite'] = false;    
                        }
                        $ratingReview = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->get()->toArray();
                        $reviewCount = RatingReview::where('receiver_id', $restaurant['id'])->where('receiver_type', '2')->where('review', '!=', '')->count();
                        if($ratingReview){
                            $ratings = 0.0;
                            foreach ($ratingReview as $k => $ratreviw) {
                                $ratings = $ratings+$ratreviw['rating'];
                            }
                            $avergeRatings = round($ratings/count($ratingReview), 1);
                            
                        }else{
                            $avergeRatings = 0.0;
                        }
                        $restaurants[$key]['average_rating'] = $avergeRatings;
                        $restaurants[$key]['total_rating'] = count($ratingReview);
                        $restaurants[$key]['reviews'] = $reviewCount;
                    // }else{
                    //     $restaurants[$key]['average_rating'] = 0.0;    
                    //     $restaurants[$key]['total_rating'] = 0;
                    //     $restaurants[$key]['favourite'] = false;    
                    // }
                    $cuisins = array();
                    foreach ($cuisineRes as $key1 => $cuisine) {
                        $cuisins[] = $cuisines[$cuisine['cuisine_id']];
                    }
                    $restaurants[$key]['cuisines'] = implode(',', $cuisins);
                    $items = Item::where('restaurant_id', $restaurant['id'])
                                        ->where('approved', '1')
                                        ->select('id', 'image', 'approx_prep_time')
                                        ->limit(5)
                                        ->get()
                                        ->toArray();
                        //return $items;
                        //if($items){
                            $restaurants[$key]['items'] = $items;
                            //$final = $restaurants;
                        // }else{
                        //     $restaurants = [];
                        // }
                }
                //echo'<pre>';print_r($restaurants);die;
                $finalRestaurant = array();
                foreach ($restaurants as $k => $restaurant) {
                    //if restaurant does't have items than skip
                    $items = Item::where('restaurant_id', $restaurant['id'])->where('approved', '1')->get()->toArray();
                    //echo'<pre>';print_r($items);die;
                    if(!$items){
                        continue;
                    }
                     $finalRestaurant[] = $restaurant;
                }
                if($finalRestaurant){
                    $setting = Setting::where('id', '1')->first();
                    $extraInfo = array();
                    $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
                    $extraInfo['min_order_vale'] = $setting['min_order'];
                    $extraInfo['min_kilo_meter'] = $setting['min_km'];
                    return response()->json([
                                                'status' => true,
                                                'message' => "Restaurants Found.",
                                                'data' => array('main_info' => $finalRestaurant, 'extra_info' => $extraInfo)
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "No Restaurants Found.",
                                                'data' => $finalRestaurant
                                            ], 200);    
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "No Restaurants Found.",
                                            'data' => $restaurants
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function cancelReasons(){
        try{
            $cancelReasons = OrderCancelReason::all();   
            
            if($cancelReasons){
                return response()->json([
                                            'message' => "Reasons Found Successfully.",
                                            'status' => true,
                                            'data' => $cancelReasons
                                        ], 200);    
            }else{
                return response()->json([
                                            'message' => "Reasons Not Found.",
                                            'status' => false,
                                            'data' => $cancelReasons
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function nearbyDrivers(Request $request){
        try{
            $rules = [
                        'latitude' => 'required',
                        'longitude' => 'required',
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

            $lat = $request->latitude;
            $long = $request->longitude;
            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;

            $query = "SELECT `id`,`latitude`,`longitude`, ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( `latitude` ) ) * COS( RADIANS( $lat )) * COS( RADIANS( `longitude` ) - RADIANS( $long )) ) * 6371 AS `distance` FROM `users` WHERE ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( `latitude` ) ) * COS( RADIANS( $lat )) * COS( RADIANS( `longitude` ) - RADIANS( $long )) ) * 6371 < $distance And `role` IN(3) AND busy_status = '0'   ORDER BY `distance` ASC";
            $nearByDriver = DB::select(DB::raw($query));
            //echo'<pre>';print_r($nearByDriver);die;
            if($nearByDriver){
                foreach ($nearByDriver as $key => $driver) {
                    $ratingReview = RatingReview::where('receiver_id', $driver->id)->where('receiver_type', '3')->get()->toArray();
                    if($ratingReview){
                        $ratings = 0.0;
                        foreach ($ratingReview as $k => $ratreviw) {
                            $ratings = $ratings+$ratreviw['rating'];
                        }
                        $avergeRatings = round($ratings/count($ratingReview), 1);
                        
                    }else{
                        $avergeRatings = 0.0;
                    }
                    //echo'<pre>';print_r($customizedData[$k1]['restaurants'][$k2]);die;
                    $nearByDriver[$key]->average_rating = $avergeRatings;
                    $nearByDriver[$key]->total_rating = count($ratingReview);
                }
                return response()->json([
                                            'message' => "Drivers Found Successfully.",
                                            'status' => true,
                                            'data' => $nearByDriver
                                        ], 200);    
            }else{
                return response()->json([
                                            'message' => "Drivers Not Found.",
                                            'status' => false,
                                            'data' => $nearByDriver
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function acceptBooking(Request $request){
        try{
            $rules = [
                        'booking_id' => 'required',
                        'status' => 'required', // 1 : accepted ,2 : rejected
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

            $bookingId = $request->booking_id;
            $user = Auth::user();
            $booking = TableBooking::where('id', $bookingId)->first();
            $customer = User::where('id', $booking->user_id)->first();
            if($request->status == '2'){
                //send notification to customer.
                $message = "Restaurant Not Accepted Your Booking";
                $frenchMessage = $this->translation($message);
                if($customer->notification == '1'){
                    if($customer->language == '1'){
                        $msg = $message;    
                    }else{
                        $msg = $frenchMessage[0];    
                    }
                    
                    
                    //$deta = array('notification_type' => '6', 'type' => $cancelRsn['type']);
                    $userTokens = UserToken::where('user_id', $customer->id)->get()->toArray();
                    if($userTokens){
                        foreach ($userTokens as $tokenKey => $userToken) {
                            
                            if($userToken['device_type'] == '0'){
                                $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '22'));    
                            }
                            if($userToken['device_type'] == '1'){
                                $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '22'));    
                            }
                        }
                    }
                    
                }

                $saveNotification = new Notification;
                $saveNotification->user_id = $customer->id;
                $saveNotification->restaurant_id = $user->id;
                $saveNotification->notification = $message;
                $saveNotification->french_notification = $frenchMessage[0];
                $saveNotification->role = '2';
                $saveNotification->read = '0';
                $saveNotification->notification_type = '22';
                $saveNotification->image = $user->image;
                $saveNotification->save();

                $booking->booking_status = '3';
                if($booking->save()){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Booking rejected By Restaurant.",
                                                'data' => $booking
                                            ], 200); 
                }else{
                    return response()->json([
                                                'message' => "Something Went Wrong!",
                                                'status' => false,
                                            ], 422);
                }
            }
            $booking->booking_status = '2';
            if($booking->save()){
                $message = "Restaurant Accepted Your Booking";
                $frenchMessage = $this->translation($message);
                if($customer->notification == '1'){
                    if($customer->language == '1'){
                        $msg = $message;    
                    }else{
                        $msg = $frenchMessage[0];    
                    }
                    
                    
                    //$deta = array('notification_type' => '6', 'type' => $cancelRsn['type']);
                    $userTokens = UserToken::where('user_id', $customer->id)->get()->toArray();
                    if($userTokens){
                        foreach ($userTokens as $tokenKey => $userToken) {
                            
                            if($userToken['device_type'] == '0'){
                                $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '19'));    
                            }
                            if($userToken['device_type'] == '1'){
                                $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '19'));    
                            }
                        }
                    }
                }
                $saveNotification = new Notification;
                $saveNotification->user_id = $customer->id;
                $saveNotification->restaurant_id = $user->id;
                $saveNotification->notification = $message;
                $saveNotification->french_notification = $frenchMessage[0];
                $saveNotification->role = '2';
                $saveNotification->read = '0';
                $saveNotification->notification_type = '19';
                $saveNotification->image = $user->image;
                $saveNotification->save();

                return response()->json([
                                            'status' => true,
                                            'message' => "Booking Accepted By Restaurant.",
                                            'data' => $booking
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

    public function startPreparing(Request $request){
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

            $orderId = $request->order_id;
            $order = Order::where('id', $orderId)->first();
            if($order){
                $order->order_status = '9';
                $customer = User::where('id', $order['user_id'])->first();
                $restaurant = User::where('id', $order['restaurant_id'])->first();
                $driver = User::where('id', $order['driver_id'])->first();
                $order->request_time = Carbon::now();
                if($order->save()){
                    //notification to customer and restaurant.
                    $requestedTime = $order['request_time'];
                    if($requestedTime != ""){
                        $preparingTime = $order['preparing_time'];
                        //echo "+$preparingTime minutes";die;

                        $endPreparingTime = date('Y-m-d H:i:s',strtotime("+$preparingTime minutes",strtotime($requestedTime)));
                        //return $endPreparingTime;
                        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
                        if($order["id"] == 49){
                           // echo $currentTime;
                            // echo"<br>";
                           // echo $endPreparingTime;die;   
                        }
                         
                        if($currentTime >= $endPreparingTime){
                            $timeRemaining = 0;
                        }else{
                            $timeRemaining = strtotime($endPreparingTime) - strtotime($currentTime);
                        }
                    }else{
                        $timeRemaining = 0;
                    }
                    $order->timeRemaining = $timeRemaining;

                    $message = "Restaurant has started preparing the order.";
                    $frenchMessage = $this->translation($message);

                    if($customer->language == '1'){
                        $msg = $message;
                    }else{
                        $msg = $frenchMessage[0];
                    }

                    $deta = array(  
                                    "order_id" => $order->id,
                                    //"restaurant_id" => $order->restaurant_id,
                                    "restaurant_name" => $restaurant->name,
                                    "restaurant_lat" => $restaurant->latitude,
                                    "restaurant_long" => $restaurant->longitude,
                                    "restaurant_image" => $restaurant->image,
                                    "restaurant_address" => $restaurant->address,
                                    "restaurant_preparing_time" => $order->preparing_time,
                                    "notification_type" => '9'

                                );
                    if($customer->notification == '1'){
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
                    $saveNotification->user_id = $customer->id;
                    $saveNotification->order_id = $order->id;
                    $saveNotification->restaurant_id = $order->restaurant_id;
                    $saveNotification->notification = $message;
                    $saveNotification->french_notification = $frenchMessage[0];
                    $saveNotification->role = '2';
                    $saveNotification->read = '0';
                    $saveNotification->image = $restaurant->image;
                    $saveNotification->notification_type = '9';
                    $saveNotification->save();

                    if($driver['busy_status'] == '0'){
                        //$message = "New order from $restaurant->name";
                        $orderId = $order['id'];
                        $restaurantName = $restaurant->name;
                        $message = "The Order #$orderId isnow being prepared by $restaurantName.Stay nearby to pick it up immediately when it's ready.";
                        $frenchMessage = $this->translation($message);
                        if($driver['language'] == '1'){
                            $msg = $message;
                        }else{
                            $msg = $frenchMessage[0];
                        }
                        if($driver['device_type'] == '0'){
                            $sendNotification = $this->sendPushNotification($driver['device_token'],$msg,$deta);    
                        }
                        if($driver['device_type'] == '1'){
                            $sendNotification = $this->iosPushNotification($driver['device_token'],$msg,$deta);    
                        }
                    }

                    return response()->json([
                                                'message' => "Restaurant Start Preparing Order",
                                                'status' => true,
                                                'data' => $order
                                            ], 200);    
                }else{
                    return response()->json([
                                                'message' => "Something Went Wrong!",
                                                'status' => false,
                                            ], 422);
                }
            }else{
                return response()->json([
                                            'message' => "Order not found",
                                            'status' => false,
                                            'data' => $order
                                        ], 200);    
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function acceptOrder(Request $request){
        try{

            $rules = [
                        'order_id' => 'required',
                        'status' => 'required', // 1 : accepted ,2 : rejected
                        //'reason_id' => 'required',
                        //'preparing_time' => 'required',
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
            
            $orderId = $request->order_id;
            $status = $request->status;
            $reasonId = $request->reason_id;
            //echo $status;die;
            $cancelRsn = OrderCancelReason::where('id', $reasonId)->first();
            $order = Order::where('id', $orderId)->first();
            $customer = User::where('id', $order['user_id'])->first();
            $restaurant = User::where('id', $order['restaurant_id'])->first();
            if($status == '2'){
                $rules1 = [
                            'reason_id' => 'required',
                        ];

                $validator = Validator::make($request->all(), $rules1);

                if($validator->fails())
                {
                    return response()->json([
                                                'status' => false,
                                                "message" => $validator->errors()->first(),
                                               //'errors' => $validator->errors()->toArray(),
                                            ], 422);              
                }
                //notification to customer
                if($customer->language == '1'){
                    $message = $cancelRsn['message'];    
                }else{
                    $message = $cancelRsn['french_message'];    
                }
                if($customer->notification == '1'){
                    
                    
                    $deta = array('notification_type' => '6', 'type' => $cancelRsn['type'], 'order_id' => $order['id']);
                    $userTokens = UserToken::where('user_id', $order['user_id'])->get()->toArray();
                    if($userTokens){
                        foreach ($userTokens as $tokenKey => $userToken) {
                            
                            if($userToken['device_type'] == '0'){
                                $sendNotification = $this->sendPushNotification($userToken['device_token'],$message,$deta);    
                            }
                            if($userToken['device_type'] == '1'){
                                $sendNotification = $this->iosPushNotification($userToken['device_token'],$message,$deta);    
                            }
                        }
                    }
                }

                if($restaurant->image){
                    $restaurantimage = $restaurant->image;   
                }else{
                    $restaurantimage = '';
                }
                

                $saveNotification = new Notification;
                $saveNotification->user_id = $order['user_id'];
                $saveNotification->order_id = $order['id'];
                $saveNotification->restaurant_id = $order['restaurant_id'];
                $saveNotification->notification = $cancelRsn['message'];
                $saveNotification->french_notification = $cancelRsn['french_message'];
                $saveNotification->role = '2';
                $saveNotification->read = '0';
                $saveNotification->image = $restaurantimage;
                $saveNotification->notification_type = '6';
                $saveNotification->save();
                //order rejected by restaurant.
                //$reject = Order::where('id', $orderId)->update(['order_status' => '6']);
                $order->order_status = '6';
                $order->cancel_type = $cancelRsn['type'];
                if($order->save()){
                    $transaction = new Transaction();
                    $transaction->user_id = $order->user_id; 
                    $transaction->order_id = $order->id;
                    $transaction->amount = $order->final_price;
                    $transaction->reason = "refund back";
                    $transaction->type = "3";
                    $transaction->save();
                    return response()->json([
                                                'status' => true,
                                                'message' => "Order Rejected By Restaurant.",
                                                'data' => $order
                                            ], 200); 
                }

                $updateCart = Cart::where('id', $order['cart_id'])->update(['status' => '1']);

            }

            /*$delete = Cart::where('id', $order['cart_id'])->delete();
            $delete = CartItemsDetail::where('cart_id', $order['cart_id'])->delete();*/

            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;

            $query = "SELECT `id`,`busy_status`,`language`,`device_type`,`device_token`, ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $restaurant->latitude ) ) + COS( RADIANS( `latitude` ) ) * COS( RADIANS( $restaurant->latitude )) * COS( RADIANS( `longitude` ) - RADIANS( $restaurant->longitude )) ) * 6371 AS `distance` FROM `users` WHERE ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $restaurant->latitude ) ) + COS( RADIANS( `latitude` ) ) * COS( RADIANS( $restaurant->latitude )) * COS( RADIANS( `longitude` ) - RADIANS( $restaurant->longitude )) ) * 6371 < $distance And `role` IN(3) AND busy_status = '0'   ORDER BY `distance` ASC";
            $nearByDrivers = DB::select(DB::raw($query));
            //echo'<pre>';print_r($nearByDrivers);die;
            if($nearByDrivers){
                foreach ($nearByDrivers as $key => $nearByDriver) {
                    $deta = array(  
                                    "order_id" => $orderId,
                                    //"restaurant_id" => $order->restaurant_id,
                                    "restaurant_name" => $restaurant->name ,
                                    "restaurant_lat" => $restaurant->latitude,
                                    "restaurant_long" => $restaurant->longitude,
                                    "restaurant_image" => $restaurant->image,
                                    "restaurant_address" => $restaurant->address,
                                    "notification_type" => '302'

                                );
                    if($order['order_type'] == '1'){
                        if($nearByDriver->busy_status == '0'){
                            //$message = "New order from $restaurant->name";
                            $orderId = $order['id'];
                            $message = "You have a new Order #$orderId request from $restaurant->name Restaurant";
                            $frenchMessage = $this->translation($message);
                            if($nearByDriver->language == '1'){
                                $msg = $message;
                            }else{
                                $msg = $frenchMessage[0];
                            }
                            if($nearByDriver->device_type == '0'){
                                $sendNotification = $this->sendPushNotification($nearByDriver->device_token,$msg,$deta);    
                            }
                            if($nearByDriver->device_type == '1'){
                                $sendNotification = $this->iosPushNotification($nearByDriver->device_token,$msg,$deta);    
                            }
                        }
                    }
                }
            }
            
            if($order["is_schedule"] == '1'){
                $order->order_status = '2';//accepted by restaurant
                //$order->request_time = Carbon::now();
                if($request->has('preparing_time') && !empty($request->preparing_time)){
                    $preparing_time = $order->preparing_time = $request->preparing_time;

                    $selectedTime = date("Y-m-d h:i:s");
                    $endTime = strtotime("+$preparing_time minutes", strtotime($selectedTime));
                    $endDateTime =  date('Y-m-d h:i:s', $endTime);
                    $order->preparing_end_time = $endDateTime;

                }
                //$acceptOrder = Order::where('id', $orderId)->update(['order_status' => '2']);
                $order->save();

                return response()->json([
                                            'status' => true,
                                            'message' => "Order Accepted By Restaurant.",
                                            'data' => $order
                                        ], 200);
            }else{
               //send notification to driver
                $order->order_status = '2';//accepted by restaurant
                //$order->request_time = Carbon::now();
                if($request->has('preparing_time') && !empty($request->preparing_time)){
                    $preparing_time = $order->preparing_time = $request->preparing_time;

                    $selectedTime = date("Y-m-d h:i:s");
                    $endTime = strtotime("+$preparing_time minutes", strtotime($selectedTime));
                    $endDateTime =  date('Y-m-d h:i:s', $endTime);
                    $order->preparing_end_time = $endDateTime;

                }
                //$acceptOrder = Order::where('id', $orderId)->update(['order_status' => '2']);
                if($order->save()){

                    $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
                    
                    //notification to customer
                    $orderId = $order->id;
                    $restaurantName = $restaurant->name;
                    $message = "Order #$orderId is Accepted by $restaurantName";
                    $frenchMessage = $this->translation($message);

                    if($customer->language == '1'){
                        $msg = $message;
                    }else{
                        $msg = $frenchMessage[0];
                    }

                    $restaurantName = $userName[$order->restaurant_id];

                    $deta = array(  
                                    "order_id" => $orderId,
                                    //"restaurant_id" => $order->restaurant_id,
                                    "restaurant_name" => $restaurantName,
                                    "restaurant_lat" => $restaurant->latitude,
                                    "restaurant_long" => $restaurant->longitude,
                                    "restaurant_image" => $restaurant->image,
                                    "restaurant_address" => $restaurant->address,
                                    "restaurant_preparing_time" => $order->preparing_time,
                                    "notification_type" => '2'

                                );
                    if($customer->notification == '1'){
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
                    $saveNotification->user_id = $customer->id;
                    $saveNotification->order_id = $order->id;
                    $saveNotification->restaurant_id = $order->restaurant_id;
                    $saveNotification->notification = $message;
                    $saveNotification->french_notification = $frenchMessage[0];
                    $saveNotification->role = '2';
                    $saveNotification->read = '0';
                    $saveNotification->image = $restaurant->image;
                    $saveNotification->notification_type = '2';
                    $saveNotification->save();

                    $lat = $order->end_lat;
                    $long = $order->end_long;

                    $setting = Setting::where('id', '1')->first();
                    $distance = $setting->distance;

                    $sqlQry = "SELECT *,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                        FROM users
                        WHERE
                        ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                        * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance AND `role` = '2'
                        ORDER BY `distance`";
                    $nearByCustomers = DB::select(DB::raw($sqlQry));
                    if($order->order_type == '1'){
                        if($nearByCustomers){
                            //echo'<pre>';print_r($nearByCustomer);die;
                            foreach ($nearByCustomers as $key => $nearByCustomer) {
                                if($nearByCustomer->id == $order->user_id){
                                    continue;
                                }
                                if($nearByCustomer->notification == '1'){
                                    
                                    $customerName = $nearByCustomer->name;
                                    $message = "Driver is near your location. you can order any food item from your near by restaurant";
                                    $frenchMessage = $this->translation($message);
                                    if($nearByCustomer->language == '1'){
                                        $msg = $message;
                                    }else{
                                        $msg = $frenchMessage[0];
                                    }
                                    //$deta = $order;
                                    $deta = array(  
                                                    "order_id" => $order->id,
                                                    "restaurant_id" => $order->restaurant_id,
                                                    "restaurant_name" => $restaurant->name ,
                                                    "restaurant_lat" => $restaurant->latitude,
                                                    "restaurant_long" => $restaurant->longitude,
                                                    "restaurant_image" => $restaurant->image,
                                                    "restaurant_address" => $restaurant->address,
                                                    "notification_type" => '100'

                                                );
                                    //$deta = array('notification_type' => '0');
                                    $userTokens = UserToken::where('user_id', $nearByCustomer->id)->get()->toArray();
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
                                $saveNotification->timer = '1';
                                $saveNotification->image = $restaurant->image;
                                $saveNotification->notification_type = '100';
                                $saveNotification->save();
                            }
                            
                        }
                    }

                    // $message1 = "Your order is being prepared";

                    // $restaurantName = $userName[$order->restaurant_id];

                    // $deta = array(  
                    //                 "order_id" => $orderId,
                    //                 //"restaurant_id" => $order->restaurant_id,
                    //                 "restaurant_name" => $restaurantName ,
                    //                 "restaurant_lat" => $restaurant->latitude,
                    //                 "restaurant_long" => $restaurant->longitude,
                    //                 "restaurant_image" => $restaurant->image,
                    //                 "restaurant_address" => $restaurant->address,
                    //                 "notification_type" => '2'

                    //             );
                    // if($customer->notification == '1'){
                    //     $userTokens = UserToken::where('user_id', $order->user_id)->get()->toArray();
                    //     if($userTokens){
                    //         foreach ($userTokens as $tokenKey => $userToken) {
                    //             if($userToken['device_type'] == '0'){
                    //                 $sendNotification = $this->sendPushNotification($userToken['device_token'],$message1,$deta);    
                    //             }
                    //             if($userToken['device_type'] == '1'){
                    //                 $sendNotification = $this->iosPushNotification($userToken['device_token'],$message1,$deta);    
                    //             }
                    //         }
                    //     }
                    // }

                    
                    return response()->json([
                                                'status' => true,
                                                'message' => "Orders Accepted By Restaurant.",
                                                'data' => $order
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Something went wrong.",
                                                //'data' => $user
                                            ], 404);
                }  
            }
            
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function addParentCuisine(Request $request){
        try{
            $rules = [
                        'name' => 'required',
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
            $cusineName = $request->name;
            $checkCuisine = RestaurantParentCuisine::where('restaurant_id', $user->id)
                                                    ->where('name', 'like', '%' . $cusineName . '%')
                                                    ->first();

            if($checkCuisine){
                return response()->json([
                                            'message' => "Category name already exist.",
                                            'status' => false,
                                        ], 200);    
            }
            $englishWords = array($cusineName);
            $frenchWords = $this->translation($englishWords);
            $parentCuisine = new RestaurantParentCuisine;
            $parentCuisine->name = $cusineName;
            $parentCuisine->restaurant_id = $user->id;
            $parentCuisine->french_name = $frenchWords[0];
            if($request->has('start_time')  && !empty($request->start_time)){
                $parentCuisine->start_time = $request->start_time;
            }
            if($request->has('end_time')  && !empty($request->end_time)){
                $parentCuisine->end_time = $request->end_time;
            }
            $parentCuisine->approved = "1";
            if($parentCuisine->save()){
                return response()->json([
                                            'message' => "Category Successfully Added",
                                            'status' => true,
                                            'data' => $parentCuisine
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

    public function editParentCuisine(Request $request){
        try{
            $rules = [
                        'parent_cuisine_id' => 'required',
                        'name' => 'required'
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
            $cusineName = $request->name;
            $checkCuisine = RestaurantParentCuisine::where('restaurant_id', $user->id)
                                                    ->where('name', $cusineName)
                                                    ->first();

            if($checkCuisine){
                return response()->json([
                                            'message' => "Category name already exist.",
                                            'status' => false,
                                        ], 200);    
            }
            $parentCuisineId = $request->parent_cuisine_id;
            $englishWords = array($cusineName);
            $frenchWords = $this->translation($englishWords);
            $parentCuisine = RestaurantParentCuisine::where('id', $parentCuisineId)->first();
            if($parentCuisine){
                $parentCuisine->name = $cusineName;
                $parentCuisine->french_name = $frenchWords[0];
                if($request->has('start_time')  && !empty($request->start_time)){
                    $parentCuisine->start_time = $request->start_time;
                }
                if($request->has('end_time')  && !empty($request->end_time)){
                    $parentCuisine->end_time = $request->end_time;
                }
                $parentCuisine->approved = "1";
                if($parentCuisine->save()){
                    return response()->json([
                                                'message' => "Category Successfully Updated",
                                                'status' => true,
                                                'data' => $parentCuisine
                                            ], 200);
                }else{
                    return response()->json([
                                                'message' => "Something Went Wrong!",
                                                'status' => false,
                                            ], 422);
                }
            }else{
               return response()->json([
                                            'message' => "Category Not Found",
                                            'status' => false,
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function deleteParentCuisine(Request $request){
        try{
            $rules = [
                        'parent_cuisine_id' => 'required',
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

            $parentCuisineId = $request->parent_cuisine_id;
            $parentCuisine = RestaurantParentCuisine::where('id', $parentCuisineId)->first();
            if($parentCuisine){
                $delete = RestaurantParentCuisine::where('id', $parentCuisineId)->delete();
                if($delete){
                    return response()->json([
                                                'message' => "Category Successfully Deleted.",
                                                'status' => true,
                                            ], 200);
                }else{
                    return response()->json([
                                            'message' => "Something Went Wrong!",
                                            'status' => false,
                                        ], 422);
                }
            }else{
               return response()->json([
                                            'message' => "Category Not Found",
                                            'status' => false,
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function completePickupOrder($id){
        try{
            $order = Order::where('id', $id)->first();
            
            if($order){
                $order->order_status = '5';
                if($order->save()){
                    //send notification to user and add money in restaurant wallet.
                    $user = User::where('id', $order['user_id'])->first();
                    $restaurantuser = User::where('id', $order['restaurant_id'])->first();
                    if($restaurantuser){
                         if($restaurantuser->image){
                            $restaurantimage = $restaurantuser->image;
                         }else{
                            $restaurantimage = "";
                         }
                    }else{

                         $restaurantimage = "";
                    }

                    $orderId = $order->id;
                   
                    $message = "#$orderId Order is Completed";
                    $frenchMessage = $this->translation($message);
                    if($user->language == '1'){
                        $msg = $message;
                    }else{
                        $msg = $frenchMessage[0];
                    }

                    //if($user->notification == '1'){
                        $amount = $order['final_price'];
                        $deta['order_id'] = $order['id'];
                        $deta['notification_type'] = '309';
                        $userTokens = UserToken::where('user_id', $restaurantuser->id)->get()->toArray();
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
                    //}



                    // $saveNotification = new Notification;
                    // $saveNotification->user_id = $order->user_id;
                    // $saveNotification->notification = $message;
                    // $saveNotification->french_notification = $frenchMessage[0];
                    // $saveNotification->role = '2';
                    // $saveNotification->read = '0';
                    // $saveNotification->image = $restaurantimage;
                    // $saveNotification->notification_type = '11';
                    // $saveNotification->save();


                    return response()->json([
                                                'message' => "Order Completed.",
                                                'status' => true,
                                            ], 200);
                }else{
                    return response()->json([
                                                'message' => "Something Went Wrong!",
                                                'status' => false,
                                            ], 422);        
                }
            }else{
                return response()->json([
                                            'message' => "Order Not Found.",
                                            'status' => false,
                                        ], 200);    
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function restaurantReviews($id){
        try{
            $users = User::pluck('name', 'id')->toArray();
            $users_image = User::pluck('image', 'id')->toArray();
            $reviews = RatingReview::where(['receiver_id' => $id, 'receiver_type' => '2'])->get()->toArray();
            if($reviews){
                foreach ($reviews as $key => $review) {
                    $reviews[$key]['sender_name'] = $users[$review['sender_id']];
                    $reviews[$key]['sender_image'] = $users_image[$review['sender_id']];
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Reviews Found Successfully.",
                                            'data' => $reviews
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Reviews Not Found.",
                                            'data' => $reviews
                                        ], 404);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    // public function createRecipt(Request $request){
    //     try{
    //         $rules = [
    //                     'account_number' => 'required',
    //                     'bank_code' => 'required', 
    //                     "name" =>'required',
    //                     "amount" => 'required'
    //                 ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if($validator->fails())
    //         {
    //             return response()->json([
    //                                         'status' => false,
    //                                         "message" => $validator->errors()->first(),
    //                                        //'errors' => $validator->errors()->toArray(),
    //                                     ], 422);              
    //         }
    //         //$id = 3;
    //         $url = 'https://api.paystack.co/transferrecipient';
    //         $fields = array(
    //                             "type" => "nuban",
    //                             "name" => $request->name,
    //                             "description" => "Creating Account",
    //                             "account_number" => $request->account_number,
    //                             "bank_code" => $request->bank_code,
    //                             "currency" => "NGN",
    //                             "metadata" => array("orderId" => 1)
    //                         );

    //         $fields = json_encode($fields);
    //         $headers = array(
    //                             'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e',
    //                             "Content-Type: application/json"
    //                         );
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    //         $result = curl_exec($ch);
    //         //curl_close($ch);
    //         $data = json_decode($result);
    //         echo "<pre>";print_r($data->status);die;

    //         $recipitId = $data->data->recipient_code;
    //         $response = $this->payout($request->amount,$recipitId);
    //         if($response->status == 1){
    //             return response()->json([
    //                                         'message' => "Payout Done",
    //                                         'status' => true,
    //                                         'data' => $response->data
    //                                     ], 200);
    //         }else{
    //             return response()->json([
    //                                         'message' => "Wrong Account number or bank code!",
    //                                         'status' => false,
    //                                     ], 400);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json([
    //                                     'message' => "Something Went Wrong!",
    //                                     'status' => false,
    //                                 ], 422);
    //     }
            

    // }

    public function check(Request $request){
        $result = array();
        //The parameter after verify/ is the transaction reference to be verified
        $url = 'https://api.paystack.co/transfer/verify/ufutowxd8s';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
          $ch, CURLOPT_HTTPHEADER, [
                                    'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e'
                                    ]
                    );
        $request = curl_exec($ch);
        curl_close($ch);

        if ($request) {
            $result = json_decode($request, true);
           //  print_r($result);die;
            if($result){
              if($result['data']){
                //something came in
                if($result['data']['status'] == 'success'){
                  // the transaction was successful, you can deliver value
                  /* 
                  @ also remember that if this was a card transaction, you can store the 
                  @ card authorization to enable you charge the customer subsequently. 
                  @ The card authorization is in: 
                  @ $result['data']['authorization']['authorization_code'];
                  @ PS: Store the authorization with this email address used for this transaction. 
                  @ The authorization will only work with this particular email.
                  @ If the user changes his email on your system, it will be unusable
                  */
                  echo "Transaction was successful";
                }else{
                  // the transaction was not successful, do not deliver value'
                  // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                  echo "Transaction was not successful: Last gateway response was: ".$result['data']['gateway_response'];
                }
              }else{
                echo $result['message'];
              }

            }else{
              //print_r($result);
              die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
            }
          }else{
            //var_dump($request);
            die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
          }
    }

    public function walletIn(Request $request){
        try{
            $rules = [
                        'transaction_data' => 'required',
                        'amount' => 'required'
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
            $transaction = new Transaction;
            $transaction->user_id = $user->id;
            $transaction->transaction_data = $request->transaction_data;
            $transaction->amount = $request->amount;
            $transaction->type = '3';
            $transaction->save();
            $user = User::where('id', $request->user()->id)->first();
            $user->wallet = $user->wallet+$request->amount;
            if($user->save()){
                if($user->notification == '1'){
                    $amount = $request->amount;
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
                                $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array());    
                            }
                            if($userToken['device_type'] == '1'){
                                $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array());    
                            }
                        }
                    }
                }

                return response()->json([
                                            'status' => true,
                                            'message' => "Amount Successfully Added to your wallet.",
                                            'wallet' => $user->wallet
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Something Went Wrong!"
                                        ], 422);
            }


                
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function payout(Request $request){
        try{
            // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
            $rules = [
                        'receipt_id' => 'required',
                        'amount' => 'required'
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

            // $user = Auth::user();
            // echo'<pre>';print_r($user);die;
            $user = User::where('id', $request->user()->id)->first();
            if($user->wallet < $request->amount){
                return response()->json([
                                            'status' => false,
                                            "message" => "You don't have suffient balance in your wallet.",
                                           //'errors' => $validator->errors()->toArray(),
                                        ], 422); 
            }

            $ch = curl_init();

            $data = array(
                            "source" => "balance",
                            "reason" => "test",
                            "amount" => $request->amount,
                            "recipient" => $request->receipt_id
                        );
            $data1 = json_encode($data);

            curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transfer');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
            curl_setopt($ch, CURLOPT_POST, 1);

            $headers = array();
            $headers[] = 'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $response = json_decode($result);
            if($response->status == 1){
                //subtract amount from wallet
                //entry in transaction table
                $oldBal = $user->wallet;
                $user->wallet = $oldBal-$request->amount;
                $transaction = new Transaction;
                $transaction->user_id = $user->id;
                $transaction->transaction_data = json_encode($response);
                $transaction->reference = $request->receipt_id;
                $transaction->amount = $request->amount;
                $transaction->type = '1';
                $transaction->save();
                if($user->save()){
                    return response()->json([
                                                'message' => "Payout Done!",
                                                'status' => true,
                                                'data' => $response
                                            ], 200);    
                }else{
                    return response()->json([
                                                'message' => "Something went wrong.",
                                                'status' => false,
                                                //'data' => $response
                                            ], 422);        
                }
            }else{
                return response()->json([
                                            'message' => $response->message,
                                            'status' => false,
                                            'data' => $response
                                        ], 422);    
            }
            //echo $status = $response->status;die;
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function getRecipit(request $request){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transferrecipient');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $a = json_decode($result);

        echo "<pre>";print_r($a);die;
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


    public function sendPushNotification($token,$msg="",$deta) {
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

    public function iosPushNotification($token,$msg="",$deta) {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
                        'sound' => 'Default',
                        "type"=> "test",
                        "data"=>"test",
                        "base_url"=>url("/"),
                        "body" => $msg,
                        "title" => "Grigora",
                        "deta" => $deta,
                        //"type" => $type,
                        //"data"=>$data
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

    
}
