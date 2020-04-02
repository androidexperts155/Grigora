<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Item;
use App\Setting;
use App\Order;
use App\OrderDetail;
use App\DriverRequest;
use App\RestaurantCuisine;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;

class AssignDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Assign:driver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Driver To Order';

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
        $orderData = Order::where('order_status', '2')->get()->toArray();
        if(!empty($orderData)){
            foreach ($orderData as $orderKey => $orderValue) {
                //echo $orderValue['id'];
                $order = Order::where("id",$orderValue['id'])->first();
                $userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
                $customer = User::where('id', $order->user_id)->first();
                $restaurant = User::where('id', $order->restaurant_id)->first();
                if($order->order_status == '3'){ 
                    $message = "Order Is Accepted By Driver";
                    $deta = array('order_id' => $order->id);
                    if($restaurant['device_type'] == '0'){
                        $sendNotification = $this->sendPushNotification($restaurant['device_token'],$message,$deta);    
                    }
                    if($restaurant['device_type'] == '1'){
                        $sendNotification = $this->iosPushNotification($restaurant['device_token'],$message,$deta);    
                    }
                    if($customer->notification == '1'){
                      $message1 = "Driver Is Reaching The Restaurant To Pick Up Your Order";
                      if($customer['device_type'] == '0'){
                          $sendNotification = $this->sendPushNotification($customer['device_token'],$message1,$deta = array());    
                      }
                      if($customer['device_type'] == '1'){
                          $sendNotification = $this->iosPushNotification($customer['device_token'],$message1,$deta = array());    
                      }
                    }
                    echo "Driver Found";
                }else if($order->order_status == '2'){
                    $customerData = User::where("id",$order->user_id)->first();
                    $restaurant = User::where('id', $order->restaurant_id)->first();
                    $requestedDrivers = DriverRequest::where("order_id",$orderValue['id'])->pluck("driver_id","driver_id")->toArray();

                    if(!empty($requestedDrivers)){
                       $str = " and id Not IN (".implode(",", $requestedDrivers).") ";
                    }else{
                       $str = "";    
                    }
//echo $str;

                      $query = "SELECT
                               `id`,`device_type`,`device_token`,
                               ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $restaurant->latitude ) ) + COS( RADIANS( `latitude` ) )
                               * COS( RADIANS( $restaurant->latitude )) * COS( RADIANS( `longitude` ) - RADIANS( $restaurant->longitude )) ) * 3953 AS `distance`
                               FROM `users`
                               WHERE
                               ACOS( SIN( RADIANS( `latitude` ) ) * SIN( RADIANS( $restaurant->latitude ) ) + COS( RADIANS( `latitude` ) )
                               * COS( RADIANS( $restaurant->latitude )) * COS( RADIANS( `longitude` ) - RADIANS( $restaurant->longitude )) ) * 3959 < 10 And `role` IN('3') AND busy_status = '0' $str  ORDER BY `distance` DESC"; 
                               $nearByDriver = DB::select(DB::raw($query));
                               //die;
                               //echo "<pre>";print_r($nearByDriver);die;
                    if($order->driver_id == ""){  // rejected
                        $restaurantName = $userName[$order->restaurant_id];

                        if(!empty($nearByDriver)){
                            $data = array(  
                                        "order_id" => $order->id,
                                        //"restaurant_id" => $order->restaurant_id,
                                        "restaurant_name" => $restaurantName,
                                        "restaurant_lat" => $restaurant->latitude,
                                        "restaurant_long" => $restaurant->longitude,
                                        "restaurant_image" => $restaurant->image,
                                        "restaurant_address" => $restaurant->address,

                                    );
                            $message = "New order for you from $restaurantName restaurant";
                            if($nearByDriver[0]->device_type == '0'){
                                $sendNotification = $this->sendPushNotification($nearByDriver[0]->device_token,$message,$data);    
                            }

                            if($nearByDriver[0]->device_type == '1'){
                                $sendNotification = $this->iosPushNotification($nearByDriver[0]->device_token,$message,$data);    
                            }

                            $driverRequest = new DriverRequest();
                            $driverRequest->order_id = $order->id;
                            $driverRequest->driver_id = $nearByDriver[0]->id;

                            if($driverRequest->save()){
                                $order->driver_id = $nearByDriver[0]->id;
                                $currentTime = Carbon::now();
                                $order->request_time = $currentTime;  
                                if($order->save()){
                                    //echo "Checking For Driver";
                                    User::where("id",$nearByDriver[0]->id)->update(["busy_status" => "1"]);
                                }else{
                                    echo "Somthing Went Wrong";
                                    
                                } 
                            }else{
                                echo "Somthing Went Wrong";
                                
                            }



                        }else{
                            echo "No driver Found";
                        }
                    }else{
                        $requestedTime = $order->request_time;

                           //$selectedTime = "9:15:00";
                        $requestedTime = strtotime("+1 minutes", strtotime($requestedTime));
                       

                        $currentTime = Carbon::now();
                        $currentTimeStr = strtotime($currentTime);
                        //echo $currentTime; echo ;
                        if($currentTimeStr >  $requestedTime){ // if 1 min exceed
                            
                            $driverRequest = DriverRequest::where(["order_id" => $order->id,"driver_id" => $order->driver_id])->first();
                            $driverRequest->status = '3';
                            User::where("id",$order->driver_id)->update(["busy_status" => '0']);

                            if($driverRequest->save()){
                                $order->driver_id = null;
                                $order->request_time = null;   
                                if($order->save()){
                                    if(!empty($nearByDriver)){
                                        $restaurantName = $userName[$order->restaurant_id];
                                        $message = "New order for you from $restaurantName restaurant";
                                        if($nearByDriver[0]->device_type == '0'){
                                            $sendNotification = $this->sendPushNotification($nearByDriver[0]->device_token,$message,$data = array());    
                                        }
                                        if($nearByDriver[0]->device_type == '1'){
                                            $sendNotification = $this->iosPushNotification($nearByDriver[0]->device_token,$message,$data = array());    
                                        }

                                        $driverRequest = new DriverRequest();

                                        $driverRequest->order_id = $order->id;
                                        $driverRequest->driver_id = $nearByDriver[0]->id;

                                        if($driverRequest->save()){
                                            User::where("id",$nearByDriver[0]->id)->update(["busy_status" => "1"]);
                                            echo "Checking For Driver";
                                        }

                                    }else{
                                        echo "No Driver Found";
                                        
                                    }
                                }
                            }
                        }else{
                            echo "checking Driver with time";
                        }
                    }            

                }

            }
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
}
