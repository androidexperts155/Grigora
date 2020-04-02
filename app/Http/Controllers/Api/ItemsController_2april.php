<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Validator;
use App\User;
use App\Category;
use App\Favourite;
use App\RatingReview;
use App\SubCategory;
use App\ItemCategory;
use App\ItemSubCategory;
use App\UserToken;
use App\Item;
use App\Setting;

class ItemsController extends Controller
{
    public function add(Request $request){
    	try{
            //echo"<pre>";print_r($request->item_categories);die;
            // $a = json_decode($request->item_categories);
            // echo"<pre>";print_r($a);die;
            //echo "<pre>";print_r($request->all());die;
    		$rules = [
		                'name' => 'required',
		                'image' => 'required',
		                'cuisine_id' => 'required',//name
                        //'parent_cuisine_id' => 'required',
		                'description' => 'required',
		                'price' => 'required',//base price
		                'offer_price' => 'required',//empty
                        'pure_veg' => 'required',//0=>not pure veg, 1=>pure veg, 2=>contains egg
                        //'in_offer' => 'required',//remove
                        'item_categories' => 'required',
                        'approx_prep_time' => 'required',
                        //item_sub_category(name[name,add-on-price])
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

            if($user->pure_veg == 1){
                if($request->pure_veg == '0' || $request->pure_veg == '2'){
                    return response()->json([
                                                'message' => "Your restaurant serves all vegetarian dishes.please change setting to add extra dishes.",
                                                'status' => false,
                                            ], 422);
                }
            }
            $checkItem = Item::where(['name' =>  $request->name, 'cuisine_id' => $request->cuisine_id, 'restaurant_id' => $user->id])->first();
            //return $checkItem;
            if($checkItem){
                return response()->json([
                                            'message' => "Item name already exists.",
                                            'status' => false,
                                        ], 422);
            }

            if( $request->file('image')!= ""){
                if (!file_exists( public_path('/images/items'))) {
                    mkdir(public_path('/images/items'), 0777, true);
                }
                $path =public_path('/images/items/');
                $image = $request->file('image');
                $itemImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/items');
                $image->move($destinationPath, $itemImage);
                $url = url('/images/items/');
                $url = str_replace('/index.php', '', $url);
                $itemImage = $url.'/'.$itemImage;
            }else{
                $itemImage = "";  
            }

            //$user = Auth::user();

            $englishWords = array($request->get('name'), $request->get('description'));
            $frenchWords = $this->translation($englishWords);
            //echo'<pre>';print_r($frenchWords);die;
            $item = new Item;
            $item->name = $request->name;
            $item->french_name = $frenchWords[0];
            $item->restaurant_id = $user->id;
            $item->cuisine_id = $request->cuisine_id;
            if($request->has('parent_cuisine_id') && !empty($request->parent_cuisine_id)){
                $item->parent_cuisine_id = $request->parent_cuisine_id;
            }
            $item->description = $request->description;
            $item->french_description = $frenchWords[1];
            $item->price = $request->price;
            $item->image = $itemImage;
            $item->offer_price = $request->offer_price;
            $item->approx_prep_time = $request->approx_prep_time;
            //$item->in_offer = $request->in_offer;
            $item->pure_veg = $request->pure_veg;
            $item->status = '1';
            
            if($item->save()){
                $itemCategories = json_decode($request->item_categories);
                foreach ($itemCategories as $key => $itemCategorie) {
                    $englishWords = array($itemCategorie->name);
                    $frenchWords = $this->translation($englishWords);
                    $itemCategory = new ItemCategory;
                    $itemCategory->item_id = $item->id;
                    $itemCategory->name = $itemCategorie->name;
                    $itemCategory->french_name = $frenchWords[0];
                    $itemCategory->selection = $itemCategorie->selection;
                    $itemCategory->save();
                    foreach ($itemCategorie->item_sub_category as $key1 => $itemCategori) {
                        $englishWords = array($itemCategori->name);
                        $frenchWords = $this->translation($englishWords);
                        $itemSubCat = new ItemSubCategory;
                        $itemSubCat->item_cat_id = $itemCategory->id;
                        $itemSubCat->name = $itemCategori->name;
                        $itemSubCat->french_name = $frenchWords[0];
                        $itemSubCat->add_on_price = $itemCategori->add_on_price;
                        $itemSubCat->save();
                    }
                }
                
            	return response()->json([
						                    'status' => true,
						                    'message' => "Item Added Successfully.Wait For Admin Approval.",
						                    'data' => $item
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

    public function edit(Request $request){
        try{
            $rules = [
                        'item_id' => 'required',
                        'name' => 'required',
                        //'image' => 'required',
                        'cuisine_id' => 'required',
                        'description' => 'required',
                        'price' => 'required',
                        'offer_price' => 'required',
                        'pure_veg' => 'required',
                        //'in_offer' => 'required',
                        'item_categories' => 'required',
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

            $categoryId = $request->cuisine_id;

            if( $request->file('image')!= ""){
                if (!file_exists( public_path('/images/items'))) {
                    mkdir(public_path('/images/items'), 0777, true);
                }
                $path =public_path('/images/items/');
                $image = $request->file('image');
                $itemImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/items');
                $image->move($destinationPath, $itemImage);
                $url = url('/images/items/');
                $url = str_replace('/index.php', '', $url);
                $itemImage = $url.'/'.$itemImage;
            }else{
                $item = Item::where('id', $request->get('item_id'))->first();
                $itemImage = $item->image;  
            }

            

            $englishWords = array($request->get('name'), $request->get('description'));
            $frenchWords = $this->translation($englishWords);
            //echo'<pre>';print_r($frenchWords);die;

            $item = Item::where('id', $request->get('item_id'))->first();
            $item->name = $request->name;
            $item->french_name = $frenchWords[0];
            $item->restaurant_id = $user->id;
            $item->cuisine_id = $categoryId;
            if($request->has('parent_cuisine_id') && !empty($request->parent_cuisine_id)){
                $item->parent_cuisine_id = $request->parent_cuisine_id;
            }
            $item->description = $request->description;
            if($request->name == $request->description){
               $item->french_description = $frenchWords[0];
            }else{
                $item->french_description = $frenchWords[1];
            }
            
            $item->price = $request->price;
            $item->image = $itemImage;
            $item->offer_price = $request->offer_price;
            //$item->in_offer = $request->in_offer;
            $item->pure_veg = $request->pure_veg;
            //$item->status = '1';
            
            if($item->save()){
                $itemCats = ItemCategory::where('item_id', $item->id)->get()->toArray();
                if($itemCats){
                    foreach ($itemCats as $key => $itemCat) {
                        $itemSubCategory = ItemSubCategory::where('item_cat_id', $itemCat['id'])->delete();
                    }
                    $itemCatsDel = ItemCategory::where('item_id', $item->id)->delete();
                }
                $itemCategories = json_decode($request->item_categories);
                if($itemCategories){
                    foreach ($itemCategories as $key => $itemCategorie) {
                        $englishWords = array($itemCategorie->name);
                        $frenchWords = $this->translation($englishWords);
                        $itemCategory = new ItemCategory;
                        $itemCategory->item_id = $item->id;
                        $itemCategory->name = $itemCategorie->name;
                        $itemCategory->french_name = $frenchWords[0];
                        $itemCategory->selection = $itemCategorie->selection;
                        $itemCategory->save();
                        foreach ($itemCategorie->item_sub_category as $key1 => $itemCategori) {
                            $englishWords = array($itemCategori->name);
                            $itemSubCat = new ItemSubCategory;
                            $itemSubCat->item_cat_id = $itemCategory->id;
                            $itemSubCat->name = $itemCategori->name;
                            $itemSubCat->french_name = $frenchWords[0];
                            $itemSubCat->add_on_price = $itemCategori->add_on_price;
                            $itemSubCat->save();
                        }
                    }
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Item Updated Successfully.",
                                            'data' => $item
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

    public function delete($id){
        try{
            
            $itemCats = ItemCategory::where('item_id', $id)->get()->toArray();
            foreach ($itemCats as $key => $itemCat) {
                $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCat['id'])->delete();
            }

            $deleteItemCats = ItemCategory::where('item_id', $id)->delete();
            $deleteItem = Item::where('id', $id)->delete();    
            if($deleteItem){
                return response()->json([
                                            'status' => true,
                                            'message' => "Item Deleted Successfully.",
                                            //'data' => $item
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

    public function changeItemStatus(Request $request){
        try{
            $rules = [
                        'item_id' => 'required',
                        'status' => 'required',//1:available, 0:not available
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

            $itemId = $request->item_id;
            $status = $request->status;

            $user = Auth::user();

            $checkItem = Item::where('id', $itemId)->first();
            if($checkItem){
                if($status == '1'){

                    $message = "Item is Available now";
                    $setting = Setting::where('id', '1')->first();
                    $distance = $setting->distance;
                    $lat = $user->latitude;
                    $long = $user->longitude;

                    $query = "SELECT id,device_token,device_type,notification,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                            * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                            FROM users
                            WHERE
                            ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                            * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance
                            AND `role` = '2' ORDER BY `distance`";
                    $nearByCustomers = DB::select(DB::raw($query));

                    if($nearByCustomers){
                        foreach ($nearByCustomers as $key => $nearByCustomer) {
                            if($nearByCustomer->notification == '1'){
                                $itemName = $checkItem['name'];
                                $message = "$itemName is now available.";
                                $userTokens = UserToken::where('user_id', $nearByCustomer->id)->get()->toArray();
                                if($userTokens){
                                    foreach ($userTokens as $tokenKey => $userToken) {
                                        if($userToken['device_type'] == '0'){
                                            $sendNotification = $this->sendPushNotification($userToken['device_token'],$message,$deta = array());    
                                        }
                                        if($userToken['device_type'] == '1'){
                                            $sendNotification = $this->iosPushNotification($userToken['device_token'],$message,$deta = array());    
                                        }
                                    }
                                }
                            }
                        }
                    }
                }else{
                    $message = "Item not Available";
                }
                $checkItem->status = $status;
                if($checkItem->save()){
                    return response()->json([
                                                'message' => $message,
                                                'status' => true,
                                            ], 200);    
                }else{
                    return response()->json([
                                                'message' => "Something went wrong.",
                                                'status' => false,
                                            ], 422);    
                }

            }else{
                return response()->json([
                                            'message' => "Item not Found.",
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

    public function getItemByCategory(Request $request){
        try{
            $rules = [
                        'id' => 'required',
                        'lat' => 'required',
                        'long' => 'required',
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

            $catId = $request->id;
            $lat = $request->lat;
            $log = $request->long;

            $categories = Category::where('status', '1')->pluck('name', 'id')->toArray();
            $query = "SELECT
                       `id`,
                       ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( `latitude` ) )
                       * COS( RADIANS( $lat )) * COS( RADIANS( `longitude` ) - RADIANS( $log )) ) * 6371 AS `distance`
                       FROM `users`
                       WHERE
                       ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( `latitude` ) )
                       * COS( RADIANS( $lat )) * COS( RADIANS( `longitude` ) - RADIANS( $log )) ) * 6371 < 50 And `role` = 4
                       ORDER BY `distance`";
            $restaurants = DB::select(DB::raw($query));
            //echo "<pre>";print_r($restaurants);die;
            $restaurantRes = array();
            if($restaurants){
                foreach ($restaurants as $resKey => $resValue) {
                    $restaurantRes[$resValue->id]  = $resValue->id;
                }
            }

            $items = Item::where('category_id', $catId)->whereIn("restaurant_id",$restaurantRes)->get()->toArray();
            //echo "<pre>";print_r($items);die;
            $user = Auth::user();
            if($items){
                foreach ($items as $key => $item) {
                    $restaurant = User::where('id', $item['restaurant_id'])->first();
                    $items[$key]['restaurant_offer'] = $restaurant['offer'];
                    $items[$key]['restaurant_name'] = $restaurant['name'];
                    $items[$key]['restaurant_image'] = $restaurant['image'];
                    $items[$key]['restaurant_address'] = $restaurant['address'];
                    $itemCategories = ItemCategory::where('item_id', $item['id'])->get()->toArray();
                    $items[$key]['category_name'] = $categories[$item['category_id']];
                    if($itemCategories){
                        foreach ($itemCategories as $key1 => $itemCategorie) {
                            $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                            $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                        }
                        $items[$key]['item_categories'] = $itemCategories;
                    }else{
                        $items[$key]['item_categories'] = array();
                    }
                    if($request->has('user_id') && !empty($request->user_id)){
                        $favourite = Favourite::where(['user_id' => $request->user_id, 'restaurant_id' => $item['restaurant_id']])->first();
                        if($favourite){
                            $items[$key]['favourite'] = true;    
                        }else{
                            $items[$key]['favourite'] = false;    
                        }
                        $ratingReview = RatingReview::where(['receiver_id' => $item['id'], 'receiver_type' => '1'])->get()->toArray();
                        if($ratingReview){
                            $ratings = 0.0;
                            foreach ($ratingReview as $k => $ratreviw) {
                                $ratings = $ratings+$ratreviw['rating'];
                            }
                            $avergeRatings = round($ratings/count($ratingReview), 1);
                            
                        }else{
                            $avergeRatings = 0.0;
                        }
                        $items[$key]['avg_ratings'] = $avergeRatings;
                    }else{
                        $avergeRatings = 0.0;
                        $items[$key]['favourite'] = false;   
                        $items[$key]['avg_ratings'] = $avergeRatings;
                    }

                }
                
                return response()->json([
                                            'status' => true,
                                            'message' => "Items Found Successfully.",
                                            'data' => $items
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Item Not Found.",
                                            'data' => $items
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function getItemDetail($itemId){
        try{
            $items = Item::where('id', $itemId)->get()->toArray();

            if($items){
                foreach ($items as $key => $item) {
                    $itemCategories = ItemCategory::where('item_id', $item['id'])->get()->toArray();
                    if($itemCategories){
                        foreach ($itemCategories as $key1 => $itemCategorie) {
                            $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                            $itemCategories[$key1]['item_sub_category'] = $itemSubCat;
                        }
                        $items['item_categories'] = $itemCategories;
                    }else{
                        $items['item_categories'] = array();
                    }
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Items Found Successfully.",
                                            'data' => $items
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Item Not Found.",
                                            'data' => $items
                                        ], 200);
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
}
