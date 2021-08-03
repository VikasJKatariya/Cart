<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once("send_grid/vendor/autoload.php");
require_once("twilio-php-master/Twilio/autoload.php");

use Twilio\Rest\Client;
class V1 extends CI_Controller
{


    public function sendreminder(){
        
        $reminders = $this->db->get_where("reminders",array("reminderId"=>'26'))->row_array();
        $date  =  date('Y-m-d'); //date("Y-m-d");
        $time =  date('H:i:00',strtotime(date("Y-m-d H:i:s"))); //date('H:i:s');

       
        $todayNotify = $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.location_name,r.group_to_ary,r.send_to_ary,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile,r.text_msg_notify")->from("reminder_admin_users as ru")->join("reminders as r","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->order_by("r.date","asc")->where(array('r.date'=>$date,'r.time'=> $time))->get()->result_array();
        
        $str = $this->db->last_query();
       
        if(!empty($todayNotify)){
            foreach ($todayNotify as $reminder) {
                $numbers = unserialize($reminder['send_to_ary']);
                if(!empty($numbers))
                {
                    foreach ($numbers as $number) {
                        
                        $userexist = $this->db->query('SELECT * FROM users where mobile='.$number['mobile']);
                        /*echo 'Mobile number :- '.$number['mobile'].'<br>';
                            echo 'text_msg_notify  :- '.$reminder['text_msg_notify'].'<br>';
                            echo 'register '.$userexist->num_rows().'<br>';
                            echo '--------------------------------------------------<br>';*/
                        
                        if($userexist->num_rows() == 0 || $reminder['text_msg_notify'] == 1){
                            /*echo 'Mobile number :- '.$number['mobile'].'<br>';
                            echo 'text_msg_notify  :- '.$reminder['text_msg_notify'].'<br><br><br><br>';*/
                            try {
                                $num = (int) $number['mobile']; 
                            $num = (($num >= 0) ? '+' : '') . $num;

                            $sid = TWILIO_SID;
                            $token = TWILIO_token;
                            $client = new Client($sid, $token);
                            
                            
                                $client->messages->create(
                                    
                                    $number['mobile'],
                                    array(
                                        'from' => Twlio_number,
                                        'body' => "\n Hey ".$number['name'].", \n Just to remind you: ".$reminder['description'].".\n From : ".$reminder['admin_name'],
                                    )
                                );
                            
                            
                            } catch (TwilioException $e) {
                                $result['status'] = 0;
                                $result['msg'] = $e->getMessage();
                               
                            }   catch (Twilio\Exceptions\RestException $e) {
                                $result['status'] = 0;
                                $result['msg'] = $e->getMessage();
                               
                            }

                        }
                            

                    }    

                    
                }
                


            }

             exit;
            
        }

    }
    public function inviteuser(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if(empty($params['mobile'])){
                $result['status'] = 0;
                $result['msg'] = "Please select atleast one number.";
                json_encode_help($result);
            }else if(empty($params['userId'])){
                $result['status'] = 0;
                $result['msg'] = "User id is required.";
                json_encode_help($result);            
            }else{
                  
                $userDet = $this->db->get_where("users",array("userId"=>$params['userId']))->row_array();
                if(!empty($userDet)){

                    try {
                        $sid = TWILIO_SID;
                        $token = TWILIO_token;
                        $client = new Client($sid, $token);

                        foreach ($params['mobile'] as $number) {
                            $client->messages->create(
                                
                                $number,
                                array(
                                    'from' => Twlio_number,
                                    'body' => 'You are invited by '.$userDet['name'].'. Please click this link to install the app: '.base_url().'install.html',
                                )
                            );
                        }
                        $result['status'] = 1;
                        $result['msg'] = "Invitation sent successfully.";
                        json_encode_help($result);
                    } catch (TwilioException $e) {
                        $result['status'] = 0;
                        $result['msg'] = $e->getMessage();
                        json_encode_help($result);
                    }   catch (Twilio\Exceptions\RestException $e) {
                        $result['status'] = 0;
                        $result['msg'] = $e->getMessage();
                        json_encode_help($result);
                    }

                }else{
                    $result['status'] = 0;
                    $result['msg'] = "User id is invalid.";
                    json_encode_help($result);  
                }
                  
                

            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required.";
            json_encode_help($result);
        }
    }




        
    
    
    /*-- START REGISTER API --*/
    public function register(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['name'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Name is required";
                json_encode_help($result);
            }else if($params['email'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Email is required";
                json_encode_help($result);            
            }else if($params['password'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Password is required";
                json_encode_help($result);            
            }else if($params['mobile'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Mobile is required";
                json_encode_help($result);            
            }else if($params['deviceType'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Device type is required";
                json_encode_help($result);            
            }else if($params['deviceToken'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Device Token is required";
                json_encode_help($result);            
            }else{
                $loginUser = $this->db->get_where("users",array("email"=>$params['email']))->row();
                
                $ins['name'] = $params['name'];
                $ins['email'] = $params['email'];
                $ins['password'] = $params['password'];
                $ins['mobile'] = str_replace("+","",$params['mobile']);
                $ins['otp'] = generate_OTP_token(6);
                $ins['deviceType'] = $params['deviceType'];
                $ins['deviceToken'] = $params['deviceToken'];
                $ins['status'] = 1;
                $ins['otptime'] = $ins['created_at'] = $ins['updated_at'] = date("Y-m-d h:i:s");
                //print_r($ins);die;
                if(!empty($loginUser)){//update
                    if($loginUser->status==0){//deactive
                        $this->db->where("userId",$loginUser->userId)->update("users",$ins);
                        $ins['userId'] = $loginUser->userId;
                    }else{
                        $result['status'] = 0;
                        $result['msg'] = "Email address is already exists";
                        json_encode_help($result);
                    }
                }else{//insert
                    $this->db->insert("users",$ins);
                    $ins['userId'] = $this->db->insert_id();
                }
                
                //Text msg send
                $from = "919998730557";
                $to = $ins['mobile'];
                $messagebody = "Hello ".$ins['name'].",
                                You have successfully register in Reminder App";
                text_msg_send($from,$to,$messagebody);
                //Email Send
                $to = $ins['email'];
                $app_msg = $subject = "Register successfully";
                $body = "Hello ".$ins['name'].",<br><br>
                         You have successfully register in Reminder App<br>
                         
                         <b>You can now use all the features of the Reminder app by loggin in with your credentials with which you just regietered.</b><br>
                         <br><b>Reminder App Team</b>";
                //Notifications send
                /*if($ins['deviceType']==0){//Android
                    fcm_send($ins['deviceToken'],$app_msg);
                }elseif($ins['deviceType']==1){//IOs
                    apns_send(array($ins['deviceToken']),$app_msg);
                }*/   
                email_send('',$to,'',$subject,$body);
                $ins['password'] = $params['password'];                
                $ins['push_notify'] = 0;
                
                $result['status'] = 1;
                $result['msg'] = "Register successfully";
                $result['data'] = $ins;
                json_encode_help($result);
            
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter is required";
            json_encode_help($result);
        }        
    }
    /*-- END REGISTER API --*/

    /*-- START LOGIN API --*/
    public function login(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['email'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Email is required";
                json_encode_help($result);
            }else if($params['password'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Password is required";
                json_encode_help($result);            
            }else if($params['deviceToken'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Device Token is required";
                json_encode_help($result);
            }else if(!isset($params['deviceType']) || $params['deviceType'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Device Type is required";
                json_encode_help($result);
            }else{
                $loginUser = $this->db->get_where("users",array("email"=>$params['email']))->row();
                if(!empty($loginUser)){
                    if($loginUser->password == $params['password']){//pass is coorect
                        if($loginUser->status==1){
                            if($loginUser->deviceToken != $params['deviceToken']){//update token
                                $upd['deviceToken'] = $params['deviceToken'];
                            }
                            if(isset($params['deviceType'])){//update token
                                $upd['deviceType'] = $params['deviceType'];
                            }
                            //update securityToken
                            $upd['securityToken'] = create_new_password(16);
                            $this->db->where("userId",$loginUser->userId)->update("users",$upd);
                            //success login
                            $data['userId'] = $loginUser->userId;
                            $data['name'] = $loginUser->name;
                            $data['email'] = $loginUser->email;
                            $data['mobile'] = $loginUser->mobile;
                            $data['address'] = $loginUser->address;
                            $data['push_notify'] = $loginUser->push_notify;
                            $data['securityToken'] = $upd['securityToken'];
                            
                            $result['status'] = 1;
                            $result['msg'] = "Login successfully";
                            $result['data'] = $data;
                            json_encode_help($result);
                        }else{
                            $result['status'] = 0;
                            $result['msg'] = "The account is not active. Please contact administrator.";
                            json_encode_help($result);
                        }
                    }else{
                            $result['status'] = 0;
                            $result['msg'] = "Please enter your correct password";
                            json_encode_help($result);                        
                    }
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Account with this email does not exists.";
                    json_encode_help($result);
                }               
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END LOGIN API --*/

    /*-- START Reset OTP API --*/
    public function resend_otp(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }else{
                $userDet = $this->db->get_where("users",array("userId"=>$params['userId']))->row_array();
                if(!empty($userDet)){
                    $upd['otp'] = generate_OTP_token(6);
                    $upd['otptime'] = date("Y-m-d h:i:s");
                    $this->db->where("userId",$params['userId'])->update("users",$upd);
                    
                    //Notifications send
                    /*$app_msg = "Resend OTP sent on your email address";
                    if($userDet['deviceType']==0){//Android
                        fcm_send($userDet['deviceToken'],$app_msg);
                    }elseif($userDet['deviceType']==1){//IOs
                        apns_send(array($userDet['deviceToken']),$app_msg);
                    } */
                    //Text msg send
                    $from = "919998730557";
                    $to = $userDet['mobile'];
                    $messagebody = "Hello ".$userDet['name'].",<br><br>
                             You have successfully register in Reminder App<br>
                             Your OTP is <b>".$upd['otp']."</b>";
                    text_msg_send($from,$to,$messagebody);                
                    //Email Send
                    $to = $userDet['email'];
                    $subject = "Resend OTP";
                    $body = "Hello ".$userDet['name'].",<br><br>
                             You have successfully register in Reminder App<br>
                             Your OTP is <b>".$upd['otp']."</b><br><br>
                             <br><br><br>Good luck out there!<br><b>Reminder App Team</b>";

                    email_send('',$to,'',$subject,$body);

                    $result['status'] = 1;
                    $result['msg'] = "OTP sent on your registered email";
                    $result['data'] = $upd;
                    json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Invalid User";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required Resend";
            json_encode_help($result);
        }
    }
    /*-- END Reset OTP API --*/

    /*-- START Check OTP API --*/
    public function check_otp(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }elseif($params['otp'] == ""){
                $result['status'] = 0;
                $result['msg'] = "OTP is required";
                json_encode_help($result);
            }else{
                $userDet = $this->db->get_where("users",array("userId"=>$params['userId']))->row_array();
                if(!empty($userDet)){
                    if($userDet['otp']==$params['otp']){
                        $oldTime = $userDet['otptime'];
                        //$curentTime = date("Y-m-d h:i:s");
                        //$diff = get_time_difference($oldTime, $curentTime);
                        $datetime1 = new DateTime();
                        $datetime2 = new DateTime($oldTime);
                        $interval = $datetime1->diff($datetime2);
                        $diff = $interval->format('%i');
                        if($diff<=2){
                            //update status
                            $upd['status'] = 1;
                            $this->db->where("userId",$params['userId'])->update("users",$upd);

                            $result['status'] = 1;
                            $result['msg'] = "Login successfully";
                            $result['push_notify'] = $userDet['push_notify'];
                            json_encode_help($result);
                        }else{
                            $result['status'] = 0;
                            $result['msg'] = "OTP Time is expired";
                            json_encode_help($result);
                        }
                        
                    }else{
                        $result['status'] = 0;
                        $result['msg'] = "OTP is incorrect";
                        json_encode_help($result);
                    }
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Invalid User";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END Check OTP API --*/

    /*-- START forgot_password API --*/
    public function forgot_password(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['email'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Email is required";
                json_encode_help($result);
            }else{
                $userDet = $this->db->get_where("users",array("email"=>$params['email']))->row_array();
                if(!empty($userDet)){                                            
                        //Email Send
                        $to = $userDet['email'];
                        $subject = "Forgot Password";
                        $body = "Hello ".$userDet['name'].",<br><br>
                                 Below is login details<br>
                                 Your password is <b>".$userDet['password']."</b><br><br>
                                 <br><br><br>Good luck out there!<br><b>Reminder App Team</b>";

                        email_send('',$to,'',$subject,$body);

                        $result['status'] = 1;
                        $result['msg'] = "Password sent successfully on Email";
                        json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Email address is incorrect";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END forgot_password API --*/

    /*-- START change_password API --*/
    public function change_password(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }elseif($params['oldPassword'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Old password is required";
                json_encode_help($result);
            }elseif($params['newPassword'] == ""){
                $result['status'] = 0;
                $result['msg'] = "New password is required";
                json_encode_help($result);
            }else{
                $userDet = $this->db->get_where("users",array("userId"=>$params['userId']))->row_array();
                if(!empty($userDet)){
                    if($userDet['password']==$params['oldPassword']){       
                        //update status
                        $upd['password'] = $params['newPassword'];
                        $this->db->where("userId",$params['userId'])->update("users",$upd);                                         
                        //Email Send
                        $to = $userDet['email'];
                        $subject = "Change Password";
                        $body = "Hello ".$userDet['name'].",<br><br>
                                 Below is login details<br>
                                 Your new password is <b>".$upd['password']."</b><br><br>
                                 <br><br><br>Good luck out there!<br><b>Reminder App Team</b>";

                        email_send('',$to,'',$subject,$body);

                        $result['status'] = 1;
                        $result['msg'] = "Your Password changed successfully";
                        json_encode_help($result);
                    }else{
                        $result['status'] = 0;
                        $result['msg'] = "Old password is incorrect";
                        json_encode_help($result);
                    }
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Invalid User";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END change_password API --*/

    /*-- START update_profile API --*/
    public function update_profile(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }elseif($params['name'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Name is required";
                json_encode_help($result);
            }elseif($params['mobile'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Mobile is required";
                json_encode_help($result);
            }elseif($params['address'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Address is required";
                json_encode_help($result);
            }else{
                $userDet = $this->db->get_where("users",array("userId"=>$params['userId']))->row_array();
                if(!empty($userDet)){
                        $upd['name'] = $params['name'];
                        $upd['mobile'] = str_replace("+","",$params['mobile']);
                        $upd['address'] = $params['address'];
                        $this->db->where("userId",$params['userId'])->update("users",$upd);    

                        $loginUser = $this->db->get_where("users",array("userId"=>$params['userId']))->row();
                        $data['userId'] = $loginUser->userId;
                        $data['name'] = $loginUser->name;
                        $data['email'] = $loginUser->email;
                        $data['mobile'] = $loginUser->mobile;
                        $data['address'] = $loginUser->address;
                        $data['securityToken'] = $loginUser->securityToken;

                        $result['status'] = 1;
                        $result['msg'] = "Profile updated successfully";
                        $result['data'] = $data;
                        json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Invalid User";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END update_profile API --*/

    /*-- START Account Setting --*/
    public function account_setting(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }else if($params['push_notify'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Push notify is required";
                json_encode_help($result);            
            /*}else if($params['email_notify'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Email notify is required";
                json_encode_help($result);            
            }else if($params['text_msg_notify'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Text msg notify is required";
                json_encode_help($result);  */          
            }else{
                $upd['push_notify'] = $params['push_notify'];
                /*$upd['email_notify'] = $params['email_notify'];
                $upd['text_msg_notify'] = $params['text_msg_notify'];*/
                $this->db->where('userId',$params['userId'])->update("users",$upd);
                
                $result['status'] = 1;
                $result['msg'] = "Account setting saved successfully";
                json_encode_help($result);
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- End Account Setting --*/

    
    /*-- START Contact Synchronize --*/
    public function contacts_sync(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }else if($params['contacts'] == ""){
                $result['status'] = 0;
                $result['msg'] = "contacts list is required";
                json_encode_help($result);            
            }elseif(!is_array($params['contacts'])){
                $result['status'] = 0;
                $result['msg'] = "contacts list is required in Array Formate";
                json_encode_help($result);      
            }else{
               // print_r($params['contacts']);die; exit;
                if(!empty($params['contacts'])){
                    $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row();

                    $countryCode = substr($userDet->mobile, 0, 2);
                    $mobilesAry = array_filter(array_column($params['contacts'], 'mobile'));
                    $mobiles = array_removePlus_addCountry($mobilesAry,$countryCode);
                    //print_r($mobiles);die;

                    $accounts = $this->db->select('mobile')->where_in("mobile",$mobiles)->get("users")->result_array();
                    //print($this->db->last_query());die;
                    $resultmobiles = array_column($accounts, 'mobile');
                    //echo $this->db->last_query();die;
                    $data = array_values(array_unique($resultmobiles)); 
                    //print_r($data);die;
                }
                $result['status'] = 1;
                $result['msg'] = "contacts sync successfully";
                $result['data'] = $data;
                json_encode_help($result);
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- End Account Setting --*/

    /*-- START add_group API --*/
    public function add_group(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['name'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Group Name is required";
                json_encode_help($result);
            }elseif($params['group_admin'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Group Admin is required";
                json_encode_help($result);
            }elseif($params['members'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Members list is required";
                json_encode_help($result);
            }elseif(!is_array($params['members'])){
                $result['status'] = 0;
                $result['msg'] = "Member list is required in Array Formate";
                json_encode_help($result);
            }else{
                $ins['name'] = $params['name'];
                $ins['group_admin'] = $params['group_admin'];                
                //$grpMem = array_merge( $params['members'],array($params['group_admin']) );
                //$grpMem = $params['members'];
                $ins['members'] = serialize($params['members']);
                $ins['created_at'] = $ins['updated_at'] = date("Y-m-d h:i:s");
                $this->db->insert("groups",$ins);
                $data['groupId'] = $this->db->insert_id();
                if($data['groupId']){
                    $result['status'] = 1;
                    $result['msg'] = "Group saved successfully";
                    $result['data'] = $data;
                    json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Something went wrong";
                    json_encode_help($result);
                }  
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END add_group API --*/

    /*-- START group_list API --*/
    public function group_list(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }else{
                if(isset($params['groupId'])){
                    $this->db->where("groupId",$params['groupId']);
                }
                $groupsList = $this->db->get_where("groups",array("group_admin"=>$params['userId']))->result();
                if(!empty($groupsList)){
                    foreach ($groupsList as $value) {
                        $row['groupId'] = $value->groupId;
                        $row['name'] = $value->name;
                        //member list 
                        //$memList = explode(",", $value->members);
                        //$userList = $this->db->select('userId,name')->where_in("userId",$memList)->get("users")->result_array();  
                        $userList = unserialize($value->members);                 
                        
                        $row['members'] = $userList;

                        $data[] = $row;
                    }
                    $result['status'] = 1;
                    $result['msg'] = "Group List successfully";
                    $result['data'] = $data;
                    json_encode_help($result);

                }else{
                    $result['status'] = 0;
                    $result['msg'] = "No groups available";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END group_list API --*/

    /*-- START group_delete API --*/
    public function group_delete(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['groupId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "group id is required";
                json_encode_help($result);
            }elseif(!is_array($params['groupId'])){
                $result['status'] = 0;
                $result['msg'] = "group id is required in Array Formate";
                json_encode_help($result);
            }else{               
                $this->db->where_in("groupId",$params['groupId'])->delete("groups");

                if( $this->db->affected_rows() ){                    
                    $result['status'] = 1;
                    $result['msg'] = "Group deleted successfully";
                    json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Something went wrong";
                    json_encode_help($result);
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END group_delete API --*/

    /*-- START add_reminder_category API --*/
    public function add_reminder_category(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }elseif($params['name'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Category name is required";
                json_encode_help($result);
            }else{                
                $ins['admin'] = $params['userId'];                
                $ins['name'] = $params['name'];
                $exist = $this->db->get_where("reminder_category",$ins)->row();
                if(!empty($exist)){
                    $result['status'] = 0;
                    $result['msg'] = "Reminder Category already exists";
                    json_encode_help($result);
                }else{
                    $ins['created_at'] = $ins['updated_at'] = date("Y-m-d h:i:s");
                    $this->db->insert("reminder_category",$ins);
                    if($this->db->insert_id()){
                        $result['status'] = 1;
                        $result['msg'] = "Reminder Category saved successfully";
                        json_encode_help($result);
                    }else{
                        $result['status'] = 0;
                        $result['msg'] = "Something went wrong";
                        json_encode_help($result);
                    }  
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- End add_reminder_category  --*/

    /*-- START reminder_category_list API --*/
    public function reminder_category_list(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }else{ 
                $ins['admin'] = $params['userId'];    
                $exist = $this->db->select("categoryId as category_id,name")->order_by("name")->get_where("reminder_category",$ins)->result_array();
                if(!empty($exist)){
                    $result['status'] = 1;
                    $result['msg'] = "Reminder Category list";
                    $result['data'] = $exist;
                    json_encode_help($result);
                }else{                   
                    $result['status'] = 0;
                    $result['msg'] = "no data found";
                    json_encode_help($result);                    
                }
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- End reminder_category_list  --*/

    /*-- START add_reminder API --*/
    public function add_reminder(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['name'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder Name is required";
                json_encode_help($result);
            }elseif($params['categoryId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "categoryId is required";
                json_encode_help($result);
            }elseif($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }elseif($params['date'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Date is required";
                json_encode_help($result);
            }elseif($params['time'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Time is required";
                json_encode_help($result);            
            }elseif($params['lat'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Latitude is required";
                json_encode_help($result);
            }elseif($params['long'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Longitude is required";
                json_encode_help($result);
            }elseif($params['groups'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Group list is required";
                json_encode_help($result);
            }elseif(!is_array($params['groups'])){
                $result['status'] = 0;
                $result['msg'] = "Group list is required in Array Formate";
                json_encode_help($result);
            }elseif($params['members'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Member list is required";
                json_encode_help($result);
            }elseif(!is_array($params['members'])){
                $result['status'] = 0;
                $result['msg'] = "Member list is required in Array Formate";
                json_encode_help($result);
            }elseif($params['request_for'] == ""){ //1-Me, 0-Other
                $result['status'] = 0;
                $result['msg'] = "Request For is required";
                json_encode_help($result);
            }elseif($params['reminder_status'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder status is required";
                json_encode_help($result);
            }else{
                
                $ins['name'] = $params['name'];
                $ins['categoryId'] = $params['categoryId'];
                $ins['description'] = (isset($params['description']))?$params['description']:'';
                $ins['date'] = date("Y-m-d", strtotime($params['date']));
                $ins['time'] = $params['time'];
                $ins['admin'] = $params['userId'];
                $ins['latitude'] = $params['lat'];
                $ins['longitude'] = $params['long'];
                $ins['location_name'] = (isset($params['location_name']))?$params['location_name']:'';
                $ins['group_to_ary'] = (!empty($params['groups'])) ? serialize($params['groups']) : '';
                $ins['send_to_ary'] = (!empty($params['members'])) ? serialize($params['members']) : '';
                $ins['email_notify'] = $params['email_notify'];
                $ins['text_msg_notify'] = $params['text_msg_notify'];
                $ins['reminder_status'] = $params['reminder_status'];
                $ins['created_at'] = $ins['updated_at'] = date("Y-m-d h:i:s");
                $this->db->insert("reminders",$ins);
                $reminder_id = $this->db->insert_id();

                /*For remider users table*/
                $grpMem = $mem = array();
                if(!empty($params['groups'])){
                    $groupIds = array_column($params['groups'],"id");
                    //print_r($groupIds);die;
                    $grpMemAry = $this->db->select("members")->where_in("groupId",$groupIds)->get("groups")->result_array();
                    if(!empty($grpMemAry)){
                        foreach ($grpMemAry as $grp) {
                            $grpMem = array_merge($grpMem, unserialize($grp['members']));
                        }
                    }
                    //$memAry = implode(",",array_column($grpMemAry,"members"));
                    //$grpMem = explode(",", $memAry);                    
                }
                if(!empty($params['members'])){
                    $mem = $params['members'];
                }                
                $members = array_merge($grpMem, $mem);               
                $uniqueMems = array_map("unserialize", array_unique(array_map("serialize", $members)));
                //print_r($members);print_r($uniqueMems);die;
                $mobilesAryTemp = array_column($uniqueMems,'mobile');    

                $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row();
                $countryCode = substr($userDet->mobile, 0, 2);
                $mobilesAry = array_removePlus_addCountry($mobilesAryTemp,$countryCode);            
                if($reminder_id){
                    if(!empty($uniqueMems)){
                        foreach ($uniqueMems as $value) {
                            $count = $this->db->select("id")->get_where("reminder_future_map",array("admin"=>$ins['admin'],"member"=>$value['mobile']))->num_rows();

                            $row = array();
                            $row['reminderId'] = $reminder_id;
                            $row['admin'] = $ins['admin'];
                            $row['name'] = $value['name'];
                            if(strlen($value['mobile'])==10){
                                $row['mobile'] = $countryCode.$value['mobile'];
                            }else{
                                $row['mobile'] = str_replace("+","",$value['mobile']);
                            } 
                             
                            if($params['request_for']==1){//reminder for me
                                $row['status'] = "accept";
                                $row['requestFor'] = 1;
                            }else{
                                if($count>0){
                                    $row['status'] = "accept";
                                }
                            }
                            
                            $this->db->insert("reminder_admin_users",$row);
                        }

                        if($params['request_for']==0){//reminder for others
                            //SEND NOTIFICATIONS 
                            $usersAnd = $usersIos = array();                       
                            /*---Push notify---*/
                            //print_r($mobilesAry);die;
                            $msgStr = 'You should be confirmation of reminder '.$params['name'];
                           /* $msg['reminder_id'] = $reminder_id;
                            $msg['name'] = trim($ins['name']);
                            $msg['description'] = trim($ins['description']);
                            $msg['location_name'] = trim($ins['location_name']);
                            $msgStr = $msg;*/
                            // print_r($mobilesAry);die;
                            //Android
                            $usersAnd = $this->db->select("deviceToken")->where_in("mobile",$mobilesAry)->get_where("users",array("deviceType"=>0,"push_notify"=>0))->result_array();
                            if(!empty($usersAnd)){
                                fcm_send(array_column($usersAnd,"deviceToken"),$msgStr);
                            }
                            
                            //IOS
                            $usersIos = $this->db->select("deviceToken")->where_in("mobile",$mobilesAry)->get_where("users",array("deviceType"=>1,"push_notify"=>0))->result_array();
                            //print_r($mobilesAry);pirnt_r($usersAnd);print_r($usersIos);
                            if(!empty($usersIos)){
                                apns_send(array_column($usersIos,"deviceToken"),$msgStr);
                            }

                            /*---Email notify---*/
                            if($params['email_notify']==1){
                                $adminEmail = $this->db->select("email")->get_where("users",array("userId"=>$params['userId']))->row_array();
                                $usersEmail = $this->db->select("email")->where_in("mobile",$mobilesAry)->get("users")->result_array();

                                $from = $adminEmail['email'];
                                $bcc = array_column($usersEmail,"email");
                                $subject = 'Reminder App – new remider';
                                $body = 'You have received a new reminder from '.$params['name'];

                                email_send($from,'',$bcc,$subject,$body);

                            }
                            
                            /*---Text notify---*/
                            /*if($params['text_msg_notify']==1){
                                $mobilesAry
                            }*/
                        }
                    }
                    $result['status'] = 1;
                    $result['msg'] = "New reminder added for you";
                    json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Something went wrong";
                    json_encode_help($result);
                }  
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END add_reminder API --*/
    
    /*-- START update_reminder API --*/
    public function update_reminder(){
        $result = array();
        $params = json_decode_help();
        if(!empty($params)){
            if($params['reminderId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder Id is required";
                json_encode_help($result);
            }elseif($params['name'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder Name is required";
                json_encode_help($result);
            }elseif($params['categoryId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "categoryId is required";
                json_encode_help($result);
            }elseif($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
            }elseif($params['date'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Date is required";
                json_encode_help($result);
            }elseif($params['time'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Time is required";
                json_encode_help($result);            
            }elseif($params['lat'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Latitude is required";
                json_encode_help($result);
            }elseif($params['long'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Longitude is required";
                json_encode_help($result);
            }elseif($params['groups'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Group list is required";
                json_encode_help($result);
            }elseif(!is_array($params['groups'])){
                $result['status'] = 0;
                $result['msg'] = "Group list is required in Array Formate";
                json_encode_help($result);
            }elseif($params['members'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Member list is required";
                json_encode_help($result);
            }elseif(!is_array($params['members'])){
                $result['status'] = 0;
                $result['msg'] = "Member list is required in Array Formate";
                json_encode_help($result);
            }elseif($params['request_for'] == ""){ //1-Me, 0-Other
                $result['status'] = 0;
                $result['msg'] = "Request For is required";
                json_encode_help($result);
            }elseif($params['reminder_status'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder status is required";
                json_encode_help($result);
            }else{
                
                $upd['name'] = $params['name'];
                $upd['categoryId'] = $params['categoryId'];
                $upd['description'] = (isset($params['description']))?$params['description']:'';
                $upd['date'] = date("Y-m-d", strtotime($params['date']));
                $upd['time'] = $params['time'];
                $upd['admin'] = $params['userId'];
                $upd['latitude'] = $params['lat'];
                $upd['longitude'] = $params['long'];
                $upd['location_name'] = (isset($params['location_name']))?$params['location_name']:'';
                $upd['group_to_ary'] = (!empty($params['groups'])) ? serialize($params['groups']) : '';
                $upd['send_to_ary'] = (!empty($params['members'])) ? serialize($params['members']) : '';
                $upd['email_notify'] = $params['email_notify'];
                $upd['text_msg_notify'] = $params['text_msg_notify'];
                $upd['reminder_status'] = $params['reminder_status'];
                $upd['updated_at'] = date("Y-m-d h:i:s");
                $this->db->where("reminderId",$params['reminderId'])->update("reminders",$upd);
                $reminder_id = $params['reminderId'];

                /*For remider users table*/
                $grpMem = $mem = array();
                if(!empty($params['groups'])){
                    $groupIds = array_column($params['groups'],"id");
                    //print_r($groupIds);die;
                    $grpMemAry = $this->db->select("members")->where_in("groupId",$groupIds)->get("groups")->result_array();
                    if(!empty($grpMemAry)){
                        foreach ($grpMemAry as $grp) {
                            $grpMem = array_merge($grpMem, unserialize($grp['members']));
                        }
                    }
                    //$memAry = implode(",",array_column($grpMemAry,"members"));
                    //$grpMem = explode(",", $memAry);                    
                }
                if(!empty($params['members'])){
                    $mem = $params['members'];
                }                
                $members = array_merge($grpMem, $mem);               
                $uniqueMems = array_map("unserialize", array_unique(array_map("serialize", $members)));
                //print_r($members);print_r($uniqueMems);die;
                $mobilesAryTemp = array_column($uniqueMems,'mobile');    

                $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row();
                $countryCode = substr($userDet->mobile, 0, 2);
                $mobilesAry = array_removePlus_addCountry($mobilesAryTemp,$countryCode);            
                if($reminder_id){
                    if(!empty($uniqueMems)){
                        //delete users
                        $this->db->where("reminderId",$reminder_id)->delete("reminder_admin_users");

                        foreach ($uniqueMems as $value) {
                            $count = $this->db->select("id")->get_where("reminder_future_map",array("admin"=>$upd['admin'],"member"=>$value['mobile']))->num_rows();

                            $row = array();
                            $row['reminderId'] = $reminder_id;
                            $row['admin'] = $upd['admin'];
                            $row['name'] = $value['name'];
                            if(strlen($value['mobile'])==10){
                                $row['mobile'] = $countryCode.$value['mobile'];
                            }else{
                                $row['mobile'] = str_replace("+","",$value['mobile']);
                            } 
                             
                            if($params['request_for']==1){//reminder for me
                                $row['status'] = "accept";
                                $row['requestFor'] = 1;
                            }else{
                                if($count>0){
                                    $row['status'] = "accept";
                                }
                            }
                            
                            $this->db->insert("reminder_admin_users",$row);
                        }

                        if($params['request_for']==0){//reminder for others
                            //SEND NOTIFICATIONS 
                            $usersAnd = $usersIos = array();                       
                            /*---Push notify---*/
                            //print_r($mobilesAry);die;
                            $msgStr = 'You have received a new reminder from '.$params['name'];
                           /* $msg['reminder_id'] = $reminder_id;
                            $msg['name'] = trim($ins['name']);
                            $msg['description'] = trim($ins['description']);
                            $msg['location_name'] = trim($ins['location_name']);
                            $msgStr = $msg;*/
                            //print_r($mobilesAry);die;
                            //Android
                            $usersAnd = $this->db->select("deviceToken")->where_in("mobile",$mobilesAry)->get_where("users",array("deviceType"=>0,"push_notify"=>0))->result_array();
                            if(!empty($usersAnd)){
                                fcm_send(array_column($usersAnd,"deviceToken"),$msgStr);
                            }
                            
                            //IOS
                            $usersIos = $this->db->select("deviceToken")->where_in("mobile",$mobilesAry)->get_where("users",array("deviceType"=>1,"push_notify"=>0))->result_array();
                            //print_r($mobilesAry);print_r($usersAnd);print_r($usersIos);
                            if(!empty($usersIos)){
                                apns_send(array_column($usersIos,"deviceToken"),$msgStr);
                            }

                            /*---Email notify---*/
                            if($params['email_notify']==1){
                                $adminEmail = $this->db->select("email")->get_where("users",array("userId"=>$params['userId']))->row_array();
                                $usersEmail = $this->db->select("email")->where_in("mobile",$mobilesAry)->get("users")->result_array();

                                $from = $adminEmail['email'];
                                $bcc = array_column($usersEmail,"email");
                                $subject = 'Reminder App – update remider';
                                $body = 'You should be confirmation of reminder '.$params['name'];

                                email_send($from,'',$bcc,$subject,$body);

                            }
                            
                            /*---Text notify---*/
                            /*if($params['text_msg_notify']==1){
                                $mobilesAry
                            }*/
                        }
                    }
                    $result['status'] = 1;
                    $result['msg'] = "Reminder updated successfully";
                    json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Something went wrong";
                    json_encode_help($result);
                }  
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END update_reminder API --*/

    /*-- START reminder_notify_list API --*/
    public function reminder_notify_list(){
        $result = array();
        $params = json_decode_help();
        
        if(!empty($params)){
             if($params['member'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User mobile is required";
                json_encode_help($result);
             }elseif($params['time'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Time is required";
                json_encode_help($result);    
             }else{
               //$result = $this->db->get_where("reminder_admin_users",array("mobile"=>$params['member'],"status"=>"pending"))->result_array();
               $todayNotify = $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.location_name,r.group_to_ary,r.send_to_ary,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminder_admin_users as ru")->join("reminders as r","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->order_by("r.date","asc")->where(array('ru.mobile'=>$params['member'],'ru.status'=>'pending','ru.requestFor'=>0,'r.date'=>date("Y-m-d"),'r.time>='=>$params['time']))->get()->result_array();

               $upcommingNotify = $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.location_name,r.group_to_ary,r.send_to_ary,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminder_admin_users as ru")->join("reminders as r","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->order_by("r.date","asc")->where(array('ru.mobile'=>$params['member'],'ru.status'=>'pending','ru.requestFor'=>0,'r.date >'=>date("Y-m-d")))->get()->result_array();

               //echo $this->db->last_query(); die;
               $reminder_notify = array();
               $resultNotify=array_merge($todayNotify,$upcommingNotify);
               /*print_r($todayNotify);
               print_r($upcommingNotify);
               print_r($resultNotify);die;*/
               if(!empty($resultNotify)){
                    foreach ($resultNotify as $value) {
                         if($value['date']==date("Y-m-d") && $value['time']<$params['time']){
                            continue;
                         }
                         $row = array();
                          /*IS FUTURE */
                         $isn['admin'] = $value['admin_Id'];
                         $isn['member'] = $params['member'];
                         $exist = $this->db->select("id")->get_where("reminder_future_map",$isn)->num_rows();

                         $row['reminderId'] =  $value['reminderId'];
                         $row['name'] =  $value['name'];
                         $row['description'] =  $value['description'];
                         $row['date'] =  $value['date'];
                         $row['time'] =  $value['time'];
                         $row['latitude'] =  $value['latitude'];
                         $row['longitude'] =  $value['longitude'];
                         $row['location_name'] =  $value['location_name'];
                         $row['category_name'] =  ($value['category_name']!=null)?$value['category_name']:"";
                         $row['admin_Id'] =  $value['admin_Id'];
                         $row['admin_name'] =  $value['admin_name'];
                         $row['groups'] =  (unserialize($value['group_to_ary'])!=false)?unserialize($value['group_to_ary']):array();
                         $row['members'] =  (unserialize($value['send_to_ary'])!=false)?unserialize($value['send_to_ary']):array();
                         $row['isFuture'] = ($exist>0)?1:0;
                         $reminder_notify[] = $row; 

                       
                    }
                    $result['status'] = 1;
                    $result['msg'] = "Reminder notification list successfully";
                    $result['data'] = $reminder_notify;
                    json_encode_help($result);
               }else{
                    $result['status'] = 0;
                    $result['msg'] = "No data found";
                    json_encode_help($result);
               }                          
             }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }    
    /*-- END reminder_notify_list API --*/

    /*-- START reminder_accept_reject API --*/
    public function reminder_accept_reject(){
        $result = array();
        $params = json_decode_help();
        
        if(!empty($params)){
             if($params['reminderId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder id is required";
                json_encode_help($result);
             }else if($params['member'] == ""){
                $result['status'] = 0;
                $result['msg'] = "member is required";
                json_encode_help($result);
             }else if($params['status'] == ""){
                $result['status'] = 0;
                $result['msg'] = "status accept/reject is required";
                json_encode_help($result);
             }else{
                /*IS FUTURE */
                $res = $this->db->get_where("reminders",array("reminderId"=>$params['reminderId']))->row_array();

                $adminDet = $this->db->get_where("users",array("userId"=>$res['admin']))->row_array();
                $countryCode = substr($adminDet['mobile'], 0, 2);
                if(strlen($params['member'])==10){
                    $isn['member'] = $countryCode.$params['member'];
                }else{
                    $isn['member'] = str_replace("+","",$params['member']);
                } 
                if(isset($params['isFuture'])){  
                    $isn['admin'] = $res['admin'];
                      
                    
                    $exist = $this->db->get_where("reminder_future_map",$isn)->row();
                    if($params['isFuture']==1){//isFuture ON
                        if(empty($exist)){
                            $this->db->insert("reminder_future_map",$isn);
                        }
                    }else if($params['isFuture']==0){//isFuture OFF
                        if(!empty($exist)){
                            $this->db->where($isn)->delete("reminder_future_map");
                        }
                    }
                }

                $whereAry['reminderId'] = $params['reminderId'];
                $whereAry['mobile'] = $isn['member'];
                $upd['status'] = $params['status'];
                if($params['status'] == 1)
                    $response_msg = 'Reminder request has been rejected.';
                else if($params['status'] == 0)
                    $response_msg = 'Reminder request has been accepted.';
                $this->db->where($whereAry)->update("reminder_admin_users",$upd);
                if( $this->db->affected_rows() ){
                        //SEND NOTIFICATIONS
                        //$adminDet = $this->db->get_where("users",array("userId"=>$res['admin']))->row_array();
                        $userDet = $this->db->get_where("users",array("mobile"=>$isn['member']))->row_array();
                        /*echo $this->db->last_query();
                        print_r($userDet);die;*/
                        /*---Push notify---*/
                        $msg = ucwords($userDet['name']).' has '.$params['status'].'ed your '.$res['name'].' remider';
                        //Android
                        if($adminDet['deviceType']==0){
                            fcm_send(array($adminDet['deviceToken']),$msg);
                        }
                        
                        //IOS
                        if($adminDet['deviceType']==1){
                            apns_send(array($adminDet['deviceToken']),$msg);
                        }                       

                        /*---Email notify---*/
                        if($res['email_notify']==1){
                            $from = $userDet['email'];
                            $to = $adminDet['email'];
                            $subject = 'Reminder App – Remider notify of approved/rejected';
                            $body = $msg;
                                                       
                            email_send($from,$to,'',$subject,$body);
                        }
                        
                        /*---Text notify---*/
                        /*if($params['text_msg_notify']==1){
                            $mobilesAry
                        }*/

                    $result['status'] = 1;
                    $result['msg'] = $response_msg;
                    json_encode_help($result);
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "Something went wrong";
                    json_encode_help($result);
                }                
             }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }    
    /*-- END reminder_accept_reject API --*/

    /*-- START reminder_list API --*/
    public function reminder_list(){
        $result = $todayReminderM = $upcommingReminderM = array();
        $params = json_decode_help();
        
        if(!empty($params)){
             if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
             }else if($params['time'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Current time is required";
                json_encode_help($result);
             }else{
                   //Remider Category list
                   $ins['admin'] = 0;    
                   $exist = $this->db->select("categoryId,name")->get_where("reminder_category",$ins)->result_array();
                    

                   $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row_array();
                   $todayReminder =  $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.group_to_ary,r.send_to_ary,r.reminder_status,r.location_name,ru.requestFor,ru.status,r.categoryId as categoryId,r.email_notify,r.text_msg_notify,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminders as r")->join("reminder_admin_users as ru","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->order_by("r.time","asc")->where(array('ru.mobile'=>$userDet['mobile'],'ru.status'=>'accept','ru.remFiredbyLocation'=>'0','ru.is_deleted'=>'0','r.date'=>date("Y-m-d"),'r.time>='=>$params['time']))->get()->result_array();
                   
                   $upcommingReminder =  $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.group_to_ary,r.send_to_ary,r.reminder_status,r.location_name,ru.requestFor,ru.status,r.categoryId as categoryId,r.email_notify,r.text_msg_notify,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminders as r")->join("reminder_admin_users as ru","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->order_by("r.date","asc")->where(array('ru.mobile'=>$userDet['mobile'],'ru.status'=>'accept','ru.remFiredbyLocation'=>'0','ru.is_deleted'=>'0','r.date >'=>date("Y-m-d")))->get()->result_array();
                    //echo $this->db->last_query();die;
                   if(!empty($todayReminder)){
                        foreach ($todayReminder as $value) {
                             $row = array();
                             $row['reminderId'] =  $value['reminderId'];
                             $row['name'] =  $value['name'];
                             $row['description'] =  $value['description'];
                             $row['date'] =  $value['date'];
                             $row['time'] =  $value['time'];
                             $row['latitude'] =  $value['latitude'];
                             $row['longitude'] =  $value['longitude'];
                             $row['location_name'] =  $value['location_name'];
                             $row['email_notify'] =  $value['email_notify'];
                             $row['text_msg_notify'] =  $value['text_msg_notify'];
                             $row['category_id'] =  $value['categoryId'];
                             $row['category_name'] =  ($value['category_name']!=null)?$value['category_name']:"";
                             $row['admin_id'] =  $value['admin_Id'];
                             $row['admin_name'] =  $value['admin_name']; 
                             $row['requestFor'] =  $value['requestFor'];                              
                             $row['reminder_status'] =  $value['reminder_status'];
                             $row['groups'] =  (unserialize($value['group_to_ary'])!=false)?unserialize($value['group_to_ary']):array();
                             $row['members'] =  (unserialize($value['send_to_ary'])!=false)?unserialize($value['send_to_ary']):array();  
                             $todayReminderM[] = $row;                        
                        }
                   }
                   if(!empty($upcommingReminder)){
                        foreach ($upcommingReminder as $value) {
                             $row = array();
                             $row['reminderId'] =  $value['reminderId'];
                             $row['name'] =  $value['name'];
                             $row['description'] =  $value['description'];
                             $row['date'] =  $value['date'];
                             $row['time'] =  $value['time'];
                             $row['latitude'] =  $value['latitude'];
                             $row['longitude'] =  $value['longitude'];
                             $row['location_name'] =  $value['location_name'];
                             $row['email_notify'] =  $value['email_notify'];
                             $row['text_msg_notify'] =  $value['text_msg_notify'];
                             $row['category_id'] =  $value['categoryId'];
                             $row['category_name'] =  ($value['category_name']!=null)?$value['category_name']:"";
                             $row['admin_id'] =  $value['admin_Id'];
                             $row['admin_name'] =  $value['admin_name'];
                             $row['requestFor'] =  $value['requestFor']; 
                             $row['reminder_status'] =  $value['reminder_status'];
                             $row['groups'] =  (unserialize($value['group_to_ary'])!=false)?unserialize($value['group_to_ary']):array();
                             $row['members'] =  (unserialize($value['send_to_ary'])!=false)?unserialize($value['send_to_ary']):array();  
                             $upcommingReminderM[] = $row; 
                        }
                   }
                    $data['today'] = $todayReminderM;
                    $data['upcommin'] = $upcommingReminderM;
                    $data['category_list'] = $exist;
                    $result['status'] = 1;
                    $result['msg'] = "Reminder list successfully";
                    $result['data'] = $data;
                    json_encode_help($result);
                
             }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END reminder_list API --*/   

    /*-- START get_reminder API --*/
    public function get_reminder(){
        $result = $getReminderM = array();
        $params = json_decode_help();
        
        if(!empty($params)){
             if($params['reminderId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder id is required";
                json_encode_help($result);
             }else{
                   
                   $getReminder =  $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.group_to_ary,r.send_to_ary,r.reminder_status,r.location_name,r.email_notify,r.text_msg_notify,ru.requestFor,ru.status,r.categoryId as categoryId,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminders as r")->join("reminder_admin_users as ru","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->order_by("r.time","asc")->where(array('ru.reminderId'=>$params['reminderId']))->get()->result_array();
                   
                  
                   if(!empty($getReminder)){
                        foreach ($getReminder as $value) {
                             $row = array();
                             $row['reminderId'] =  $value['reminderId'];
                             $row['name'] =  $value['name'];
                             $row['description'] =  $value['description'];
                             $row['date'] =  $value['date'];
                             $row['time'] =  $value['time'];
                             $row['latitude'] =  $value['latitude'];
                             $row['longitude'] =  $value['longitude'];
                             $row['location_name'] =  $value['location_name'];
                             $row['email_notify'] =  $value['email_notify'];
                             $row['text_msg_notify'] =  $value['text_msg_notify'];
                             $row['category_id'] =  $value['categoryId'];
                             $row['category_name'] =  ($value['category_name']!=null)?$value['category_name']:"";
                             $row['admin_id'] =  $value['admin_Id'];
                             $row['admin_name'] =  $value['admin_name']; 
                             $row['requestFor'] =  $value['requestFor']; 
                             $row['reminder_status'] =  $value['reminder_status'];
                             $row['groups'] =  (unserialize($value['group_to_ary'])!=false)?unserialize($value['group_to_ary']):array();
                             $row['members'] =  (unserialize($value['send_to_ary'])!=false)?unserialize($value['send_to_ary']):array();  
                             $getReminderM[] = $row;                        
                        }
                   }
                  
                    
                    $result['status'] = 1;
                    $result['msg'] = "Reminder details successfully";
                    $result['data'] = $getReminderM;
                    json_encode_help($result);
                
             }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END get_reminder API --*/   

    /*-- START reminder_history API --*/
    public function reminder_history(){
        $result = $pastRemSR = array();
        $params = json_decode_help();
        if(!empty($params)){
             if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);    
             }else if($params['time'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Current time is required";
                json_encode_help($result);        
             }else{
                 $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row_array();

                 /*pastRemSent*/
                 if(isset($params['search']) && $params['search'] != ""){
                    $this->db->like('r.name', $params['search']);
                    //$this->db->like('description', $params['search']);
                 }
                 if(isset($params['start_date']) && isset($params['end_date']) && $params['start_date'] != "" && $params['end_date'] != ""){//both
                    $this->db->where('r.date >=',$params['start_date'] );
                    $this->db->where('r.date <=', $params['end_date'] );
                 }else if(isset($params['start_date']) && $params['start_date'] != "" && $params['end_date'] == ""){//start_date
                    $this->db->where('r.date >=',$params['start_date'] );
                 }else if(isset($params['end_date']) && $params['start_date'] == "" && $params['end_date'] != ""){//end_date
                    $this->db->where('r.date <=', $params['end_date'] );
                 }else if(isset($params['time'])){
                     // $this->db->where('r.date <=', date("Y-m-d") );
                     // $this->db->where('r.time <=', $params['time'] );
                 }
                $pastRemSent =  $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.group_to_ary,r.send_to_ary,r.reminder_status,r.location_name,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminders as r")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->where(array('r.admin'=>$params['userId']))->order_by("r.date","desc")->order_by("r.time", "desc")->get()->result_array();
                // echo $this->db->last_query();die;

                 /*pastRemReceive*/
                 if(isset($params['search']) && $params['search'] != ""){
                    $this->db->like('r.name', $params['search']);
                    //$this->db->like('description', $params['search']);
                 }
                 if(isset($params['start_date']) && isset($params['end_date']) && $params['start_date'] != "" && $params['end_date'] != ""){//both
                    $this->db->where('r.date >=',$params['start_date'] );
                    $this->db->where('r.date <=', $params['end_date'] );
                 }else if(isset($params['start_date']) && $params['start_date'] != "" && $params['end_date'] == ""){//start_date
                    $this->db->where('r.date >=',$params['start_date'] );
                 }else if(isset($params['end_date']) && $params['start_date'] == "" && $params['end_date'] != ""){//end_date
                    $this->db->where('r.date <=', $params['end_date'] );
                 }else if(isset($params['time'])){
                     // $this->db->where('r.date <=', date("Y-m-d") );
                     // $this->db->where('r.time <= ', $params['time']);
                 }
                $pastRemReceive = $this->db->select("r.reminderId,r.name,r.description,r.date,r.time,r.latitude,r.longitude,r.group_to_ary,r.send_to_ary,r.reminder_status,r.location_name,ru.status,c.name as category_name,u.name as admin_name,u.userId as admin_Id,u.mobile as admin_mobile")->from("reminders as r")->join("reminder_admin_users as ru","r.reminderId=ru.reminderId")->join("users as u","r.admin=u.userId")->join("reminder_category as c","r.categoryId=c.categoryId","left")->where(array('ru.mobile'=>$userDet['mobile'],'ru.status'=>'accept'))->order_by("r.date","desc")->order_by("r.time", "desc")->get()->result_array();
                // echo '<br><br><br>'.$this->db->last_query();die;
                $pastRemSentM = $pastRemReceiveM = array();
                if(!empty($pastRemSent)){
                    foreach ($pastRemSent as $value) {
                         if($value['date']==date("Y-m-d") && $value['time']>$params['time']){
                            continue;
                         }
                         $row = array();
                         $row['reminderId'] =  $value['reminderId'];
                         $row['name'] =  $value['name'];
                         $row['description'] =  $value['description'];
                         $row['date'] =  $value['date'];
                         $row['time'] =  $value['time'];
                         $row['latitude'] =  $value['latitude'];
                         $row['longitude'] =  $value['longitude'];
                         $row['location_name'] =  $value['location_name'];
                         $row['category_name'] =  ($value['category_name']!=null)?$value['category_name']:"";
                         $row['admin_Id'] =  $value['admin_Id'];
                         $row['admin_name'] =  $value['admin_name'];
                         $row['reminder_status'] =  $value['reminder_status'];
                         $row['groups'] =  (unserialize($value['group_to_ary'])!=false)?unserialize($value['group_to_ary']):array();
                         $row['members'] =  (unserialize($value['send_to_ary'])!=false)?unserialize($value['send_to_ary']):array();
                         $pastRemSentM[] = $row;                        
                    }
               }

               if(!empty($pastRemReceive)){
                    foreach ($pastRemReceive as $value) {
                        if($value['date']==date("Y-m-d") && $value['time']>$params['time']){
                            continue;
                         }
                         $row = array();
                         $row['reminderId'] =  $value['reminderId'];
                         $row['name'] =  $value['name'];
                         $row['description'] =  $value['description'];
                         $row['date'] =  $value['date'];
                         $row['time'] =  $value['time'];
                         $row['latitude'] =  $value['latitude'];
                         $row['longitude'] =  $value['longitude'];
                         $row['location_name'] =  $value['location_name'];
                         $row['category_name'] =  ($value['category_name']!=null)?$value['category_name']:"";
                         $row['admin_Id'] =  $value['admin_Id'];
                         $row['admin_name'] =  $value['admin_name'];
                         $row['reminder_status'] =  $value['reminder_status'];
                         $row['groups'] =  (unserialize($value['group_to_ary'])!=false)?unserialize($value['group_to_ary']):array();
                         $row['members'] =  (unserialize($value['send_to_ary'])!=false)?unserialize($value['send_to_ary']):array();
                         $pastRemReceiveM[] = $row; 
                    }
               }
                //print_r($pastRemReceive); print_r($pastRemReceiveM);die;
                $pastRemSR['sent'] = $pastRemSentM;
                $pastRemSR['receive'] = $pastRemReceiveM;
                //print_r($pastRemSR);die();
                $result['status'] = 1;
                $result['msg'] = "Reminder history successfully";
                $result['data'] = $pastRemSR;
                json_encode_help($result);
                
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }
    /*-- END reminder_history API --*/  

    public function reminder_delete(){
        $result = array();
        $params = json_decode_help();
        
        if(!empty($params)){
             if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User id is required";
                json_encode_help($result);
             }else if($params['reminderId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder id is required";
                json_encode_help($result);
             }else{  
                $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row_array();
                $remDet =$this->db->get_where("reminders",array("reminderId"=>$params['reminderId']))->row_array();
                if($remDet['admin']==$params['userId']){//Admin User
                    $this->db->where("reminderId",$params['reminderId'])->where("admin",$params['userId'])->delete("reminders");                
                    if( $this->db->affected_rows() ){
                        $this->db->where("reminderId",$params['reminderId'])->where("admin",$params['userId'])->delete("reminder_admin_users");
                        
                        $result['status'] = 1;
                        $result['msg'] = "Reminder deleted successfully";
                        json_encode_help($result);
                    }else{
                        $result['status'] = 0;
                        $result['msg'] = "This user is not authorised person for delete reminder";
                        json_encode_help($result);
                    }  
                }else{//member user
                    $updRu['is_deleted'] = 1;
                    $this->db->where(array("reminderId"=>$params['reminderId'],"mobile"=>$userDet['mobile']))->update("reminder_admin_users",$updRu);
                    //echo $this->db->last_query();die;
                    if( $this->db->affected_rows() ){
                        $result['status'] = 1;
                        $result['msg'] = "Reminder deleted successfully";
                        json_encode_help($result);
                    }else{
                        $result['status'] = 0;
                        $result['msg'] = "This user is not authorised person for delete reminder";
                        json_encode_help($result);
                    }
                }
                       
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }

    public function reminder_fired_by_location(){
        $result = array();
        $params = json_decode_help();
        
        if(!empty($params)){
             if($params['userId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "User Id is required";
                json_encode_help($result);
             }else if($params['reminderId'] == ""){
                $result['status'] = 0;
                $result['msg'] = "Reminder id is required";
                json_encode_help($result);
             }elseif(!is_array($params['reminderId'])){
                $result['status'] = 0;
                $result['msg'] = "Reminder id is required in Array Format";
                json_encode_help($result);
             }else{ 
                $userDet = $this->db->select("mobile")->get_where("users",array("userId"=>$params['userId']))->row_array();               
                $remUserLists = $this->db->where_in("reminderId",$params['reminderId'])->get_where("reminder_admin_users",array("mobile"=>$userDet['mobile']))->result_array();                
                if( !empty($remUserLists) ){
                    foreach ($remUserLists as $value) {
                        $upd['remFiredbyLocation']="1";
                        $this->db->where("id",$value['id'])->update("reminder_admin_users",$upd);
                    }               
                    $result['status'] = 1;
                    $result['msg'] = "Reminder location fired successfully";
                    json_encode_help($result);
                    
                }else{
                    $result['status'] = 0;
                    $result['msg'] = "This user is not authorised person for this reminder";
                    json_encode_help($result);
                }         
            }
        }else{
            $result['status'] = 0;
            $result['msg'] = "Parameter are required";
            json_encode_help($result);
        }
    }

    public function fcm_send(){
        $params = json_decode_help();
        if($params['deviceToken'] == ""){
            $result['status'] = 0;
            $result['msg'] = "deviceToken is required";
            json_encode_help($result);
        }else if($params['msg'] == ""){
            $result['status'] = 0;
            $result['msg'] = "msg is required";
            json_encode_help($result);            
        }else{
            fcm_send(array($params['deviceToken']),$params['msg']);
            $result['status'] = 1;
            $result['msg'] = "Test notification successfully";
            json_encode_help($result);
        }
    }
    public function apns_send(){
        $params = json_decode_help();
        if($params['deviceToken'] == ""){
            $result['status'] = 0;
            $result['msg'] = "deviceToken is required";
            json_encode_help($result);
        }else if($params['msg'] == ""){
            $result['status'] = 0;
            $result['msg'] = "msg is required";
            json_encode_help($result);            
        }else{
            apns_send(array($params['deviceToken']),$params['msg']);
            $result['status'] = 1;
            $result['msg'] = "Test notification successfully";
            json_encode_help($result);
        }        
    }
    public function nexo(){
        text_msg_send("919998730557","919662847726","Have a Good Day. :)");
    }
    public function emailSending(){
       $subject = "Test message from sendgrid 11";
       $message = "Have a Good Day. :)";
       email_send('','milantest@yopmail.com','sanjay.panchal@whizsolutions.co.uk',$subject,$message);
       $result['status'] = 1;
       $result['msg'] = "Email sent successfully";
       json_encode_help($result);
    }
    public function iOSNotification(){
        $params = json_decode_help();
        if($params['deviceToken'] == ""){
            $result['status'] = 0;
            $result['msg'] = "deviceToken is required";
            json_encode_help($result);
        }else if($params['msg'] == ""){
            $result['status'] = 0;
            $result['msg'] = "msg is required";
            json_encode_help($result);            
        }else{
           $success = iOSNotification($title='MY Title', $params['msg'], $description='description', $params['deviceToken']);
           if($success){
                $result['status'] = 1;
                $result['msg'] = "Test notification successfully";
                json_encode_help($result);
           }else{
                $result['status'] = 0;
                $result['msg'] = "not sent";
                json_encode_help($result);
           }
        }
    }
}
?>