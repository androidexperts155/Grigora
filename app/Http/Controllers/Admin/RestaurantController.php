<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Cuisine;
use App\Item;
use App\RestaurantCuisine;
use App\Mail\ApproveAcount;
use App\RatingReview;
use App\UserOrderType;
use Mail;

class RestaurantController extends Controller
{
    public function add(){
        $cusines = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();
    	return view('admin.restaurant.add', ['cusines' => $cusines]);
    }

    public function itemList($id){
        $items = Item::where('restaurant_id', $id)->get()->toArray();
        return view('admin.restaurant.items', ['items' =>$items]);
    }

	public function save(Request $request){
        //echo'<pre>';print_r($request->all());die;
        $request->validate([
                            'email' => 'required|email|unique:users',
                            'password' => 'required',
                            'phone' => 'required|unique:users',
                            'image' => 'required',
                            'id_proof' => 'required',
                            'address' => 'required',
                            'lat' => 'required',
                            'long' => 'required',
                            'pure_veg' => 'required',
                            'pickup' => 'required',
                            'preparing_time' => 'required',
                            'opening_time' => 'required',
                            'closing_time' => 'required',
                            'full_time' => 'required',
                            'franchisee_proof' => 'required'
                           
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
        }

        //echo'<pre>';print_r($request->all());die;
        $englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);
        $englishWords_address = array($request->get('address'));
        $frenchWords_address = $this->translation($englishWords_address);
    	$password = $request->get('password');

        $user = new User;
        $user->name = $request->get('name');
        $user->french_name = $frenchWords['0'];
        $user->address = $request->get('address');
        $user->french_address = $frenchWords_address['0'];
        $user->email = $request->get('email');
        $user->phone = $request->get('phone');
        $user->image = $profileImage;
        $user->id_proof = $idProofImage;
        $user->franchisee_proof = $franchiseeProofImage;
        $user->password = bcrypt($password);
        $user->latitude = $request->get('lat');
        $user->longitude =$request->get('long');
        $user->pure_veg  = $request->get('pure_veg');
        $user->pickup = $request->get('pickup');
        $user->preparing_time = $request->get('preparing_time');
        $user->opening_time = date("H:i:s", strtotime($request->get('opening_time')));
        $user->closing_time = date("H:i:s", strtotime($request->get('closing_time')));;
        $user->full_time = $request->get('full_time');
        $user->role = '4';
        if($user->save()){

             $userId = $user->id;
                $userName = rand ( 1000000 , 9999999 );
                $userName = 'G'.$userName;
                $update = User::where('id', $user->id)->update(['username' => $userName]);
           /* foreach ($request->cusines as $key => $cuisinId) {
                $cuisins = Cuisine::where('id', $cuisinId)->first();
                $cuisin = new RestaurantCuisine;
                $cuisin->restaurant_id = $request->id;
                $cuisin->cuisine_id = $cuisinId;
                $cuisin->save();
            }*/
            return redirect('restaurant/list')->with('message', 'Restaurant Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }    

    public function list(){
    	$users = User::where('role', '4')->orderBy('id', 'Desc')->get()->toArray();
        //echo'<pre>';print_r($users);die;
        if($users){
            foreach ($users as $key => $user) {
            
                $ratings = RatingReview::where('receiver_type', '2')
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
                $users[$key]['average_rating'] = $avergeRating;
                $users[$key]['total_rating'] = $totalRating;
            }
        // }else{
        //     $users['average_rating'] = $avergeRating;
        //     $users['total_rating'] = $totalRating;
        }
    	return view('admin.restaurant.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = User::where('id', $id)->first();
        $restaurantCuisine = RestaurantCuisine::where('restaurant_id', $id)->pluck('cuisine_id', 'id')->toArray();
        $cusines = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();
    	return view('admin.restaurant.edit', ['users' => $users, 'cusines' => $cusines, 'restaurantCuisine' => $restaurantCuisine]);
    }

    public function view($id){
        $users = User::where('id', $id)->first();
        $restaurantCuisine = RestaurantCuisine::where('restaurant_id', $id)->pluck('cuisine_id', 'id')->toArray();
        $cusines = Cuisine::where('status', '1')->pluck('name', 'id')->toArray();
        $cuisins = array('None');
        if($cusines){
            foreach ($restaurantCuisine as $key => $cuisineId) {
                $cuisins[] = $cusines[$cuisineId];
            }
        }

        $ratings = RatingReview::where('receiver_type', '2')
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

        $users->cuisins = implode(' , ', $cuisins);
        return view('admin.restaurant.view', ['users' => $users]);
    }

    public function delete($id){
    	$user = User::where('id', $id)->delete();
        $check = RestaurantCuisine::where('restaurant_id', $id)->get()->toArray();
        if($check){
            $delete = RestaurantCuisine::where('restaurant_id', $id)->delete();
        }
    	if($user){
            return redirect('restaurant/list')->with('message', 'Restaurant Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function approve($id){
        $user = User::where('id', $id)->first();
        $user->approved = '1';
        if($user->save()){
            $customers = User::where('role', '2')
                              ->where('approved', '1')
                              ->get()
                              ->toArray();
            if($customers){
                foreach ($customers as $key => $customer) {
                
                    $check = UserOrderType::where('user_id', $customer['id'])
                                            ->where('restaurant_id', $id)
                                            ->first();
                    if($check){
                    }else{
                        $userOrderType = new UserOrderType;
                        $userOrderType->user_id = $customer['id'];
                        $userOrderType->restaurant_id = $id;
                        $userOrderType->save();
                    }
                }
            }
            Mail::to($user->email)->send(new ApproveAcount($user->name));
            return redirect('restaurant/list')->with('message', 'Restaurant Successfully Approved.');
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
        //echo'<pre>';print_r($request->all());die;

        $update = User::where('id', $request->id)->update([
                                                            'name' => $request->name,
                                                            'email' => $request->email,
                                                            'phone' => $request->phone,
                                                            'image' => $profileImage,
                                                            'id_proof' => $idProofImage
                                                            ]);

        $check = RestaurantCuisine::where('restaurant_id', $request->id)->get()->toArray();
        
        if($check){
            $delete = RestaurantCuisine::where('restaurant_id', $request->id)->delete();
        }

        foreach ($request->cusines as $key => $cuisinId) {
            $cuisins = Cuisine::where('id', $cuisinId)->first();
            $cuisin = new RestaurantCuisine;
            $cuisin->restaurant_id = $request->id;
            $cuisin->cuisine_id = $cuisinId;
            $cuisin->save();
        }
        

        if($update){
            return redirect('restaurant/list')->with('message', 'Restaurant Successfully Updated.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
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
