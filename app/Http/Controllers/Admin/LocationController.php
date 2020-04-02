<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\LocationType;
use App\UsersLocation;

class LocationController extends Controller
{
    public function list(){
    	$locations = LocationType::orderBy('id', 'Desc')->get()->toArray();
    	return view('admin.location.index', ['locations' => $locations]);
    }

    public function add(){
    	return view('admin.location.add');
    }

    public function save(Request $request){
        //echo'<pre>';print_r($request->all());die;
    	if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/location'))) {
                mkdir(public_path('/images/location'), 0777, true);
            }
            $path =public_path('/images/location/');
            $image = $request->file('image');
            $locationImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/location');
            $image->move($destinationPath, $locationImage);
            $url = url('/images/location/');
            $url = str_replace('/index.php', '', $url);
            $locationImage = $url.'/'.$locationImage;
        }else{
            $locationImage = "";  
        }
        
    	$englishWords = array($request->get('name'),$request->get('description'));
        $frenchWords = $this->translation($englishWords);
        //echo'<pre>';print_r($frenchWords);die;
    	$locationType = new LocationType;
    	$locationType->name = $request->name;
    	$locationType->french_name = $frenchWords[0];
    	$locationType->description = $request->description;
        if($request->name == $request->description){
    	   $locationType->french_description = $frenchWords[0];
        }else{
            $locationType->french_description = $frenchWords[1];
        }
    	$locationType->image = $locationImage;
    	if($locationType->save()){
    		return redirect('location/list')->with('message', 'Location Type Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }

    public function update(Request $request){
    	if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/location'))) {
                mkdir(public_path('/images/location'), 0777, true);
            }
            $path =public_path('/images/location/');
            $image = $request->file('image');
            $locationImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/location');
            $image->move($destinationPath, $locationImage);
            $url = url('/images/location/');
            $url = str_replace('/index.php', '', $url);
            $locationImage = $url.'/'.$locationImage;
        }else{
        	$location = LocationType::where('id', $request->id)->first();
            $locationImage = $location->image;  
        }

    	$englishWords = array($request->get('name'),$request->get('description'));
        $frenchWords = $this->translation($englishWords);
        //echo'<pre>';print_r($frenchWords);die;
        $location = LocationType::where('id', $request->get('id'))->first();
     	$location->name = $request->name;
    	$location->french_name = $frenchWords[0];
    	$location->description = $request->description;
    	if($request->name == $request->description){
            $location->french_description = $frenchWords[0];
        }else{
            $location->french_description = $frenchWords[1];
        }
    	$location->image = $locationImage;
    	if($location->save()){
    		return redirect('location/list')->with('message', 'Location Type Successfully Updated.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }   
    }

    public function edit($id){
    	$location = LocationType::where('id', $id)->first();
        //echo'<pre>';print_r($location);die;
    	return view('admin.location.edit', ['location' => $location]);
    }

    public function delete($id){
    	$delete = LocationType::where('id', $id)->delete();
    	$delete1 = UsersLocation::where('location_type_id', $id)->delete();
    	if($delete){
    		return redirect('location/list')->with('message', 'Location Type Successfully Updated.');
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
