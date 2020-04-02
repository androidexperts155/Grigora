<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Chat;
use App\Chat_head;
use App\Issue;
use App\SubIssue;
use URL;
use Validator;
use DB;
use Auth;
use App\User;

class ChatController extends Controller
{
    public function chat(Request $request){

    try{

       $loggedInUser = Auth::user();
       

            $rules = [                    
               'issue_id' => 'required',
               
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
            	//$ticket_id = 'Tic_'.$loggedInUser->id.$this->random_strings(6);
              $ticket_id = 'TIC'.$loggedInUser->id.$this->generateRandomString('6');
            }else{
            	$ticket_id = $request->get('ticket_id');
            }
            $chat = new Chat;
            $chat->ticket_id = $ticket_id;
            $chat->issue_id = $request->get('issue_id');
            $chat->subissue_id = $request->get('subissue_id');
            $chat->sender_id = $loggedInUser->id;
			      $chat->reciever_id = '1';
			      $chat->message = $request->get('message');
			      $chat->save();

            $chat_head_chk = Chat_head::where('ticket_id',$ticket_id)->first();
            if($chat_head_chk){
                  Chat_head::where('ticket_id',$ticket_id)->update([
                    'last_message' => $request->get('message'),
                    'sender_id' => $loggedInUser->id,
                    'reciever_id' => '1',
                  ]);
            }else{
                $chat_head = new Chat_head;
                $chat_head->ticket_id = $ticket_id;
                $chat_head->issue_id = $request->get('issue_id');
                $chat_head->subissue_id = $request->get('subissue_id');
                $chat_head->sender_id = $loggedInUser->id;
                $chat_head->reciever_id = '1';
                $chat_head->last_message = $request->get('message');
                $chat_head->save();

            }
		     


			if($chat){
				$data = Chat::where('id', $chat->id)->first();
				return response()->json([
                            "status" => true,
                            "message" => 'Chat saved succesfully',
                            'data' => $data,
                                                 
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

    public function chat_heads(Request $request){

    try{

       $loggedInUser = Auth::user();
       

           $chat_heads = Chat_head::where('sender_id',$loggedInUser->id)->orwhere('reciever_id',$loggedInUser->id)->orderby('updated_at','DESC')->get();
           $data = array();
           foreach ($chat_heads as $key => $value) {
            $sender = User::where('id',$value->sender_id)->first();
            $reciever = User::where('id',$value->reciever_id)->first();
            $issue = Issue::where('id',$value->issue_id)->first();
            if(is_null($value->subissue_id)){
                $subissue_name = '';
            }else{
              $subissue = SubIssue::where('id',$value->subissue_id)->first();

              $subissue_name = $subissue->name;
            }
              $data[$key]['id'] = $value->id;
              $data[$key]['ticket_id'] = $value->ticket_id;
              $data[$key]['issue_id'] = $value->issue_id;
              $data[$key]['issue_name'] = $issue->name;
              $data[$key]['subissue_id'] = $value->subissue_id;
              $data[$key]['subissue_name'] = $subissue_name;
              $data[$key]['sender_id'] = $value->sender_id;
              $data[$key]['sender_name'] = $sender->name;
              $data[$key]['reciever_id'] = $value->reciever_id;
              $data[$key]['reciever_name'] = $reciever->name;
              $data[$key]['last_message'] = $value->last_message;
              $data[$key]['message_type'] = $value->message_type;
              $data[$key]['created_at'] = $value->created_at;
              $data[$key]['updated_at'] = $value->updated_at;

             # code...
           }
         


      if($data){
        
        return response()->json([
                            "status" => true,
                            'data' => $data,
                                                 
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

    public function getchat(Request $request){

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

   function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    } 
}
