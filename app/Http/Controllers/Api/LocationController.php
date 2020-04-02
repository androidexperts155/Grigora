<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\LocationType;
use App\UsersLocation;
use Auth;
use Validator;

class LocationController extends Controller
{
	public function locationTypes()    {
		try{
			$locations = LocationType::orderBy('id', 'Desc')->get()->toArray();	
			if($locations){
				return response()->json([
                                            'status' => true,
                                            'message' => "Location Type Found.",
                                            'data' => $locations
                                        ], 200);
			}else{
				return response()->json([
                                            'status' => false,
                                            'message' => "Location Type Not Found.",
                                            'data' => $locations
                                        ], 200);
			}
		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

	public function addUserLocation(Request $request){
		try{

			$rules = [
                        'location_type_id' => 'required',
                        'address' => 'required',
                        'latitude' => 'required',
                        'longitude' => 'required',
                        //'complete_address' => 'required',
                   	];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json([
                                            'status' => false,
                                            'message' => $errors,
                                            //'errors' => $errors
                                        ], 400);
            }

            $locationType = LocationType::where('id', $request->location_type_id)->first();

			$user = Auth::user();
			$englishWords = array($request->get('address'));
        	$frenchWords = $this->translation($englishWords);
			$userLocation = new UsersLocation;	
			$userLocation->user_id = $user->id;
			$userLocation->location_type_id = $request->location_type_id;
			$userLocation->address = $request->address;
			$userLocation->french_address = $frenchWords[0];
            if($request->has('complete_address') && !empty($request->complete_address)){
                $englishWords1 = array($request->get('complete_address'));
                $frenchWords1 = $this->translation($englishWords1);
                $userLocation->complete_address = $request->complete_address;
                $userLocation->complete_french_address = $frenchWords1[0];
            }
			$userLocation->latitude = $request->latitude;
			$userLocation->longitude = $request->longitude;
			if($userLocation->save()){
                $userLocation->location_type_name = $locationType->name;
                $userLocation->location_type_french_name = $locationType->french_name;
				return response()->json([
                                            'status' => true,
                                            'message' => "User's Location Added.",
                                            'data' => $userLocation
                                        ], 200);
			}else{
				return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);	
			}
		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

	public function getUserLocations(){
		try{
			$user = Auth::user();
			$userLocations = UsersLocation::where('user_id', $user->id)->get()->toArray();
			if($userLocations){
                foreach ($userLocations as $key => $userLocation) {
                    $locationType = LocationType::where('id', $userLocation['location_type_id'])->first();
                    $userLocations[$key]['location_type_name'] = $locationType['name'];
                    $userLocations[$key]['location_type_french_name'] = $locationType['french_name'];
                }
				return response()->json([
                                            'status' => true,
                                            'message' => "User's Location Found.",
                                            'data' => $userLocations
                                        ], 200);
			}else{
				return response()->json([
                                            'status' => false,
                                            'message' => "User's Location Not Found.",
                                            'data' => $userLocations
                                        ], 200);
			}
		}catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
        }
	}

    public function deleteUserLocation($id){
        try{
            $checkLocation = UsersLocation::where('id', $id)->first();
            if($checkLocation){
                $deleteLocation = UsersLocation::where('id', $id)->delete();
                if($deleteLocation){
                    return response()->json([
                                                'status' => true,
                                                'message' => "User's Location Deleted.",
                                                //'data' => $userLocation
                                            ], 200);
                }else{
                    return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);        
                }
            }else{
                return response()->json([
                                            'status' => false,
                                            'message' => "User's Location Not Found.",
                                            //'data' => $userLocation
                                        ], 200);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'status' => false,
                                        'message' => "Something Went Wrong!"
                                    ], 422);
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
