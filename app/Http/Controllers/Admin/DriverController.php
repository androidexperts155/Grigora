<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\RatingReview;
use App\NinjaAttendance;
use App\Mail\ApproveAcount;
use Mail;
use DateTime;
use App\CompanyOffline;

class DriverController extends Controller
{
    public function add(){
    	return view('admin.driver.add');
    }

	public function save(Request $request){
        //echo'<pre>';print_r($request->all());die;
        $request->validate([
                            'email' => 'required|email|unique:users',
                            'password' => 'required',
                            'phone' => 'required|unique:users',
                            'image' => 'required',
                            'id_proof' => 'required'
                        ]);
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
        }
    	$password = $request->get('password');
        $user = new User;
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->phone = $request->get('phone');
        $user->image = $profileImage;
        $user->id_proof = $idProofImage;
        $user->password = bcrypt($password);
        $user->role = '3';
        if($user->save()){
            return redirect('driver/list')->with('message', 'Driver Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }    

    public function updateStatus($user_id,$status,$attendance_id=null){
        
        $users = User::where('role', '3')->where("id",$user_id)->update(["busy_status"=>$status]);

        $users_res = User::where("id",$user_id)->first();

        
        if($status == 0){

            $companyoffline =   CompanyOffline::where('name','CompanyOffline')->update([
            'status' => '1',
            ]);

            $ninja_attendance = new NinjaAttendance;
            $ninja_attendance->driver_id = $user_id;
            $ninja_attendance->online_time = date('Y-m-d H:i:s');
            $ninja_attendance->save();

            $message = "Driver Marked Online.";
            $notification_message = "You Are online Now";
            $deta = array(
                'user_date' => $users_res,
                'notification_type' => '113',
            );
        }else{
            if($attendance_id){
                $data = NinjaAttendance::where('id',$attendance_id)->update([
                    'offline_time' => date('Y-m-d H:i:s'),
                ]);
            }
           
            $message = "Driver Marked Offline.";
            $notification_message = "You Are offline";
            $deta = array(
                'user_date' => $users_res,
                'notification_type' => '114',
            );
            
        }
        if($users_res->device_type == 0){
            $this->sendPushNotification($users_res->device_token,$notification_message,$deta);
        }else{
           $iosPushNotification =  $this->iosPushNotification($users_res->device_token,$notification_message,$deta);    
        
        }
        return redirect('driver/list')->with('message', $message);
    }

    public function list(){
    	$users = User::where('role', '3')->orderBy('id', 'Desc')->get()->toArray();
        if($users){
            foreach ($users as $key => $user) {
                $date_week = \Carbon\Carbon::today()->subDays(7);
                $date_month = \Carbon\Carbon::today()->subDays(30);
                $attendance = NinjaAttendance::where('driver_id',$user['id'])->orderby('id','DESC')->first();
                if($attendance){
                    $attendance_id = $attendance->id;
                }else{
                     $attendance_id = '';
                }
                $weeklyhour= array();
                $monthlyhour= array();
                $Driverattendance_weekly = NinjaAttendance::where('driver_id',$user['id'])->where('created_at', '>=', $date_week)->get();

                foreach ($Driverattendance_weekly as $key1 => $value1) {

                    # code...
                    $date1 = $value1->online_time;
                    $date2 = $value1->offline_time;
                    $timestamp1 = strtotime($date1);
                    $timestamp2 = strtotime($date2);
                    //$date1 = new DateTime($value1->online_time);
                    //$date2 = new DateTime($value1->offline_time);
                     
                   // $diff = $date2->diff($date1)->format("%s");
                    if(!empty($timestamp2)&&!empty($timestamp1)){
                        $diff =  $timestamp2 - $timestamp1;
                      $weeklyhour[] = $diff; 
                    }
                      

                }
                    $init = array_sum($weeklyhour);

                    $hours = floor($init / 3600);
                    $minutes = floor(($init / 60) % 60);
                    $seconds = $init % 60;
                    $weeklyfinal = $hours.':'.$minutes.':'.$seconds;
                    
                $Driverattendance_monthly = NinjaAttendance::where('driver_id',$user['id'])->where('created_at', '>=', $date_month)->get();

                
                foreach ($Driverattendance_monthly as $key2 => $value2) {
                    # code...
                    $date1 = $value2->online_time;
                    $date2 = $value2->offline_time;
                    $timestamp1 = strtotime($date1);
                    $timestamp2 = strtotime($date2);
                    if(!empty($timestamp2)&&!empty($timestamp1)){
                        $diff =  $timestamp2 - $timestamp1;
                      $monthlyhour[] = $diff; 
                    }
                   
                    

                }
                     $init1 = array_sum($monthlyhour);
                    $hours1 = floor($init1 / 3600);
                    $minutes1 = floor(($init1 / 60) % 60);
                    $seconds1 = $init % 60;
                    $monthlyfinal = $hours1.':'.$minutes1.':'.$seconds1;


                
                $ratings = RatingReview::where('receiver_type', '3')
                                                        ->where('receiver_id', $user['id'])
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
                $users[$key]['attendance_id'] =  $attendance_id;
                $users[$key]['weeklyhour'] =   $weeklyfinal;
                $users[$key]['monthlyhour'] = $monthlyfinal; 

                $users[$key]['average_rating'] = $avergeRating;
                $users[$key]['total_rating'] = $totalRating;
            }

        // }else{
        //     $users['average_rating'] = $avergeRating;
        //     $users['total_rating'] = $totalRating;
        }
    	return view('admin.driver.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = User::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
    	return view('admin.driver.edit', ['users' => $users]);
    }

    public function view($id){
        // $users = DB::table('users')
        //                         ->leftJoin('user_rewards', 'users.id', '=', 'user_rewards.user_id')
        //                         ->where('users.id', $id)
        //                         ->first();
        $users = User::where('id', $id)->first();
        $ratings = RatingReview::where('receiver_type', '3')
                                                    ->where('receiver_id', $id)
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
        $users->average_rating = $avergeRating;
        $users->total_rating = $totalRating;
        return view('admin.driver.view', ['users' => $users]);
    }

    public function delete($id){
    	$user = User::where('id', $id)->delete();
    	if($user){
            return redirect('driver/list')->with('message', 'Driver Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function approve($id){
        $user = User::where('id', $id)->first();
        $user->approved = '1';
        if($user->save()){
            Mail::to($user->email)->send(new ApproveAcount($user->name));
            return redirect('driver/list')->with('message', 'Driver Successfully Approved.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function update(Request $request){
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
            $user = User::where('id', $request->get('id'))->first();  
            $idProofImage = $user->id_proof;
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
            $user = User::where('id', $request->get('id'))->first();  
            $profileImage = $user->image; 
        }
        $update = User::where('id', $request->id)->update([
                                                            'name' => $request->name,
                                                            'email' => $request->email,
                                                            'phone' => $request->phone,
                                                            'image' => $profileImage,
                                                            'id_proof' => $idProofImage
                                                            ]);
        if($update){
            return redirect('driver/list')->with('message', 'Driver Successfully Updated.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
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
}
