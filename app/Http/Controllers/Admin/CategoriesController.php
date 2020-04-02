<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Category;

class CategoriesController extends Controller
{
    public function add(){
    	return view('admin.categories.add');
    }

	public function save(Request $request){
        

        if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/categories'))) {
                mkdir(public_path('/images/categories'), 0777, true);
            }
            $path =public_path('/images/categories/');
            $image = $request->file('image');
            $cuisineImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/categories');
            $image->move($destinationPath, $cuisineImage);
            $url = url('/images/categories/');
            $url = str_replace('/index.php', '', $url);
            $cuisineImage = $url.'/'.$cuisineImage;
        }else{
            $cuisineImage = "";  
        }

    	$englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);

        $categories = new Category;
        $categories->name = $request->get('name');
        $categories->french_name = $frenchWords[0];
        $categories->image = $cuisineImage;
        $categories->status = '1';
        if($categories->save()){
            return redirect('categories/list')->with('message', 'Category Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }    

    public function list(){
    	$users = Category::where('status', '1')->orderBy('id', 'Desc')->get()->toArray();
    	return view('admin.categories.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = Category::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
    	return view('admin.categories.edit', ['users' => $users]);
    }

    public function view($id){
        $users = Category::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
        return view('admin.categories.view', ['users' => $users]);
    }

    public function delete($id){
    	$user = Category::where('id', $id)->delete();
    	if($user){
            return redirect('categories/list')->with('message', 'Category Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function update(Request $request){
        
        if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/categories'))) {
                mkdir(public_path('/images/categories'), 0777, true);
            }
            $path =public_path('/images/categories/');
            $image = $request->file('image');
            $cuisineImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/categories');
            $image->move($destinationPath, $cuisineImage);
            $url = url('/images/categories/');
            $url = str_replace('/index.php', '', $url);
            $cuisineImage = $url.'/'.$cuisineImage;
        }else{
            $user = Category::where('id', $request->get('id'))->first();  
            $cuisineImage = $user->image;
        }

        $englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);
//echo'<pre>';print_r($frenchWords);die;
        $update = Category::where('id', $request->id)->update([
                                                            'name' => $request->name,
                                                            'french_name' => $frenchWords[0],
                                                            'image' => $cuisineImage,
                                                            ]);
        if($update){
            return redirect('categories/list')->with('message', 'Category Successfully Updated.');
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
