<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Brand;

class BrandsController extends Controller
{
    public function add(){
    	return view('admin.brands.add');
    }

	public function save(Request $request){

		if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/brands'))) {
                mkdir(public_path('/images/brands'), 0777, true);
            }
            $path =public_path('/images/brands/');
            $image = $request->file('image');
            $brandImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/brands');
            $image->move($destinationPath, $brandImage);
            $url = url('/images/brands/');
            $url = str_replace('/index.php', '', $url);
            $brandImage = $url.'/'.$brandImage;
        }else{
            $brandImage = "";  
        }

    	$englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);

        $brand = new Brand;
        $brand->name = $request->get('name');
        $brand->french_name = $frenchWords[0];
        $brand->image = $brandImage;
        $brand->status = '1';
        if($brand->save()){
            return redirect('brand/list')->with('message', 'Brand Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }    

    public function list(){
    	$users = Brand::orderBy('status', 'Asc')->get()->toArray();
    	return view('admin.brands.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = Brand::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
    	return view('admin.brands.edit', ['users' => $users]);
    }

    public function approve($id){
        $approve = Brand::where('id', $id)->update(['status' => '1']);
        if($approve){
            return redirect('brands/list')->with('message', 'Brand Successfully Approved.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function view($id){
        $users = Brand::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
        return view('admin.brands.view', ['users' => $users]);
    }

    public function delete($id){
    	$user = Brand::where('id', $id)->delete();
    	if($user){
            return redirect('brands/list')->with('message', 'Brand Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function update(Request $request){
        
        if( $request->file('image')!= ""){
            if (!file_exists( public_path('/images/brands'))) {
                mkdir(public_path('/images/brands'), 0777, true);
            }
            $path =public_path('/images/brands/');
            $image = $request->file('image');
            $cuisineImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/brands');
            $image->move($destinationPath, $cuisineImage);
            $url = url('/images/brands/');
            $url = str_replace('/index.php', '', $url);
            $cuisineImage = $url.'/'.$cuisineImage;
        }else{
            $user = Brand::where('id', $request->get('id'))->first();  
            $cuisineImage = $user->image;
        }

        

        $englishWords = array($request->get('name'));
        $frenchWords = $this->translation($englishWords);
//echo'<pre>';print_r($frenchWords);die;
        $update = Brand::where('id', $request->id)->update([
                                                            'name' => $request->name,
                                                            'french_name' => $frenchWords[0],
                                                            'image' => $cuisineImage,
                                                            ]);
        if($update){
            return redirect('brand/list')->with('message', 'Brand Successfully Updated.');
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
