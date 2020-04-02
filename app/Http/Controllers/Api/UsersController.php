<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Mail;
use Validator;
use App\User;
use App\Cuisine;
use App\RestaurantCuisine;
use App\Favourite;
use App\RatingReview;
use App\Order;
use App\OrderDetail;
use App\Item;
use App\ItemCategory;
use App\ItemSubCategory;
use App\ContactUs;
use App\Notification;
use App\Setting;
use App\AccountDetail;
use App\UserToken;
use App\UsersLocation;
use App\Filter;
use App\FilterList;
use App\Promocode;
use App\Brand;
use App\Cart;
use App\Issue;
use App\SubIssue;
use App\Faq;
use App\TableBooking;
use App\GiftCard;
use App\VoucherCard;
use App\FaqCategory;
use App\VoucherCardCode;
use App\RestaurantPromo;
use App\UserOrderType;
//use App\RatingReview;
use App\Transaction;
use App\CompanyOffline;
use Auth;
use Hash;
use DB;
use App\Mail\ForgotPassword;
use App\Mail\AccountVerification;
use Carbon\Carbon;
use App\AboutUs;

class UsersController extends Controller
{
    public function phoneLogin(Request $request){
        try{
            $rules = [
                        'phone' => 'required',
                        'role' => 'required',
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

            //Auth::attempt(['phone' => $request->get('phone') , 'role' => $request->get('role')]);
            $user = User::where('phone', $request->phone)->where('role', $request->role)->first();
            //if(Auth::check()){
            if($user){
                //$user = Auth::user();  
                if($user->role == '3' || $user->role == '4'){
                    if($user->approved == '0'){
                        return response()->json([
                                                    'status' => true,
                                                    'message' => 'Your Document verification is pending',
                                                ], 400);    
                    }
                }

                if($user->role == '2'){
                    if($user->approved == '0'){
                        return response()->json([
                                                    'status' => true,
                                                    'message' => 'Your Account is not verified yet.please verify your account first',
                                                ], 400);    
                    }
                }

                if($user->role == '3'){
                    DB::table('oauth_access_tokens')
                                            ->where('user_id', Auth::user()->id)
                                            ->update([
                                                        'revoked' => true
                                                    ]);
                }

                $token = $user->createToken($user->id. ' token ')->accessToken;

                $ratingReview = RatingReview::where(['receiver_id' => $user->id, 'receiver_type' => '2'])->get()->toArray();
                if($ratingReview){
                    $ratings = 0.0;
                    foreach ($ratingReview as $k => $ratreviw) {
                        $ratings = $ratings+$ratreviw['rating'];
                    }
                    $avergeRatings = round($ratings/count($ratingReview), 1);
                    
                }else{
                    $avergeRatings = 0.0;
                }
                $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                if($checkUserLocation){
                    $user->have_address = true;
                }else{
                    $user->have_address = false;
                }
                $user->avg_ratings = $avergeRatings;
                return response()->json([
                                            'status' => true,
                                            'message' => 'Successfully Logged In!',
                                            'data' => $user,
                                            'access_token' => $token,
                                            'token_type' => 'Bearer',
                                        ], 200);
            }else{
                return response()->json([
                                           "status" => false,
                                           "message" => 'Account does not exists.',
                                       ], 422);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function login(Request $request){
    	try{
    		$rules = [
                        'username' => 'required',
                        'password' => 'required',
                        'role' => 'required',
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

    //         if($request->get('role') == '3' || $request->get('role') == '4'){
				// $extraRules = [
	   //              'phone' => 'required',
	   //          ];
	   //          $validator = Validator::make($request->all(), $extraRules);

	   //          if($validator->fails())
	   //          {
	   //              return response()->json([
	   //              	'status' => false,
	   //                 "message" => $validator->errors()->first(),
	   //                 'errors' => $validator->errors()->toArray(),
	   //             ], 422);               
	   //          }

	   //          if(Auth::attempt(['phone' => $request->get('phone') , 'password' => $request->get('password'), 'role' => $request->get('role')])){
    //         		$user = Auth::user();           
    //             	$token = $user->createToken($user->id. ' token ')->accessToken;
    //             	return response()->json([
				// 		                'status' => true,
				// 		                'message' => 'Successfully Logged In!',
				// 		                'data' => $user,
				// 		                'access_token' => $token,
				// 		                'token_type' => 'Bearer',
				// 		            ], 200);
	   //      	}else{
	   //      		return response()->json([
				// 		                   "status" => false,
				// 		                   "message" => 'Please enter correct username and password.',
				// 		               ], 422);
	   //      	}
    //         } 
    //         if($request->get('role') == '2'){
				// $extraRules = [
	   //              'email' => 'required',
	   //          ];
	   //          $validator = Validator::make($request->all(), $extraRules);

	   //          if($validator->fails())
	   //          {
	   //              return response()->json([
	   //              	'status' => false,
	   //                 "message" => $validator->errors()->first(),
	   //                 'errors' => $validator->errors()->toArray(),
	   //             ], 422);               
	   //          }
                if(filter_var($request->get('username'), FILTER_VALIDATE_EMAIL)) {
                    //user sent their email 
                    Auth::attempt(['email' => $request->get('username') , 'password' => $request->get('password'), 'role' => $request->get('role')]);
                } else {
                    //they sent their phone instead 
                    Auth::attempt(['phone' => $request->get('username') , 'password' => $request->get('password'), 'role' => $request->get('role')]);
                }

	            if(Auth::check()){
            		$user = Auth::user();  
                                    
                    if($user->role == '3' || $user->role == '4'){
                        if($user->approved == '0'){
                            return response()->json([
                                                        'status' => true,
                                                        'message' => 'You Need Approval From Admin',
                                                    ], 400);    
                        }
                    }

                    if($user->role == '2'){
                        if($user->approved == '0'){
                            return response()->json([
                                                        'status' => true,
                                                        'message' => 'Your Account is not verified yet.please verify your account first',
                                                    ], 400);    
                        }
                    }

                    if($user->role == '3'){
                        DB::table('oauth_access_tokens')
                                                ->where('user_id', Auth::user()->id)
                                                ->update([
                                                            'revoked' => true
                                                        ]);
                    }

                	$token = $user->createToken($user->id. ' token ')->accessToken;
                    
                    if($user->role == '3'){
                        $receiverType = "3";
                    }elseif($user->role == '4') {
                        $receiverType = "2";
                    }else{
                        $receiverType = "";
                    }

                    $ratingReview = RatingReview::where(['receiver_id' => $user->id, 'receiver_type' => $receiverType])->get()->toArray();
                    if($ratingReview){
                        $ratings = 0.0;
                        foreach ($ratingReview as $k => $ratreviw) {
                            $ratings = $ratings+$ratreviw['rating'];
                        }
                        $avergeRatings = round($ratings/count($ratingReview), 1);
                        
                    }else{
                        $avergeRatings = 0.0;
                    }
                    $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                    if($checkUserLocation){
                        $user->have_address = true;
                    }else{
                        $user->have_address = false;
                    }
                    $user->avg_ratings = $avergeRatings;
                    
                    if($user->role == '4') {
                        //die('hi');
                        $userUpdate = User::where('id', $user->id)->update(['busy_status' => '0']);
                        //$user = User::where('id', $user->id)->first();
                        $user->busy_status = '0';
                        //$user->save();
                    }
                	return response()->json([
        						                'status' => true,
        						                'message' => 'Successfully Logged In!',
        						                'data' => $user,
        						                'access_token' => $token,
        						                'token_type' => 'Bearer',
        						            ], 200);
	        	}else{
	        		return response()->json([
    						                   "status" => false,
    						                   "message" => 'Please enter correct username and password.',
    						                ], 422);
	        	}
           // }   

            


        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function socialLogin(Request $request){
        try{
            $rules = [
                        'name' => 'required',
                        //'email' => 'required|email|unique:users',
                        //'phone' => 'required|unique:users',
                        'social_id' => 'required',
                        'login_type' => 'required',//1:instagram,2:twiter,3:facebook,4:google
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

            if($request->login_type == '1'){
                $user = User::where('instagram_id', $request->social_id)->first();
                if($user){
                    $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                    if($checkUserLocation){
                        $user->have_address = true;
                    }else{
                        $user->have_address = false;
                    }
                    $tokenResult = $user->createToken('Personal Access Token');
                    $token = $tokenResult->token;
                    $token->save();
                    return response()->json([
                                                'status' => true,
                                                'message' => 'Successfully created user!',
                                                'data' => $user,
                                                'access_token' => $tokenResult->accessToken,
                                                'token_type' => 'Bearer',
                                            ], 200);
                }
            }elseif($request->login_type == '2'){
                $user = User::where('twiter_id', $request->social_id)->first();
                if($user){
                    $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                    if($checkUserLocation){
                        $user->have_address = true;
                    }else{
                        $user->have_address = false;
                    }
                    $tokenResult = $user->createToken('Personal Access Token');
                    $token = $tokenResult->token;
                    $token->save();
                    return response()->json([
                                                'status' => true,
                                                'message' => 'Successfully created user!',
                                                'data' => $user,
                                                'access_token' => $tokenResult->accessToken,
                                                'token_type' => 'Bearer',
                                            ], 200);
                }
            }elseif($request->login_type == '3'){
                $user = User::where('facebook_id', $request->social_id)->first();
                if($user){
                    $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                    if($checkUserLocation){
                        $user->have_address = true;
                    }else{
                        $user->have_address = false;
                    }
                    $tokenResult = $user->createToken('Personal Access Token');
                    $token = $tokenResult->token;
                    $token->save();
                    return response()->json([
                                                'status' => true,
                                                'message' => 'Successfully created user!',
                                                'data' => $user,
                                                'access_token' => $tokenResult->accessToken,
                                                'token_type' => 'Bearer',
                                            ], 200);
                }
            }elseif($request->login_type == '4'){
                $user = User::where('google_id', $request->social_id)->first();
                if($user){
                    $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                    if($checkUserLocation){
                        $user->have_address = true;
                    }else{
                        $user->have_address = false;
                    }
                    $tokenResult = $user->createToken('Personal Access Token');
                    $token = $tokenResult->token;
                    $token->save();
                    return response()->json([
                                                'status' => true,
                                                'message' => 'Successfully created user!',
                                                'data' => $user,
                                                'access_token' => $tokenResult->accessToken,
                                                'token_type' => 'Bearer',
                                            ], 200);
                }
            }
            if($request->has('email') && !empty($request->email)){
                $checkUser = User::where('email', $request->email)->first();
                if($checkUser){
                    return response()->json([
                                                'status' => false,
                                                "message" => "This email is already being taken.",
                                               //'errors' => $validator->errors()->toArray(),
                                            ], 422);    
                }
            }

            if($request->has('phone') && !empty($request->phone)){
                $checkUser = User::where('phone', $request->phone)->first();
                if($checkUser){
                    return response()->json([
                                                'status' => false,
                                                "message" => "This phone number is already being taken.",
                                               //'errors' => $validator->errors()->toArray(),
                                            ], 422);    
                }
            }

            $user = new User;
            $user->name = $request->name;
            if($request->has('email') && !empty($request->email)){
                $user->email = $request->email;
            }else{
                $user->approved = '1';
            }
            if($request->has('phone') && !empty($request->phone)){
                $user->phone = $request->phone;
            }

            if($request->login_type == '1'){
                $user->instagram_id = $request->social_id;
            }

            if($request->login_type == '2'){
                $user->twiter_id = $request->social_id;
            }

            if($request->login_type == '3'){
                $user->facebook_id = $request->social_id;
            }

            if($request->login_type == '4'){
                $user->google_id = $request->social_id;
            }

            if($user->save()){
                $userId = $user->id;
                $url = url("account-verification/$userId");
                if($request->has('email') && !empty($request->email)){
                    Mail::to($user->email)->send(new AccountVerification($user->name,$url));
                }
                $userName = rand ( 1000000 , 9999999 );
                $userName = 'G'.$userName;
                $update = User::where('id', $user->id)->update(['username' => $userName]);
                $user = User::where('id', $user->id)->first();
                $checkUserLocation = UsersLocation::where('user_id', $user->id)->first();
                if($checkUserLocation){
                    $user->have_address = true;
                }else{
                    $user->have_address = false;
                }
                // $url = url("account-verification/$userId");
                // Mail::to($user->email)->send(new AccountVerification($user->name,$url));
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();
                return response()->json([
                                            'status' => true,
                                            'message' => 'Successfully created user!',
                                            'data' => $user,
                                            'access_token' => $tokenResult->accessToken,
                                            'token_type' => 'Bearer',
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function signup(Request $request){
    	try{
            // $userName = rand ( 1000000 , 9999999 );
            //     echo $userName = 'G'.$userName;die;
    		$rules = [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required',
        				//'password_confirmation' => 'required',
        				'phone' => 'required|unique:users',
        				'role' => 'required',
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


			if($request->get('role') == '3' || $request->get('role') == '4'){
				$extraRules = [
                	                'image' => 'required',
                	                'id_proof' => 'required',
                                 /*   'description' => 'required',
                                    'account_number' => 'required',
                                    'bank_code' =>'required'*/
                	            ];
	            $validator = Validator::make($request->all(), $extraRules);

	            if($validator->fails())
	            {
	                return response()->json([
                        	                	'status' => false,
                        	                   "message" => $validator->errors()->first(),
                        	                   'errors' => $validator->errors()->toArray(),
                    	                    ], 422);               
	            }

                if($request->get('role') == '4'){
                    $extraRules1 = [
                                        //'opening_time' => 'required',//04:12:16(24 hours format)
                                        //'closing_time' => 'required',//04:12:16(24 hours format)
                                        //'full_time' => 'required',
                                        //'franchisee_proof' => 'required'
                                    ];
                    $validator = Validator::make($request->all(), $extraRules1);

                    if($validator->fails())
                    {
                        return response()->json([
                                                    'status' => false,
                                                   "message" => $validator->errors()->first(),
                                                   'errors' => $validator->errors()->toArray(),
                                                ], 422);               
                    }                    
                }

                /*$res = $this->createRecipt($name,$description,$account_number,$bank_code);
                if(!$res){
                    return response()->json([
                        'status' => false,
                       "message" => "some error in account creation",
                   ], 400);  
                }*/
            }            
            // sat work
            if($request->get('role') == '3'){
                $extraRules = [
                                    'license_image' => 'required',
                                    'address_proof' => 'required',
                                 /*   'description' => 'required',
                                    'account_number' => 'required',
                                    'bank_code' =>'required'*/
                                ];
                $validator = Validator::make($request->all(), $extraRules);

                if($validator->fails())
                {
                    return response()->json([
                                                'status' => false,
                                               "message" => $validator->errors()->first(),
                                               'errors' => $validator->errors()->toArray(),
                                            ], 422);               
                }
            }
            
            if( $request->file('license_image')!= ""){
                if (!file_exists( public_path('/images/license_image'))) {
                    mkdir(public_path('/images/license_image'), 0777, true);
                }
                $path =public_path('/images/license_image/');
                $license_image = $request->file('license_image');
                $licenseImage = time().'.'.$license_image->getClientOriginalExtension();
                $destinationPath = public_path('/images/license_image');
                $license_image->move($destinationPath, $licenseImage);
                $url = url('/images/license_image/');
                $url = str_replace('/index.php', '', $url);
                $licenseImage = $url.'/'.$licenseImage;
            }else{
                $licenseImage = "";  
            }
            // sat work

            if( $request->file('address_proof')!= ""){
                if (!file_exists( public_path('/images/address_proof'))) {
                    mkdir(public_path('/images/address_proof'), 0777, true);
                }
                $path =public_path('/images/address_proof/');
                $license_image = $request->file('address_proof');
                $addressImage = time().'.'.$license_image->getClientOriginalExtension();
                $destinationPath = public_path('/images/address_proof');
                $license_image->move($destinationPath, $addressImage);
                $url = url('/images/address_proof/');
                $url = str_replace('/index.php', '', $url);
                $addressImage = $url.'/'.$addressImage;
            }else{
                $addressImage = "";  
            }
            if( $request->file('id_proof')!= ""){
                if (!file_exists( public_path('/images/id_proof'))) {
                    mkdir(public_path('/images/id_proof'), 0777, true);
                }
                $path =public_path('/images/id_proof/');
                $image = $request->file('id_proof');
                $idProofImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/id_proof');
                $image->move($destinationPath, $idProofImage);
                $url = url('/images/id_proof/');
                $url = str_replace('/index.php', '', $url);
                $idProofImage = $url.'/'.$idProofImage;
            }else{
                $idProofImage = "";  
            }

            if( $request->file('franchisee_proof')!= ""){
                if (!file_exists( public_path('/images/franchisee_proof'))) {
                    mkdir(public_path('/images/franchisee_proof'), 0777, true);
                }
                $path =public_path('/images/franchisee_proof/');
                $image = $request->file('franchisee_proof');
                $franchiseeProofImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/franchisee_proof');
                $image->move($destinationPath, $franchiseeProofImage);
                $url = url('/images/franchisee_proof/');
                $url = str_replace('/index.php', '', $url);
                $franchiseeProofImage = $url.'/'.$franchiseeProofImage;
            }else{
                $franchiseeProofImage = "";  
            }

            if( $request->file('image')!= ""){
                if (!file_exists( public_path('/images/profile'))) {
                    mkdir(public_path('/images/profile'), 0777, true);
                }
                $path =public_path('/images/profile/');
                $image = $request->file('image');
                $profileImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/profile');
                $image->move($destinationPath, $profileImage);
                $url = url('/images/profile/');
                $url = str_replace('/index.php', '', $url);
                $profileImage = $url.'/'.$profileImage;
            }else{
                $profileImage = "";  
                if($request->has('image_url') && !empty($request->image_url)){
                    $profileImage = $request->image_url;  
                }  
            }

            $englishWords = array($request->get('name'));
            $frenchWords = $this->translation($englishWords);
            
            //echo'<pre>';print_r($frenchWords);die;
            $setting = Setting::where('id', '1')->first();

            $user = new User;
            $user->name = $request->get('name');
            $user->french_name = $frenchWords['0'];
            $user->email = $request->get('email');
            $user->phone = $request->get('phone');
            $user->image =  $profileImage;
            $user->id_proof =  $idProofImage;
            if($request->has('from_referal') && !empty($request->from_referal)){
                $user->from_referal = $request->from_referal;
                
            }

            $user->my_referal = $this->random_string(8);
            // if($request->get('role') == '2'){
            //     $user->approved = '1';
            // }   
            if($request->get('role') == '3'){
                $user->latitude = $request->latitude;
                $user->longitude = $request->longitude;
                $user->license_image = $licenseImage; // sat work
                $user->address_proof = $addressImage;
            }
            if($request->get('role') == '4'){
                // if($request->has('opening_time') && !empty($request->opening_time)){
                //     $user->opening_time = $request->opening_time;
                // }
                // if($request->has('closing_time') && !empty($request->closing_time)){
                //     $user->closing_time = $request->closing_time;
                // }
                // $user->full_time = $request->full_time;
                $user->franchisee_proof = $franchiseeProofImage;
            }
            $user->role = $request->get('role');
            $user->password = bcrypt($request->get('password'));
            
            if($user->save()){
                $userId = $user->id;
                $earnedUser = User::where('my_referal', $request->from_referal)->first();
                if($earnedUser){
                    $wallet = $earnedUser->wallet + $setting['sender_refer_earn'];
                    $earnedUser->wallet = $wallet;
                    $earnedUser->save();
                    if($earnedUser->notification == '1'){
                        $amount = $setting['sender_refer_earn'];
                        $message = "$amount Added to your wallet by referal code";
                        $frenchMessage = $this->translation($message);
                        if($earnedUser->language == '1'){
                            $msg = $message;    
                        }else{
                            $msg = $frenchMessage[0];
                        }
                        $userTokens = UserToken::where('user_id', $earnedUser->id)->get()->toArray();
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
                        $saveNotification = new Notification;
                        $saveNotification->user_id = $earnedUser->id;
                        $saveNotification->notification = $message;
                        $saveNotification->french_notification = $frenchMessage[0];
                        $saveNotification->role = '2';
                        $saveNotification->read = '0';
                        $saveNotification->image = $earnedUser->image;
                        $saveNotification->notification_type = '0';
                        $saveNotification->save();
                    }
                }
                // $userName = substr($user->email, 0, strpos($user->email, "@")); 
                // $userName = $userName.'@'.$user->id;
                $userName = rand ( 1000000 , 9999999 );
                $userName = 'G'.$userName;
                $update = User::where('id', $user->id)->update(['username' => $userName, 'wallet' => $setting['receiver_refer_earn']]);
                $user = User::where('id', $user->id)->first();
                $url = url("account-verification/$userId");
                if($request->get('role') == '2'){
                    Mail::to($user->email)->send(new AccountVerification($user->name,$url));
                }                
            	$tokenResult = $user->createToken('Personal Access Token');
	            $token = $tokenResult->token;
	            $token->save();
	            return response()->json([
    						                'status' => true,
    						                'message' => 'Successfully created user!',
    						                'data' => $user,
    						                'access_token' => $tokenResult->accessToken,
    						                'token_type' => 'Bearer',
    						            ], 200);
            }else{
            	return response()->json([
    						                'status' => false,
    						                'message' => 'Something went wrong.',
    						            ], 404);
            }

    	}catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    function random_string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function createRecipt($name,$description,$account_number,$bank_code){
        try{

            $url = 'https://api.paystack.co/transferrecipient';
            $fields = array(
                                "type" => "nuban",
                                "name" => $name,
                                "description" => $description,
                                "account_number" => $account_number,
                                "bank_code" => $bank_code,
                                "currency" => "NGN",
                                "metadata" => array("orderId" => 1)
                            );

            $fields = json_encode($fields);
            $headers = array(
                                'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e',
                                "Content-Type: application/json"
                            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $result = curl_exec($ch);
            //curl_close($ch);
            $data = json_decode($result);
            $recipitId = $data->data->recipient_code;
            return $recipitId;

        } catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
            

    }

    public function tableBookingList(){
        try{
            $user = Auth::user();
            $tableBookings = TableBooking::where('user_id', $user->id)
                                            ->orderBy('id', 'Desc')
                                            ->get()->toArray();
            if($tableBookings){
                foreach ($tableBookings as $key => $tableBooking) {
                    $customer = User::where('id', $tableBooking['user_id'])->first();
                    $restaurant = User::where('id', $tableBooking['restaurant_id'])->first();
                    $tableBookings[$key]['customer_name'] = $customer['name'];
                    $tableBookings[$key]['customer_french_name'] = $customer['french_name'];
                    $tableBookings[$key]['customer_name'] = $customer['image'];

                    $tableBookings[$key]['restaurant_name'] = $restaurant['name'];
                    $tableBookings[$key]['restaurant_french_name'] = $restaurant['french_name'];
                    $tableBookings[$key]['restaurant_image'] = $restaurant['image'];
                    $tableBookings[$key]['restaurant_address'] = $restaurant['address'];
                }
                return response()->json([
                                            'message' => "Table Booking Found.",
                                            'status' => false,
                                            'data' => $tableBookings
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => "Table Booking Not Found.",
                                            'status' => false,
                                            'data' => $tableBookings
                                        ], 200);    
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function userInfo(){
        try{
            $user = Auth::user();
            if($user->role == '4'){
                $ratings = RatingReview::where('receiver_type', '2')
                                        ->where('receiver_id', $user->id)
                                        ->get()
                                        ->toArray();
            }elseif ($user->role == '3') {
                $ratings = RatingReview::where('receiver_type', '3')
                                        ->where('receiver_id', $user->id)
                                        ->get()
                                        ->toArray();
            }else{
                $ratings = "";
            }
            $avergeRating = 0.0;
            if($ratings){
                $ratingArr = array();
                foreach ($ratings as $key1 => $rating) {
                    $ratingArr[] = $rating['rating'];
                }
                $totalRating = (string) count($ratings);
                $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
            }else{
                $totalRating = "0";
            }
            $user['avg_ratings'] = $avergeRating;
            $user['total_rating'] = $totalRating;
            if($user){
                return response()->json([
                                            'status' => true,
                                            'message' => 'User Info Successfully Found',
                                            'data' => $user,
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => 'User Info Not Found',
                                            'data' => $user,
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function forgotPassword(Request $request){
        
        $rules = [
                   'email' => 'required|email'
               ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
                return response()->json([
                    'status' => false,
                    'message' => "Incorrect details",
                    //'errors' => $errors
                ], 400);
        }else{
            $email = $request->email;
            $user = User::where('email', $request->get('email'))->first();
            if($user){
                //$dummy_pass =  rand ( 1000 , 9999 );
                $userId = urlencode(base64_encode($user->id));
                $changePasswordUrl = url("change-password/$userId");
                //$changePasswordUrl = "url('')";
                //echo $changePasswordUrl;die;
                Mail::to($email)->send(new ForgotPassword($user->name,$changePasswordUrl));
                $mutable = Carbon::now()->addMinutes(15);
                //echo $mutable;die;
                //$update = User::where('email', $request->get('email'))->update(['otp' => $dummy_pass, 'otp_expire_time' => $mutable]);
                //$user = User::where('email', $request->get('email'))->first();
                //$result['message'] = 'Please check your email for new password';
                //$user->reset_code = $dummy_pass;
                return response()->json([
                                            'status' => true,
                                            'message' => "Please check your email for reset password.",
                                            //'data' => $user
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "This Email Is not Register With Us"
                                        ], 422);
            }
                
        }
    }

    public function changePassword(Request $request){
        try{
            $rules = [
                        'old_password' => 'required',
                        'password' => 'required',
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

            $password = bcrypt($request->get('password'));
            //$mutable = Carbon::now();
            $user = Auth::user();
            if(Hash::check($request->get('old_password'), $user->password)){
                $update = User::where('id', $user->id)->update(['password' => $password]);
                if($update){
                    DB::table('oauth_access_tokens')
                                            ->where('user_id', $user->id)
                                            ->update([
                                                        'revoked' => true
                                                    ]);
                    return response()->json([
                                               "message" => "User's password changed successfully.",
                                               'status' => true,
                                            ]);
                }else{
                    return response()->json([
                                               "message" => "Something went wrong.",
                                               'status' => false,
                                            ]);
                }
            }else{
                return response()->json([
                                           "message" => "Old password not matched.",
                                           'status' => false,
                                        ],400);
            }

            

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    } 

    public function changeLanguage($language){
        try{
            $user = Auth::user();
            $changeLanguage = User::where('id', $user->id)->update(['language' => $language]);
            $userInfo = User::where('id', $user->id)->first();
            if($changeLanguage){
                return response()->json([
                                            'status' => true,
                                            'message' => "Language Successfully Updated.",
                                            'data' => $userInfo
                                        ], 200);
            }else{
                return response()->json([
                                           "message" => "Something went wrong.",
                                           'status' => false,
                                        ]);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function completeProfile(Request $request){
        try{
            $rules = [
                       'address' => 'required',
                       'lat' => 'required',
                       'long' => 'required',
                       //'cuisines' => 'required',
                       'pure_veg' => 'required',
                       'pickup' => 'required',
                       'preparing_time' => 'required',
                       //'opening_time' => 'required',
                       //'closing_time' => 'required',
                       'full_time' => 'required',
                    ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                    return response()->json([
                                                'status' => false,
                                                'message' => $validator->errors()->first(),
                                                'errors' => $errors
                                            ], 400);
            }
            //echo'<pre>';print_r($request->all());die;
            $user = Auth::user();
            //echo'<pre>';print_r($user);die;
            $englishWords = array($request->get('address'));
            $frenchWords = $this->translation($englishWords);

            if($request->has('opening_time') && !empty($request->opening_time)){
                $openingTime = $request->opening_time;
                $closingTime = $request->closing_time;
            }else{
                $openingTime = '';
                $closingTime = '';
            }

            $updateProfile = User::where('id', $user->id)->update([
                                                                    'address' => $request->address,
                                                                    'french_address' => $frenchWords[0],
                                                                    'latitude' => $request->lat,
                                                                    'longitude' => $request->long,
                                                                    'pure_veg' => $request->pure_veg,
                                                                    'pickup' => $request->pickup,
                                                                    'preparing_time' => $request->preparing_time,
                                                                    'opening_time' => $openingTime,
                                                                    'closing_time' => $closingTime,
                                                                    'full_time' => $request->full_time
                                                                ]);
            // if($request->cuisines){
            //     //echo'hi';die;array_unique
            //     $cuisinesArr = explode(',', $request->cuisines);
            //     foreach ($cuisinesArr as $key => $cuisinId) {
            //         $cuisins = Cuisine::where('id', $cuisinId)->first();
            //         $cuisin = new RestaurantCuisine;
            //         $cuisin->restaurant_id = $user->id;
            //         $cuisin->cuisine_id = $cuisinId;
            //         $cuisin->save();
            //     }
            // }

            $user = User::where('id', $user->id)->first();
            if($updateProfile){
                return response()->json([
                                            'status' => true,
                                            'message' => "User's Profile Completed Successfully.",
                                            'data' => $user
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => 'Something went wrong.',
                                        ], 422);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function editProfile(Request $request){
        try{
            $rules = [
                        'name' => 'required',
                        //'address' => 'required',
                        //'lat' => 'required',
                        //'long' => 'required',
                        //'cuisines' => 'required',
                        //'busy_status' => 'required',
                        //'phone' => 'required',
                    ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return response()->json([
                                            'status' => false,
                                            'message' => $validator->errors()->first(),
                                            'errors' => $errors
                                        ], 400);
            }

            $user = Auth::user();

            if($user->role == '4'){
                $rules = [
                            //'opening_time' => 'required',
                            //'closing_time' => 'required',
                            'pure_veg' => 'required',
                            'full_time' => 'required',
                            'pickup' => 'required'

                        ];
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $errors = $validator->errors()->toArray();
                        return response()->json([
                                                    'status' => false,
                                                    'message' => $validator->errors()->first(),
                                                    'errors' => $errors
                                                ], 400);
                }
            }

            if( $request->file('image')!= ""){
                if (!file_exists( public_path('/images/profile'))) {
                    mkdir(public_path('/images/profile'), 0777, true);
                }
                $path =public_path('/images/profile/');
                $image = $request->file('image');
                $profileImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/profile');
                $image->move($destinationPath, $profileImage);
                $url = url('/images/profile/');
                $url = str_replace('/index.php', '', $url);
                $profileImage = $url.'/'.$profileImage;
            }else{
                $profileImage = $user->image;  
            }

            if( $request->file('license_image')!= ""){
                if (!file_exists( public_path('/images/license_image'))) {
                    mkdir(public_path('/images/license_image'), 0777, true);
                }
                $path =public_path('/images/license_image/');
                $image = $request->file('license_image');
                $license_image = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/license_image');
                $image->move($destinationPath, $license_image);
                $url = url('/images/license_image/');
                $url = str_replace('/index.php', '', $url);
                $license_image = $url.'/'.$license_image;
            }else{
                $license_image = $user->image;  
            }

            if( $request->file('address_proof')!= ""){
                if (!file_exists( public_path('/images/address_proof'))) {
                    mkdir(public_path('/images/address_proof'), 0777, true);
                }
                $path =public_path('/images/address_proof/');
                $image = $request->file('address_proof');
                $addressImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/address_proof');
                $image->move($destinationPath, $addressImage);
                $url = url('/images/address_proof/');
                $url = str_replace('/index.php', '', $url);
                $addressImage = $url.'/'.$addressImage;
            }else{
                $addressImage = $user->address_proof;  
            }

            $englishWords = array($request->get('name'),$request->get('address'));
            $frenchWords = $this->translation($englishWords);
            $profile = User::where('id', $user->id)->first();
            $profile->name = $request->name;
            $profile->french_name = $frenchWords[0];
            if($request->has('address') && !empty($request->address)){
                $profile->address = $request->address;
                $profile->french_address = $frenchWords[1];
                if($request->lat != 0){
                    $profile->latitude = $request->lat;
                    $profile->longitude = $request->long;
                }
            }
            if($user->role == '4'){
                if($request->has('opening_time') && !empty($request->opening_time)){
                    $profile->opening_time = $request->opening_time;
                    $profile->closing_time = $request->closing_time;
                }
                $profile->full_time = $request->full_time;
                $profile->pickup = $request->pickup;
                $profile->pure_veg = $request->pure_veg;
                if($request->pickup == '0'){
                    $update = UserOrderType::where('restaurant_id', $user->id)
                                            ->where('order_type', '2')
                                            ->update(['order_type' => '1']);
                    
                }
                $message = "Restaurant Profile Updated Successfully.";
            }else{
                $message = "User's Profile Updated Successfully.";
            }
            $profile->image = $profileImage;
            $profile->license_image = $license_image;
            $profile->address_proof = $addressImage;
            if($request->has('phone') && !empty($request->phone)){
                $profile->phone = $request->phone;
            }
            //$profile->busy_status = $request->busy_status;
            if($profile->save()){
                return response()->json([
                                            'status' => true,
                                            'message' => $message,
                                            'data' => $profile
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => 'Something went wrong.',
                                        ], 422);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function userToken(Request $request){
        try{
            $rules = [
                        'device_token' => 'required',
                        'device_type' => 'required',             
                        //'device_id' => 'required' 
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
            $token = $request->get('device_token');
            $userId = $user->id;
            $type = $request->get('device_type');
            if($user->role == '2' || $user->role == '4'){
                $rules = [             
                            'device_id' => 'required' 
                        ];
                $validator = Validator::make($request->all(), $rules);

                if($validator->fails())
                {
                    return response()->json([
                                               "message" => "Something went wrong!",
                                               'errors' => $validator->errors()->toArray(),
                                            ], 422);               
                }

                $userToken = UserToken::where('user_id', $userId)->where('device_id', $request->device_id)->first();   
                if($userToken){
                    $usersToken = UserToken::where('user_id', $userId)->where('device_id', $request->device_id)->update(['device_token' => $token, 'device_type' => $type]);   
                }else{
                    $usersToken = new UserToken;
                    $usersToken->user_id = $userId;
                    $usersToken->device_id = $request->device_id;
                    $usersToken->device_type = $request->device_type;
                    $usersToken->device_token = $request->device_token;
                    $usersToken->save();
                }
                //echo'<pre>';print_r($usersToken);die;
                $usersToken = User::where('id', $userId)->update(['device_token' => $token, 'device_type' => $type]);    
            }else{
                $usersToken = User::where('id', $userId)->update(['device_token' => $token, 'device_type' => $type]);    
            }
            
            //echo'<pre>';print_r($usersToken);die;
            //$update = UserToken::where('id', $userId)->update(['device_token' => $token, 'device_type' => $type]);
            if($usersToken){
                return response()->json([
                                           "message" => "User token updated successfully.",
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
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }

    }

    public function markFavourite(Request $request){
        try{
            $rules = [
                        'status' => 'required',
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
            $check = Favourite::where(['user_id' => $user->id, 'restaurant_id' => $restaurantId])->first();

            if($check){
                if($request->status == '0'){
                    $delete = Favourite::where(['user_id' => $user->id, 'restaurant_id' => $restaurantId])->delete();                    
                    return response()->json([
                                                'status' => true,
                                                'message' => "Restaurant Marked As UnFavourite.",
                                                //'data' => $user
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => true,
                                                'message' => "Restaurant Already Marked As Favourite.",
                                                //'data' => $user
                                            ], 200);
                }
                
            }else{
                if($request->status == '0'){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Restaurant Do not exists In your Favourites.",
                                                //'data' => $user
                                            ], 200);   
                }
                $favourite = new Favourite;   
                $favourite->user_id = $user->id;
                $favourite->restaurant_id = $restaurantId;
                if($favourite->save()){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Restaurant Mark As Favourite.",
                                                //'data' => $user
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

    public function markUnFavourite($restaurantId){
        try{
            $user = Auth::user();
            $favourite = Favourite::where(['user_id' => $user->id,'restaurant_id' => $restaurantId])->delete();   
            
            if($favourite){
                return response()->json([
                                            'status' => true,
                                            'message' => "Restaurant Unmark As Favourite.",
                                            //'data' => $user
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

    public function getDriverLatLong($orderId){
        try{
            $order = Order::where('id', $orderId)->first();
            $usersData = User::where('id', $order->driver_id)->first();
            if($usersData){
                return response()->json([
                                            'status' => true,
                                            'message' => "Driver data found.",
                                            'data' => $user
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Driver data not found.",
                                            'data' => $user
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function addItemsRating(Request $request){
        try{
            $rules = [
                        'order_id' => 'required',
                        //'receiver_id' => 'required',
                        'ratings' => 'required',              
                        //'review' => 'required',
                        //'receiver_type' => 'required'//1=>dish,2=>restaurant,3=>driver
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
            //echo $request->ratings;
            $ratings = json_decode($request->ratings, true);
            //echo'<pre>';print_r($ratings);die;
            //if($ratings){
                //{"1":"2","3":"4"}
                foreach ($ratings as $receiverId => $ratingReview) {
                    $rating = new RatingReview;
                    $rating->order_id = $request->order_id;
                    $rating->sender_id = $user->id;
                    $rating->receiver_id = $receiverId;
                    $rating->receiver_type = '1';
                    $rating->rating = $ratingReview['rating'];
                    $rating->review = $ratingReview['review'];
                    $rating->save();
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Ratings Successfully Added.",
                                            //'data' => $rating
                                        ], 200);
            //}
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function addRatingReview(Request $request){
        try{
            $rules = [
                        'order_id' => 'required',
                        'receiver_id' => 'required',
                        'rating' => 'required',              
                        //'review' => 'required',
                        //'good_review' => 'required',
                        //'bad_review' => 'required',
                        'receiver_type' => 'required',//1=>dish,2=>restaurant,3=>driver
                        //'amount' => 'required'
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
            $check = RatingReview::where(['order_id' => $request->order_id,'sender_id' => $user->id, 'receiver_id' => $request->receiver_id, 'receiver_type' => $request->receiver_type])->first();
            if($check){
                return response()->json([
                                            'status' => true,
                                            'message' => "You Already Sent Rating Reviews.",
                                            'data' => $check
                                        ], 200);
            }

            $order = Order::where('id', $request->order_id)->select('id')->first();

            $rating = new RatingReview;
            $rating->order_id = $request->order_id;
            $rating->sender_id = $user->id;
            $rating->receiver_id = $request->receiver_id;
            $rating->receiver_type = $request->receiver_type;
            if($request->has('review') && !empty($request->review)){
                $rating->review = $request->review;
            }
            if($request->has('good_review') && !empty($request->good_review)){
                $rating->good_review = $request->good_review;
            }
            if($request->has('bad_review') && !empty($request->bad_review)){
                $rating->bad_review = $request->bad_review;
            }
            $rating->rating = $request->rating;
            if($rating->save()){
                if($request->receiver_type == '3'){
                    if($request->has('amount') && !empty($request->amount)){
                        $amount = $request->amount;
                        if($user->wallet < $amount){
                            return response()->json([
                                                      'message' => "You Doesn't have sufficent amount in your wallet.",
                                                      'status' => false,
                                                  ], 200);
                        }
                        //$order = Order::where('id', $orderId)->first();
                        $driverId = $request->receiver_id;
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
                                $deta = $order;
                                $deta->notification_type = "308";
                                $message = "You Receive a tip of $amount from $user->name";
                                $frenchMessage = $this->translation($message);
                                if($driver->language == '1'){
                                    $msg = $message;
                                }else{
                                    $msg = $frenchMessage[0];
                                }
                                if($driver->device_type == '0'){
                                    $sendNotification = $this->sendPushNotification($driver->device_token,$msg,$deta);    
                                }
                                if($driver->device_type == '1'){
                                    $sendNotification = $this->iosPushNotification($driver->device_token,$msg,$deta);    
                                }

                                return response()->json([
                                                            'message' => "Rating Reviews and Tip Successfully Added",
                                                            'status' => true,
                                                        ], 200);
                              //}
                            }else{
                              return response()->json([
                                                        'message' => "Something Went Wrong!",
                                                        'status' => false,
                                                    ], 422);
                            }
                        }
                    }
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Rating Reviews Successfully Added.",
                                            'data' => $rating
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

    public function customerOngoingOrders(){
        try{
            $user = Auth::user();
            //echo $user->id;die;
            $orders = Order::where('user_id', $user->id)->whereIn('order_status', ['0','2','3','4','7','9'])->where('is_schedule', '<>', '1')->orderBy('id', 'Desc')->get()->toArray();
            $itemNames = Item::where('status', '1')->pluck('name', 'id')->toArray();
            foreach ($orders as $key2 => $order) {
                //if restaurant doesn't take any action
                
                if($order['order_status'] == '0'){
                    $createdAt = date("Y-m-d H:i:s", strtotime("+10 minutes", strtotime($order['created_at'])));
                    //$createdAt = date("Y-m-d H:i:s", strtotime($order['created_at']));
                    $currentTime = date("Y-m-d H:i:s");
                    
                    if($createdAt <= $currentTime){
                        $update = Order::where('id', $order['id'])->update(['order_status' => '6', 'cancel_type' => '5']);
                    }
                }

                //echo'<pre>';print_r($order);die;
                $restaurantInfo = User::where('id', $order['restaurant_id'])->first();
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
                            $finalResultToAppend = array();
                            $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                            //echo'<pre>';print_r($itemCategory);die;
                            $itemChoices[$key]->name = $itemCategory['name'];
                            $itemChoices[$key]->french_name = $itemCategory['french_name'];
                            $itemChoices[$key]->selection = $itemCategory['selection'];
                            $itemSubCats = explode(',', $itemChoice->item_sub_category);
                            foreach ($itemSubCats as $key3 => $itemSubCat) {
                                $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

                                $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price']
                                                    );  
                                
                                //die;

                                // $itemChoices[$key]['item_sub_category'][$key1]['name'] = $itemSubCategory['name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['french_name'] = $itemSubCategory['french_name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['add_on_price'] = $itemSubCategory['add_on_price'];
                            }
                            $itemChoices[$key]->item_sub_category = $finalResultToAppend;
                        }

                    }else{
                        $itemChoices = array();
                    }
                    $checkItemRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $items['id'])->first();
                    if($checkItemRating){
                        $orderdetails[$key1]['is_item_rated'] = '1';
                    }else{
                        $orderdetails[$key1]['is_item_rated'] = '0';
                    }
                    $orderdetails[$key1]['item_choices'] = $itemChoices;
                    $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                    $orderdetails[$key1]['item_name'] = $items['name'];
                }
                $checkResRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $order['restaurant_id'])->first();
                
                if($checkResRating){
                    $orders[$key2]['is_restaurant_rated'] = '1';    
                }else{
                    $orders[$key2]['is_restaurant_rated'] = '0';    
                }

                $checkDriRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $order['driver_id'])->first();
                
                if($checkDriRating){
                    $orders[$key2]['is_driver_rated'] = '1';    
                }else{
                    $orders[$key2]['is_driver_rated'] = '0';    
                }

                $orders[$key2]['restaurant_name'] = $restaurantInfo['name'];
                $orders[$key2]['restaurant_french_name'] = $restaurantInfo['french_name'];
                $orders[$key2]['restaurant_image'] = $restaurantInfo['image'];
                $orders[$key2]['user_name'] = $userInfo['name'];
                $orders[$key2]['user_french_name'] = $userInfo['french_name'];
                $orders[$key2]['user_image'] = $userInfo['image'];
                $orders[$key2]['user_email'] = $userInfo['email'];
                $orders[$key2]['user_phone'] = $userInfo['phone'];
                $orders[$key2]['order_details'] = $orderdetails;
            }
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
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function customerUpcomingOrders(){
        try{
            $user = Auth::user();
            //echo $user->id;die;
            $orders = Order::where('user_id', $user->id)->whereIn('order_status', ['1','2','3','4','7','9'])->where('is_schedule', '1')->orderBy('id', 'Desc')->get()->toArray();
            $itemNames = Item::where('status', '1')->pluck('name', 'id')->toArray();
            foreach ($orders as $key2 => $order) {
                //if restaurant doesn't take any action
                
                if($order['order_status'] == '0'){
                    $createdAt = date("Y-m-d H:i:s", strtotime("+10 minutes", strtotime($order['created_at'])));
                    //$createdAt = date("Y-m-d H:i:s", strtotime($order['created_at']));
                    $currentTime = date("Y-m-d H:i:s");
                    
                    if($createdAt <= $currentTime){
                        $update = Order::where('id', $order['id'])->update(['order_status' => '6']);
                    }
                }

                //echo'<pre>';print_r($order);die;
                $restaurantInfo = User::where('id', $order['restaurant_id'])->first();
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
                            $finalResultToAppend = array();
                            $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                            //echo'<pre>';print_r($itemCategory);die;
                            $itemChoices[$key]->name = $itemCategory['name'];
                            $itemChoices[$key]->french_name = $itemCategory['french_name'];
                            $itemChoices[$key]->selection = $itemCategory['selection'];
                            $itemSubCats = explode(',', $itemChoice->item_sub_category);
                            foreach ($itemSubCats as $key3 => $itemSubCat) {
                                $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

                                $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price']
                                                    );  
                                
                                //die;

                                // $itemChoices[$key]['item_sub_category'][$key1]['name'] = $itemSubCategory['name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['french_name'] = $itemSubCategory['french_name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['add_on_price'] = $itemSubCategory['add_on_price'];
                            }
                            $itemChoices[$key]->item_sub_category = $finalResultToAppend;
                        }

                    }else{
                        $itemChoices = array();
                    }
                    $checkItemRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $items['id'])->first();
                    if($checkItemRating){
                        $orderdetails[$key1]['is_item_rated'] = '1';
                    }else{
                        $orderdetails[$key1]['is_item_rated'] = '0';
                    }
                    $orderdetails[$key1]['item_choices'] = $itemChoices;
                    $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                    $orderdetails[$key1]['item_name'] = $items['name'];
                }
                $checkResRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $order['restaurant_id'])->first();
                
                if($checkResRating){
                    $orders[$key2]['is_restaurant_rated'] = '1';    
                }else{
                    $orders[$key2]['is_restaurant_rated'] = '0';    
                }

                $checkDriRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $order['driver_id'])->first();
                
                if($checkDriRating){
                    $orders[$key2]['is_driver_rated'] = '1';    
                }else{
                    $orders[$key2]['is_driver_rated'] = '0';    
                }

                $ratings = RatingReview::where('receiver_type', '2')
                                                ->where('receiver_id', $order['restaurant_id'])
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


                $orders[$key2]['total_rating'] = $totalRating;
                $orders[$key2]['total_review'] = $totalReview;
                $orders[$key2]['restaurant_name'] = $restaurantInfo['name'];
                $orders[$key2]['restaurant_name'] = $restaurantInfo['name'];
                $orders[$key2]['restaurant_french_name'] = $restaurantInfo['french_name'];
                $orders[$key2]['restaurant_image'] = $restaurantInfo['image'];
                $orders[$key2]['user_name'] = $userInfo['name'];
                $orders[$key2]['user_french_name'] = $userInfo['french_name'];
                $orders[$key2]['user_image'] = $userInfo['image'];
                $orders[$key2]['user_email'] = $userInfo['email'];
                $orders[$key2]['user_phone'] = $userInfo['phone'];
                $orders[$key2]['order_details'] = $orderdetails;
            }
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
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function customerPastOrders(){
        try{
            $user = Auth::user();
            //echo $user->id;die;
            $orders = Order::where('user_id', $user->id)->whereIn('order_status', ['5','6','8'])->orderBy('id', 'Desc')->get()->toArray();
            //echo'<pre>';print_r($orders);die;
            $itemNames = Item::where('status', '1')->pluck('name', 'id')->toArray();
            //echo'<pre>';print_r($itemNames);
            foreach ($orders as $key2 => $order) {
                //echo'<pre>';print_r($order);die;
                $driverInfo = User::where('id', $order['driver_id'])->first();
                $restaurantInfo = User::where('id', $order['restaurant_id'])->first();
                $userInfo = User::where('id', $order['user_id'])->first();
                //$orders[$key]['user_name'] = $userName[$order['user_id']];
                $orderdetails = OrderDetail::where('order_id', $order['id'])->get()->toArray();
                //echo'<pre>';print_r($orderdetails);die;
                foreach ($orderdetails as $key1 => $orderdetail) {
                    $items = Item::where('id', $orderdetail['item_id'])->first();
                    //echo'<pre>';print_r($items);die;
                    if($orderdetail['item_choices'] != ""){
                        $itemChoices = json_decode($orderdetail['item_choices']);
                        //echo'<pre>';print_r($itemChoices);die;
                        foreach ($itemChoices as $key => $itemChoice) {
                            $finalResultToAppend = array();
                            $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                            //echo'<pre>';print_r($itemCategory);die;
                            $itemChoices[$key]->name = $itemCategory['name'];
                            $itemChoices[$key]->french_name = $itemCategory['french_name'];
                            $itemChoices[$key]->selection = $itemCategory['selection'];
                            $itemSubCats = explode(',', $itemChoice->item_sub_category);
                            foreach ($itemSubCats as $key3 => $itemSubCat) {
                                $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

                                $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price']
                                                    );  
                                
                                //die;

                                // $itemChoices[$key]['item_sub_category'][$key1]['name'] = $itemSubCategory['name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['french_name'] = $itemSubCategory['french_name'];
                                // $itemChoices[$key]['item_sub_category'][$key1]['add_on_price'] = $itemSubCategory['add_on_price'];
                            }
                            $itemChoices[$key]->item_sub_category = $finalResultToAppend;
                        }

                    }else{
                        $itemChoices = array();
                    }
                    $checkItemRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $items['id'])->where('receiver_type', '1')->first();
                    //echo'<pre>';print_r($checkItemRating);die;
                    if($checkItemRating){
                        $orderdetails[$key1]['is_item_rated'] = '1';
                    }else{
                        $orderdetails[$key1]['is_item_rated'] = '0';
                    }

                    $orderdetails[ $key1]['item_choices'] = $itemChoices;
                    $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                    $orderdetails[$key1]['item_name'] = $items['name'];
                }
                $checkResRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $order['restaurant_id'])->first();
                
                if($checkResRating){
                    $orders[$key2]['is_restaurant_rated'] = '1';    
                }else{
                    $orders[$key2]['is_restaurant_rated'] = '0';    
                }

                $checkDriRating = RatingReview::where('order_id', $order['id'])->where('receiver_id', $order['driver_id'])->first();
                
                if($checkDriRating){
                    $orders[$key2]['is_driver_rated'] = '1';    
                }else{
                    $orders[$key2]['is_driver_rated'] = '0';    
                }

                $ratings = RatingReview::where('receiver_type', '3')
                                        ->where('receiver_id', $driverInfo['id'])
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

                $driverOrdersCount = Order::where('driver_id', $driverInfo['id'])->where('order_status', '5')->count();

                //echo'<pre>';print_r($driverInfo);die;
                $orders[$key2]['driver_order_count'] = $driverOrdersCount;
                $orders[$key2]['driver_total_rating'] = $totalRating;
                $orders[$key2]['driver_average_rating'] = $avergeRating;
                $orders[$key2]['driver_name'] = $driverInfo['name'];
                $orders[$key2]['driver_image'] = $driverInfo['image'];
                $orders[$key2]['restaurant_name'] = $restaurantInfo['name'];
                $orders[$key2]['restaurant_french_name'] = $restaurantInfo['french_name'];
                $orders[$key2]['restaurant_image'] = $restaurantInfo['image'];
                $orders[$key2]['user_name'] = $userInfo['name'];
                $orders[$key2]['user_french_name'] = $userInfo['french_name'];
                $orders[$key2]['user_image'] = $userInfo['image'];
                $orders[$key2]['user_email'] = $userInfo['email'];
                $orders[$key2]['user_phone'] = $userInfo['phone'];
                $orders[$key2]['order_details'] = $orderdetails;
            }
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
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function orderDetails($orderId){
        try{
            $orders = Order::where('id', $orderId)->first();
            if($orders){
                $requestedTime = $orders['request_time'];
                //return $requestedTime;
                if($requestedTime != ""){
                    $preparingTime = $orders['preparing_time'];
                    //echo "+$preparingTime minutes";die;

                    $endPreparingTime = date('Y-m-d H:i:s',strtotime("+$preparingTime minutes",strtotime($requestedTime)));
                    //return $endPreparingTime;
                    $currentTime = Carbon::now()->format('Y-m-d H:i:s');
                    // echo $currentTime;
                    // echo"<br>";
                    // echo $endPreparingTime;die;
                    if($currentTime >= $endPreparingTime){
                        $timeRemaining = 0;
                    }else{
                        $timeRemaining = strtotime($endPreparingTime) - strtotime($currentTime);
                    }
                    
                }else{
                    $timeRemaining = 0;
                }
                //$timeRemaining = gmdate("H:i:s", $timeRemaining);
                //return $timeRemaining;
                $orders['time_remaining'] = $timeRemaining;
                $itemNames = Item::where('status', '1')->pluck('name', 'id')->toArray();
                //echo'<pre>';print_r($itemNames);
                //foreach ($orders as $key => $order) {
                    $driverInfo = User::where('id', $orders['driver_id'])->first();
                    $userInfo = User::where('id', $orders['user_id'])->first();
                    $restaurantInfo = User::where('id', $orders['restaurant_id'])->first();
                      $restaurantCuisinesIds = Item::where('restaurant_id', $orders['restaurant_id'])
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
                    //$orders[$key]['user_name'] = $userName[$order['user_id']];
                    $orderdetails = OrderDetail::where('order_id', $orders['id'])->get()->toArray();
                    //echo'<pre>';print_r($orderdetails);die;
                    foreach ($orderdetails as $key1 => $orderdetail) {
                        $items = Item::where('id', $orderdetail['item_id'])->first();
                        if($orderdetail['item_choices'] != ""){
                            $itemChoices = json_decode($orderdetail['item_choices']);
                          
                            //echo'<pre>';print_r($itemChoices);die;
                            foreach ($itemChoices as $key => $itemChoice) {
                                $finalResultToAppend = array();
                                $itemCategory = ItemCategory::where('id', $itemChoice->id)->first();
                                //echo'<pre>';print_r($itemCategory);die;
                                $itemChoices[$key]->name = $itemCategory['name'];
                                $itemChoices[$key]->french_name = $itemCategory['french_name'];
                                $itemChoices[$key]->selection = $itemCategory['selection'];
                                $itemSubCats = explode(',', $itemChoice->item_sub_category);
                                foreach ($itemSubCats as $key3 => $itemSubCat) {
                                    $itemSubCategory = ItemSubCategory::where('id', $itemSubCat)->first();

                                    $finalResultToAppend[] = array("id" => $itemSubCategory['id'],
                                            "name" => $itemSubCategory['name'],
                                            "french_name" => $itemSubCategory['french_name'], 
                                            "add_on_price" => $itemSubCategory['add_on_price']
                                                    );
                                    
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
                        $orderdetails[$key1]['item_choices'] = $itemChoices;
                        $orderdetails[$key1]['item_french_name'] = $items['french_name'];
                        $orderdetails[$key1]['item_name'] = $items['name'];
                        $orderdetails[$key1]['veg'] = $items['pure_veg'];
                    }

                    if($driverInfo){
                        $ratings = RatingReview::where('receiver_type', '3')
                                        ->where('receiver_id', $driverInfo['id'])
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

                        $driverOrdersCount = Order::where('driver_id', $driverInfo['id'])->where('order_status', '5')->count();
                        $driverName = $driverInfo['name'];
                        $driverImage = $driverInfo['image'];
                        $driverEmail = $driverInfo['email'];
                        $driverPhone = $driverInfo['phone'];
                        $driverLat = $driverInfo['latitude'];
                        $driverLong = $driverInfo['longitude'];
                    }else{
                        $driverOrdersCount = "0";
                        $totalRating = "0";
                        $avergeRating = "0.0";
                        $driverName = "";
                        $driverImage = "";
                        $driverEmail = "";
                        $driverPhone = "";
                        $driverLat = "";
                        $driverLong = "";
                    }

                    $orders['driver_order_count'] = $driverOrdersCount;
                    $orders['driver_average_rating'] = $avergeRating;
                    $orders['driver_total_rating'] = $totalRating;
                    $orders['driver_name'] = $driverName;
                    $orders['driver_image'] = $driverImage;
                    $orders['driver_email'] = $driverEmail;
                    $orders['driver_phone'] = $driverPhone;
                    $orders['driver_lat'] = $driverLat;
                    $orders['driver_long'] = $driverLong;
                    $orders['restaurant_name'] = $restaurantInfo['name'];
                    $orders['restaurant_image'] = $restaurantInfo['image'];
                    $orders['restaurant_phone'] = $restaurantInfo['phone'];
                    $orders['restaurant_cusines'] = $cuisinName;
                    $orders['restaurant_email'] = $restaurantInfo['email'];
                    $orders['restaurant_lat'] = $restaurantInfo['latitude'];
                    $orders['restaurant_long'] = $restaurantInfo['longitude'];
                    $orders['user_name'] = $userInfo['name'];
                    $orders['user_image'] = $userInfo['image'];
                    $orders['user_email'] = $userInfo['email'];
                    $orders['user_phone'] = $userInfo['phone'];
                    $orders['order_details'] = $orderdetails;
                //}  
                if($orders){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Order Found.",
                                                'data' => $orders
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Order Not Found.",
                                                'data' => $orders
                                            ], 400);
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Order Not Found.",
                                            'data' => $orders
                                        ], 400);
            }   
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function availableStatus(Request $request){
        try{
            $rules = [
                        'status' => 'required',//0:online, 1:offline
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
            //echo '<pre>';print_r($user);die;
            $status = $request->status;
            $updateStatus = User::where('id', $user->id)->update(['busy_status' => $status]);
            $data = User::where('id', $user->id)->first();
            if($status == '0'){
                $msg = "Online";
            }else{
                $msg = "Offline";
            }
            if($data['role'] == '3'){
                $receiverType = "3";
            }else{
                $receiverType = "2";
            }

            $ratings = RatingReview::where('receiver_type', $receiverType)
                                            ->where('receiver_id', $data['id'])
                                            ->get()
                                            ->toArray();
            $avergeRating = 0.0;
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
            $data['avg_ratings'] = $avergeRating;
            $data['total_rating'] = (string) $totalRating;

            if($updateStatus){
                return response()->json([
                                            'status' => true,
                                            'message' => $msg,
                                            'data' => $data
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

    public function notificationSetting(Request $request){
        try{
           $rules = [
                        'status' => 'required',
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
            //echo '<pre>';print_r($user);die;
            $status = $request->status;
            $updateStatus = User::where('id', $user->id)->update(['notification' => $status]);
            $data = User::where('id', $user->id)->first();
            if($updateStatus){
                return response()->json([
                                            'status' => true,
                                            'message' => "Notification Status Updated Successfully.",
                                            'data' => $data
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

    public function getWallet(){
        try{
            $user = Auth::user();
            $setting = Setting::where('id', '1')->first();
            $history = array();
            if($user->role == '4'){
                $orders = Order::where('restaurant_id', $user->id)->where('order_status', '5')->get()->toArray();
                //echo'<pre>';print_r($orders);die;
                foreach ($orders as $key => $order) {
                    $appAmount = ($order['final_price']-$order['delivery_fee'])*$setting['app_fee']/100;
                    $amount = round(($order['final_price']-$order['delivery_fee'])-$appAmount,2);
                    $userData = User::where('id', $order['user_id'])->first();
                    //echo'<pre>';print_r($userData['created_at']);die;
                    //$driverData = User::where('id', $order['driver_id'])->first();
                    $history[] = array('order_id' => $order['id'], 'user_name' => $userData['name'], 'user_french_name' => $userData['french_name'], 'user_image' => $userData['image'], 'user_address' => $order['delivery_address'], 'created_at' => $userData['created_at']->toDateTimeString(), 'amount' => $amount);
                }
                //echo'<pre>';print_r($history);die;
            }
            if($user->role == '3'){
                $orders = Order::where('driver_id', $user->id)->where('order_status', '5')->get()->toArray();   
                foreach ($orders as $key => $order) {
                    $userData = User::where('id', $order['user_id'])->first();
                    //$restaurantData = User::where('id', $order['restaurant_id'])->first();
                    $history[] = array('order_id' => $order['id'], 'user_name' => $userData['name'], 'user_french_name' => $userData['french_name'], 'user_image' => $userData['image'], 'user_address' => $order['delivery_address'], 'created_at' => $userData['created_at']->toDateTimeString(), 'amount' => $order['delivery_fee']);
                }
            }
            
            if($user){
                return response()->json([
                                            'status' => true,
                                            'message' => "Wallet Found.",
                                            'wallet' => $user->wallet,
                                            'wallet_id' => $user->username,
                                            'naira_to_points' =>$setting['naira_to_points'],
                                            'data' => $history,
                                            'receipt_id' => $user->receipt_id
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Wallet Not Found.",
                                            'wallet' => $user->wallet,
                                            'wallet_id' => $user->username,
                                            'naira_to_points' =>$setting['naira_to_points'],
                                            'data' => $history,
                                            'receipt_id' => $user->receipt_id
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }


    public function saveAccountDetails(Request $request){
        try{
            $rules = [
                        //'user_id' => 'required',
                        'name' => 'required',
                        'account_number' => 'required',
                        'bank_code' => 'required',
                       // 'amount' => 'required'
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

            $user = User::where('id', $request->user()->id)->first();
            $recipitId = $user->receipt_id;
            $checkAccountDetail = AccountDetail::where('user_id', $user->id)->first();
            if($checkAccountDetail){
                //delete receipt_id and accout details
                //$delete = AccountDetail::where('user_id', $user->id)->first();


                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transferrecipient/{$recipitId}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');


                $headers = array();
                $headers[] = 'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e';
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $data = json_decode($result);
                //$delete = AccountDetail::where('user_id', $user->id)->delete();
                
            }//else{

                $url = 'https://api.paystack.co/transferrecipient';
                $fields = array(
                                    "type" => "nuban",
                                    "name" => $request->name,
                                    "description" => "Creating Account",
                                    "account_number" => $request->account_number,
                                    "bank_code" => $request->bank_code,
                                    "currency" => "NGN",
                                   // "metadata" => array("orderId" => 1)
                                );

                $fields = json_encode($fields);
                $headers = array(
                                    'Authorization: Bearer sk_test_30fef7c57cdc7f4554abce4bce9f0ab7a1cbf44e',
                                    "Content-Type: application/json"
                                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

                $result = curl_exec($ch);
                //curl_close($ch);
                $data = json_decode($result);
                //echo "<pre>";print_r($data);die;
                if($data->status == 1){
                    $recipitId = $data->data->recipient_code;    
                    $user->receipt_id = $recipitId;
                    if($user->save()){
                        if($checkAccountDetail){
                            $checkAccountDetail->user_id = $user->id;
                            $checkAccountDetail->name = $request->name;
                            $checkAccountDetail->account_number = $request->account_number;
                            $checkAccountDetail->bank_code = $request->bank_code;
                            $checkAccountDetail->receipt_id = $recipitId;
                            $checkAccountDetail->save();
                            return response()->json([
                                                        'message' => "Account Details Updated Successfully.",
                                                        'status' => true,
                                                        'data' => $checkAccountDetail
                                                    ], 200);
                        }else{
                            $accountDetails = new AccountDetail;
                            $accountDetails->user_id = $user->id;
                            $accountDetails->name = $request->name;
                            $accountDetails->account_number = $request->account_number;
                            $accountDetails->bank_code = $request->bank_code;
                            $accountDetails->receipt_id = $recipitId;
                            $accountDetails->save();
                            return response()->json([
                                                        'message' => "Account Details Added Successfully.",
                                                        'status' => true,
                                                        'data' => $accountDetails
                                                    ], 200);
                        }
                        
                    }else{
                        return response()->json([
                                                    'message' => "Some Error",
                                                    'status' => false,
                                                    'data' => []
                                                ], 400);
                    }
                }else{
                    return response()->json([
                                                'message' => $data->message,
                                                'status' => false,
                                                'data' => []
                                            ], 400);
                }
            //}
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function getAccountDetails(){
        try{
            $user = Auth::user();
            $accountDetails = AccountDetail::where('user_id', $user->id)->first();
            
            if($accountDetails){
                return response()->json([
                                            'status' => true,
                                            'message' => "Account Details Found.",
                                            'data' => $accountDetails
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Account Details Not Found.",
                                            'data' => $accountDetails
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function getCustomer(){
        try{
            $user = Auth::user();
            $users = User::where('role', '2')
                            ->where('id', '<>', $user->id)
                            ->get()
                            ->toArray();
            if($users){
                return response()->json([
                                            'status' => true,
                                            'message' => "User's Found.",
                                            'data' => $users
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "User's Not Found.",
                                            'data' => $users
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function searchCustomer(Request $request){
        try{
            $rules = [
                        'username' => 'required',
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

            $email = $request->username;
            /*$users = User::where('email', 'LIKE', '%' . $email . '%')
                            ->where('role', '2')
                            ->where('id', '<>', $user->id)
                            ->orWhere('name', 'LIKE', '%' . $email . '%')
                            ->get()
                            ->toArray();*/

            $users = User::where(function ($query) use($email) {
                                    $query->where('role', 2)
                                          ->Where('id', '<>', 1);
                                })->where(function ($query) use($email) {
                                    $query->where('name','LIKE', '%' . $email . '%')
                                          ->orWhere('username','LIKE', '%' . $email . '%');
                                })->get()->toArray();

           // echo "<pre>";print_r($res);die;                    

            if($users){
                return response()->json([
                                            'status' => true,
                                            'message' => "User's Found.",
                                            'data' => $users
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "User's Not Found.",
                                            'data' => $users
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function searchUser(Request $request){
        try{
            $rules = [
                        'username' => 'required',
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

            $username = $request->username;
            

            $users = User::where('username', $username)->first();

           // echo "<pre>";print_r($res);die;                    

            if($users){
                return response()->json([
                                            'status' => true,
                                            'message' => "User Found.",
                                            'data' => $users
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "User Not Found.",
                                            'data' => $users
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function deleteNotification(Request $request){
        try{
            $user = Auth::user();
            if($request->has('notification_id') && !empty($request->notification_id))   {
                
                //Delete Selected
                $delete = Notification::where('id', $request->notification_id)->delete();

                if($delete){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Notification Deleted.",
                                                //'data' => $users
                                            ], 200);
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Something Went Wrong!"
                                            ], 422);
                }

            }else{
                //Delete All
                $delete = Notification::where('user_id', $user->id)->delete();

                if($delete){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Notification Deleted.",
                                                //'data' => $users
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

    public function walletHistory(){
        try{
            $user = Auth::user();

            $accountInfo = AccountDetail::where('user_id', $user->id)
                                            ->select('account_number')
                                            ->first();
            $transactions = Transaction::where('user_id', $user->id)
                                        ->whereIn('type', ["1",'3','4','5','6',"7","8"])
                                        ->orderBy('created_at', 'Desc')
                                        ->get()->toArray();
            $setting = Setting::where('id', '1')->first();
            $usersImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
            $usersName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
            $usersEmail = User::where('role', '<>', '1')->pluck('email', 'id')->toArray();
            $usersAddress = User::where('role', '<>', '1')->pluck('address', 'id')->toArray();
            //echo'<pre>';print_r($transactions);die;
            if($transactions){
                foreach ($transactions as $key => $transaction) {
                    $users = User::where('id', $transaction['user_id'])->first();
                    $otherUser = User::where('id', $transaction['transaction_data'])->first();
                    //$transactions[$key]['wallet'] = $users['wallet'];
                    //$transactions[$key]['wallet_id'] = $users['username'];
                    //$transactions[$key]['naira_to_points'] = $setting['naira_to_points'];
                    $transactions[$key]['user_image'] = $users['image'];
                    $transactions[$key]['user_name'] = $users['name'];
                    $transactions[$key]['user_email'] = $users['email'];
                    $transactions[$key]['user_wallet_id'] = $users['username'];
                    $transactions[$key]['user_account_number'] = $accountInfo['account_number'];
                    if($transaction['type'] == '5' || $transaction['type'] == '6' || $transaction['type'] == '9'){
                        if($transaction['transaction_data']){
                            $transactions[$key]['other_user_image'] = $otherUser['image'];
                            $transactions[$key]['other_user_name'] = $otherUser['name'];
                            $transactions[$key]['other_user_email'] = $otherUser['email'];
                            $transactions[$key]['other_user_wallet_id'] = $otherUser['username'];
                        }else{
                            $transactions[$key]['other_user_image'] = "";
                            $transactions[$key]['other_user_name'] = "";
                            $transactions[$key]['other_user_email'] = "";    
                            $transactions[$key]['other_user_wallet_id'] = "";
                        }
                    }elseif($transaction['type'] == '4'){
                        $order = Order::where('id', $transaction['order_id'])->first();
                        if($order){
                            $transactions[$key]['other_user_image'] = $usersImage[$order['restaurant_id']];
                            $transactions[$key]['other_user_name'] = $usersName[$order['restaurant_id']];
                            $transactions[$key]['other_user_email'] = $usersEmail[$order['restaurant_id']];
                        }else{
                            $transactions[$key]['other_user_image'] = "";
                            $transactions[$key]['other_user_name'] = "";
                            $transactions[$key]['other_user_email'] = "";    
                        }
                    }elseif($transaction['type'] == '7'){
                        $order = Order::where('id', $transaction['order_id'])->first();
                        if($order){
                            $transactions[$key]['customer_image'] = $usersImage[$order['user_id']];
                            $transactions[$key]['customer_name'] = $usersName[$order['user_id']];
                            $transactions[$key]['customer_email'] = $usersEmail[$order['user_id']];
                            $transactions[$key]['customer_address'] = $usersAddress[$order['user_id']];
                            
                        }else{
                            $transactions[$key]['customer_image'] = "";
                            $transactions[$key]['customer_name'] = "";
                            $transactions[$key]['customer_email'] = "";    
                            $transactions[$key]['customer_address'] ="";
                        }
                    }else{
                        $transactions[$key]['other_user_image'] = "";
                        $transactions[$key]['other_user_name'] = "";
                        $transactions[$key]['other_user_email'] = "";
                    }
                }
                $data = [
                            'wallet' => $user->wallet,
                            'wallet_id' => $user->username,
                            'naira_to_points' => $setting['naira_to_points'],
                            'history' => $transactions,
                            'receipt_id' => $user->receipt_id
                            //'account_info' => $accountInfo
                        ];
                return response()->json([
                                            'status' => true,
                                            'message' => "Wallet History Found.",
                                            'data' => $data,
                                            
                                            //'wallet' => $user->wallet,
                                            //'wallet_id' => $user->username
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => true,
                                            'message' => "Wallet History Not Found.",
                                            'data' => [
                                                            'wallet' => $user->wallet,
                                                            'wallet_id' => $user->username,
                                                            'naira_to_points' => $setting['naira_to_points'],
                                                            'history' => $transactions,
                                                            'receipt_id' => $user->receipt_id
                                                        ],

                                            //'wallet' => $user->wallet,
                                            //'wallet_id' => $user->username
                                        ], 200);
            } 

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function sendWalletMoney(Request $request){
        try{
            $rules = [
                        'username' => 'required',
                        'amount' => 'required',
                        //'reason' => 'required',
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

            $user = User::where('id', $request->user()->id)->first();
            $otherUser = User::where('username', $request->username)->where('role', '2')->first();
            if($otherUser){
                if($otherUser['id'] == $user->id){
                    return response()->json([
                                                'status' => false,
                                                'message' => "You Can't transfer to your own wallet."
                                            ], 200);
                }
                //echo $user->wallet;
                //echo'<br>';
                //echo $request->amount;die;
                if($user->wallet > $request->amount){
                    $deductionAmount = $user['wallet']-$request->amount;
                    $user->wallet = $deductionAmount;
                    $user->save();
                    $additionAmount = $otherUser['wallet']+$request->amount;
                    $otherUser->wallet = $additionAmount;
                    if($otherUser->save()){

                        $transaction = new Transaction;
                        $transaction->user_id = $user['id'];
                        //$transaction->order_id = $order->id;
                        $transaction->transaction_data = $otherUser['id'];
                        //$transaction->reference = $request->receipt_id;
                        $transaction->amount = $request->amount;
                        $transaction->type = '5';
                        if($request->has('reason') && !empty($request->reason)){
                            $transaction->reason = $request->reason;
                        }
                        $transaction->save();

                        $transaction = new Transaction;
                        $transaction->user_id = $otherUser['id'];
                        //$transaction->order_id = $order->id;
                        $transaction->transaction_data = $user['id'];
                        //$transaction->reference = $request->receipt_id;
                        $transaction->amount = $request->amount;
                        if($request->has('reason') && !empty($request->reason)){
                            $transaction->reason = $request->reason;
                        }
                        $transaction->type = '6';
                        $transaction->save();

                        if($user->notification == '1'){
                            $amount = $request->amount;
                            $otherUserName = $otherUser->name;
                            $message = "Paid $amount to $otherUserName";
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
                                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '15'));    
                                    }
                                    if($userToken['device_type'] == '1'){
                                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '15'));    
                                    }
                                }
                            }
                        }

                        if($otherUser->notification == '1'){
                            $amount = $request->amount;
                            $userName = $user->name;
                            $message = "$userName sent you $amount.";
                            $frenchMessage = $this->translation($message);
                            if($otherUser->language == '1'){
                                $msg = $message;
                            }else{
                                $msg = $frenchMessage[0];
                            }

                            $userTokens = UserToken::where('user_id', $otherUser->id)->get()->toArray();
                            if($userTokens){
                                foreach ($userTokens as $tokenKey => $userToken) {
                                    if($userToken['device_type'] == '0'){
                                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '16'));    
                                    }
                                    if($userToken['device_type'] == '1'){
                                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '16'));    
                                    }
                                }
                            }
                        }

                        return response()->json([
                                                    'status' => true,
                                                    'message' => "Money Send Successfully.",
                                                    'wallet' => $user->wallet,
                                                    'data' => []
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
                                                'message' => "User's Doesn't Have Sufficient Amount.",
                                                //'data' => $transaction
                                            ], 200);
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "User's Wallet Id Not Found.",
                                            //'data' => $transaction
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function contactUs(Request $request){
        try{
            $rules = [
                        //'order_id' => 'required',
                        'sender_type' => 'required',//1=>user,2=>restaurant,3=>driver
                        'contact_type' => 'required',//1=>complaint,2=>feedback,3=>suggestion
                        'description' => 'required'
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

            $contactUs = new ContactUs;
            if($request->has('order_id') && !empty($request->order_id)){
                if($user->role == '4'){
                    $order = Order::where('id', $request->order_id)
                                    ->where('restaurant_id', $user->id)
                                    ->first();
                    if($order){

                    }else{
                        return response()->json([
                                                    'status' => false,
                                                    'message' => "Invalid OrderId.",
                                                    //'data' => $order
                                                ], 200);
                    }
                }

                if($user->role == '3'){
                    $order = Order::where('id', $request->order_id)
                                    ->where('driver_id', $user->id)
                                    ->first();

                    if($order){

                    }else{
                        return response()->json([
                                                    'status' => false,
                                                    'message' => "Invalid OrderId.",
                                                    //'data' => $order
                                                ], 200);
                    }
                }                
                $contactUs->order_id = $request->order_id;
            }
            $contactUs->sender_type = $request->sender_type;
            $contactUs->contact_type = $request->contact_type;
            $contactUs->description = $request->description;
            if($contactUs->save()){
                return response()->json([
                                            'status' => true,
                                            'message' => "You Request Successfully Send to Admin.",
                                            //'data' => $order
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

    // public function customerHomeScreen(Request $request){
    //     try{
    //         $rules = [
    //                     'filter_id' => 'required',//comma seperated
    //                     'cuisine_id' => 'required',
    //                     //'user_id' => 'required',//required when login
    //                     'latitude' => 'required',
    //                     'longitude' => 'required',
    //                     //'price_range' => 'required',//comma seperated
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

    //         if($request->filter_id == '0'){
    //             $filterId = $request->filter_id;
    //             $filterIds = [];
    //         }else{
    //             $filterIds = explode(',', $request->filter_id);
    //             $filterId = "";
    //         }
    //         $cuisineId = $request->cuisine_id;
    //         $lat = $request->latitude;
    //         $long = $request->longitude;
    //         if($request->has('price_range') && !empty($request->price_range)){
    //             $priceRange = explode(',', $request->price_range);
    //         }else{
    //             $priceRange = [];
    //         }

    //         if($request->has('rating_range') && !empty($request->rating_range)){
    //             $ratingRange = $request->rating_range;
    //         }else{
    //             $ratingRange = '0.0';
    //         }

    //         $user = Auth::user();

    //         $setting = Setting::where('id', '1')->first();

    //         $finalData = array();
    //         $checkcart = Cart::where('user_id', $user->id)->where('group_order', '<>', '1')->where('status', '1')->first();
    //         //return $checkcart;
    //         if($checkcart){
    //             $restaurant = User::where('id', $checkcart->restaurant_id)->first();
    //             $checkcart['restaurant_name'] = $restaurant->name;
    //             if($checkcart['group_order'] == '1'){
    //                 $finalData['is_cart'] = null;
    //             }else{
    //                 $finalData['is_cart'] = $checkcart;
    //             }
    //         }else{
    //             $finalData['is_cart'] = null;
    //         }
            
    //         $finalData['base_delivery_fee'] = $setting['delivery_fee'];
    //         $finalData['min_order_vale'] = $setting['min_order'];
    //         $finalData['min_kilo_meter'] = $setting['min_km'];
    //         $finalData['wallet'] = $user->wallet;

    //         $filters = Filter::where('status', '1')->get()->toArray();
    //         foreach ($filters as $key => $filter) {

    //             if($filter['id'] == '4'){
    //                 $filters[$key]['multi_selected'] = $request->price_range;
    //             }

    //             if($filter['id'] == '1'){
    //                 $filters[$key]['multi_selected'] = "";   
    //             }

    //             if(in_array($filter['id'], $filterIds)){
    //                 $filters[$key]['selected'] = true;
    //             }else{
    //                 $filters[$key]['selected'] = false;
    //             }

    //         }
    //         $finalData['filters'] = $filters;

    //         $promos = Promocode::where('status', '1')->get()->toArray();
    //         $finalData['promos'] = $promos;

    //         $cuisines = Cuisine::where('status', '1')->get()->toArray();
    //         foreach ($cuisines as $key1 => $cuisine) {
    //             if($cuisineId == $cuisine['id']){
    //                 $cuisines[$key1]['selected'] = true;
    //             }else{
    //                 $cuisines[$key1]['selected'] = false;
    //             }
    //         }
    //         $finalData['cuisines'] = $cuisines;

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
    //                 $item = Item::where('restaurant_id', $restaurantsUnderLoc->id)->first();
    //                 if($item){
    //                     $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
    //                 }
    //             }
    //         }

    //         $customizedData = FilterList::where('status', '1')->get()->toArray();
    //         foreach ($customizedData as $key2 => $data) {
    //             //1:Your Favourite, 2:New in Grigora, 3:Order Again, 4:Popular, 5:Near By, 6:Top Cuisine

    //             if($data['id'] == '1'){ 
    //                 //Your Favourite
    //                 $FavouriteRestaurants = array();
    //                 //if($request->has('user_id') && !empty($request->user_id)){
    //                 if($filterId == '0' && $cuisineId == '0'){
    //                     //return $restaurantsUnderLocationIds;
    //                     $restaurantIds = Favourite::where('user_id', $user->id)
    //                                                 //->limit(5)
    //                                                 ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                     //return $restaurantIds;
    //                     $FavouriteRestaurants = User::whereIn('id', $restaurantIds)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->limit('5')
    //                                                 ->get()
    //                                                 ->toArray();
                        
    //                 }elseif($filterIds && $cuisineId == '0'){
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $restaurantIds = Favourite::where('user_id', $user->id)
    //                                                 //->limit(5)
    //                                                 ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                     //return $restaurantIds;
    //                         $FavouriteRestaurants1 = User::whereIn('id', $restaurantIds)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->limit('5')
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $FavouriteRestaurants1 = array();                            
    //                     }
    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $restaurantIds = Favourite::where('user_id', $user->id)
    //                                                     //->limit(5)
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();

    //                         $FavouriteRestaurants2 = User::whereIn('id', $restaurantIds)
    //                                                         ->where('pickup', '1')
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit('5')
    //                                                         ->get()
    //                                                         ->toArray(); 
    //                     }else{
    //                         $FavouriteRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $restaurantIds = Favourite::where('user_id', $user->id)
    //                                                     //->limit(5)
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();

    //                         $FavouriteRestaurants3 = User::whereIn('id', $restaurantIds)
    //                                                     ->where('pure_veg', '1')
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit('5')
    //                                                     ->get()
    //                                                     ->toArray();    
    //                     }else{
    //                         $FavouriteRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price 1:below 10, 2:below 100, 3:below 1000
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $FavouriteRestaurants4 = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit('5')
    //                                                     ->get()
    //                                                     ->toArray();

    //                     }else{
    //                         $FavouriteRestaurants4 = array();
    //                     }

    //                     $FavouriteRestaurants = array_merge($FavouriteRestaurants1, $FavouriteRestaurants2, $FavouriteRestaurants3, $FavouriteRestaurants4);

    //                 }elseif($filterId == '0' && $cuisineId != '0'){
    //                     $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                             ->where('approved', '1')
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();

    //                     $restaurantIds2 = Favourite::where('user_id', $request->user_id)
    //                                                     //->limit(5)
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();

    //                     $restaurantIds = array_intersect($restaurantIds1, $restaurantIds2);
    //                     $FavouriteRestaurants = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit('5')
    //                                                     ->get()
    //                                                     ->toArray();

    //                 }else{
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $restaurantIds = Favourite::where('user_id', $user->id)
    //                                                 //->limit(5)
    //                                                 ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                     //return $restaurantIds;
    //                         $FavouriteRestaurants1 = User::whereIn('id', $restaurantIds)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->limit('5')
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $FavouriteRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $restaurantIds1 = Favourite::where('user_id', $request->user_id)
    //                                                     //->limit(5)
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         $restaurantIds2 = Item::where('cuisine_id', $cuisineId)
    //                                             ->where('approved', '1')
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);

    //                         $FavouriteRestaurants2 = User::whereIn('id', $restaurantIds)
    //                                                         ->where('pickup', '1')
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit('5')
    //                                                         ->get()
    //                                                         ->toArray();

    //                     }else{
    //                         $FavouriteRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $restaurantIds1 = Favourite::where('user_id', $request->user_id)
    //                                                     //->limit(5)
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();

    //                         $restaurantIds2 = Item::where('cuisine_id', $cuisineId)
    //                                             ->where('approved', '1')
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);

    //                         $FavouriteRestaurants3 = User::whereIn('id', $restaurantIds)
    //                                                     ->where('pure_veg', '1')
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit('5')
    //                                                     ->get()
    //                                                     ->toArray(); 
    //                     }else{
    //                         $FavouriteRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $FavouriteRestaurants4 = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit('5')
    //                                                     ->get()
    //                                                     ->toArray(); 
    //                     }else{
    //                         $FavouriteRestaurants4 = array();
    //                     }
    //                     $FavouriteRestaurants = array_merge($FavouriteRestaurants1, $FavouriteRestaurants2, $FavouriteRestaurants3, $FavouriteRestaurants4) ;
    //                 }
    //                 //}
    //                 //return $FavouriteRestaurants;
    //                 $customizedData[$key2]['restaurants'] = $FavouriteRestaurants;
    //             }elseif($data['id'] == '2'){
    //                 //New in Grigora
    //                 //echo'<pre>';print_r($restaurantsUnderLocation);die;
    //                 if($filterId == '0' && $cuisineId == '0'){
    //                     $newRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }elseif($filterIds && $cuisineId == '0'){
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $newRestaurants1 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $newRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $newRestaurants2 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->where('pickup', '1')
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $newRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $newRestaurants3 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->where('pure_veg', '1')
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $newRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price

    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $newRestaurants4 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $newRestaurants4 = array();
    //                     }

    //                     $newRestaurants = array_merge($newRestaurants1, $newRestaurants2, $newRestaurants3, $newRestaurants4);

    //                 }elseif($filterId == '0' && $cuisineId != '0'){
    //                     $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                     $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                     $newRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }else{
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $newRestaurants1 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $newRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $newRestaurants2 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('id', $restaurantIds)
    //                                                 ->where('pickup', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $newRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $newRestaurants3 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('id', $restaurantIds)
    //                                                 ->where('pure_veg', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $newRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $newRestaurants4 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('id', $restaurantIds)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $newRestaurants4 = array();
    //                     }

    //                     $newRestaurants = array_merge($newRestaurants1, $newRestaurants2, $newRestaurants3, $newRestaurants4);
    //                 }
    //                 //echo'<pre>';print_r($newRestaurants);die;
    //                 $customizedData[$key2]['restaurants'] = $newRestaurants;
    //             }elseif($data['id'] == '3'){
    //                 //Order Again
    //                 $pastOrderedRestaurants = array();
    //                 //if($request->has('user_id') && !empty($user->id)){
    //                 if($filterId == '0' && $cuisineId == '0'){
    //                     $restaurantIds = Order::where('user_id', $user->id)
    //                                             ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                             ->where('order_status', '5')
    //                                             //->limit(5)
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();
    //                     //return $restaurantIds;
    //                     $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     //return $pastOrderedRestaurants;
    //                 }elseif($filterId != '0' && $cuisineId == '0'){
    //                     if($filterId == '1'){
    //                         //rating
    //                         $restaurantIds = Order::where('user_id', $user->id)
    //                                             ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                             ->where('order_status', '5')
    //                                             //->limit(5)
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();
    //                     //return $restaurantIds;
    //                     $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     }elseif($filterId == '2'){
    //                         //pickup
    //                         $restaurantIds = Order::where('user_id', $user->id)
    //                                                 ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->where('order_status', '5')
    //                                                 //->limit(5)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                         $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                         ->where('pickup', '1')
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit(5)
    //                                                         ->get()
    //                                                         ->toArray();
    //                     }elseif($filterId == '3'){
    //                         //veg
    //                         $restaurantIds = Order::where('user_id', $user->id)
    //                                                 ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->where('order_status', '5')
    //                                                 //->limit(5)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                         $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                         ->where('pure_veg', '1')
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit(5)
    //                                                         ->get()
    //                                                         ->toArray();
    //                     }else{
    //                         //price
    //                         if($priceRange == '1'){
    //                             $restaurantIds = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }elseif($priceRange == '2') {
    //                             $restaurantIds = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }elseif($priceRange == '3') {
    //                             $restaurantIds = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds = array();
    //                         }

    //                         $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit(5)
    //                                                         ->get()
    //                                                         ->toArray();
    //                     }
    //                 }elseif($filterId == '0' && $cuisineId != '0'){
    //                     $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                             ->where('approved', '1')
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();

    //                     $restaurantIds2 = Order::where('user_id', $user->id)
    //                                             ->where('restaurant_id', $restaurantsUnderLocationIds)
    //                                             ->where('order_status', '5')
    //                                             //->limit(5)
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();
    //                     $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);
    //                     $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                 }else{
    //                     if($filterId == '1'){
    //                         //rating
    //                         $restaurantIds = Order::where('user_id', $user->id)
    //                                             ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                             ->where('order_status', '5')
    //                                             //->limit(5)
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();
    //                     //return $restaurantIds;
    //                     $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     }elseif($filterId == '2'){
    //                         //pickup
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                             ->where('approved', '1')
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();

    //                         $restaurantIds2 = Order::where('user_id', $user->id)
    //                                                 ->where('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->where('order_status', '5')
    //                                                 //->limit(5)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);
    //                         $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                         ->where('pickup', '1')
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit(5)
    //                                                         ->get()
    //                                                         ->toArray();
    //                     }elseif($filterId == '3'){
    //                         //veg
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                             ->where('approved', '1')
    //                                             ->pluck('restaurant_id')
    //                                             ->toArray();

    //                         $restaurantIds2 = Order::where('user_id', $user->id)
    //                                                 ->where('restaurant_id', $restaurantsUnderLocationIds)
    //                                                 ->where('order_status', '5')
    //                                                 //->limit(5)
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();
    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);
    //                         $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                         ->where('pure_veg', '1')
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit(5)
    //                                                         ->get()
    //                                                         ->toArray();
    //                     }else{
    //                         //price
    //                         if($priceRange == '1'){
    //                             $restaurantIds = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }elseif($priceRange == '2') {
    //                             $restaurantIds = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }elseif($priceRange == '3') {
    //                             $restaurantIds = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds = array();
    //                         }

    //                         $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
    //                                                         ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                         ->limit(5)
    //                                                         ->get()
    //                                                         ->toArray();

    //                     }
    //                 }

    //                 //}
    //                 //die('hi');
    //                 //return $pastOrderedRestaurants;
    //                 $customizedData[$key2]['restaurants'] = $pastOrderedRestaurants;
    //             }elseif($data['id'] == '4'){
    //                 //Popular
    //                 if($filterId == '0' && $cuisineId == '0'){
    //                     $nearByRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }elseif($filterIds && $cuisineId == '0'){
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $nearByRestaurants1 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $nearByRestaurants2 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->where('pickup', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $nearByRestaurants3 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->where('pure_veg', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $nearByRestaurants4 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants4 = array();
    //                     }

    //                     $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);

    //                 }elseif($filterId == '0' && $cuisineId != '0'){
    //                     $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                     $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                     $nearByRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->where('id', $restaurantIds)
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }else{
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $nearByRestaurants1 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $nearByRestaurants2 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->where('pickup', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $nearByRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $nearByRestaurants3 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->where('pure_veg', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $nearByRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $nearByRestaurants4 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->orderBy('id', 'DESC')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $nearByRestaurants4 = array();
    //                     }

    //                     $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);
    //                 }
    //                 $customizedData[$key2]['restaurants'] = $nearByRestaurants;
    //             }elseif($data['id'] == '5'){
    //                 //Near By
    //                 if($filterId == '0' && $cuisineId == '0'){
    //                     $nearByRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }elseif($filterIds && $cuisineId == '0'){
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $nearByRestaurants1 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $nearByRestaurants2 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->where('pickup', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $nearByRestaurants3 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->where('pure_veg', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $nearByRestaurants4 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants4 = array();
    //                     }

    //                     $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);

    //                 }elseif($filterId == '0' && $cuisineId != '0'){
    //                     $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                     $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                     $nearByRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->where('id', $restaurantIds)
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }else{
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $nearByRestaurants1 = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('id', $restaurantsUnderLocationIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                     }else{
    //                         $nearByRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $nearByRestaurants2 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->where('pickup', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $nearByRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $nearByRestaurants3 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->where('pure_veg', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $nearByRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $nearByRestaurants4 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->orderBy('id', 'DESC')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $nearByRestaurants4 = array();
    //                     }

    //                     $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);
    //                 }
    //                 $customizedData[$key2]['restaurants'] = $nearByRestaurants;
    //             }elseif($data['id'] == '6'){
    //                 //Top Cuisine
    //                 //$topCuisineRestaurants = $cuisines;
    //                 $topCuisineRestaurants = Cuisine::where('status', '1')
    //                                     ->inRandomOrder()
    //                                     ->limit(5)
    //                                     ->get()
    //                                     ->toArray();
    //                 if($filterId == '0' && $cuisineId == '0'){

    //                 }elseif($filterId != '0' && $cuisineId == '0'){
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                     }else{

    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                     }else{

    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                     }else{

    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                     }else{

    //                     }

    //                 }elseif($filterId == '0' && $cuisineId != '0'){

    //                 }else{
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                     }else{

    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                     }else{

    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                     }else{

    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                     }else{

    //                     }
    //                 }
    //                 $customizedData[$key2]['restaurants'] = $topCuisineRestaurants;
    //             }elseif($data['id'] == '7'){
    //                 //Top Brands
                    
    //                 if($filterId == '0' && $cuisineId == '0'){
    //                     $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                     $topBrandsRestaurants = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('id', $restaurantsUnderLocationIds)
    //                                                 ->whereIn('name', $allBrandNames)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                 }elseif($filterIds && $cuisineId == '0'){
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants1 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('id', $restaurantsUnderLocationIds)
    //                                                 ->whereIn('name', $allBrandNames)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants2 = User::where('role', '4')
    //                                                     ->where('approved', '1')
    //                                                     ->where('pickup', '1')
    //                                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                                     ->whereIn('name', $allBrandNames)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->orderBy('id', 'Desc')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants3 = User::where('role', '4')
    //                                                     ->where('approved', '1')
    //                                                     ->where('pure_veg', '1')
    //                                                     ->whereIn('id', $restaurantsUnderLocationIds)
    //                                                     ->whereIn('name', $allBrandNames)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->orderBy('id', 'Desc')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants4 = User::where('role', '4')
    //                                                     ->where('approved', '1')
    //                                                     ->where('pure_veg', '1')
    //                                                     ->whereIn('id', $restaurantIds)
    //                                                     ->whereIn('name', $allBrandNames)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->orderBy('id', 'Desc')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants4 = array();
    //                     }

    //                     $topBrandsRestaurants = array_merge($topBrandsRestaurants1, $topBrandsRestaurants2, $topBrandsRestaurants3, $topBrandsRestaurants4);

    //                 }elseif($filterId == '0' && $cuisineId != '0'){
    //                     $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                     $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                     $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                     $topBrandsRestaurants = User::where('role', '4')
    //                                             ->where('approved', '1')
    //                                             ->whereIn('name', $allBrandNames)
    //                                             ->where('id', $restaurantIds)
    //                                             ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                             ->orderBy('id', 'Desc')
    //                                             ->limit(5)
    //                                             ->get()
    //                                             ->toArray();
    //                 }else{
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants1 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('id', $restaurantsUnderLocationIds)
    //                                                 ->whereIn('name', $allBrandNames)
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants1 = array();
    //                     }

    //                     if(in_array('2', $filterIds)){
    //                         //pickup
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants2 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('name', $allBrandNames)
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->where('pickup', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants2 = array();
    //                     }

    //                     if(in_array('3', $filterIds)){
    //                         //veg
    //                         $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
    //                                                 ->where('approved', '1')
    //                                                 ->pluck('restaurant_id')
    //                                                 ->toArray();

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants3 = User::where('role', '4')
    //                                                 ->where('approved', '1')
    //                                                 ->whereIn('name', $allBrandNames)
    //                                                 ->where('id', $restaurantIds)
    //                                                 ->where('pure_veg', '1')
    //                                                 ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                 ->orderBy('id', 'Desc')
    //                                                 ->limit(5)
    //                                                 ->get()
    //                                                 ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants3 = array();
    //                     }

    //                     if(in_array('4', $filterIds)){
    //                         //price
    //                         if(in_array('1', $priceRange)){
    //                             $restaurantIds1 = Item::where('price', '<=', '10')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds1 = array();
    //                         }

    //                         if(in_array('2', $priceRange)){
    //                             $restaurantIds2 = Item::where('price', '<=', '100')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->where('approved', '1')
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds2 = array();
    //                         }

    //                         if(in_array('3', $priceRange)){
    //                             $restaurantIds3 = Item::where('price', '<=', '1000')
    //                                                     ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
    //                                                     ->where('cuisine_id', $cuisineId)
    //                                                     ->pluck('restaurant_id')
    //                                                     ->toArray();
    //                         }else{
    //                             $restaurantIds3 = array();
    //                         }

    //                         $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

    //                         $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
    //                         $topBrandsRestaurants4 = User::where('role', '4')
    //                                                     ->where('approved', '1')
    //                                                     ->where('pure_veg', '1')
    //                                                     ->whereIn('id', $restaurantIds)
    //                                                     ->whereIn('name', $allBrandNames)
    //                                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                                     ->orderBy('id', 'Desc')
    //                                                     ->limit(5)
    //                                                     ->get()
    //                                                     ->toArray();
    //                     }else{
    //                         $topBrandsRestaurants4 = array();
    //                     }
    //                 }
    //                 $customizedData[$key2]['restaurants'] = $topBrandsRestaurants;
    //             }
    //             // elseif($data['id'] == '8'){
    //             //     //Trending
    //             //     $itemids = OrderDetail::orderBy('id', 'Desc')
    //             //                             ->groupBy('item_id')
    //             //                             ->pluck('item_id', 'item_id')
    //             //                             ->toArray();

    //             //     $trendingItems = Item::whereIn('id', $itemids)
    //             //                             ->limit(6)
    //             //                             ->get()
    //             //                             ->toArray();
    //             //     if($trendingItems){
    //             //         $appFee = $setting['app_fee'];
    //             //         foreach ($trendingItems as $keyT => $trendingItem) {
    //             //             $cuisins = Cuisine::where('id', $trendingItem['cuisine_id'])->first();
    //             //             $oldprice = $trendingItem['price'];
    //             //             $appPrice = $oldprice*$appFee/100;
    //             //             $trendingItems[$keyT]['price'] = round($oldprice+$appPrice, 2);
    //             //             $trendingItems[$keyT]['cuisine_name'] = $cuisins['name'];

    //             //             $restaurantInfo = User::where('id', $trendingItem['restaurant_id'])->first();
    //             //             $trendingItems[$keyT]['restaurant_name'] = $restaurantInfo['name'];
    //             //             $trendingItems[$keyT]['restaurant_french_name'] = $restaurantInfo['french_name'];

    //             //             $itemCategories = ItemCategory::where('item_id', $trendingItem['id'])->get()->toArray();
    //             //             if($itemCategories){
    //             //                 foreach ($itemCategories as $keyC => $itemCategorie) {
    //             //                     $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
    //             //                     $itemCategories[$keyC]['item_sub_category'] = $itemSubCat;
    //             //                 }
    //             //                 $trendingItems[$keyT]['item_categories'] = $itemCategories;
    //             //             }else{
    //             //                 $trendingItems[$keyT]['item_categories'] = array();
    //             //             }

    //             //             $ratings = RatingReview::where('receiver_type', '1')
    //             //                                     ->where('receiver_id', $trendingItem['id'])
    //             //                                     ->get()
    //             //                                     ->toArray();
    //             //             $avergeRating = "0.0";
    //             //             if($ratings){
    //             //                 $ratingArr = array();
    //             //                 foreach ($ratings as $key2 => $rating) {
    //             //                     $ratingArr[] = $rating['rating'];
    //             //                 }
    //             //                 $totalRating = count($ratings);
    //             //                 $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
    //             //             }else{
    //             //                 $totalRating = "0";
    //             //             }
    //             //             $trendingItems[$keyT]['avg_ratings'] = $avergeRating;
    //             //             $trendingItems[$keyT]['total_rating'] = $totalRating;

    //             //             if($checkcart){
    //             //                 $cartItemsDetailCount = CartItemsDetail::where('cart_id', $checkcart['id'])
    //             //                                                 ->where('user_id', $user->id)
    //             //                                                 ->where('item_id', $trendingItem['id'])
    //             //                                                 ->sum('quantity');
    //             //                 $trendingItems[$keyT]['item_count_in_cart'] = $cartItemsDetailCount;
    //             //             }else{
    //             //                 $trendingItems[$keyT]['item_count_in_cart'] = 0;
    //             //             }
    //             //         }
    //             //     }else{
    //             //         $trendingItems = array();
    //             //     }
    //             //     $customizedData[$key2]['restaurants'] = $trendingItems;
    //             // }
                
    //         }
    //         //echo "<pre>";print_r($customizedData);die;
    //         foreach ($customizedData as $k1 => $customizedDat) {
    //             //echo'<pre>';print_r($customizedDat);die;
    //             if(!empty($customizedDat['restaurants'])){
    //                 $filteredRestaurants = array();
    //                 foreach($customizedDat['restaurants'] as $k2 => $restaurant){
                        
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
    //                     $customizedData[$k1]['restaurants'][$k2]['average_rating'] = $avergeRatings;
    //                     $customizedData[$k1]['restaurants'][$k2]['total_rating'] = count($ratingReview);

    //                     $restaurant['average_rating'] = $avergeRatings;
    //                     $restaurant['total_rating'] = count($ratingReview);

                        
    //                     if(in_array('1', $filterIds)){
    //                         //rating
    //                         if($ratingRange == '0.0'){
    //                             //all data
    //                             //$filteredRestaurants[] = $restaurant;
    //                         }else{

    //                             //filtered data
    //                             if($avergeRatings >= $ratingRange){
    //                                 $filteredRestaurants[] = $restaurant;  
    //                             }
    //                             /*if($ratingRange <= $avergeRatings){
    //                               $customizedData[$k1]["restaurants"][] = $restaurant;
    //                             }*/
    //                         }
    //                     }else{  
    //                         //all data
    //                         $filteredRestaurants[] = $restaurant;
    //                     }

    //                    // echo "<pre>";print_r($filteredRestaurants);die;
    //                 }
    //                 //echo "<pre>";print_r($filteredRestaurants);
    //                 $customizedData[$k1]["restaurants"] = $filteredRestaurants;
    //             }
    //         }
    //         //die;
    //         //echo'<pre>';print_r($customizedData);die;
    //         $finalData['customized_data'] = $customizedData;

    //         $allRestaurants = User::whereIn('id', $restaurantsUnderLocationIds)
    //                                     ->where('role', '4')
    //                                     ->where('approved', '1')
    //                                     ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
    //                                     ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
    //                                     ->get()
    //                                     ->toArray(); 

    //         if($allRestaurants){
    //             foreach ($allRestaurants as $keyRes => $allRestaurant) {
    //                 $ratings = RatingReview::where('receiver_type', '2')
    //                                                     ->where('receiver_id', $allRestaurant['id'])
    //                                                     ->get()
    //                                                     ->toArray();
    //                 $avergeRating = "0.0";
    //                 if($ratings){
    //                     $ratingArr = array();
    //                     foreach ($ratings as $key1 => $rating) {
    //                         $ratingArr[] = $rating['rating'];
    //                     }
    //                     $totalRating = count($ratings);
    //                     $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
    //                 }else{
    //                     $totalRating = "0";
    //                 }
    //                 $allRestaurants[$keyRes]['average_rating'] = $avergeRating;
    //                 $allRestaurants[$keyRes]['total_rating'] = $totalRating;

    //                 $items = Item::where('restaurant_id', $allRestaurant['id'])
    //                                 ->where('approved', '1')
    //                                 ->select('id', 'image')
    //                                 ->limit(5)
    //                                 ->get();
    //                 $allRestaurants[$keyRes]['items'] = $items;
    //             }
    //         }

    //         $finalData['all_restaurants'] = $allRestaurants;

    //         //Notifications true or false boolean value passed to check if any unread notifications exists
    //         $user = Auth::user();
    //         $notifications = Notification::where('user_id', $user->id)
    //                                         ->where('read','0')
    //                                         ->orderBy('id', 'Desc')
    //                                         ->get()
    //                                         ->toArray();
    //         if(empty($notifications)){
    //             $notifications_unread = false;
    //         }else{
    //             $notifications_unread = true;
    //         }
    //         $finalData['notifications'] = $notifications_unread;
    //         return response()->json([
    //                                     'status' => true,
    //                                     'message' => "Data Found Successfully.",
    //                                     'data' => $finalData
    //                                 ], 200);

    //     }catch (Exception $e) {
    //         return response()->json([
    //                                     'status' => false,
    //                                     'message' => "Something Went Wrong!"
    //                                 ], 422);
    //     }
    // }

    public function customerHomeScreen(Request $request){
        try{
            $rules = [
                        'filter_id' => 'required',//comma seperated
                        'cuisine_id' => 'required',
                        'user_id' => 'required',//required when login
                        'latitude' => 'required',
                        'longitude' => 'required',
                        'login_type' => 'required', // with login ,2 without login
                        //'price_range' => 'required',//comma seperated
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

            if($request->login_type == 2){
                $withoutlogin_deviceId = $request->user_id;
                $request->user_id = 0;
            }else{
                $withoutlogin_deviceId = "";
            }

            if($request->filter_id == '0'){
                $filterId = $request->filter_id;
                $filterIds = [];
            }else{
                $filterIds = explode(',', $request->filter_id);
                $filterId = "";
            }
            $cuisineId = $request->cuisine_id;
            $lat = $request->latitude;
            $long = $request->longitude;
            if($request->has('price_range') && !empty($request->price_range)){
                $priceRange = explode(',', $request->price_range);
            }else{
                $priceRange = [];
            }

            if($request->has('rating_range') && !empty($request->rating_range)){
                $ratingRange = $request->rating_range;
            }else{
                $ratingRange = '0.0';
            }

            // $user = Auth::user();

            $setting = Setting::where('id', '1')->first();

            $finalData = array();
            if(($request->login_type == 2 && !empty($withoutlogin_deviceId)) || $request->user_id != '0'){
                if($request->login_type == 2){
                    $checkcart = Cart::where('user_id', $withoutlogin_deviceId)->where('group_order', '<>', '1')->where('status', '1')->first();
                }else{
                 $checkcart = Cart::where('user_id', $request->user_id)->where('group_order', '<>', '1')->where('status', '1')->first();
                }
                //echo $withoutlogin_deviceId;
                //echo "<pre>";print_r($checkcart);die;
                if($checkcart){
                    $restaurant = User::where('id', $checkcart->restaurant_id)->first();
                    $checkcart['restaurant_name'] = $restaurant->name;
                    if($checkcart['group_order'] == '1'){
                        $finalData['is_cart'] = null;
                    }else{
                        $finalData['is_cart'] = $checkcart;
                    }
                }else{
                    $finalData['is_cart'] = null;
                }
            }else{
                $finalData['is_cart'] = null;
            }
            if($request->user_id != '0'){
              $userdata =  User::where('id',$request->user_id)->first();
              $wallet = $userdata['wallet'];
            }else{
                $wallet = '0.00';
            }

            $offline =  CompanyOffline::where('name','CompanyOffline')->first();
            $company_offline = $offline->status;

            $drivers_offlineStatus = User::where('role','3')->where('busy_status','0')->first();
            if($drivers_offlineStatus){
                 $driverstatus = true;
            }else{
                $driverstatus = false;
            }
            //$finalData['company_status'] = $company_offline;
            $finalData['driver_status'] = $driverstatus;
            
            $finalData['base_delivery_fee'] = $setting['delivery_fee'];
            $finalData['min_order_vale'] = $setting['min_order'];
            $finalData['min_kilo_meter'] = $setting['min_km'];
            $finalData['wallet'] = $wallet;

            $filters = Filter::where('status', '1')->get()->toArray();
            foreach ($filters as $key => $filter) {

                if($filter['id'] == '4'){
                    $filters[$key]['multi_selected'] = $request->price_range;
                }

                if($filter['id'] == '1'){
                    $filters[$key]['multi_selected'] = "";   
                }

                if(in_array($filter['id'], $filterIds)){
                    $filters[$key]['selected'] = true;
                }else{
                    $filters[$key]['selected'] = false;
                }

            }
            $finalData['filters'] = $filters;

            $promos = Promocode::where('status', '1')->get()->toArray();
            $finalData['promos'] = $promos;

            $cuisines = Cuisine::where('status', '1')->get()->toArray();
            foreach ($cuisines as $key1 => $cuisine) {
                if($cuisineId == $cuisine['id']){
                    $cuisines[$key1]['selected'] = true;
                }else{
                    $cuisines[$key1]['selected'] = false;
                }
            }
            $finalData['cuisines'] = $cuisines;

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
                    $item = Item::where('restaurant_id', $restaurantsUnderLoc->id)->first();
                    if($item){
                        $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
                    }
                }
            }

            $customizedData = FilterList::where('status', '1')->get()->toArray();
            foreach ($customizedData as $key2 => $data) {
                //1:Your Favourite, 2:New in Grigora, 3:Order Again, 4:Popular, 5:Near By, 6:Top Cuisine

                if($data['id'] == '1'){ 
                    //Your Favourite
                    $FavouriteRestaurants = array();
                    //if($request->has('user_id') && !empty($request->user_id)){
                    if($filterId == '0' && $cuisineId == '0'){
                        //return $restaurantsUnderLocationIds;
                        if($request->user_id != '0'){
                            $restaurantIds = Favourite::where('user_id', $request->user_id)
                                                    //->limit(5)
                                                    ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                            //return $restaurantIds;
                            $FavouriteRestaurants = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray();
                        }else{
                            $FavouriteRestaurants = array(); 
                        }
                        
                        
                    }elseif($filterIds && $cuisineId == '0'){
                        if(in_array('1', $filterIds)){
                            //rating
                             if($request->user_id != '0'){

                                $restaurantIds = Favourite::where('user_id', $request->user_id)
                                                    //->limit(5)
                                                    ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                                //return $restaurantIds;
                                $FavouriteRestaurants1 = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray();

                             }else{
                                $FavouriteRestaurants1 = array();
                             }
                            
                        }else{
                            $FavouriteRestaurants1 = array();                            
                        }
                        if(in_array('2', $filterIds)){


                            //pickup
                            if($request->user_id != '0'){
                                    $restaurantIds = Favourite::where('user_id', $request->user_id)
                                                        //->limit(5)
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();

                            $FavouriteRestaurants2 = User::whereIn('id', $restaurantIds)
                                                            ->where('pickup', '1')
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit('5')
                                                            ->get()
                                                            ->toArray();
                            }else{
                                $FavouriteRestaurants2 = array();
                            }
                         
                        }else{
                            $FavouriteRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            if($request->user_id != '0'){
                                $restaurantIds = Favourite::where('user_id', $request->user_id)
                                                        //->limit(5)
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();

                            $FavouriteRestaurants3 = User::whereIn('id', $restaurantIds)
                                                        ->where('pure_veg', '1')
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray(); 
                            }else{
                                $FavouriteRestaurants3 = array();
                            }
                               
                        }else{
                            $FavouriteRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price 1:below 10, 2:below 100, 3:below 1000
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $FavouriteRestaurants4 = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray();

                        }else{
                            $FavouriteRestaurants4 = array();
                        }

                        $FavouriteRestaurants = array_merge($FavouriteRestaurants1, $FavouriteRestaurants2, $FavouriteRestaurants3, $FavouriteRestaurants4);

                    }elseif($filterId == '0' && $cuisineId != '0'){
                        $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                ->where('approved', '1')
                                                ->pluck('restaurant_id')
                                                ->toArray();
                        if($request->user_id != '0'){
                        $restaurantIds2 = Favourite::where('user_id', $request->user_id)
                                                        //->limit(5)
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                        }else{
                          $restaurantIds2 = array();  
                        }

                        $restaurantIds = array_intersect($restaurantIds1, $restaurantIds2);
                        $FavouriteRestaurants = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray();

                    }else{
                        if(in_array('1', $filterIds)){
                            //rating
                            if($request->user_id != '0'){
                            $restaurantIds = Favourite::where('user_id', $request->user_id)
                                                    //->limit(5)
                                                    ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                        //return $restaurantIds;
                            $FavouriteRestaurants1 = User::whereIn('id', $restaurantIds)
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->limit('5')
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $FavouriteRestaurants1 = array();
                        }
                        }else{
                            $FavouriteRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            if($request->user_id != '0'){
                            $restaurantIds1 = Favourite::where('user_id', $request->user_id)
                                                        //->limit(5)
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }
                            $restaurantIds2 = Item::where('cuisine_id', $cuisineId)
                                                ->where('approved', '1')
                                                ->pluck('restaurant_id')
                                                ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);

                            $FavouriteRestaurants2 = User::whereIn('id', $restaurantIds)
                                                            ->where('pickup', '1')
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit('5')
                                                            ->get()
                                                            ->toArray();

                        }else{
                            $FavouriteRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg

                            $restaurantIds1 = Favourite::where('user_id', $request->user_id)
                                                        //->limit(5)
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();

                            $restaurantIds2 = Item::where('cuisine_id', $cuisineId)
                                                ->where('approved', '1')
                                                ->pluck('restaurant_id')
                                                ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);

                            $FavouriteRestaurants3 = User::whereIn('id', $restaurantIds)
                                                        ->where('pure_veg', '1')
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray(); 
                        }else{
                            $FavouriteRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $FavouriteRestaurants4 = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit('5')
                                                        ->get()
                                                        ->toArray(); 
                        }else{
                            $FavouriteRestaurants4 = array();
                        }
                        $FavouriteRestaurants = array_merge($FavouriteRestaurants1, $FavouriteRestaurants2, $FavouriteRestaurants3, $FavouriteRestaurants4) ;
                    }
                    //}
                    //return $FavouriteRestaurants;
                    $customizedData[$key2]['restaurants'] = $FavouriteRestaurants;
                }elseif($data['id'] == '2'){
                    //New in Grigora
                    //echo'<pre>';print_r($restaurantsUnderLocation);die;
                    if($filterId == '0' && $cuisineId == '0'){
                        $newRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }elseif($filterIds && $cuisineId == '0'){
                        if(in_array('1', $filterIds)){
                            //rating
                            $newRestaurants1 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $newRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $newRestaurants2 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->where('pickup', '1')
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $newRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $newRestaurants3 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->where('pure_veg', '1')
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $newRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price

                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $newRestaurants4 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $newRestaurants4 = array();
                        }

                        $newRestaurants = array_merge($newRestaurants1, $newRestaurants2, $newRestaurants3, $newRestaurants4);

                    }elseif($filterId == '0' && $cuisineId != '0'){
                        $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                        $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                        $newRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }else{
                        if(in_array('1', $filterIds)){
                            //rating
                            $newRestaurants1 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $newRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $newRestaurants2 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('id', $restaurantIds)
                                                    ->where('pickup', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $newRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $newRestaurants3 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('id', $restaurantIds)
                                                    ->where('pure_veg', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $newRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $newRestaurants4 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('id', $restaurantIds)
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $newRestaurants4 = array();
                        }

                        $newRestaurants = array_merge($newRestaurants1, $newRestaurants2, $newRestaurants3, $newRestaurants4);
                    }
                    //echo'<pre>';print_r($newRestaurants);die;
                    $customizedData[$key2]['restaurants'] = $newRestaurants;
                }elseif($data['id'] == '3'){
                    //Order Again
                    $pastOrderedRestaurants = array();
                    //if($request->has('user_id') && !empty($user->id)){
                    if($filterId == '0' && $cuisineId == '0'){
                        if($request->user_id != '0'){

                        $restaurantIds = Order::where('user_id', $request->user_id)
                                                ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                ->where('order_status', '5')
                                                //->limit(5)
                                                ->pluck('restaurant_id')
                                                ->toArray();
                        //return $restaurantIds;
                        $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit(5)
                                                        ->get()
                                                        ->toArray();

                        }else{
                            $pastOrderedRestaurants = array();
                        }
                      
                        //return $pastOrderedRestaurants;
                    }elseif($filterId != '0' && $cuisineId == '0'){
                        if($filterId == '1'){
                            //rating
                        if($request->user_id != '0'){
                            $restaurantIds = Order::where('user_id', $request->user_id)
                                                ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                ->where('order_status', '5')
                                                //->limit(5)
                                                ->pluck('restaurant_id')
                                                ->toArray();
                            //return $restaurantIds;
                            $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit(5)
                                                            ->get()
                                                            ->toArray();
                        }else{
                            $pastOrderedRestaurants = array();
                        }

                        }elseif($filterId == '2'){
                            //pickup
                        if($request->user_id != '0'){
                            $restaurantIds = Order::where('user_id', $request->user_id)
                                                    ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->where('order_status', '5')
                                                    //->limit(5)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                            $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                            ->where('pickup', '1')
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit(5)
                                                            ->get()
                                                            ->toArray();
                        }else{
                            $pastOrderedRestaurants = array();
                        }
                        }elseif($filterId == '3'){
                            //veg
                            if($request->user_id != '0'){
                                $restaurantIds = Order::where('user_id', $request->user_id)
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('order_status', '5')
                                                        //->limit(5)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                                $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                                ->where('pure_veg', '1')
                                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                                ->limit(5)
                                                                ->get()
                                                                ->toArray();
                            }else{
                                $pastOrderedRestaurants = array();
                            }
                        }else{
                            //price
                            if($priceRange == '1'){
                                $restaurantIds = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }elseif($priceRange == '2') {
                                $restaurantIds = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }elseif($priceRange == '3') {
                                $restaurantIds = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds = array();
                            }

                            $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit(5)
                                                            ->get()
                                                            ->toArray();
                        }
                    }elseif($filterId == '0' && $cuisineId != '0'){
                        $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                ->where('approved', '1')
                                                ->pluck('restaurant_id')
                                                ->toArray();
                        if($request->user_id != '0'){
                           $restaurantIds2 = Order::where('user_id', $request->user_id)
                                                ->where('restaurant_id', $restaurantsUnderLocationIds)
                                                ->where('order_status', '5')
                                                //->limit(5)
                                                ->pluck('restaurant_id')
                                                ->toArray(); 
                        }else{
                            $restaurantIds2 = array();
                        }
                        
                        $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);
                        $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->limit(5)
                                                        ->get()
                                                        ->toArray();
                    }else{
                        if($filterId == '1'){
                            //rating
                            if($request->user_id != '0'){
                                $restaurantIds = Order::where('user_id', $request->user_id)
                                                    ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->where('order_status', '5')
                                                    //->limit(5)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                                //return $restaurantIds;
                                $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                                ->limit(5)
                                                                ->get()
                                                                ->toArray();
                            }else{
                                $pastOrderedRestaurants = array();
                            }
                        }elseif($filterId == '2'){
                            //pickup
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                ->where('approved', '1')
                                                ->pluck('restaurant_id')
                                                ->toArray();
                            if($request->user_id != '0'){

                                 $restaurantIds2 = Order::where('user_id', $request->user_id)
                                                    ->where('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->where('order_status', '5')
                                                    //->limit(5)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }
                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);
                            $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                            ->where('pickup', '1')
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit(5)
                                                            ->get()
                                                            ->toArray();
                        }elseif($filterId == '3'){
                            //veg
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                ->where('approved', '1')
                                                ->pluck('restaurant_id')
                                                ->toArray();
                            if($request->user_id != '0'){
                              $restaurantIds2 = Order::where('user_id', $request->user_id)
                                                    ->where('restaurant_id', $restaurantsUnderLocationIds)
                                                    ->where('order_status', '5')
                                                    //->limit(5)
                                                    ->pluck('restaurant_id')
                                                    ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }
                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2);
                            $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                            ->where('pure_veg', '1')
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit(5)
                                                            ->get()
                                                            ->toArray();
                        }else{
                            //price
                            if($priceRange == '1'){
                                $restaurantIds = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }elseif($priceRange == '2') {
                                $restaurantIds = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }elseif($priceRange == '3') {
                                $restaurantIds = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds = array();
                            }

                            $pastOrderedRestaurants = User::whereIn('id', $restaurantIds)
                                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                            ->limit(5)
                                                            ->get()
                                                            ->toArray();

                        }
                    }

                    //}
                    //die('hi');
                    //return $pastOrderedRestaurants;
                    $customizedData[$key2]['restaurants'] = $pastOrderedRestaurants;
                }elseif($data['id'] == '4'){
                    //Popular
                    if($filterId == '0' && $cuisineId == '0'){
                        $nearByRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }elseif($filterIds && $cuisineId == '0'){
                        if(in_array('1', $filterIds)){
                            //rating
                            $nearByRestaurants1 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $nearByRestaurants2 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->where('pickup', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $nearByRestaurants3 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->where('pure_veg', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $nearByRestaurants4 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants4 = array();
                        }

                        $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);

                    }elseif($filterId == '0' && $cuisineId != '0'){
                        $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                        $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                        $nearByRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->where('id', $restaurantIds)
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }else{
                        if(in_array('1', $filterIds)){
                            //rating
                            $nearByRestaurants1 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $nearByRestaurants2 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->where('pickup', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->where('id', $restaurantIds)
                                                    ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $nearByRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $nearByRestaurants3 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->where('pure_veg', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->where('id', $restaurantIds)
                                                    ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $nearByRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $nearByRestaurants4 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->where('id', $restaurantIds)
                                                    ->orderBy('id', 'DESC')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $nearByRestaurants4 = array();
                        }

                        $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);
                    }
                    $customizedData[$key2]['restaurants'] = $nearByRestaurants;
                }elseif($data['id'] == '5'){
                    //Near By
                    if($filterId == '0' && $cuisineId == '0'){
                        $nearByRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }elseif($filterIds && $cuisineId == '0'){
                        if(in_array('1', $filterIds)){
                            //rating
                            $nearByRestaurants1 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $nearByRestaurants2 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->where('pickup', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $nearByRestaurants3 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->where('pure_veg', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $nearByRestaurants4 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants4 = array();
                        }

                        $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);

                    }elseif($filterId == '0' && $cuisineId != '0'){
                        $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                        $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                        $nearByRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->where('id', $restaurantIds)
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }else{
                        if(in_array('1', $filterIds)){
                            //rating
                            $nearByRestaurants1 = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('id', $restaurantsUnderLocationIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                        }else{
                            $nearByRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $nearByRestaurants2 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->where('pickup', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->where('id', $restaurantIds)
                                                    ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $nearByRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $nearByRestaurants3 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->where('pure_veg', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->where('id', $restaurantIds)
                                                    ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $nearByRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $nearByRestaurants4 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->where('id', $restaurantIds)
                                                    ->orderBy('id', 'DESC')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $nearByRestaurants4 = array();
                        }

                        $nearByRestaurants = array_merge($nearByRestaurants1, $nearByRestaurants2, $nearByRestaurants3, $nearByRestaurants4);
                    }
                    $customizedData[$key2]['restaurants'] = $nearByRestaurants;
                }elseif($data['id'] == '6'){
                    //Top Cuisine
                    //$topCuisineRestaurants = $cuisines;
                    $topCuisineRestaurants = Cuisine::where('status', '1')
                                        ->inRandomOrder()
                                        ->limit(5)
                                        ->get()
                                        ->toArray();
                    if($filterId == '0' && $cuisineId == '0'){

                    }elseif($filterId != '0' && $cuisineId == '0'){
                        if(in_array('1', $filterIds)){
                            //rating
                        }else{

                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                        }else{

                        }

                        if(in_array('3', $filterIds)){
                            //veg
                        }else{

                        }

                        if(in_array('4', $filterIds)){
                            //price
                        }else{

                        }

                    }elseif($filterId == '0' && $cuisineId != '0'){

                    }else{
                        if(in_array('1', $filterIds)){
                            //rating
                        }else{

                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                        }else{

                        }

                        if(in_array('3', $filterIds)){
                            //veg
                        }else{

                        }

                        if(in_array('4', $filterIds)){
                            //price
                        }else{

                        }
                    }
                    $customizedData[$key2]['restaurants'] = $topCuisineRestaurants;
                }elseif($data['id'] == '7'){
                    //Top Brands
                    
                    if($filterId == '0' && $cuisineId == '0'){
                        $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                        $topBrandsRestaurants = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('id', $restaurantsUnderLocationIds)
                                                    ->whereIn('name', $allBrandNames)
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                    }elseif($filterIds && $cuisineId == '0'){
                        if(in_array('1', $filterIds)){
                            //rating
                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants1 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('id', $restaurantsUnderLocationIds)
                                                    ->whereIn('name', $allBrandNames)
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $topBrandsRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants2 = User::where('role', '4')
                                                        ->where('approved', '1')
                                                        ->where('pickup', '1')
                                                        ->whereIn('id', $restaurantsUnderLocationIds)
                                                        ->whereIn('name', $allBrandNames)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->orderBy('id', 'Desc')
                                                        ->limit(5)
                                                        ->get()
                                                        ->toArray();
                        }else{
                            $topBrandsRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants3 = User::where('role', '4')
                                                        ->where('approved', '1')
                                                        ->where('pure_veg', '1')
                                                        ->whereIn('id', $restaurantsUnderLocationIds)
                                                        ->whereIn('name', $allBrandNames)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->orderBy('id', 'Desc')
                                                        ->limit(5)
                                                        ->get()
                                                        ->toArray();
                        }else{
                            $topBrandsRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants4 = User::where('role', '4')
                                                        ->where('approved', '1')
                                                        ->where('pure_veg', '1')
                                                        ->whereIn('id', $restaurantIds)
                                                        ->whereIn('name', $allBrandNames)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->orderBy('id', 'Desc')
                                                        ->limit(5)
                                                        ->get()
                                                        ->toArray();
                        }else{
                            $topBrandsRestaurants4 = array();
                        }

                        $topBrandsRestaurants = array_merge($topBrandsRestaurants1, $topBrandsRestaurants2, $topBrandsRestaurants3, $topBrandsRestaurants4);

                    }elseif($filterId == '0' && $cuisineId != '0'){
                        $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                        $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                        $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                        $topBrandsRestaurants = User::where('role', '4')
                                                ->where('approved', '1')
                                                ->whereIn('name', $allBrandNames)
                                                ->where('id', $restaurantIds)
                                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                ->orderBy('id', 'Desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();
                    }else{
                        if(in_array('1', $filterIds)){
                            //rating
                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants1 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('id', $restaurantsUnderLocationIds)
                                                    ->whereIn('name', $allBrandNames)
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $topBrandsRestaurants1 = array();
                        }

                        if(in_array('2', $filterIds)){
                            //pickup
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants2 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('name', $allBrandNames)
                                                    ->where('id', $restaurantIds)
                                                    ->where('pickup', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $topBrandsRestaurants2 = array();
                        }

                        if(in_array('3', $filterIds)){
                            //veg
                            $restaurantIds1 = Item::where('cuisine_id', $cuisineId)
                                                    ->where('approved', '1')
                                                    ->pluck('restaurant_id')
                                                    ->toArray();

                            $restaurantIds = array_merge($restaurantIds1, $restaurantsUnderLocationIds);
                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants3 = User::where('role', '4')
                                                    ->where('approved', '1')
                                                    ->whereIn('name', $allBrandNames)
                                                    ->where('id', $restaurantIds)
                                                    ->where('pure_veg', '1')
                                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                    ->orderBy('id', 'Desc')
                                                    ->limit(5)
                                                    ->get()
                                                    ->toArray();
                        }else{
                            $topBrandsRestaurants3 = array();
                        }

                        if(in_array('4', $filterIds)){
                            //price
                            if(in_array('1', $priceRange)){
                                $restaurantIds1 = Item::where('price', '<=', '10')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds1 = array();
                            }

                            if(in_array('2', $priceRange)){
                                $restaurantIds2 = Item::where('price', '<=', '100')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->where('approved', '1')
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds2 = array();
                            }

                            if(in_array('3', $priceRange)){
                                $restaurantIds3 = Item::where('price', '<=', '1000')
                                                        ->whereIn('restaurant_id', $restaurantsUnderLocationIds)
                                                        ->where('cuisine_id', $cuisineId)
                                                        ->pluck('restaurant_id')
                                                        ->toArray();
                            }else{
                                $restaurantIds3 = array();
                            }

                            $restaurantIds = array_merge($restaurantIds1, $restaurantIds2, $restaurantIds3);

                            $allBrandNames = Brand::where('status', '1')->pluck('name')->toArray();
                            $topBrandsRestaurants4 = User::where('role', '4')
                                                        ->where('approved', '1')
                                                        ->where('pure_veg', '1')
                                                        ->whereIn('id', $restaurantIds)
                                                        ->whereIn('name', $allBrandNames)
                                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                                        ->orderBy('id', 'Desc')
                                                        ->limit(5)
                                                        ->get()
                                                        ->toArray();
                        }else{
                            $topBrandsRestaurants4 = array();
                        }
                        $topBrandsRestaurants = array_merge($topBrandsRestaurants1, $topBrandsRestaurants2, $topBrandsRestaurants3, $topBrandsRestaurants4);
                    }
                    
                    $customizedData[$key2]['restaurants'] = $topBrandsRestaurants;
                }
                // elseif($data['id'] == '8'){
                //     //Trending
                //     $itemids = OrderDetail::orderBy('id', 'Desc')
                //                             ->groupBy('item_id')
                //                             ->pluck('item_id', 'item_id')
                //                             ->toArray();

                //     $trendingItems = Item::whereIn('id', $itemids)
                //                             ->limit(6)
                //                             ->get()
                //                             ->toArray();
                //     if($trendingItems){
                //         $appFee = $setting['app_fee'];
                //         foreach ($trendingItems as $keyT => $trendingItem) {
                //             $cuisins = Cuisine::where('id', $trendingItem['cuisine_id'])->first();
                //             $oldprice = $trendingItem['price'];
                //             $appPrice = $oldprice*$appFee/100;
                //             $trendingItems[$keyT]['price'] = round($oldprice+$appPrice, 2);
                //             $trendingItems[$keyT]['cuisine_name'] = $cuisins['name'];

                //             $restaurantInfo = User::where('id', $trendingItem['restaurant_id'])->first();
                //             $trendingItems[$keyT]['restaurant_name'] = $restaurantInfo['name'];
                //             $trendingItems[$keyT]['restaurant_french_name'] = $restaurantInfo['french_name'];

                //             $itemCategories = ItemCategory::where('item_id', $trendingItem['id'])->get()->toArray();
                //             if($itemCategories){
                //                 foreach ($itemCategories as $keyC => $itemCategorie) {
                //                     $itemSubCat = ItemSubCategory::where('item_cat_id', $itemCategorie['id'])->get()->toArray();
                //                     $itemCategories[$keyC]['item_sub_category'] = $itemSubCat;
                //                 }
                //                 $trendingItems[$keyT]['item_categories'] = $itemCategories;
                //             }else{
                //                 $trendingItems[$keyT]['item_categories'] = array();
                //             }

                //             $ratings = RatingReview::where('receiver_type', '1')
                //                                     ->where('receiver_id', $trendingItem['id'])
                //                                     ->get()
                //                                     ->toArray();
                //             $avergeRating = "0.0";
                //             if($ratings){
                //                 $ratingArr = array();
                //                 foreach ($ratings as $key2 => $rating) {
                //                     $ratingArr[] = $rating['rating'];
                //                 }
                //                 $totalRating = count($ratings);
                //                 $avergeRating = round(array_sum($ratingArr)/$totalRating,1);
                //             }else{
                //                 $totalRating = "0";
                //             }
                //             $trendingItems[$keyT]['avg_ratings'] = $avergeRating;
                //             $trendingItems[$keyT]['total_rating'] = $totalRating;

                //             if($checkcart){
                //                 $cartItemsDetailCount = CartItemsDetail::where('cart_id', $checkcart['id'])
                //                                                 ->where('user_id', $user->id)
                //                                                 ->where('item_id', $trendingItem['id'])
                //                                                 ->sum('quantity');
                //                 $trendingItems[$keyT]['item_count_in_cart'] = $cartItemsDetailCount;
                //             }else{
                //                 $trendingItems[$keyT]['item_count_in_cart'] = 0;
                //             }
                //         }
                //     }else{
                //         $trendingItems = array();
                //     }
                //     $customizedData[$key2]['restaurants'] = $trendingItems;
                // }
                
            }
            //echo "<pre>";print_r($customizedData);die;
            foreach ($customizedData as $k1 => $customizedDat) {
                //echo'<pre>';print_r($customizedDat);die;
                if(!empty($customizedDat['restaurants'])){
                    $filteredRestaurants = array();
                    foreach($customizedDat['restaurants'] as $k2 => $restaurant){
                        
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
                        $customizedData[$k1]['restaurants'][$k2]['average_rating'] = $avergeRatings;
                        $customizedData[$k1]['restaurants'][$k2]['total_rating'] = count($ratingReview);

                        $restaurant['average_rating'] = $avergeRatings;
                        $restaurant['total_rating'] = count($ratingReview);

                        
                        if(in_array('1', $filterIds)){
                            //rating
                            if($ratingRange == '0.0'){
                                //all data
                                //$filteredRestaurants[] = $restaurant;
                            }else{

                                //filtered data
                                if($avergeRatings >= $ratingRange){
                                    $filteredRestaurants[] = $restaurant;  
                                }
                                /*if($ratingRange <= $avergeRatings){
                                  $customizedData[$k1]["restaurants"][] = $restaurant;
                                }*/
                            }
                        }else{  
                            //all data
                            $filteredRestaurants[] = $restaurant;
                        }

                       // echo "<pre>";print_r($filteredRestaurants);die;
                    }
                    //echo "<pre>";print_r($filteredRestaurants);
                    $customizedData[$k1]["restaurants"] = $filteredRestaurants;
                }
            }
            //die;
            //echo'<pre>';print_r($customizedData);die;
            $finalData['customized_data'] = $customizedData;

            $allRestaurants = User::whereIn('id', $restaurantsUnderLocationIds)
                                        ->where('role', '4')
                                        ->where('approved', '1')
                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                        ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                        ->get()
                                        ->toArray(); 

            if($allRestaurants){
                foreach ($allRestaurants as $keyRes => $allRestaurant) {
                    $ratings = RatingReview::where('receiver_type', '2')
                                                        ->where('receiver_id', $allRestaurant['id'])
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
                    $allRestaurants[$keyRes]['average_rating'] = $avergeRating;
                    $allRestaurants[$keyRes]['total_rating'] = $totalRating;

                    $items = Item::where('restaurant_id', $allRestaurant['id'])
                                    ->where('approved', '1')
                                    ->select('id', 'image', 'approx_prep_time')
                                    ->limit(5)
                                    ->get();
                    $allRestaurants[$keyRes]['items'] = $items;
                }
            }

            $finalData['all_restaurants'] = $allRestaurants;

            //Notifications true or false boolean value passed to check if any unread notifications exists
            if($request->user_id != '0'){
                $notifications = Notification::where('user_id', $request->user_id)
                                                ->where('read','0')
                                                ->orderBy('id', 'Desc')
                                                ->get()
                                                ->toArray();
                if(empty($notifications)){
                    $notifications_unread = false;
                }else{
                    $notifications_unread = true;
                }
            }else{
               $notifications_unread = false; 
            }   
            $finalData['notifications'] = $notifications_unread;
            return response()->json([
                                        'status' => true,
                                        'message' => "Data Found Successfully.",
                                        'data' => $finalData
                                    ], 200);

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function promoRestaurants(Request $request){
        try{
            $rules = [
                        'latitude' => 'required',
                        'longitude' =>'required',
                        'promo_id' => 'required'
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
            $promoId = $request->promo_id;

            $setting = Setting::where('id', '1')->first();
            $distance = $setting->distance;

            $sqlQry = "SELECT *,ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371 AS distance
                FROM users
                WHERE
                ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $lat ) ) + COS( RADIANS( latitude ) )
                * COS( RADIANS( $lat )) * COS( RADIANS( longitude ) - RADIANS( $long )) ) * 6371  < $distance AND `role` = '4'
                ORDER BY `distance`";
            
            $result = DB::select(DB::raw($sqlQry));
            $resIds = array();
            if($result){
                foreach ($result as $keyRes => $valueRes) {
                    $item = Item::where('restaurant_id', $valueRes->id)->where('approved', '1')->first();
                    if($item){
                        $resIds[] = $valueRes->id;
                    }
                }
            }

            $restaurantsids = RestaurantPromo::where('promo_id', $promoId)
                                            ->pluck('restaurant_id')
                                            ->toArray();

            if($restaurantsids){
                $restaurantIds = array_merge($resIds, $restaurantsids);    
            }else{
                $restaurantIds = array();
            }
            
            $restaurants = User::whereIn('id', $restaurantIds)
                                ->where('approved', '1')
                                ->where('role', '4')
                                ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                ->get()
                                ->toArray();

            if($restaurants){
                foreach ($restaurants as $key => $restaurant) {

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

                    $items = Item::where('restaurant_id', $restaurant['id'])
                                        ->where('approved', '1')
                                        ->select('id', 'image', 'approx_prep_time')
                                        ->limit(5)
                                        ->get()
                                        ->toArray();

                    $restaurants[$key]['average_rating'] = $avergeRatings;
                    $restaurants[$key]['total_rating'] = count($ratingReview);
                    $restaurants[$key]['items'] = $items; 
                }

                $setting = Setting::where('id', '1')->first();
                $extraInfo = array();
                $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
                $extraInfo['min_order_vale'] = $setting['min_order'];
                $extraInfo['min_kilo_meter'] = $setting['min_km'];

                return response()->json([
                                            'status' => true,
                                            'message' => "Restaurants Found.",
                                            'data' => array('main_info' => $restaurants, 'extra_info' => $extraInfo)
                                        ], 200);    
            }else{
                return response()->json([
                                        'status' => false,
                                        'message' => "Restaurants Not Found.",
                                        'data' => $restaurants
                                    ], 200);    
            }

            
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function getVoucherCodes(){
        try{
            $voucherCode = VoucherCard::where('status', '1')->get()->toArray();
            if($voucherCode){
                return response()->json([
                                            'status' => true,
                                            'message' => "Cards Found.",
                                            'data' => $voucherCode
                                        ], 200);    
            }else{
                return response()->json([
                                            'status' => true,
                                            'message' => "Cards Not Found.",
                                            'data' => $voucherCode
                                        ], 200);    
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
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
    
    public function voucherCard($id){
        try{
            $voucherCard = VoucherCard::where('id', $id)->first();
            $user = Auth::user();
            if($voucherCard){

                $voucherCode = 'VC'.$id.$this->generateRandomString('6');
                $newVoucherCode = new VoucherCardCode;
                $newVoucherCode->user_id = $user->id;
                $newVoucherCode->voucher_id = $id;
                $newVoucherCode->valid = '1';
                $newVoucherCode->amount = $voucherCard->amount;
                $newVoucherCode->voucher_code = $voucherCode;
                $newVoucherCode->save();
                $voucherCard['voucher_card_code'] = $newVoucherCode;
                return response()->json([
                                            'status' => true,
                                            'message' => "Card Found.",
                                            'data' => $voucherCard
                                        ], 200);  
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Card Not Found.",
                                            'data' => $voucherCard
                                        ], 200);    
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function buyCard(Request $request){
        try{
            $rules = [
                        'voucher_code' => 'required',
                        'payment_method' => 'required'//2=>card,3=>grigora wallet
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
            $paymentMethod = $request->payment_method;
            $voucherCode = $request->voucher_code;
            $VoucherCardCode = VoucherCardCode::where('voucher_code', $voucherCode)->first();
            $amount = $VoucherCardCode['amount'];
            if($paymentMethod == '3'){
                //buy from wallet
                if($user->wallet < $amount){
                    return response()->json([
                                                'status' => false,
                                                'message' => "Your Doesn't Have Sufficient Amount."
                                            ], 200);  
                }
                $giftCard = new GiftCard;
                $giftCard->sender_id = '0';
                $giftCard->user_id = $user->id;
                $giftCard->voucher_code = $voucherCode;
                $giftCard->amount = $amount;
                if($giftCard->save()){
                    $walletLeft = $user->wallet - $amount;
                    $userWallet = User::where('id', $user->id)->update(['Wallet' => $walletLeft]);
                    $VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->update(['valid' => '0']);
                    //notification to receiver

                    if($user->notification == '1'){
                            
                        $userName = $user->name;
                        $message = "$amount is deducted from you wallet.";
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
                                    $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '20'));    
                                }
                                if($userToken['device_type'] == '1'){
                                    $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '20'));    
                                }
                            }
                        }

                        $saveNotification = new Notification;
                        $saveNotification->user_id = $user->id;
                        $saveNotification->notification = $message;
                        $saveNotification->french_notification = $frenchMessage[0];
                        $saveNotification->role = '2';
                        $saveNotification->read = '0';
                        $saveNotification->image = $user->image;
                        $saveNotification->notification_type = '20';
                        //$transaction->reason = "Buy Voucher Card";
                        $saveNotification->save();

                    }


                    $transaction = new Transaction;
                    $transaction->user_id = $user->id;
                    //$transaction->order_id = $order->id;
                    //$transaction->transaction_data = $receiverUser['id'];
                    //$transaction->reference = $request->receipt_id;
                    $transaction->amount = $amount;
                    $transaction->type = '4';
                    // if($request->has('reason') && !empty($request->reason)){
                        $transaction->reason = "Buy Gift Card";
                    // }
                    $transaction->save();

                    //notification to receiver
                    return response()->json([
                                                'status' => true,
                                                'message' => "Gift Card Purchased."
                                            ], 200);    
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Something Went Wrong!"
                                            ], 422);
                }
            }else{
                $rules = [
                            
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
                //buy from card  
                $reference = $request->reference;
                $giftCard = new GiftCard;
                $giftCard->sender_id = '0';
                $giftCard->user_id = $user->id;
                $giftCard->voucher_code = $voucherCode;
                $giftCard->amount = $amount;
                if($giftCard->save()){
                    $VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->update(['valid' => '0']);
                    //notification to receiver

                    $transaction = new Transaction;
                    $transaction->user_id = $user->id;
                    //$transaction->order_id = $order->id;
                   //$transaction->transaction_data = json_encode($response);
                    //$transaction->reference = $request->receipt_id;
                    $transaction->amount = $amount;
                    $transaction->type = '2';
                    $transaction->reason = 'Buy Gift Card';
                    $transaction->save();

                    //notification to receiver
                    return response()->json([
                                                'status' => true,
                                                'message' => "Gift Card Purchased."
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

    public function sendGiftCard(Request $request){
        try{
            $rules = [
                        'email' => 'required',
                        'voucher_code' => 'required',
                        //'payment_method' => 'required'//2=>card,3=>grigora wallet
                        //'amount' => 'required'
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

            $email = $request->email;
            
            $user = Auth::user();

            $VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->first();
            $amount = $VoucherCardCode['amount'];
            $checkGiftCard = GiftCard::where('user_id', $user->id)->where('voucher_code', $request->voucher_code)->first();
            if($checkGiftCard){
                $deleteGiftCard = GiftCard::where('user_id', $user->id)->where('voucher_code', $request->voucher_code)->delete();
                $receiverUser = User::where('email', $email)->first();
                if($receiverUser){
                    
                    $giftCard = new GiftCard;
                    $giftCard->sender_id = $user->id;
                    $giftCard->user_id = $receiverUser['id'];
                    $giftCard->voucher_code = $request->voucher_code;
                    $giftCard->amount = $amount;
                    if($giftCard->save()){
                        $VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->update(['valid' => '0']);
                        //notification to receiver

                        if($receiverUser['notification'] == '1'){
                            
                            $userName = $user->name;
                            $message = "$userName sent you a gift card of $amount.";
                            $frenchMessage = $this->translation($message);
                            if($receiverUser['language'] == '1'){
                                $msg = $message;
                            }else{
                                $msg = $frenchMessage[0];
                            }
                            $userTokens = UserToken::where('user_id', $receiverUser['id'])->get()->toArray();
                            if($userTokens){
                                foreach ($userTokens as $tokenKey => $userToken) {
                                    if($userToken['device_type'] == '0'){
                                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '17'));    
                                    }
                                    if($userToken['device_type'] == '1'){
                                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '17'));    
                                    }
                                }
                            }

                            $saveNotification = new Notification;
                            $saveNotification->user_id = $receiverUser['id'];
                            $saveNotification->notification = $message;
                            $saveNotification->french_notification = $frenchMessage[0];
                            $saveNotification->role = '2';
                            $saveNotification->read = '0';
                            $saveNotification->image = $user->image;
                            $saveNotification->notification_type = '17';
                            //$saveNotification->reason = "receive gift";
                            $saveNotification->save();

                        }
                        //notification to receiver
                        return response()->json([
                                                    'status' => true,
                                                    'message' => "Gift Card Sent Successfully."
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
                                                'message' => "User Not Found"
                                            ], 200);    
                }
            }else{

                $rules = [
                            
                            'payment_method' => 'required'
                        ];

                $validator = Validator::make($request->all(), $rules);

                if($validator->fails())
                {
                    return response()->json([
                                               "message" => "Something went wrong!",
                                               'errors' => $validator->errors()->toArray(),
                                            ], 422);               
                }

                $paymentMethod = $request->payment_method;
                if($paymentMethod == '3'){
                    //sent from grigora wallet
                    if($user->wallet < $amount){
                        return response()->json([
                                                    'status' => false,
                                                    'message' => "Your Doesn't Have Sufficient Amount."
                                                ], 200);  
                    }
                    //return $user;
                    $receiverUser = User::where('email', $email)->first();
                    if($receiverUser){
                        
                        $giftCard = new GiftCard;
                        $giftCard->sender_id = $user->id;
                        $giftCard->user_id = $receiverUser['id'];
                        $giftCard->voucher_code = $request->voucher_code;
                        $giftCard->amount = $amount;
                        if($giftCard->save()){
                            $walletLeft = $user->wallet - $amount;
                            $userWallet = User::where('id', $user->id)->update(['Wallet' => $walletLeft]);
                            $VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->update(['valid' => '0']);
                            //notification to receiver
                            $transaction = new Transaction;
                            $transaction->user_id = $user->id;
                            //$transaction->order_id = $order->id;
                            $transaction->transaction_data = $receiverUser['id'];
                            //$transaction->reference = $request->receipt_id;
                            $transaction->amount = $amount;
                            $transaction->type = '5';
                            // if($request->has('reason') && !empty($request->reason)){
                                $transaction->reason = "Gift Card";
                            // }
                            $transaction->save();

                            if($user->notification == '1'){
                                
                                $userName = $user->name;
                                $message = "$amount is deducted from you wallet.";
                                $frenchMessage = $this->translation($message);
                                if($user->language == '1'){
                                    $msg = $message;
                                }else{
                                    $msg = $frenchMessage[0];
                                }
                                $userTokens = UserToken::where('user_id', $user->id)->get()->toArray();
                                if($userTokens){
                                    foreach ($userTokens as $tokenKey => $userToken) {
                                        // if($userToken['device_type'] == '0'){
                                        //     $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '21'));    
                                        // }
                                        // if($userToken['device_type'] == '1'){
                                        //     $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '21'));    
                                        // }
                                        if($userToken['device_type'] == '0'){
                                            $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array());    
                                        }
                                        if($userToken['device_type'] == '1'){
                                            $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array());    
                                        }
                                    }
                                }

                                $saveNotification = new Notification;
                                $saveNotification->user_id = $user->id;
                                $saveNotification->notification = $message;
                                $saveNotification->french_notification = $frenchMessage[0];
                                $saveNotification->role = '2';
                                $saveNotification->read = '0';
                                $saveNotification->image = $receiverUser['image'];
                                $saveNotification->notification_type = '21';
                                //$saveNotification->reason = "send gift";
                                $saveNotification->save();

                            }

                            if($receiverUser['notification'] == '1'){
                                
                                $userName = $user->name;
                                $message = "$userName sent you a gift card of $amount.";
                                $frenchMessage = $this->translation($message);
                                if($receiverUser['language'] == '1'){
                                    $msg = $message;
                                }else{
                                    $msg = $frenchMessage[0];
                                }
                                $userTokens = UserToken::where('user_id', $receiverUser['id'])->get()->toArray();
                                if($userTokens){
                                    foreach ($userTokens as $tokenKey => $userToken) {
                                        if($userToken['device_type'] == '0'){
                                            $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '17'));    
                                        }
                                        if($userToken['device_type'] == '1'){
                                            $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '17'));    
                                        }
                                    }
                                }

                                $saveNotification = new Notification;
                                $saveNotification->user_id = $receiverUser['id'];
                                $saveNotification->notification = $message;
                                $saveNotification->french_notification = $frenchMessage[0];
                                $saveNotification->role = '2';
                                $saveNotification->read = '0';
                                $saveNotification->image = $user->image;
                                $saveNotification->notification_type = '17';
                                //$transaction->reason = "receive gift";
                                $saveNotification->save();

                            }
                            //notification to receiver
                            return response()->json([
                                                        'status' => true,
                                                        'message' => "Gift Card Sent Successfully."
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
                                                    'message' => "User Not Found"
                                                ], 200);    
                    }
                }else{
                    //sent from card
                    $rules = [
                            
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

                    $receiverUser = User::where('email', $email)->first();
                    if($receiverUser){
                        $checkGiftCard = GiftCard::where('user_id', $user->id)->where('voucher_code', $request->voucher_code)->first();
                        if($checkGiftCard){
                            $checkGiftCard->valid = "0";
                            $checkGiftCard->save();
                        }
                        $giftCard = new GiftCard;
                        $giftCard->sender_id = $user->id;
                        $giftCard->user_id = $receiverUser['id'];
                        $giftCard->voucher_code = $request->voucher_code;
                        $giftCard->amount = $amount;
                        if($giftCard->save()){

                            
                            
                            $VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->update(['valid' => '0']);
                            //notification to receiver
                            $transaction = new Transaction;
                            $transaction->user_id = $user->id;
                            //$transaction->order_id = $order->id;
                           //$transaction->transaction_data = json_encode($response);
                            //$transaction->reference = $request->receipt_id;
                            $transaction->amount = $amount;
                            $transaction->type = '2';
                            $transaction->reason = 'Buy Gift Card';
                            $transaction->save();


                            if($receiverUser['notification'] == '1'){
                                
                                $userName = $user->name;
                                $message = "$userName sent you a gift card of $amount.";
                                $frenchMessage = $this->translation($message);
                                if($receiverUser['language'] == '1'){
                                    $msg = $message;
                                }else{
                                    $msg = $frenchMessage[0];
                                }
                                $userTokens = UserToken::where('user_id', $receiverUser['id'])->get()->toArray();
                                if($userTokens){
                                    foreach ($userTokens as $tokenKey => $userToken) {
                                        if($userToken['device_type'] == '0'){
                                            $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '17'));    
                                        }
                                        if($userToken['device_type'] == '1'){
                                            $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array('notification_type' => '17'));    
                                        }
                                    }
                                }

                                $saveNotification = new Notification;
                                $saveNotification->user_id = $receiverUser['id'];
                                $saveNotification->notification = $message;
                                $saveNotification->french_notification = $frenchMessage[0];
                                $saveNotification->role = '2';
                                $saveNotification->read = '0';
                                $saveNotification->image = $user->image;
                                $saveNotification->notification_type = '17';
                                //$transaction->reason = "receive gift";
                                $saveNotification->save();

                            }
                            //notification to receiver
                            return response()->json([
                                                        'status' => true,
                                                        'message' => "Gift Card Sent Successfully."
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
                                                    'message' => "User Not Found"
                                                ], 200);    
                    }

                }
            }
            
            
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function mygiftCards(){
        try{
            $user = Auth::user();
            $giftCard = GiftCard::where('user_id', $user->id)->where('redemed', '0')->where('sender_id', '<>', '0')->get()->toArray();
            if($giftCard){
                return response()->json([
                                            'status' => true,
                                            'message' => "Voucher Cards Found.",
                                            'data' => $giftCard
                                        ], 200);    
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Voucher Cards Not Found.",
                                            'data' => $giftCard
                                        ], 200);    
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function myPurchsedcards(){
        try{
           $user = Auth::user();
            $giftCard = GiftCard::where('user_id', $user->id)->where('redemed', '0')->where('sender_id', '0')->get()->toArray();
            if($giftCard){
                foreach ($giftCard as $key => $card) {
                    $voucherCode = VoucherCard::where('amount', $card['amount'])->first();
                    $giftCard[$key]['voucher_image'] = $voucherCode['voucher_image'];
                }
                
                return response()->json([
                                            'status' => true,
                                            'message' => "Voucher Cards Found.",
                                            'data' => $giftCard
                                        ], 200);    
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Voucher Cards Not Found.",
                                            'data' => $giftCard
                                        ], 200);    
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function redemeCard(Request $request){
        try{
            $rules = [
                        'voucher_code' => 'required',
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

            $voucherCode = $request->voucher_code;
            $user = Auth::user();
            $checkGiftCard = GiftCard::where('voucher_code', $voucherCode)
                                        ->where('user_id', $user->id)
                                        ->where('redemed', '0')
                                        ->first();

            if($checkGiftCard){
            	$VoucherCardCode = VoucherCardCode::where('voucher_code', $request->voucher_code)->first();
            
                $amount = $VoucherCardCode['amount'];
                $wallet = $user->wallet + $checkGiftCard['amount'];
                $userWallet = User::where('id', $user->id)->update(['wallet' => $wallet]);
                if($userWallet){
                    $transaction = new Transaction;
                    $transaction->user_id = $checkGiftCard['sender_id'];
                    //$transaction->order_id = $order->id;
                    $transaction->transaction_data = $user->id;
                    //$transaction->reference = $request->receipt_id;
                    $transaction->amount = $amount;
                    //if($request->has('reason') && !empty($request->reason)){
                        $transaction->reason = "Receive Gift Card.";
                    //}
                    $transaction->type = '6';
                    $transaction->save();
                    $checkGiftCard->redemed = "1";
                    $checkGiftCard->save();
                    return response()->json([
                                                'status' => true,
                                                'message' => "Voucher Card Redemed Successfully.",
                                                //'data' => $checkGiftCard,
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
                                            'message' => "Voucher Code Not Found.",
                                            //'data' => $checkGiftCard,
                                        ], 200);    
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function getNotificationList(){
        try{
            $user = Auth::user();
            $notifications = Notification::where('user_id', $user->id)
                                            ->orderBy('id', 'Desc')
                                            ->get()
                                            ->toArray();

            if($notifications){
                foreach ($notifications as $key => $notification) {
                    $restaurantId = $notification['restaurant_id'];
                    if(!empty($restaurantId)){
                        $restaurant = User::where('id', $restaurantId)->first();
                        $notifications[$key]['pickup'] = $restaurant['pickup'];
                        $notifications[$key]['table_booking'] = $restaurant['table_booking'];
                        $notifications[$key]['no_of_seats'] = $restaurant['no_of_seats'];
                        $notifications[$key]['opening_time'] = $restaurant['opening_time'];
                        $notifications[$key]['closing_time'] = $restaurant['closing_time'];
                        $notifications[$key]['full_time'] = $restaurant['full_time'];
                    }
                }
                return response()->json([
                                            'message' => "Notifications Found Successfully.",
                                            'status' => true,
                                            'data' => $notifications
                                        ], 200);    
            }else{
                return response()->json([
                                            'message' => "Notifications Not Found.",
                                            'status' => false,
                                            'data' => $notifications
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function readNotification(Request $request){
        try{
            $rules = [
                        'notification_id' => 'required',//0 for all
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

            $notificationId = $request->notification_id;

            if($notificationId == 0){
                $update = Notification::where('user_id', $user->id)->update(['read' => '1']);
            }else{
                $update = Notification::where('id', $notificationId)->update(['read' => '1']);
            }

            if($update){
                return response()->json([
                                            'message' => "Notifications Read Successfully.",
                                            'status' => true,
                                           // 'data' => $notifications
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

    public function showAllFilterData(Request $request){
        try{
            $rules = [
                        'filter_type' => 'required',
                        'latitude' => 'required',
                        'longitude' => 'required',
                        "user_id" => 'required',
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

            $filtertype = $request->filter_type;
            //$user = Auth::user();
            $user_id = $request->user_id;
            $user = User::where("id",$user_id)->first();
            //echo "<pre>";print_r($user);die;
            $lat = $request->latitude;
            $long = $request->longitude;

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
                    $item = Item::where('restaurant_id', $restaurantsUnderLoc->id)->first();
                    if($item){
                        $restaurantsUnderLocationIds[] = $restaurantsUnderLoc->id;
                    }
                }
            }

            //1:Your Favourite, 2:New in Grigora, 3:Order Again, 4:Popular, 5:Near By, 6:Top Cuisine
            if($filtertype == '1'){
                //Your Favourite
                //if($request->has('user_id') && !empty($request->user_id)){
                    $restaurantIds = Favourite::where('user_id', $user->id)
                                                ->limit(30)
                                                ->pluck('restaurant_id')
                                                ->toArray();

                    $restaurants = User::whereIn('id', $restaurantIds)
                                        ->whereIn('id', $restaurantsUnderLocationIds)
                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                        ->get()
                                        ->toArray();
                // }else{
                //     $restaurants = array();
                // }
            }elseif($filtertype == '2'){
                //New in Grigora
                $restaurants = User::where('role', '4')
                                    ->whereIn('id', $restaurantsUnderLocationIds)
                                    ->where('approved', '1')
                                    ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                    ->orderBy('id', 'Desc')
                                    ->limit(30)
                                    ->get()
                                    ->toArray();
            }elseif($filtertype == '3'){
                //Order Again
                //if($request->has('user_id') && !empty($request->user_id)){
                    $restaurantIds = Order::where('user_id', $user->id)
                                            ->where('order_status', '5')
                                            ->limit(30)
                                            ->pluck('restaurant_id')
                                            ->toArray();
                    $restaurants = User::whereIn('id', $restaurantIds)
                                        ->whereIn('id', $restaurantsUnderLocationIds)
                                        ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                        ->get()
                                        ->toArray();
                // }else{
                //     $restaurants = array();
                // }
            }elseif($filtertype == '4'){
                //Popular
                $restaurants = User::where('role', '4')
                                            ->where('approved', '1')
                                            ->whereIn('id', $restaurantsUnderLocationIds)
                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                            ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                            ->limit(30)
                                            ->get()
                                            ->toArray();
            }elseif($filtertype == '5'){
                //Near By
                $restaurants = User::where('role', '4')
                                            ->where('approved', '1')
                                            ->whereIn('id', $restaurantsUnderLocationIds)
                                            ->select('id', 'name', 'french_name', 'image', 'address', 'french_address', 'latitude', 'longitude', 'promo_id', 'offer', 'opening_time', 'closing_time', 'full_time', 'pure_veg', 'pickup', 'preparing_time', 'busy_status', 'status', 'table_booking', 'no_of_seats')
                                            ->orderBy(DB::raw("3959 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(-{$long}) ) + sin( radians({$lat}) ) * sin(radians(latitude)) )"), 'DESC')
                                            ->limit(30)
                                            ->get()
                                            ->toArray();
            }elseif($filtertype == '6'){
                //Top Cuisine
                $restaurants = Cuisine::where('status', '1')
                                        ->inRandomOrder()
                                        ->limit(30)
                                        ->get()
                                        ->toArray();
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Enter correct filter type."
                                        ], 422); 
            }

            if($restaurants){
                $data = array();
                foreach($restaurants as $k2 => $restaurant){
                    $items = Item::where('restaurant_id', $restaurant['id'])
                                        ->where('approved', '1')
                                        ->select('id', 'image', 'approx_prep_time')
                                        ->limit(5)
                                        ->get()
                                        ->toArray();
                    if($items){
                        $restaurant['items'] = $items;
                        $data[] = $restaurant;
                    }
                }
                $setting = Setting::where('id', '1')->first();
                $extraInfo = array();
                $extraInfo['base_delivery_fee'] = $setting['delivery_fee'];
                $extraInfo['min_order_vale'] = $setting['min_order'];
                $extraInfo['min_kilo_meter'] = $setting['min_km'];

                if($data){
                    return response()->json([
                                                'status' => true,
                                                'message' => "Restaurants Found.",
                                                'data' => array('main_info' => $data, 'extra_info' => $extraInfo)
                                            ], 200); 
                }else{
                    return response()->json([
                                                'status' => false,
                                                'message' => "Restaurants Not Found.",
                                                'data' => $restaurants
                                            ], 200); 
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Restaurants Not Found.",
                                            'data' => $restaurants
                                        ], 200); 
            }

        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function getContactUs(){
        try{
            $issues = Issue::where('status', '1')->get()->toArray();
            $faqs = Faq::where('status', '1')->get()->toArray();
            $data = array();
            $data['issues'] = $issues;
            $data['faqs'] = $faqs;
            return response()->json([
                                        'status' => true,
                                        'message' => "Data Found.",
                                        'data' => $data
                                    ], 200);
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function subIssues(Request $request){
        try{
            $rules = [
                        //'issue_id' => 'required',
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
            $issueId = $request->issue_id;
            if($request->has('issue_id') && !empty($request->issue_id)){
                $subIssue = SubIssue::where('issue_id', $issueId)->get()->toArray();
            }else{
                $subIssue = SubIssue::get()->toArray();
            }
            if($subIssue){
                return response()->json([
                                            'status' => true,
                                            'message' => "SubIssues Found.",
                                            'data' => $subIssue
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "SubIssues Not Found.",
                                            'data' => $subIssue
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function faqs(){
        try{
            $faqCats = FaqCategory::all()->toArray();
            if($faqCats){
                foreach ($faqCats as $key => $faqCat) {
                
                    $faqs = Faq::where('faq_categoryid', $faqCat['id'])
                                ->where('status', '1')
                                ->get()
                                ->toArray();
                    $faqCats[$key]['faqs'] = $faqs;
                }
                return response()->json([
                                            'status' => true,
                                            'message' => "Faq's Found.",
                                            'data' => $faqCats
                                        ], 200);
                
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "Faq's Found.",
                                            'data' => $faqCats
                                        ], 200);
            }
            
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
    }

    public function logOut(Request $request){
        //echo'<pre>';print_r($request->user());die;
        $userId = $request->user()->id;
        $user = User::where("id","=",$userId)->first();
        if($user->role == '2'){
            $rules = [  
                        'device_id' => 'required'
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
            $check = UserToken::where('user_id', $userId)->where('device_id', $request->device_id)->first();
            if($check){
                $userToken = UserToken::where('user_id', $userId)->where('device_id', $request->device_id)->delete();
            }
        }
        if($user->role == '4'){
            $user->busy_status = '1';
        }
        //$user = User::where("id","=",$request->user()->id)->update(['device_token' => '', 'busy_status' => '1']);
        
        if($user->save()){
            
            if(true){
                $request->user()->token()->revoke();
                return response()->json([
                                            'status' => 1,
                                            'message' => 'Successfully logged out'
                                        ]);        
            }else{
                return response()->json([
                                            'status' => 0,
                                            'message' => 'Some Error'
                                        ]);        
            }
        }else{
            return response()->json([
                                        'status' => 0,
                                        'message' => 'No User Found'
                                    ]);        
        }
    }

    public function about_us($language){

        if($language == 'french'){
            $lang = '2';
        }else{
             $lang = '1';
        }

        $aboutus = AboutUs::where('language', $lang)->first();
        if($aboutus){
            return  $aboutus->description;      
        }else{
           return 'Something went Wrong!!';  
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

