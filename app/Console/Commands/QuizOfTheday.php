<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon;
use App\User;
use App\QuizQuestion;
use App\Notification;

class QuizOfTheday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz:day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Announce winner of the quiz.';

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
        $quiz = QuizQuestion::where('status', '1')->first();
        $myTime = Carbon\Carbon::now();
        //echo $myTime->toDateTimeString();
        //echo"--------";
        //echo $quiz['created_at'];
        //echo"----";
        if($quiz){
            $questionId = $quiz['id'];
            $resultAnnounceTime =  date("Y-m-d H:i:s", strtotime($quiz['created_at'].'+12 hours'));
            //echo $resultAnnounceTime;
            if($resultAnnounceTime == $myTime){
                $shortListedUser = ShortListedUser::where('question_id', $questionId)->get()->toArray();
                if($shortListedUser){
                    $noOfWinners = $quiz['no_of_winners'];
                    $winnersIds = ShortListedUser::where('question_id', $questionId)
                                                    ->random($noOfWinners)
                                                    ->pluck('user_id', 'id')
                                                    ->toArray();
                    $users = User::whereIn($winnersIds)
                                    ->get()
                                    ->toArray();
                    foreach ($users as $key => $user) {
                        //send notification to winners and add in users_offers table
                        $updateQuiz = QuizQuestion::where('id', $questionId)->update(['status' => '0']);
                        $usersOffer = new Usersoffer;
                        $usersOffer->user_id = $user['id'];
                        $usersOffer->question_id = $questionId;
                        $usersOffer->save();
                        $point = $quiz['offer_points'];
                        $message = "$point Points Added to your wallet";
                        $frenchMessage = $this->translation($message);
                        if($user['language'] == '1'){
                          $msg = $message;
                        }else{
                          $msg = $frenchMessage[0];
                        }
                        if($user['notification'] == '1'){
                            $amount = $order['final_price'];
                            $userTokens = UserToken::where('user_id', $user['id'])->get()->toArray();
                            if($userTokens){
                                foreach ($userTokens as $tokenKey => $userToken) {
                                    if($userToken['device_type'] == '0'){
                                        $sendNotification = $this->sendPushNotification($userToken['device_token'],$msg,$deta = array("notification_type" => '9'));    
                                    }
                                    if($userToken['device_type'] == '1'){
                                        $sendNotification = $this->iosPushNotification($userToken['device_token'],$msg,$deta = array("notification_type" => '9'));    
                                    }
                                }
                            }
                        }

                        $saveNotification = new Notification;
                        $saveNotification->user_id = $user['id'];
                        $saveNotification->notification = $message;
                        $saveNotification->french_notification = $frenchMessage[0];
                        $saveNotification->role = '2';
                        $saveNotification->read = '0';
                        $saveNotification->save();

                        $walletPoints = $user->wallet + $point;
                        $updateWallet = User::where('id', $user['id'])->update(['wallet' => $walletPoints]);
                    }
                }else{
                    echo "No One Seleted Write Answer.";
                }
            }else{
                echo "waiting for winner announcing.";
            }
        }else{
            echo"No Quiz For Today.";
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
}
