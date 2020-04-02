<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Validator;
use random_strings; 
use App\Chat;
use App\Chat_head;
use App\User;
use App\Issue;
use App\UserToken;
use DateTime;
use Carbon\Carbon;

class ChatController extends Controller
{

  	public function chat_list(){

      $chat_users = Chat_head::all();
      $data = array();
      foreach ($chat_users as $key => $value) {
  
        if($value->sender_id == '1'){
           $user = User::where('id',$value->reciever_id)->first();
        }else{
          $user = User::where('id',$value->sender_id)->first();
        }
        $issue =  Issue::where('id',$value->issue_id)->first();

          $date = date('d/m/Y', strtotime($value->updated_at));

          if($date == date('d/m/Y')) {

            $date = date('H:i',strtotime($value->updated_at));
          } 
          else if($date == date('d/m/Y',strtotime(Carbon::yesterday()))) {
            $date = 'Yesterday';
          }else{
            $date = date('Y-m-d',strtotime($value->updated_at));
          }

        $data[$key]['ticket_id'] = $value->ticket_id;
        $data[$key]['issue_id'] = $value->issue_id;
         $data[$key]['issue'] = $issue->name;
        $data[$key]['subissue_id'] = $value->subissue_id;
        $data[$key]['sender_id'] = $value->sender_id;
        $data[$key]['reciever_id'] = $value->reciever_id;
        $data[$key]['last_message'] = $value->last_message;
        $data[$key]['user_name'] = $user->name;
        $data[$key]['user_image'] = $user->image;
        $data[$key]['time']  = $date;
        $data[$key]['user_id'] = $user->id;
        
        # code...
      }
  		return view('admin.chat.chat_list')->with(compact('data'));
  	}
    
  	public function chatview($id,$ticket_id){
      $user = User::where('id',$id)->first();
      $chat = Chat::where('ticket_id',$ticket_id)->get();
      $chatdata = array();
      foreach ($chat as $key => $value) {

        $date = date('d/m/Y', strtotime($value->updated_at));

        if($date == date('d/m/Y')) {

          $date = date('H:i',strtotime($value->updated_at));
        } 
        else if($date == date('d/m/Y',strtotime(Carbon::yesterday()))) {
          $date = 'Yesterday';
        }else{
          $date = date('Y-m-d',strtotime($value->updated_at));
        }

       if($value->sender_id == '1'){
           $user_data = User::where('id',$value->reciever_id)->first();
           $user_admin = User::where('id',$value->sender_id)->first();
        }else{
          $user_data = User::where('id',$value->sender_id)->first();
          $user_admin = User::where('id',$value->reciever_id)->first();
        }

        $chatdata[$key]['user_name'] = $user_data->name;
        $chatdata[$key]['admin_name'] = $user_admin->name;
        $chatdata[$key]['user_image'] = $user_data->image;
        $chatdata[$key]['admin_image'] = $user_admin->image;
        $chatdata[$key]['sender_id'] = $value->sender_id;
        $chatdata[$key]['reciever_id'] = $value->reciever_id;
        $chatdata[$key]['ticket_id'] = $value->ticket_id;
        $chatdata[$key]['issue_id'] = $value->issue_id;
        $chatdata[$key]['subissue_id'] = $value->subissue_id;
        $chatdata[$key]['message'] = $value->message;
        $chatdata[$key]['date'] = $date;
        # code...
      }
      
  		return view('admin.chat.chat')->with(compact('chatdata','user','ticket_id'));
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
    

     public function getchat(Request $request,$athleteid=null,$coachid=null)
      {
        $user = User::where('id',$request->user_id)->first();
        $chat = Chat::where('ticket_id',$request->ticket_id)->orderby('id','DESC')->get()->reverse(); 
             foreach ($chat as $key => $value) {

          $date = date('d/m/Y', strtotime($value->updated_at));

          if($date == date('d/m/Y')) {

            $date = date('H:i',strtotime($value->updated_at));
          } 
          else if($date == date('d/m/Y',now() - (24 * 60 * 60))) {
            $date = 'Yesterday';
          }else{
            $date = date('Y-m-d',strtotime($value->updated_at));
          }
       if($value->sender_id == '1'){
           $user_data = User::where('id',$value->reciever_id)->first();
           $user_admin = User::where('id',$value->sender_id)->first();
        }else{
          $user_data = User::where('id',$value->sender_id)->first();
          $user_admin = User::where('id',$value->reciever_id)->first();
        }
        $chatdata[$key]['user_name'] = $user_data->name;
        $chatdata[$key]['admin_name'] = $user_admin->name;
        $chatdata[$key]['user_image'] = $user_data->image;
        $chatdata[$key]['admin_image'] = $user_admin->image;
        $chatdata[$key]['sender_id'] = $value->sender_id;
        $chatdata[$key]['reciever_id'] = $value->reciever_id;
        $chatdata[$key]['ticket_id'] = $value->ticket_id;
        $chatdata[$key]['issue_id'] = $value->issue_id;
        $chatdata[$key]['subissue_id'] = $value->subissue_id;
        $chatdata[$key]['message'] = $value->message;
        $chatdata[$key]['date'] = $date;
        # code...
      }
        $ticket_id = $request->ticket_id;

       $view = view('admin.chat.conversation_detail',compact('chatdata','user','ticket_id'))->render();
         return response()->json(['html'=>$view]);  
       
       
     }
  	
   public function chat(Request $request){

    try{

        
       $loggedInUser = Auth::user();

            $rules = [                      
               'issue_id' => 'required',
               'subissue_id' => 'required',
               'message' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

                  if($validator->fails())
                {
                    return response()->json([
                       "message" => "Something went wrong!",
                       'errors' => $validator->errors()->toArray(),
                   ], 422);               
                }
           
            if($request->get('ticket_id') == ""){
            	$ticket_id = 'TIC'.$loggedInUser->id.$this->generateRandomString('6');
            }else{
            	$ticket_id = $request->get('ticket_id');
            }
            $chat = new Chat;
            $chat->ticket_id = $ticket_id;
            $chat->issue_id = $request->get('issue_id');
            $chat->subissue_id = $request->get('subissue_id');
            $chat->sender_id = $request->get('sender_id');
      			$chat->reciever_id = $request->get('reciever_id');
      			$chat->message = $request->get('message');
            $chat->message_type = '1';
      			$chat->save();

            $chat_head_chk = Chat_head::where('ticket_id',$ticket_id)->first();
            if($chat_head_chk){
                  Chat_head::where('ticket_id',$ticket_id)->update([
                    'last_message' => $request->get('message'),
                    'sender_id' => '1',
                    'reciever_id' => $request->get('reciever_id'),
                  ]);
            }else{
                $chat_head = new Chat_head;
                $chat_head->ticket_id = $ticket_id;
                $chat_head->issue_id = $request->get('issue_id');
                $chat_head->subissue_id = $request->get('subissue_id');
                $chat_head->sender_id = '1';
                $chat_head->reciever_id = $request->get('reciever_id');
                $chat_head->last_message = $request->get('message');
                $chat_head->save();

            }
      

			if($chat){
				$chat = Chat::where('ticket_id', $chat->ticket_id)->first();
        $customers = User::where('id', $request->get('reciever_id'))->get()->toArray();
  
        foreach ($customers as $key => $customer) {

         $userTokens = UserToken::where('user_id', $customer['id'])->get()->toArray();

         if($userTokens){
          $message = $request->get('message');
          $deta = array(
            'notification_type' => '112',
            'issue_id' => $chat->issue_id,
            'subissue_id' => $chat->subissue_id,
            'ticket_id' => $chat->ticket_id,
            'message' => $message,
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


				return response()->json([
                            "status" => true,
                            "message" => 'Chat saved succesfully',
                            'data' => $chat,
                                                 
                ], 200);
			}else{

				return response()->json([
                            "status" => false,
                            'message' => 'Something went wrong',
                                                 
                ], 200);

			}
        }
        catch(Exception $e){
            $result = [
              'error'=> $e->getMessage(). ' Line No '. $e->getLine() . ' In File'. $e->getFile()
            ];
            Log::error($e->getTraceAsString());
            $result['status'] = 0;
             return $result;
        }
        

    }


     public function SpecificUsersChat(Request $request){

    try{

       $loggedInUser = Auth::user();

            $rules = [                            
              'ticket_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

                  if($validator->fails())
                {
                    return response()->json([
                       "message" => "Something went wrong!",
                       'errors' => $validator->errors()->toArray(),
                   ], 422);               
                }
                

               $chat =  DB::table('chats')->where('ticket_id',$request->get('ticket_id'))->orderBy('updated_at','DESC')->get();
              
               $data = array();
               if($chat != ""){
                   foreach ($chat as $key => $value) {

                      $data[$key]['message'] = $value->message;
                      $data[$key]['sender_id'] = $value->sender_id;
                      $data[$key]['ticket_id'] = $value->ticket_id;
                      $data[$key]['issue_id'] = $value->issue_id;
                      $data[$key]['subissue_id'] = $value->issue_id;
                      $data[$key]['date'] = $value->updated_at;
                      
                    }

                   return response()->json([
                            "status" => 1,
                            'data' => $data,
                                                 
                             ], 200);
               }else{

                    return response()->json([
                                  "status" => 0,
                                  "message" => "Something went wrong!",
                                       
                                   ], 422);

               }
           



		    }
		        catch(Exception $e){
		            $result = [
		              'error'=> $e->getMessage(). ' Line No '. $e->getLine() . ' In File'. $e->getFile()
		            ];
		            Log::error($e->getTraceAsString());
		            $result['status'] = 0;
		             return $result;
		        }


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
