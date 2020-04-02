<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Validator;
use App\User;
use App\Category;
use App\SubCategory;
use App\Cuisine;

class CategoriesController extends Controller
{
    public function add(Request $request){
    	try{
    		$rules = [
		                'name' => 'required',
		                'image' => 'required',
		                'parent_category_id' => 'required',
            		];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
                                        	'status' => false,
                                           	"message" => $validator->errors()->first(),
                                           //'errors' => $validator->errors()->toArray(),
                                       ], 422);              
            }

            $check = Category::where('name', $request->name)->first();
            if($check){
                return response()->json([
                                            'message' => "Category name already exists.",
                                            'status' => false,
                                        ], 422);
            }

            if( $request->file('image')!= ""){
                if (!file_exists( public_path('/images/categories'))) {
                    mkdir(public_path('/images/categories'), 0777, true);
                }
                $path =public_path('/images/categories/');
                $image = $request->file('image');
                $categoryImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/categories');
                $image->move($destinationPath, $categoryImage);
                $url = url('/images/categories/');
                $url = str_replace('/index.php', '', $url);
                $categoryImage = $url.'/'.$categoryImage;
            }else{
                $categoryImage = "";  
            }

            $user = Auth::user();

            $englishWords = array($request->get('name'));
            $frenchWords = $this->translation($englishWords);

            if($request->parent_category_id == '0'){
            	$category = new Category;
            	$category->restaurant_id = $user->id;
            	$category->name = $request->name;
            	$category->french_name = $frenchWords[0];
            	$category->image = $categoryImage;
            	$category->status = '1';
            }else{
            	$category = new SubCategory;
            	$category->category_id = $request->parent_category_id;
            	$category->name = $request->name;
            	$category->french_name = $frenchWords[0];
            	$category->image = $categoryImage;
            	$category->status = '1';
            }

            if($category->save()){
            	return response()->json([
						                    'status' => true,
						                    'message' => "Category Added Successfully.",
						                    'data' => $category
						                ], 200);
            }else{
            	return response()->json([
					                        'message' => "Something Went Wrong!",
					                        'status' => false,
					                    ], 422);
            }

    	}catch (Exception $e) {
            return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
                    ], 422);
        }
    }

    public function edit(Request $request){
        try{
            $rules = [
                        'category_id' => 'required',
                        'name' => 'required',
                        //'image' => 'required',
                        //'parent_category_id' => 'required',
                    ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
                    'status' => false,
                    "message" => $validator->errors()->first(),
                   //'errors' => $validator->errors()->toArray(),
               ], 422);              
            }

            if( $request->file('image')!= ""){
                if (!file_exists( public_path('/images/categories'))) {
                    mkdir(public_path('/images/categories'), 0777, true);
                }
                $path =public_path('/images/categories/');
                $image = $request->file('image');
                $categoryImage = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/images/categories');
                $image->move($destinationPath, $categoryImage);
                $url = url('/images/categories/');
                $url = str_replace('/index.php', '', $url);
                $categoryImage = $url.'/'.$categoryImage;
            }else{
                $category = Category::where('id', $request->get('category_id'))->first();
                $categoryImage = $category->image;  
            }

            $user = Auth::user();

            $englishWords = array($request->get('name'));
            $frenchWords = $this->translation($englishWords);

            //if($request->parent_category_id == '0'){
            $category = Category::where('id', $request->get('category_id'))
                                                    ->update([
                                                                'name' => $request->get('name'),
                                                                'french_name' => $frenchWords[0],
                                                                'image' => $categoryImage
                                                            ]);
            

            if($category){
                $category = Category::where('id', $request->get('category_id'))->first();
                return response()->json([
                                            'status' => true,
                                            'message' => "Category Updated Successfully.",
                                            'data' => $category
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => "Something Went Wrong!",
                                            'status' => false,
                                        ], 422);
            }

        }catch (Exception $e) {
            return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
                    ], 422);
        }
    }

    public function delete($id){
        try{
            $delete = Category::where('id', $id)->delete();

            if($delete){
                return response()->json([
                                            'status' => true,
                                            'message' => "Category Deleted Successfully.",
                                            //'data' => $categories
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => "Something Went Wrong!",
                                            'status' => false,
                                        ], 422);
            }

        }catch (Exception $e) {
            return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
                    ], 422);
        }
    }

    public function listParentCategories(){
    	try{
    		$user = Auth::user();
    		$categories = Category::where(['restaurant_id' => $user->id])->get()->toArray();

    		if($categories){
    			return response()->json([
						                    'status' => true,
						                    'message' => "Categories Found Successfully.",
						                    'data' => $categories
						                ], 200);
    		}else{
    			return response()->json([
						                    'status' => true,
						                    'message' => "Categories Not Found.",
						                    'data' => $categories
						                ], 200);
    		}

    	}catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function listAllCategories(){
        try{
            $categories = Category::limit('5')->get()->toArray();
            //echo'<pre>';print_r($categories);die;
            if($categories){
                return response()->json([
                                            'status' => true,
                                            'message' => "Categories Found Successfully.",
                                            'data' => $categories
                                        ], 200);
            }else{
                return response()->json([
                                            'status' => true,
                                            'message' => "Categories Not Found.",
                                            'data' => $categories
                                        ], 200);
            }

        }catch (Exception $e) {
            return response()->json([
                        'message' => "Something Went Wrong!",
                        'status' => false,
                    ], 422);
        }
    }

    public function getCuisine(){
    	try{
    		$cuisine = Cuisine::where('status', '1')->get()->toArray();
    		if($cuisine){
    			return response()->json([
						                    'status' => true,
						                    'message' => "Cuisine Found Successfully.",
						                    'data' => $cuisine
						                ], 200);
    		}else{
    			return response()->json([
					                        'message' => "Something Went Wrong!",
					                        'status' => false,
					                    ], 422);
    		}
    	}catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
                                    ], 422);
        }
    }

    public function addCuisine(Request $request){
        try{
            $rules = [
                        'name' => 'required',
                        'image' => 'required',
                    ];

            $validator = Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                return response()->json([
                    'status' => false,
                    "message" => $validator->errors()->first(),
                   //'errors' => $validator->errors()->toArray(),
               ], 422);              
            }

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

            $user = Auth::user();
            if($user->language == '1'){
                $content = $request->get('name');
                $check = Cuisine::where('name', 'like', '%' . $content . '%')->first();
                if($check){
                    return response()->json([
                                                'message' => "Cuisine name already exists.",
                                                'status' => false,
                                                //'data' => $cuisine
                                            ], 200);
                }
            }else{
                $content = $request->get('name');
                $check = Cuisine::where('french_name', 'like', '%' . $content . '%')->first();
                if($check){
                    return response()->json([
                                                'message' => "Cuisine name already exists.",
                                                'status' => false,
                                                //'data' => $cuisine
                                            ], 200);
                }
            }

            $englishWords = array($request->get('name'));
            $frenchWords = $this->translation($englishWords);

            $cuisine = new Cuisine;
            $cuisine->name = $request->get('name');
            $cuisine->french_name = $frenchWords[0];
            $cuisine->image = $cuisineImage;
            $cuisine->status = '0';
            if($cuisine->save()){
                return response()->json([
                                            'message' => "Approve request send to admin.",
                                            'status' => true,
                                            'data' => $cuisine
                                        ], 200);
            }else{
                return response()->json([
                                            'message' => "Something Went Wrong!",
                                            'status' => false,
                                        ], 422);
            }
        }catch (Exception $e) {
            return response()->json([
                                        'message' => "Something Went Wrong!",
                                        'status' => false,
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
