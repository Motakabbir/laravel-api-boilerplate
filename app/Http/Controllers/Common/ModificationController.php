<?php

namespace App\Http\Controllers\Common;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use Auth;
use Image;

class ModificationController
{
    /**
     * BASE64 TO IMAGE CONVERT FUNCTION
     */
    static public function base64ToImage($base64_image,$traget_path,$watermarkPos=false){
        $filename = null;
            
        if($base64_image!= "" || !is_null($base64_image)){
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
                $image_data = base64_decode($image_data);
                $filename = uniqid().'.png';
                Storage::disk('public')->put($traget_path.'/'.$filename, $image_data);

                if($watermarkPos){
                    $imgPath = public_path('storage/'.$traget_path.'/'.$filename);
                    $img = Image::make($imgPath);
                    
                    /**
                     * Get Logo Info Data
                     */
                    $logo_info_data_path = storage_path('app/public') . "/json/logo-info.json";
                    $getContents = file_get_contents($logo_info_data_path);
                    preg_match("/\{(.*)\}/s", $getContents, $matches);
                    $data = json_decode($matches[0]);
                    
                    if($data->exist_watermark_logo){
                        $watermarkImgPath = config('global.logoz_base_path').'/'.$data->exist_watermark_logo;
                        
                        if(file_exists($watermarkImgPath)){
                            
                            $watermarkImg = Image::make($watermarkImgPath)->resize(100,  null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        
                            if($watermarkPos=='center') $img->insert($watermarkImg,$watermarkPos);
                            else $img->insert($watermarkImg, $watermarkPos, 10, 10);
                            
                            $img->save(storage_path('app/public/'.$traget_path.'/'.$filename));
                        }
                    }
                }
            }
        }

        return $filename;
    }

    /**
     * SAVE CONTENT
     */
    static public function save_content($obj, $data, $get_last_id=''){
        try{           
            $user_id = Auth::id();
            // return gettype($data);
            if(gettype($data)=='object') $getData = $data->toArray();
            else $getData = $data;

            foreach($getData as $key => $val){
                $obj->$key = $val;
            }
            
            if($user_id) $obj->created_by        = $user_id;

            if($obj->save()){
                if($get_last_id) return $obj->id;
                
                $data = [
                    'data'      => $obj,
                    'status'    => true,
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => false,
                    'code'    => '404',
                    'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t save.'
                ];

                return response()->json($data, 404);
            }
        }catch(\Exception $e){
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];

            return response()->json($data, 404);
        }
    }

    /**
     * UPDATE CONTENT
     */
    static public function update_content($obj, $data, $req_id, $field="id"){
        try{                
            $user_id = Auth::id();

            if($field=='id') $obj = $obj->find($req_id);
            else $obj = $obj->where($field, $req_id)->first();

            // return $obj;

            // return gettype($data);
            if(gettype($data)=='object') $getData = $data->toArray();
            else $getData = $data;

            foreach($getData as $key => $val){
                $obj->$key = $val;
            }
            $obj->updated_by = $user_id;

            // return ($obj);

            if($obj->update()){
                $data = [
                    'data'      => $obj,
                    'status'    => true,
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been updated successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => false,
                    'code'    => '404',
                    'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t update.'
                ];

                return response()->json($data, 404);
            }
        }catch(\Exception $e){
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];

            return response()->json($data, 404);
        }
    }
}
?>