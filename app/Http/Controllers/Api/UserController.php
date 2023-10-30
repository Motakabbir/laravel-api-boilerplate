<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\EncryptionController as EncryptionController;
use App\Http\Controllers\Common\SmsController as SmsController;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserInfo;
use App\Models\PetInfo;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Crypt;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Traits\Access;
use App\Http\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{ 
    use Access;
    use HttpResponses;
       
    protected $mobile_pattern = "/^[\+]?[0-9]{1,3}?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,9}$/";

    public function AuthCodeGenerator(User $obj, Request $request){
        $obj = $obj->find($request['user_id']);
        if(!empty($obj)){
            $obj->auth_code = mt_rand(100000, 999999);            
            if($obj->update()){
                $sms_data = [];
                $sms_data['number'] = $obj->mobile;
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour activation code is " . $obj->auth_code . "\n" . config('global.domain_url');
                $getCode = SmsController::sendTo($sms_data);
                return response()->json([
                    "status" => "success", 
                    "message" =>'New Otp Send',
                    "auth_code" =>$obj->auth_code,
                    //'sms_status' => SmsController::smsStatus($getCode),
                    'sms_status' =>1,
                    "error" => false ], 200);              
            }else  return response()->json([
                "status" => "fail", 
                "message" =>'User Blocked',
                "error" => true ], 404);
        }else  return response()->json([
            "status" => "fail", 
            "message" =>'No Data Found',
            "error" => true ], 404);
    }

    public function ForgotPassword(User $obj, Request $request){
        $email = false; $mobile = false;
        if(filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)){
            $email = true;
            $getData = $obj::select('id')
            ->where('email',trim($request['login_id']))
            ->whereNotNull('email')
            ->with('UserInfo')
            ->first();            
        }else if(preg_match($this->mobile_pattern, $request['login_id'])){
            $mobile = true;
            $getData = $obj::select('id')
            ->where('mobile','LIKE','%'.str_replace('+880','',$request['login_id']))
            ->whereNotNull('mobile')
            ->with('UserInfo')
            ->first();
        }else{
            return response()->json([
                "status" => "fail", 
                "message" =>'Invalid email or mobile number',
                "error" => true ], 404);              
        }
        if(!empty($getData)){
           // return $getData;           
            if($getData->UserInfo==null){
                return response()->json([
                    "status" => "fail", 
                    "message" =>'Invalid email or mobile number',
                    "error" => true ], 404);
            }
            $getPassword = EncryptionController::decode_content($getData->UserInfo->pass_code);
            if($email){                
                $data['html'] = "Dear ". ($getData->UserInfo?$getData->UserInfo->full_name:'') .",<br>Your forgot password request has been accepted. Your current password is ".$getPassword.".<br><br>Thank you for join with us";                

                Mail::send(['html'=>'email_template'], $data, function($message) use($request) {
                    $message->to($request['login_id'])->subject('Forgot password request | '.config('global.domain_title'));
                    $message->from('no-reply@'.config('global.domain_url'), config('global.domain_title'));
                });
                return response()->json([
                    "status" => "success", 
                    "message" =>'Email Send, Please check your Email',
                    'login_type' => 'email',
                    'code' => $getPassword,
                    "error" => false ], 200);
               
            }else if($mobile){

                $sms_data['number'] = $request['login_id'];
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour current password is ". $getPassword . "\n" . config('global.domain_url');
            
                $getCode = SmsController::sendTo($sms_data);
                return response()->json([
                    "status" => "success", 
                    "message" =>'SMS Send, Please check your SMS',
                    'login_type' => 'mobile',
                    'code' => $getPassword,
                    'sms_response' => $getCode,
                    "error" => false ], 200);
            }
        } return response()->json([
            "status" => "fail", 
            "message" =>'Invalid email or mobile number',
            "error" => true ], 404);
    }

    public function UserActivation(User $obj, Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "auth_code" => "required"
        ]);

        if($validator->fails()) {
            return $this->validationErrors($validator->errors())->setStatusCode(422);
        }

        $obj = $obj->find($request['user_id']);

        if(!empty($obj)){
            if($obj->verified){
                return response()->json([
                    "status" => "fail", 
                    "message" =>'Already Verified',
                    "error" => true ], 404);
            }elseif(!$obj->verified && $obj->auth_code===$request['auth_code']){
                $obj->auth_code = '';
                $obj->mobile_verified_at = date('Y-m-d H:i:s');
                $obj->verified = 1;
                $obj->status = 1;
                if($obj->update())
                return response()->json([
                    "status" => "success", 
                    "message" =>'Verified Successfully',
                    "error" => false ], 200);
                else return response()->json([
                    "status" => "fail", 
                    "message" =>'User Blocked',
                    "error" => true ], 404);
            }else return response()->json([
                "status" => "fail", 
                "message" =>'Otp Not matched',
                "error" => true ], 404);
        }else return response()->json([
            "status" => "fail", 
            "message" =>'No Data Found',
            "error" => true ], 404);
    }

    public function UserInfo(Request $request){       
        try{
            $user= User::with(['UserInfo','RoleInfo'])->find(Auth::id());
            if($user){
                return response()->json([
                    "status" => "success", 
                    "data" =>$user,
                    "error" => false], 200);
            }else{
                return response()->json([
                    "status" => "failed", 
                    "message" => "User Not Found",
                    "error" => true
                ], 404);  
            }
            
        }catch(Exception $exception) {
            return response()->json([
                "status" => "failed", 
                "message" => $exception->getMessage(),
                "error" => true
            ], 404);
        }
    }

    public function SocialUserInfo(Request $request){
        $data = $request['data'];
        $getUser = User::where('email',$data['email'])->first();
        // return $request->all();

        // set default password
        $get_password = '123456';

        if(empty($getUser)){
            $User = new User;
            $User->name = $data['name'];
            $User->email = isset($data['email'])?$data['email']:null;
            $User->password = bcrypt($get_password);
            $User->save();            

            return response()->json(['status' => true , 'user' => $User] , 200);
        } else return response()->json(['status' => true , 'user' => $getUser] , 200);
    }

    public function Login(Request $request){

        $validator = Validator::make($request->all(), [
            "login_id" => "required",
            "password" => "required"
        ]);

        if($validator->fails()) {
            return $this->validationErrors($validator->errors())->setStatusCode(422);
        }
        try{
            if (Auth::attempt(['email' => $request['login_id'], 'password' => $request['password']])) {
                $getUser = User::where('email', $request['login_id'])->with('userInfo')->first();
                $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
            }
            if(isset($getUser)){
                if($getUser->verified==0){
                    return response()->json([
                        "status" => "fail", 
                        "code" => "2", 
                        "message" =>'User Not verifiyed',
                        "error" => true ], 404);
                }else{
                    return response()->json([
                        "status" => "success", 
                        "code" => "1", 
                        "message" =>'Login Successfully',
                        "error" => false,
                        'user_info' => $getUser ], 200);
                }
            }else{
                return response()->json([
                    "status" => "fail", 
                    "code" => "3", 
                    "message" =>'No Data Found',
                    "error" => true ], 404);                
            }
        }catch(Exception $exception) {
            return response()->json([
                "status" => "failed", 
                "code" => "1", 
                "message" => $exception->getMessage(),
                "error" => true
            ], 404);
        }
        
    }

    /**
     * Request admin login
     * 
     * @param \App\Models\User $obj
     * @param \Illuminate\Http\Request  $request     
     * @return \Illuminate\Http\Response
     */
    public function AdminLogin(User $obj, Request $request){
        // return $request->all();
        // return Auth::user();
        // return $request->session()->all();
        if (Auth::attempt(['email' => $request['login_id'], 'password' => $request['password']])) {
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo','RoleInfo'])->first();            
        }

        if(isset($getUser)){
            if($getUser->verified==0){
                return response()->json(['status' => 2] , 200);
            }else{                
                // $getUser['token'] =  $getUser->createToken('MyApp')->accessToken;
                $token = Auth::user()->createToken('user-token');
                $getUser['token'] = $token->plainTextToken;
                return response()->json(['status' => 1, 'user_info' => $getUser] , 200);
            }
            
       }else{
           return response()->json(['status' => 0] , 200);
       }
    }
    
    public function SocialUserLogin(User $obj, Request $data){
        $loginData = []; $email = ''; $mobile = '';

        if(filter_var($data['login_id'], FILTER_VALIDATE_EMAIL)){
            $loginData = ['email' => $data['login_id']];
            $email = $data['login_id'];
        }else if(preg_match($this->mobile_pattern, $data['login_id'])){
            $loginData = ['mobile' => $data['login_id']];
            $mobile = $data['login_id'];
        }else{
            return response()->json(['msg' => 'Invalid email or mobile number','status' => 0] , 200);
        }

        $User = $obj::select('ui.pass_code')
        ->join('user_infos AS ui','ui.user_id','=','users.id')
        ->where($loginData)
        ->first();

        // set default password
        $get_password = str_random(8); // mt_rand(100000, 999999);

        DB::beginTransaction();
        
        if(empty($User)){
            $User = new User;
            if($email) $User->email = isset($email)?$email:null;
            elseif($mobile) $User->mobile = isset($mobile)?$mobile:null;
            $User->password = bcrypt($get_password);
            $User->user_type = 3;
            $User->verified = 1;
            $User->status = 1;
            
            if($User->save()){
                $getUserId = $User->id;
                
                /**
                 * User Info data insert
                 */
                $UserInfo = new UserInfo;
                $UserInfo->user_id = $getUserId;
                $UserInfo->full_name = $data['full_name'];
                $UserInfo->social_id_info = json_encode($data['social_id_info']);
                $UserInfo->pass_code = EncryptionController::encode_content($get_password);
                $UserInfo->photo = $data['photo'];
                $UserInfo->created_by = $getUserId;
                $UserInfo->save();
            }
            
            $loginData['password'] = $get_password;
        }else{
            $loginData['password'] = EncryptionController::decode_content($User->pass_code);
        }
        
        $getUser = [];
        if (Auth::attempt($loginData)) {
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo','RoleInfo'])->first();            
        }
        
        // return response()->json(['get_user' => $getUser], 200);

        if(isset($getUser)){
            DB::commit();
            $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
            return response()->json(['user_info' => $getUser, 'status' => 1] , 200);
       }else{
           DB::rollback();
           return response()->json(['status' => 0] , 200);
       }
    }

    /**
     * User Login Request
     * 
     * @param \App\Models\User $obj
     * @param \Illuminate\Http\Request  $request     
     * @return \Illuminate\Http\Response
     */
    public function UserLogin(User $obj, Request $request){   
        $validator = Validator::make($request->all(), [
            "login_id" => "required",  
            "password" => "required|min:3",
        ]);
        if($validator->fails()) {
            return $this->validationErrors($validator->errors())->setStatusCode(422);
        }
        $loginData = [];

        if(filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)){
            $loginData = ['email' => $request['login_id'], 'password' => $request['password']];
        }else if(preg_match($this->mobile_pattern, $request['login_id'])){
            $loginData = ['mobile' => $request['login_id'], 'password' => $request['password']];
        }else{
            return response()->json([
                "status" => "fail", 
                "message" =>'Invalid email or mobile number',
                "error" => true ], 404);            
        }       
        if (Auth::attempt($loginData)) {
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo','RoleInfo'])->first();            
        }
        if(isset($getUser)){
            if($getUser->verified==0){
                return response()->json([
                    "status" => "fail", 
                    "code" => "2", 
                    "message" =>'User Not verified',
                    "error" => true ], 404);
            }else{                
                $token = Auth::user()->createToken('user-token');
                $getUser['token'] = $token->plainTextToken;
                return response()->json([
                    "status" => "success", 
                    "code" => "1", 
                    "message" =>'Login Successfully',
                    "token" => $token->plainTextToken,
                    'user_info' => $getUser,
                    "error" => false ], 200);
            }            
       }else{
            return response()->json([
                "status" => "fail", 
                "code" => "0", 
                "message" =>'Login credential not matched',
                "error" => true ], 404);
       }
    }

    /**
     * User Signup/Registrtion request submit
     * 
     * @param \App\Models\User $obj
     * @param \Illuminate\Http\Request  $request     
     * @return \Illuminate\Http\Response
     */
    public function UserSignup(User $obj, Request $request) {        
        //return '1';
        // "password" => "required|string|min:6|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/"
        $validator = Validator::make($request->all(), [
            "login_id" => "required",
            "password" => "required|string|min:6|confirmed"
        ]);

        if($validator->fails()) {
            return $this->validationErrors($validator->errors())->setStatusCode(422);
        }

        $email = false; $mobile = false;
        if(filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)){
            $email = true;
            $getData = $obj::select('*')
            ->where('email',trim($request['login_id']))
            ->whereNotNull('email')
            ->first();
        }else if(preg_match($this->mobile_pattern, $request['login_id'])){
            $mobile = true;
            $getData = $obj::select('*')
            ->where('mobile','LIKE','%'.str_replace('+880','',$request['login_id']))
            ->whereNotNull('mobile')
            ->first();
        }else{
            return response()->json([
                "status" => "failed", 
                "message" =>'Invalid email or mobile number',
                "error" => true], 404);
        } 
       // return $getData;   
        if(!empty($getData)) {
            if($mobile){
                $sms_data['number'] = $getData['mobile'];
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour activation code is " . $getData['auth_code'] . "\n" . config('global.domain_url');
            
                $getCode = SmsController::sendTo($sms_data);
            }else if($email){

                $getData['html'] = "Dear,<br>Your account has been created.<br>Please activate your account. Your activation code is ".$getData['auth_code']."<br><br>Thank you for join with us";                

                Mail::send(['html'=>'email_template'], $getData, function($message) use($getData) {
                    $message->to($getData['email'])->subject('Account activation info | '.config('global.domain_title'));
                    $message->from('no-reply@'.config('global.domain_url'), config('global.domain_title'));
                });
            }
            return response()->json([
                "status" => "success", 
                "message" =>'Already Registered',
                "error" => false,
                "auth_code" =>$getData['auth_code'],
                'registered' => true,
                'req_id_type' => $email?'email':'mobile',
                'data' => $getData], 200);
                
        }

        $data = [];        
        $data['password'] = bcrypt($request['password']);
        $data['user_type'] = $request->has('user_type')?$request['user_type']:3;
        if($email) $data['email'] = $request['login_id'];
        else if($mobile) $data['mobile'] = $request['login_id'];
        $data['auth_code'] = mt_rand(100000, 999999);
        $data['verified'] = 0;
        $data['status'] = 0;
        
        try{
            $getLastId = ModificationController::save_content($obj, $data, 1);
           
            if($getLastId>0){
                $obj = new UserInfo;
                $userData = [];
                $userData = $request['user_info'];
                if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
                $userData['user_id'] = $getLastId;
                
                if($mobile){
                    $sms_data['number'] = $data['mobile'];
                    $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour activation code is " . $data['auth_code'] . "\n" . config('global.domain_url');
                
                    $getCode = SmsController::sendTo($sms_data);
                }else if($email){

                    $getData['html'] = "Dear,<br>Your account has been created.<br>Please activate your account. Your activation code is ".$data['auth_code']."<br><br>Thank you for join with us";                

                    Mail::send(['html'=>'email_template'], $getData, function($message) use($data) {
                        $message->to($data['email'])->subject('Account activation info | '.config('global.domain_title'));
                        $message->from('no-reply@'.config('global.domain_url'), config('global.domain_title'));
                    });
                }
                $getData = ModificationController::save_content($obj, $userData);
                return response()->json([
                    "status" => "success", 
                    "error" => false,
                    "auth_code" =>$data['auth_code'],
                    "message" => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
                   // 'sms_status'=>$mobile?SmsController::smsStatus($getCode):'',
                    'sms_status'=>1,
                    'user_id'       => $getLastId,
                ],201);
                
            } else {
                return response()->json([
                    "status" => "success", 
                    "error" => true,
                    "message" => '<i class="fa fa-check-circle"></i> Data has not been saved.',                   
                    'data'       => $getLastId,
                ],404);
            }            
        }catch(Exception $e){
            return response()->json([
                "status" => "failed", 
                "error" => true,
                "message" =>'<i class="fa fa-check-circle"></i> Data has not been saved.',
                'data'=>  $e->getMessage()], 404);
        }        
        
        
    }

    public function Logout(Request $request) {
        try{
            Auth::logout();
            return response()->json(['status' => true] , 200);
        }catch(Exception $e){
            return $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(User $obj, Request $request)
    {
        $messages = [
            'required' => 'This field is required',
            'unique' => 'This field is unique'
        ];
        $data = [];        
        $data['password'] = bcrypt($request['password']);
        if($request->has('user_type')) $data['user_type'] = $request['user_type'];
        $data['email'] = $request['email'];
        $data['auth_code'] = str_random(12);
        $data['verified'] = $request['verified'];
        $data['status'] = $request['status'];

        $getLastId = ModificationController::save_content($obj, $data, 1);            

        /**
         * User role save
         */
        DB::select('INSERT INTO `user_roles` (`user_id`,`role_id`) VALUES('.$getLastId.','.$request['role_info']['role_id'].')');

        $obj = new UserInfo;
        $userData = [];
        $userData = $request['user_info'];
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $getLastId;

        return ModificationController::save_content($obj, $userData);
        // return $request->all();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(User $obj, Request $request)
    {
        $user_id = Auth::id();
        
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',1)
        ->with(['UserInfo','RoleInfo'])
        ->paginate($limit);
        
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',1)
        ->with(['UserInfo','RoleInfo'])
        ->get();

        // return response()->json($getData, 200);
        return Resource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(User $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with(['UserInfo','RoleInfo'])
        ->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(User $obj, Request $request, $req_id)
    {
        $data = [];        
        if($request->has('password')) $data['password'] = bcrypt($request['password']);
        $data['email'] = $request['email'];
        $data['status'] = $request['status'];
        $data['verified'] = $request['verified'];

        ModificationController::update_content($obj, $data, $req_id);

        /**
         * User Role delete        
         */
        DB::select('DELETE FROM `user_roles` WHERE `user_id`='.$req_id);

        /**
         * User role save
         */        
        DB::select('INSERT INTO `user_roles` (`user_id`,`role_id`) VALUES('.$req_id.','.$request['role_info']['role_id'].')');

        $obj = new UserInfo;
        $userData = [];
        $userData = $request['user_info'];
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $req_id;

        return ModificationController::update_content($obj, $userData, $req_id, 'user_id');
        // return $request->all();
    }

    public function updateProfile(User $obj, Request $request){
        // return $request->all();
        $user_id = Auth::id();
       // return $user_id;
        if(!$user_id) return response()->json(["status" => "fail","message" =>'Invalid credential', "error" => false ], 404);
        else $req_id = $user_id;

        $validator = Validator::make($request->all(), [
            "email" => "required",
            "mobile" => "required",
            "full_name" => "required",
            "dob" => "required",
            
        ]);
        if($validator->fails()) {
            return $this->validationErrors($validator->errors())->setStatusCode(422);
        }  
        
        $data = [];
        if($request['email']) $data['email'] = $request['email'];
        if($request['mobile']) $data['mobile'] = $request['mobile'];
        
        // return $request->all();

        if(!empty($data)) ModificationController::update_content($obj, $data, $req_id);
        else return response()->json([
            "status" => "fail", 
            "message" =>'Fail To update Data',
            "error" => true ], 404);

        $obj = new UserInfo;
        $userData = [];
                
        $userData = $request['user_info'];
        if(gettype($userData)=='string') $userData = json_decode($userData, true);
        // return response()->json(['getDataType' => gettype($userData),'userInfo' => $userData], 200);
        if($request['full_name']!=='') $userData['full_name'] = $request['full_name'];
        if($request['mother_name']!=='') $userData['mother_name'] = $request['mother_name'];
        if($request['mother_name']!=='') $userData['mother_name'] = $request['mother_name'];
        if($request['spouse_name']!=='') $userData['spouse_name'] = $request['spouse_name'];
        if($request['gender_id']!=='') $userData['gender_id'] = $request['gender_id'];
        if($request['blood_group']!=='') $userData['blood_group'] = $request['blood_group'];
        if($request['contact_number']!=='') $userData['contact_number'] = $request['contact_number'];
        if($request['nid']!=='') $userData['nid'] = $request['nid'];
        if($request['passport']!=='') $userData['passport'] = $request['passport'];
        if($request['dob']!=='') $userData['dob'] = date('Y-m-d', strtotime($request['dob']));

        // return $userData;

        ModificationController::update_content($obj, $userData, $req_id, 'user_id');
        return response()->json([
            "status" => "success", 
            "message" =>'User data Update succefully',
            "error" => false ], 201);
    }
    
    public function changePassword(User $obj, Request $request){
        $user_id = Auth::id();
        
        if(!$user_id) return response()->json(['msg' => 'Invalid credential', 'status' => false], 200);
        else $req_id = $user_id;

        $getData = $obj->find($req_id);

        if (Hash::check($request['current_password'], $getData->password)) {
            $data = [];
            $data['password'] = bcrypt($request['new_password']);
            ModificationController::update_content($obj, $data, $req_id);

            $obj = new UserInfo;
            $userData = [];
            $userData['pass_code'] = EncryptionController::encode_content($request['new_password']);            

            return ModificationController::update_content($obj, $userData, $req_id, 'user_id');
        }else{
            return response()->json(['msg' => 'Current password didn\'t match', 'status' => false], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function showPetInfos(Request $request)
    {
        try {   
            $user_id = Auth::id();
            $data = PetInfo::with('type','breed','size')->where('owner_id',$user_id)->get();        
            if($data){           
                return $this->success(new Resource($data), Constants::GETALL, 200,true); 
            }else{             
                return $this->error('',Constants::NODATA, 404,false); 
            } 
        } catch (\Throwable $th) {
            return $this->error('',$th, 404,false); 
        }
        
       
    }

}