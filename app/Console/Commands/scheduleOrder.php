<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order;
use App\User;
use App\Setting;
use App\UserToken;
use App\Notification;
use DB;
use Carbon\Carbon;
use App\DriverRequest;
class scheduleOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiate Order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $orderResult = Order::where("is_schedule", '1')->whereIn("order_status", ['1','2', '3'])->where("notification_sent", '0')->get()->toArray();        
      if(!empty($orderResult)){
        $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
        $setting = Setting::where('id', '1')->first();
        $distance = $setting['distance'];
        foreach ($orderResult as $key => $orderValue) {
          $customer = User::where('id', $orderValue['user_id'])->first();
          $restaurant = User::where('id', $orderValue['restaurant_id'])->first();
          $scheduleTime = $orderValue["schedule_time"];
          $beforeTime = date('Y-m-d h:i:s', strtotime('-60 minutes', strtotime($scheduleTime)));
          $currentTime =  date("Y-m-d H:i:s");  
          if(strtotime($beforeTime) <= strtotime($currentTime)){
            //Notification to customer
            $message = "Restaurant is preparing your scheduled order.";
            $frenchMessage = $this->translation($message);
            if($customer->notification == '1'){
              //$customerName = $customer->name;
              if($customer->language == '1'){
                $msg = $message;
              }else{
                $msg = $frenchMessage[0];
              }
              //$deta = $order;
              $deta = array(  
                              "order_id" => $orderValue['id'],
                              //"restaurant_id" => $order->restaurant_id,
                              "restaurant_name" => $restaurant->name ,
                              "restaurant_lat" => $restaurant->latitude,
                              "restaurant_long" => $restaurant->longitude,
                              "restaurant_image" => $restaurant->image,
                              "restaurant_address" => $restaurant->address,
                              "notification_type" => '306'
                            );
              //$deta = array('notification_type' => '0');
              $userTokens = UserToken::where('user_id', $orderValue['user_id'])->get()->toArray();
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
            $saveNotification->user_id = $orderValue['user_id'];
            $saveNotification->notification = $message;
            $saveNotification->french_notification = $frenchMessage[0];
            $saveNotification->role = '2';
            $saveNotification->read = '0';
            $saveNotification->image = $restaurant->image;
            $saveNotification->notification_type = '0';
            $saveNotification->save();
            //Notification to customer

            //Notification to restaurant
            $orderId = $orderValue['id'];
            $orderUpdate = Order::where('id', $orderId)->update(['notification_sent' => '1']) ;
            $message = "Start Preparing your schedule order. #$orderId";
            $frenchMessage = $this->translation($message);
            if($restaurant->language == '1'){
              $msg = $message;
            }else{
              $msg = $frenchMessage[0];
            }
              //$deta = $order;
            $deta = array(  
                              "order_id" => $orderValue['id'],
                              //"restaurant_id" => $order->restaurant_id,
                              "restaurant_name" => $restaurant->name ,
                              "restaurant_lat" => $restaurant->latitude,
                              "restaurant_long" => $restaurant->longitude,
                              "restaurant_image" => $restaurant->image,
                              "restaurant_address" => $restaurant->address,
                              //"notification_type" => '2'

                          );
              //$deta = array('notification_type' => '0');
            $userTokens = UserToken::where('user_id', $orderValue['restaurant_id'])->get()->toArray();
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
            //Notification to restaurant

          }else{
            echo "No Schedule Order.";
          }
        }
      }else{
        echo "No Order Found to Schedule";
      }
    }

    public function sendPushNotification($token,$msg="") {
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

    public function iosPushNotification($token,$msg="") {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
                        'sound' => 'Default',
                        "type"=> "test",
                        "data"=>"test",
                        "base_url"=>url("/"),
                        "body" => $msg,
                        "title" => "Grigora",
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
