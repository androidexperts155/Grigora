<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Cuisine;

class CuisineController extends Controller
{
    public function add(){
    	return view('admin.cuisine.add');
    }

	public function save(Request $request){
        

        if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/cuisine'))) {
                mkdir(public_path('/images/cuisine'), 0777, true);
            }
            $path =public_path('/images/cuisine/');
            $image = $request->file('image');
            $cuisineImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/cuisine');
            $image->move($destinationPath, $cuisineImage);
            $url = url('/images/cuisine/');
            $url = str_replace('/index.php', '', $url);
            $cuisineImage = $url.'/'.$cuisineImage;
        }else{
            $cuisineImage = "";  
        }

        if( $request->file('icon')!= ""){
            if (!file_exists( public_path('/images/cuisine'))) {
                mkdir(public_path('/images/cuisine'), 0777, true);
            }
            $path =public_path('/images/cuisine/');
            $image = $request->file('icon');
            $cuisineIcon = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/cuisine');
            $image->move($destinationPath, $cuisineIcon);
            $url = url('/images/cuisine/');
            $url = str_replace('/index.php', '', $url);
            $cuisineIcon = $url.'/'.$cuisineIcon;
        }else{
            $cuisineIcon = "";
        }

        if( $request->file('background_icon')!= ""){
            if (!file_exists( public_path('/images/cuisine'))) {
                mkdir(public_path('/images/cuisine'), 0777, true);
            }
            $path =public_path('/images/cuisine/');
            $image = $request->file('background_icon');
            $cuisineBakImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/cuisine');
            $image->move($destinationPath, $cuisineBakImage);
            $url = url('/images/cuisine/');
            $url = str_replace('/index.php', '', $url);
            $cuisineBakImage = $url.'/'.$cuisineBakImage;
        }else{
            $cuisineBakImage = "";  
        }

    	$englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);

        $cuisine = new Cuisine;
        $cuisine->name = $request->get('name');
        $cuisine->french_name = $frenchWords[0];
        $cuisine->image = $cuisineImage;
        $cuisine->icon = $cuisineIcon;
        $cuisine->background_icon = $cuisineBakImage;
        $cuisine->status = '1';
        if($cuisine->save()){
            return redirect('cuisine/list')->with('message', 'Cuisine Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }    

    public function list(){
    	$users = Cuisine::orderBy('status', 'Asc')->get()->toArray();
    	return view('admin.cuisine.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = Cuisine::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
    	return view('admin.cuisine.edit', ['users' => $users]);
    }

    public function approve($id){
        $approve = Cuisine::where('id', $id)->update(['status' => '1']);
        if($approve){
            return redirect('cuisine/list')->with('message', 'Cuisine Successfully Approved.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function view($id){
        $users = Cuisine::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
        return view('admin.cuisine.view', ['users' => $users]);
    }

    public function delete($id){
    	$user = Cuisine::where('id', $id)->delete();
    	if($user){
            return redirect('cuisine/list')->with('message', 'Cuisine Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function update(Request $request){
        
        if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/cuisine'))) {
                mkdir(public_path('/images/cuisine'), 0777, true);
            }
            $path =public_path('/images/cuisine/');
            $image = $request->file('image');
            $cuisineImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/cuisine');
            $image->move($destinationPath, $cuisineImage);
            $url = url('/images/cuisine/');
            $url = str_replace('/index.php', '', $url);
            $cuisineImage = $url.'/'.$cuisineImage;
        }else{
            $user = Cuisine::where('id', $request->get('id'))->first();  
            $cuisineImage = $user->image;
        }

        if( $request->file('icon')!= ""){
            if (!file_exists( public_path('/images/cuisine'))) {
                mkdir(public_path('/images/cuisine'), 0777, true);
            }
            $path =public_path('/images/cuisine/');
            $image = $request->file('icon');
            $cuisineIcon = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/cuisine');
            $image->move($destinationPath, $cuisineIcon);
            $url = url('/images/cuisine/');
            $url = str_replace('/index.php', '', $url);
            $cuisineIcon = $url.'/'.$cuisineIcon;
        }else{
            $user = Cuisine::where('id', $request->get('id'))->first();  
            $cuisineIcon = $user->icon;
        }

        if( $request->file('background_icon')!= ""){
            if (!file_exists( public_path('/images/cuisine'))) {
                mkdir(public_path('/images/cuisine'), 0777, true);
            }
            $path =public_path('/images/cuisine/');
            $image = $request->file('background_icon');
            $cuisineBakImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/cuisine');
            $image->move($destinationPath, $cuisineBakImage);
            $url = url('/images/cuisine/');
            $url = str_replace('/index.php', '', $url);
            $cuisineBakImage = $url.'/'.$cuisineBakImage;
        }else{
            $user = Cuisine::where('id', $request->get('id'))->first(); 
            $cuisineBakImage = $user->background_icon;  
        }

        $englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);
//echo'<pre>';print_r($frenchWords);die;
        $update = Cuisine::where('id', $request->id)->update([
                                                            'name' => $request->name,
                                                            'french_name' => $frenchWords[0],
                                                            'image' => $cuisineImage,
                                                            'icon' => $cuisineIcon,
                                                            'background_icon' => $cuisineBakImage,
                                                            ]);
        if($update){
            return redirect('cuisine/list')->with('message', 'Cuisine Successfully Updated.');
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
