<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Wscontroller extends REST_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('Authorization_Token');
		$this->load->model('UserModel');
	}

	public function validate_access_token($headers)
	{
		try{
			if(array_key_exists('Authorization', $headers))
			{
				
			    $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
		        if($decodedToken['status'] != 1)
		        {
		        	$data = ERROR( -1,  'Token time Expire.');
					$this->response($data);
		        }
		        $data = json_decode(json_encode($decodedToken), true);   
		        return $data['data'];
			}
			else
			{
				$data = ERROR(-1, 'Token not found.');
				$this->response($data);

			}
		}catch(Exception $e)
	    {
	        return $e->getMessage();
	    }
	}

	public function register_post()
	{
		try{
			$name = $this->input->post('name');
			$email = $this->input->post('email');
			$phone = $this->input->post('phone');
			$password = $this->input->post('password');
			$role_id = $this->input->post('role_id');
			$registration_type = $this->input->post('registration_type');
			$registration_id = $this->input->post('registration_id');

			// validation
			if(empty($name)){
				$data = ERROR( 0, 'Please enter the name');

				$this->response($data);
			}

			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email');
				$this->response($data);
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$data = ERROR( 0, 'Please enter valid email');
			  	$this->response($data);
			}

			if(empty($phone)){
				$data = ERROR( 0, 'Please enter the phone');
				$this->response($data);
			}

			$is_exist = $this->UserModel->email_exist($email);

			if(!empty($is_exist)){
				$data = ERROR( 0, 'User already exist this email');
				$this->response($data);
			}

			if(empty($role_id)){
				$data = ERROR( 0, 'Please enter the role_id');
				$this->response($data);
			}

			if(empty($registration_type)){
				$data = ERROR( 0, 'Please enter the registration_type');
				$this->response($data);
			}

			if(!empty($registration_type) && $registration_type != 'Other')
			{
				$data = ERROR( 0, 'Please enter the registration_id');
				$this->response($data);
			}

			$user_data['vName'] = $name;
			$user_data['vEmail'] = $email;
			$user_data['vPhone'] = $phone;
			$user_data['iRoleId'] = $role_id;

			$user_data['eRegistrationType'] = $registration_type;
			$user_data['vRegistrationId'] = $registration_id;

			$user_data['vPassword'] = password_hash($password, PASSWORD_DEFAULT);
			$result = $this->UserModel->register_user($user_data);

			if(!empty($result))
			{
				$data = SUCCESS(1, 'User Register successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0,  'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function login_get()
	{
		try{
			$email = $this->input->get('email');
			$password = $this->input->get('password');

			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email.');
				$this->response($data);
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$data = ERROR( 0, 'Please enter valid email');
			  	$this->response($data);
			}

			if(empty($password)){
				$data = ERROR( 0, 'Please enter the password');
				$this->response($data);
			}

			$user_data['vEmail'] = $email;		
			$user_data['vPassword'] = $password;

			$is_match = $this->UserModel->login_action($user_data);
			$record = json_decode(json_encode($is_match), true);

			if(!empty($is_match))
			{
				if (password_verify($password, $record[0]['password'])) 
				{
					$token['user_id'] = $record[0]['user_id'];
					$token['name'] = $record[0]['name'];
					$token['email'] = $record[0]['email'];
					$enc_token = $this->authorization_token->generateToken($token);
					$this->UserModel->update_token($enc_token,$token['user_id']);

					$user_details = $this->UserModel->get_user($token['user_id']);
					
					$data = SUCCESS( 1,  'You have logged in successfully. ', $user_details);
					$this->response($data);
				}else{
					$data = ERROR( 0,  'Please enter valid password');
					$this->response($data);
				}
			}else{
				$data = ERROR( 0,  'Account not found with this email.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function my_profile_get()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);			

			if(!empty($this->input->get('user_id')))
			{	
				$user_id = $this->input->get('user_id');
			}
			else
			{
				$user_id = $token['user_id'];
			}

			$user = $this->UserModel->get_user($user_id);

			if(!empty($user))
			{
				$data = SUCCESS( 1, 'User data found successfully.',$user);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'User not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function update_profile_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);			
			$user_id = $token['user_id'];
			
			$name = $this->input->post('name');
			$phone = $this->input->post('phone');

			// validation
			if(empty($name)){
				$data = ERROR( 0, 'Please enter the name');

				$this->response($data);
			}

			if(empty($phone)){
				$data = ERROR( 0, 'Please enter the phone');
				$this->response($data);
			}

			$user_data['vName'] = $name;
			$user_data['vPhone'] = $phone;

			$user = $this->UserModel->update_user($user_id,$user_data);

			if(!empty($user))
			{
				$user_details = $this->UserModel->get_user($user_id);
				$data = SUCCESS( 1, 'User updated successfully.',$user_details);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'User not updated.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	
	public function get_roles_get()
	{
		try{

			$roles = $this->UserModel->user_roles();

			if(!empty($roles))
			{
				$data = SUCCESS( 1, 'Roles found successfully.',$roles);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Roles not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function change_password_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);			
			$user_id = $token['user_id'];

			$current_password = $this->input->post('current_password');
	        $new_password 	  = $this->input->post('new_password');
	        $confirm_password = $this->input->post('confirm_password');

	        if(empty($current_password)){
	            $data = array('status' => 0, 'msg' => 'Please enter your current_password');
	            $this->response($data);
	        }
	        $record = $this->UserModel->check_password($user_id);

			if(!empty($record))
			{
				if (!password_verify($current_password, $record[0]['password'])) 
				{
					$data = array('status' => 0, 'msg' => 'Your current password is wrong. Please check your current password');
	            	$this->response($data);
				}
			}

	        if(empty($new_password)){
	            $data = array('status' => 0, 'msg' => 'Please enter your new_password');
	            $this->response($data);
	        }

	        if(empty($confirm_password)){
	            $data = array('status' => 0, 'msg' => 'Please enter your confirm_password');
	            $this->response($data);
	        }
	        
	        if($confirm_password !== $new_password){
	            $data = array('status' => 0, 'msg' => 'Your new_password & confirm_password does not match');
	            $this->response($data);
	        }

	        $user_data['vPassword'] = password_hash($new_password, PASSWORD_DEFAULT);
	        $user = $this->UserModel->update_user($user_id,$user_data);
	        if(!empty($user))
			{
				$data = SUCCESS( 1, 'Password updated successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Password not updated.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function forgot_password_post()
	{
		try{
			$email = $this->input->post('email');

			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email.');
				$this->response($data);
			}

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$data = ERROR( 0, 'Please enter valid email');
			  	$this->response($data);
			}

			$is_exist = $this->UserModel->email_exist($email);

			if(empty($is_exist)){
				$data = ERROR( 0, 'User not exist with this email address.');
				$this->response($data);
			}

			$numeric = range(1, 9);
			$length = count($numeric) - 1;
			$results = array();
			for ($i = 0; $i < 6;) {
				$num = $numeric[mt_rand(0, $length)];
				if (!in_array($num, $results)) {
					$results[] = $num;
					$i++;
				}
			}
			$reset_code = implode("", $results);

			$reset_param = base64_encode($email);
			$reset_url = $this->config->item("base_url") . "reset-password.html?rsp=" . $reset_param;

			$ret_arr = array();
			$ret_arr[0]['reset_link'] = $reset_url;
			$ret_arr[0]['reset_code'] = $reset_code;
			$data['iEmailVerifyOtp'] = $ret_arr[0]['reset_code'];

			$this->UserModel->update_user_otp($email,$data);
			// $this->general->CISendMail($to = $email, $subject = 'Forgot Password', $body = "This is test.", $from_email = 'abhirpotdar@gmail.com', $from_name = 'Test', $cc = '', $bcc = '', $attach = array(), $params = array(), $reply_to = array());
			if(!empty($ret_arr))
			{
				$data = SUCCESS( 1, 'Email sent successfully to your email please check your inbox.',$ret_arr);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong.');
				$this->response($data);
			}

		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function reset_password_post()
	{
		try{
			$email 				= base64_decode($this->input->post('rsp'));
			$security_code 		= $this->input->post('security_code');
			$new_password 		= $this->input->post('new_password');
			$confirm_password 	= $this->input->post('confirm_password');


			if(empty($email)){
	            $data = array('status' => 0, 'msg' => 'Please enter your rsp');
	            $this->response($data);
	        }

			if(empty($security_code)){
	            $data = array('status' => 0, 'msg' => 'Please enter your security_code');
	            $this->response($data);
	        }

	        if(empty($new_password)){
	            $data = array('status' => 0, 'msg' => 'Please enter your new_password');
	            $this->response($data);
	        }

	        if(empty($confirm_password)){
	            $data = array('status' => 0, 'msg' => 'Please enter your confirm_password');
	            $this->response($data);
	        }
	        
	        if($confirm_password !== $new_password){
	            $data = array('status' => 0, 'msg' => 'Your new_password & confirm_password does not match');
	            $this->response($data);
	        }

	        $is_exist = $this->UserModel->check_security_code($email,$security_code);
	        $data1['vPassword'] = password_hash($confirm_password, PASSWORD_DEFAULT);

	        if(!empty($is_exist)){
	        	$data1['iEmailVerifyOtp'] ='';	
				$res = $this->UserModel->update_password($email,$data1);
				$data = array('status' => 1, 'meassage' => 'Password chnaged successfully.');
				echo json_encode($data);die;
			}else{
				$data = array('status' => 0, 'meassage' => 'Security code does not match.'); 
				echo json_encode($data);die;
			}

		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}
}
