<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Setting;
use App\Promocode;
use App\ContactUs;
use App\AboutUs;
use App\User;
use Redirect;
use App\CompanyOffline;


class SettingsController extends Controller
{

     public function viewcompanypage(){
        $offline =  CompanyOffline::where('name','CompanyOffline')->first();

        return view('admin.companyoffline', ['offline' => $offline]);
     }
    public function Company_Offline(Request $request){

      $companyoffline =   CompanyOffline::where('name','CompanyOffline')->update([
            'status' => $request->status,
        ]);

      if($companyoffline){
       $offline =  CompanyOffline::where('name','CompanyOffline')->first();
       if($offline->status == '0'){
        User::where('role','3')->update([
          'busy_status' => '1',
        ]);
          $message = 'Company Offline Successfully';
       }else{
          User::where('role','3')->update([
            'busy_status' => '0',
          ]);
          $message = 'Company Online Successfully';
       }

          return response()->json([
                                            'message' => $message,
                                            'status' => '1',
                                            
                                        ], 200);
      }else{

            return response()->json([
                                            'message' => 'Something went wrong',
                                            'status' => '0',
                                            
                                        ], 200);
      }

    }
    public function list(){
    	$setting = Setting::where('id', '1')->first();
    	return view('admin.settings', ['setting' => $setting]);
    }

    public function save(Request $request){
    	$update = Setting::where('id', '1')->update(['app_fee' => $request->app_fee, 'delivery_fee' => $request->delivery_fee, 'distance' => $request->distance, 'min_order' => $request->min_order, 'min_km' => $request->min_km, 'min_wallet' => $request->min_wallet, 'max_wallet' => $request->max_wallet, 'sender_refer_earn' => $request->sender_refer_earn, 'receiver_refer_earn' => $request->receiver_refer_earn, 'loyality' => $request->loyality,'naira_to_points' => $request->naira_to_points]);
    	return redirect('settings/list')->with('message', 'Setting Updated Successfully.');
    }

    public function notifications(){
      return view('admin.notifications');
    }
    
    public function PromoNotifications(){

      return view('admin.promo_notifications.add');
    }

     public function SendPromoNotifications(Request $request){

        $customers = User::where('role', '2')->get()->toArray();
        foreach ($customers as $key => $customer) {
         $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();
         if($userTokens){
          $message = $request->get('message');
          $deta = array();
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
          
      return  redirect()->back();
    }

    public function MealNotifications(){

      return view('admin.meal_notifications.add');
    }
    public function SendMealNotifications(Request $request){

        $customers = User::where('role', '2')->get()->toArray();
        foreach ($customers as $key => $customer) {
         $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();
         if($userTokens){
          $message = $request->get('message');
          $deta = array();
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
          
      return  redirect()->back();
    }
    
    public function PaidpageNotifications(){

      return view('admin.paidpage_notifications.add');
    }

    public function SendPaidpageNotifications(Request $request){

        $customers = User::where('role', '4')->get()->toArray();
        foreach ($customers as $key => $customer) {
         $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();
         if($userTokens){
          $message = $request->get('message');
          $deta = array();
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
          
      return  redirect()->back();
    }

    public function AddUpdateNotifications(){

      return view('admin.appupdate_notifications.add');
    }

   public function SendAddUpdateNotifications(Request $request){

    $customers = User::where('role', '2')->get()->toArray();
    foreach ($customers as $key => $customer) {
     $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();
     if($userTokens){
      $message = $request->get('message');
      $deta = array();
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
          
      return  redirect()->back();
    }

    public function sendNotification(Request $request){
      $customers = User::where('role', '2')->get()->toArray();
      foreach ($customers as $key => $customer) {
        $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();
        if($userTokens){
          $message = "";
          $deta = array();
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
    }

    public function promoCodes(){
    	$promocodes = Promocode::all();
      	return view('admin.promocode.list', ['promocodes' => $promocodes]);
    }

    public function addPromoCode(){
    	return view('admin.promocode.add');
    }

    public function editPromoCode($id){
    	$promocode = Promocode::where('id', $id)->first();
     	return view('admin.promocode.edit', ['promocode' => $promocode]);  
    }

    public function updatePromocode(Request $request){
      if( $request->file('image')!= ""){
        if (!file_exists( public_path('/images/promo'))) {
            mkdir(public_path('/images/promo'), 0777, true);
        }
        $path =public_path('/images/promo/');
        $image = $request->file('image');
        $promoImage = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/images/promo');
        $image->move($destinationPath, $promoImage);
        $url = url('/images/promo/');
        $url = str_replace('/index.php', '', $url);
        $promoImage = $url.'/'.$promoImage;
      }else{
        $promo = Promocode::where('id', $request->get('id'))->first();
        $promoImage = $promo->image;  
      }
    	$englishWords = array($request->get('name'), $request->get('description'));
      $frenchWords = $this->translation($englishWords);
    	$updatePromocode = PromoCode::where('id', $request->get('id'))->update([
                                                                        'name' => $request->get('name'),
                                                                        'image' => $promoImage,
                                                                        'french_name' => $frenchWords[0],
                                                                        'code' => $request->get('code'),
                                                                        'percentage' => $request->get('percentage'),
                                                                        //'no_of_attempts' => $request->get('no_of_attempts'),
                                                                        'description' => $request->get('description'),
                                                                        'french_description' => $frenchWords[1],
                                                                        'min_order_value' => $request->get('min_order_value')
                                                                      ]);
                                                                      
      	if($updatePromocode){
        	return redirect('/promocode/list')->with('message', 'Promo Code updated successfully.');
      	}else{
	        return redirect()->back()->with('error', 'Something went wrong.');
      	}
    }

    public function savePromoCode(Request $request){
      if( $request->file('image')!= ""){
        if (!file_exists( public_path('/images/promo'))) {
            mkdir(public_path('/images/promo'), 0777, true);
        }
        $path =public_path('/images/promo/');
        $image = $request->file('image');
        $promoImage = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/images/promo');
        $image->move($destinationPath, $promoImage);
        $url = url('/images/promo/');
        $url = str_replace('/index.php', '', $url);
        $promoImage = $url.'/'.$promoImage;
      }else{
        $promoImage = "";  
      }
    	$promocode = new Promocode;
    	$englishWords = array($request->get('name'), $request->get('description'));
        $frenchWords = $this->translation($englishWords);
      	$promocode->name = $request->get('name');
        $promocode->description = $request->get('description');
        $promocode->image = $promoImage;
      	$promocode->french_name = $frenchWords[0];
        $promocode->french_description = $frenchWords[1];
      	$promocode->code = $request->get('code');
      	$promocode->percentage = $request->get('percentage');
      	$promocode->no_of_attempts = $request->get('no_of_attempts');
        $promocode->min_order_value = $request->get('min_order_value');
      	if($promocode->save()){
	        return redirect('/promocode/list')->with('message', 'Promo Code added successfully.');
      	}else{
        	return redirect()->back()->with('error', 'Something went wrong.');
      	}
    }

    public function deletePromoCode($id){
    	$delPromocode = Promocode::where("id", $id)->delete();
      	if($delPromocode){
        	return redirect('/promocode/list')->with('message', 'Promo Code successfully deleted.');
      	}else{
        	return redirect()->back()->with('error', 'Something went wrong.');  
      	}
    }

    public function contactUs(){
      $contactUs = ContactUs::orderBy('id', 'Desc')->get()->toArray();
      return view('admin.contactUs', ['contactUs' => $contactUs]);
    }

     public function aboutUs(){
      $data = AboutUs::where('language', '1')->first();
      return view('admin.aboutus.add', ['data' => $data]);
    }

    public function aboutUsFrench(){
      $data = AboutUs::where('language', '2')->first();
      return view('admin.aboutus.french.add', ['data' => $data]);
    }
    
    public function addaboutUs(Request $request){

        $aboutus = AboutUs::where('language', $request->language)->first();

          if($aboutus){

               AboutUs::where('language',$request->language)->update([
                'description' => $request->get('description_about_us'),
               ]);

               $data = AboutUs::where('language', $request->language)->first();
               
           return Redirect::back()->with(compact('data')); 

          }else{

            $terms = new AboutUs;
            $terms->title = $request->get('title');
            $terms->description = $request->get('description_about_us'); 
            $terms->language = $request->get('language');   
            $terms->save();

            $data = AboutUs::where('language', $request->language)->first();
            
           return Redirect::back()->with(compact('data'));

          }
      return $request;

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
