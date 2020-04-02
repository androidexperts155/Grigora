<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\QuizQuestion;
use App\UserToken;
use App\User;
use Validator;
use redirect;
use App\Notification;
use Carbon;
use URL;
use DB;


class QuizController extends Controller
{
    public function list(){

    	return view('admin.quiz.add');
    }
    public function listview(){

     /// $quizquestion1 = QuizQuestion::where('created_at', Carbon::today())->first();
     $quizquestion1 =  DB::table('quiz_questions')->select(DB::raw('*'))
                  ->whereRaw('Date(created_at) = CURDATE()')->get();

        return view('admin.quiz.index', ['quiz' => $quizquestion1]);
       }
      
    
    public function view($id){
       $quizquestion = QuizQuestion::where('id', $id)->first();
      return view('admin.quiz.view', ['quiz' => $quizquestion]);
    }
     public function delete($id){
       $quizdelete = QuizQuestion::where('id', $id)->delete();
       $quizquestion1 =  DB::table('quiz_questions')->select(DB::raw('*'))
                  ->whereRaw('Date(created_at) = CURDATE()')->get();

      return redirect()->back()->with(compact('quiz',$quizquestion1));
    }
     public function edit($id){
        $quizquestion = QuizQuestion::where('id', $id)->first();
      //$quizquestion = QuizQuestion::where('created_at', date('Y-m-d'))->first();
      return view('admin.quiz.edit', ['quiz' => $quizquestion]);
    }

    public function editsave(Request $request){

      $quizdata = QuizQuestion::where('id', $request->id)->first();

         if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/quiz'))) {
                mkdir(public_path('/images/quiz'), 0777, true);
            }
            $path =public_path('/images/quiz/');
            $image = $request->file('image');
            $quizImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/quiz');
            $image->move($destinationPath, $quizImage);
            $url = url('/images/quiz/');
            $url = str_replace('/index.php', '', $url);
            $quizImage = $url.'/'.$quizImage;
        }else{
            $quizImage = $quizdata->image;  
        }

         if($request->get('answer') == ''){
            $answer = $request->get('answertext');
        }else{
          $answer = $request->get('answer');
        }
       
         if($request->get('option1') == ''){
            $option1 = '' ;
        }else{
           $option1 = $request->get('option1') ;
        }
         if($request->get('option2') == ''){
            $option2 = '' ;
        }else{
           $option2 = $request->get('option2') ;
        }
         if($request->get('option3') == ''){
            $option3 = '' ;
        }else{
           $option3 = $request->get('option3');
        }
         if($request->get('option4') == ''){
            $option4 = '' ;
        }else{
           $option4 = $request->get('option4') ;
        }

      $update = QuizQuestion::where('id', $request->id)->update([
        'question' => $request->question,
        'option1' => $request->option1, 
        'option2' => $request->option2, 
        'option3' => $request->option3, 
        'option4' => $request->option4,
        'answer' => $answer,
        'no_of_winners' => $request->no_of_winners,
        'offer_points' => $request->offer_points,
        'coupon_code' => $request->coupon_code,
           'description' => $request->description,
        'offer_expiry' => date('Y-m-d',strtotime($request->offer_expiry)),
        'image' => $quizImage,
      ]);


      return redirect('quiz/list')->with('message', 'Quiz Updated Successfully.');
    }
    
    

    public function save(Request $request){

         if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/quiz'))) {
                mkdir(public_path('/images/quiz'), 0777, true);
            }
            $path =public_path('/images/quiz/');
            $image = $request->file('image');
            $quizImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/quiz');
            $image->move($destinationPath, $quizImage);
            $url = url('/images/quiz/');
            $url = str_replace('/index.php', '', $url);
            $quizImage = $url.'/'.$quizImage;
        }else{
            $quizImage = "";  
        }
        if($request->get('answer') == ''){
            $answer = $request->get('answertext');
        }else{
          $answer = $request->get('answer');
        }
        if($request->get('type') == 'single'){
            $type = '2' ;
        }else{
           $type = '1' ;
        }
         if($request->get('option1') == ''){
            $option1 = '' ;
        }else{
           $option1 = $request->get('option1') ;
        }
         if($request->get('option2') == ''){
            $option2 = '' ;
        }else{
           $option2 = $request->get('option2') ;
        }
         if($request->get('option3') == ''){
            $option3 = '' ;
        }else{
           $option3 = $request->get('option3');
        }
         if($request->get('option4') == ''){
            $option4 = '' ;
        }else{
           $option4 = $request->get('option4') ;
        }


      $quiztodaychk =   DB::table('quiz_questions')->whereRaw('Date(created_at) = CURDATE()')->first();
  
      if(!empty($quiztodaychk)){
                 
          return redirect('quiz/list')->with('message', 'Quiz question for today already exists.');
      }else{
         $quiz = new QuizQuestion;
         $quiz->question =  $request->question;
         $quiz->option1 =  $option1;
         $quiz->option2 =  $option2;
         $quiz->option3 =  $option3;
         $quiz->option4 =  $option4;
         $quiz->answer =  $answer;
         $quiz->description =  $request->description;
          $quiz->coupon_code  =  $request->coupon_code;
         $quiz->no_of_winners =  $request->no_of_winners;
         $quiz->offer_points =  $request->offer_points;
         $quiz->type =  $type;
         $quiz->offer_expiry =  date('Y-m-d',strtotime($request->offer_expiry));
        $quiz->image =  $quizImage;
        $quiz->save();
      } 


    	

    	 $customers = User::where('role', '4')->orwhere('role', '2')->get()->toArray();
        foreach ($customers as $key => $customer) {

         $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();
         if($userTokens){
          $message = 'Join quiz of the day';
          $deta = array('notification_type' => '111'
          );
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
   $customers = User::where('role', '4')->orwhere('role', '2')->get()->toArray();
        foreach ($customers as $key => $customer) {
              $message = 'Join quiz of the day';
              $frenchMessage = $this->translation($message);
              $saveNotification = new Notification;
              $saveNotification->user_id = $customer['id'];
              $saveNotification->notification = $message;
              $saveNotification->french_notification = $frenchMessage[0];
              $saveNotification->role = '2';
              $saveNotification->read = '0';
               $saveNotification->image = URL::to('/').'/images/quiz/quiz.png';
              $saveNotification->notification_type = '111';
              $saveNotification->save();
        }

    	return redirect('quiz/list')->with('message', 'Quiz Saved Successfully.');
    }

    public function sendPushNotification($token,$msg="",$deta) {
       $url = 'https://fcm.googleapis.com/fcm/send';
       $fields = array(
             "registration_ids" => array(
                 $token
             ),
              "notification" => array(
                  "title" => "Grigora",
                  "body" => $msg,
                  "sendby" => "Grigora",
                  "establishment_detail" => "Grigora",
                  "type" => "Grigora",
                  "content-available" => 1,
                  "badge" => 0,
                "sound" => "default",
              ),
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
//AAAAl9ypsSw:APA91bE9HbQD0KBUJUngyC1GotYZaWyYbGxDs3zib6ePE-F1Mx67ii4C3DVSIxUVYjz3o7i6JTcGIws8-sdlsfa3JM0VKKqTLTVSgeB-DMJ9gdp7qmIMBJ_ilZRIzc5QqjlsxL1GGyut
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
