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
		$this->load->model('CelebrityModel');
		$this->load->model('MusicCreatorModel');
		$this->load->model('ContactModel');
		$this->load->model('PlatformModel');
		$this->load->model('CategoryModel');
		$this->load->model('CouponModel');
		$this->load->model('CartModel');
		$this->load->model('WishlistModel');
	}

	/**
	 * Validate access token
	 */
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

	/**
	 * Register User
	 */
	public function register_post()
	{
		try{
			$first_name = $this->input->post('first_name');
			$last_name = $this->input->post('last_name');
			$email = $this->input->post('email');
			$phone = $this->input->post('phone');
			$password = $this->input->post('password');
			$role_id = $this->input->post('role_id');
			$registration_type = $this->input->post('registration_type');
			$registration_id = $this->input->post('registration_id');

			// validation
			if(empty($first_name)){
				$data = ERROR( 0, 'Please enter the first_name');

				$this->response($data);
			}
			if(empty($last_name)){
				$data = ERROR( 0, 'Please enter the last_name');

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

			$user_data['vFirstName'] = $first_name;
			$user_data['vLastName'] = $last_name;
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

	/**
	 * Login User
	 */
	public function login_post()
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

			if($password == ""){
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
					$token['name'] = $record[0]['first_name'];
					$token['email'] = $record[0]['email'];
					$enc_token = $this->authorization_token->generateToken($token);
					$this->UserModel->update_token($enc_token,$token['user_id']);

					$user_details = $this->UserModel->get_user($token['user_id']);
					#print_r($user_details[0]['role_id']);die;
					if($user_details[0]['role_id1'] == '2')
					{
						$user_details[0]['creator_details'] = $this->MusicCreatorModel->get_music_creator_details($user_details[0]['user_id']);
						if(!empty($user_details[0]['creator_details'][0]['images']))
						{
							$images = json_decode($user_details[0]['creator_details'][0]['images']);
							$img1 = [];
							if(!empty($images))
							{
								foreach($images as $val)
								{
									//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
									// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
									$img1[] = $this->general->getImageUrl('profile_image', $val);
								}
							}
							$user_details[0]['creator_details'][0]['images'] = $img1;
						}
					}
					if($user_details[0]['role_id1'] == '3')
					{
						$user_details[0]['celebrity_details'] = $this->CelebrityModel->get_celebrity_details($user_details[0]['user_id']);
						if(!empty($user_details[0]['celebrity_details'][0]['images']))
						{
							$images = json_decode($user_details[0]['celebrity_details'][0]['images']);
							$img1 = [];
							if(!empty($images))
							{
								foreach($images as $val)
								{
									//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
									// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
									$img1[] = $this->general->getImageUrl('profile_image', $val);
								}
							}
							$user_details[0]['celebrity_details'][0]['images'] = $img1;
						}
						if(!empty($user_details[0]['celebrity_details'][0]['w9form']))
						{
							$user_details[0]['celebrity_details'][0]['w9form'] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/w9_form/".$user_details[0]['w9form'];
						}
					}
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

	/**
	 * My profile 
	 */
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

	/**
	 * Update profile 
	 */
	public function update_profile_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token   = $this->validate_access_token($headers);			
			$user_id = $token['user_id'];
			
			$first_name = $this->input->post('first_name');
			$last_name  = $this->input->post('last_name');
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

			$user_data['vFirstName'] = $first_name;
			$user_data['vLastName'] = $last_name;
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

	/**
	 * get roles 
	 */
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

	/**
	 * change password 
	 */
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

	/**
	 * forgot password 
	 */
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
			$this->general->CISendMail($to = $email, $subject = 'Forgot Password', $body = "This is test.", $from_email = 'noreply@purecss.co.in', $from_name = 'Test', $cc = '', $bcc = '', $attach = array(), $params = array(), $reply_to = array());
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

	/**
	 * reset password 
	 */
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

	public function add_celebrity_post()
	{
		try{
			$first_name 	   = $this->input->post('first_name');
			$last_name         = $this->input->post('last_name');
			$email             = $this->input->post('email');
			$phone             = $this->input->post('phone');
			$role_id           = '3';
			$registration_type = 'Other';

			$social_media_links = $this->input->post('social_media_links');
			

			$title 			   = $this->input->post('title');
			$tag_line 		   = $this->input->post('tag_line');
			$short_description = $this->input->post('short_description');
			$long_description  = $this->input->post('long_description');
			$categories 	   = $this->input->post('categories');
			$price 			   = $this->input->post('price');
			$is_featured 	   = $this->input->post('is_featured');
			$added_date        = date('Y-m-d H:i:s');
			$country           = $this->input->post('country');

			$account_name 	   = $this->input->post('account_name');
			$account_number    = $this->input->post('account_number');
			$bank_name 	       = $this->input->post('bank_name');
			$bank_code 	       = $this->input->post('bank_code');
			$bank_address 	   = $this->input->post('bank_address');
			$paypal_id 	       = $this->input->post('paypal_id');


			// validation
			
			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email');
				$this->response($data);
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$data = ERROR( 0, 'Please enter valid email');
			  	$this->response($data);
			}

			$is_exist = $this->UserModel->email_exist($email);

			if(!empty($is_exist)){
				$data = ERROR( 0, 'User already exist this email');
				$this->response($data);
			}

			if(empty($title))
			{
				$data = ERROR( 0, 'Please enter the title');
				$this->response($data);
			}

			if(empty($tag_line))
			{
				$data = ERROR( 0, 'Please enter the tag_line');
				$this->response($data);
			}

			if(empty($short_description))
			{
				$data = ERROR( 0, 'Please enter short_description');
				$this->response($data);
			}

			if(empty($long_description))
			{
				$data = ERROR( 0, 'Please enter the long_description');
				$this->response($data);
			}

			if(empty($categories))
			{
				$data = ERROR( 0, 'Please enter the categories');
				$this->response($data);
			}

			if(empty($price))
			{
				$data = ERROR( 0, 'Please enter the price');
				$this->response($data);
			}

			if(empty($is_featured))
			{
				$data = ERROR( 0, 'Please enter the is_featured');
				$this->response($data);
			}

			if(empty($account_name))
			{
				$data = ERROR( 0, 'Please enter account_name');
				$this->response($data);
			}

			if(empty($account_number))
			{
				$data = ERROR( 0, 'Please enter the account_number');
				$this->response($data);
			}

			if(empty($bank_name))
			{
				$data = ERROR( 0, 'Please enter the bank_name');
				$this->response($data);
			}

			if(empty($bank_code))
			{
				$data = ERROR( 0, 'Please enter the bank_code');
				$this->response($data);
			}

			if(empty($bank_address))
			{
				$data = ERROR( 0, 'Please enter the bank_address');
				$this->response($data);
			}

			$data = [];  
      		// $config['upload_path'] 		= './public/uploads/profile';
			// $config['allowed_types'] 	= 'gif|jpg|png';
			
			$imgData = [];
			$errors = [];
			$files = $_FILES;
			$upload_count = count($_FILES['profile_picture']['name']);

			for( $i = 0; $i < $upload_count; $i++ )
			{
				$imgData[] = str_replace(' ', '_', $files['profile_picture']['name'][$i]).'_'.time();

			    $_FILES['profile_picture'] = [
			        'name'     => $files['profile_picture']['name'][$i],
			        'type'     => $files['profile_picture']['type'][$i],
			        'tmp_name' => $files['profile_picture']['tmp_name'][$i],
			        'error'    => $files['profile_picture']['error'][$i],
			        'size'     => $files['profile_picture']['size'][$i]
			    ];
			    
			   	if (!empty($files["profile_picture"]["name"]))
            	{
	                $file_path = "profile_image";
	                $file_name = str_replace(' ', '_', $files["profile_picture"]["name"][$i]).'_'.time();
	                $file_tmp_path = $_FILES["profile_picture"]["tmp_name"];
	                
	                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
	                if (!$response)
	                {
	                    //file upload failed
	                }
	            }
			}
			
			$user_data['vFirstName']= $first_name;
			$user_data['vLastName'] = $last_name;
			$user_data['vEmail'] 	= $email;
			$user_data['vPhone'] 	= $phone;
			$user_data['iRoleId'] 	= $role_id;
			$user_data['vCountry'] 	= $country;
			$user_data['vImage'] 	= json_encode($imgData);

			$user_data['eRegistrationType'] = $registration_type;

			$last_id = $this->UserModel->register_user($user_data);

			if (!empty($_FILES["w9_form"]["name"]))
        	{
                $file_path = "w9_form";
                $file_name = str_replace(' ', '_', $_FILES["w9_form"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["w9_form"]["tmp_name"];
                // print_r($file_tmp_path);die;
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
                if (!$response)
                {
                    //file upload failed

                }
            }

			$celebrity_data['iUsersId'] 			= $last_id;
			$celebrity_data['vTitle'] 				= $title;
			$celebrity_data['vTagLine'] 			= $tag_line;
			$celebrity_data['vShortDescription'] 	= $short_description;
			$celebrity_data['vLongDescription'] 	= $long_description;
			$celebrity_data['vSocialMediaLinks'] = $social_media_links;
			$category_arr = explode(",",$categories);
			
			if(in_array(7, $category_arr))
			{
				$other_category = $this->input->post('other_category');
				$category_data['vCategoryName']   = $other_category;
				$category_data['vSlug'] 	  	  = strtolower($other_category);
				$category_data['vCategoryParent'] = '7';
				$category_data['dtAddedDate']  	  = date('Y-m-d H:i:s');

				$result = $this->CategoryModel->add_category($category_data);
				$category_arr[] = $result;
				$categories = implode(",",$category_arr);
			}

			$celebrity_data['vCategories'] 			= $categories;

			$celebrity_data['dPrice'] 				= $price;
			$celebrity_data['eIsFeatured'] 			= $is_featured;
			$celebrity_data['dtAddedDate'] 			= $added_date;

			$celebrity_data['vAccountName'] 		= $account_name;
			$celebrity_data['vAccountNumber'] 		= $account_number;
			$celebrity_data['vBankName'] 			= $bank_name;
			$celebrity_data['vBankCode'] 			= $bank_code;
			$celebrity_data['vBankAddress'] 		= $bank_address;
			$celebrity_data['vPaypalId'] 		    = $paypal_id;
			$celebrity_data['vW9Form'] 		        = str_replace(' ', '_', $_FILES["w9_form"]["name"]).'_'.time();
			$celebrity_data['vSocialMediaLinks']    = json_encode($social_media_links);

			$result = $this->CelebrityModel->register_celebrity($celebrity_data);

			if(!empty($result))
			{
				$data = SUCCESS(1, 'Celebrity Added successfully.',[]);
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

	public function get_celebrity_get()
	{
		try
		{
			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');

			$celebrity_id = $this->input->get('celebrity_id');

			$result = $this->CelebrityModel->get_celebrity_details($celebrity_id);
			if(empty($celebrity_id))
			{
				for ($i=0; $i < count($result) ; $i++) 
				{
					if(!empty($result[$i]['images']))
					{
						$images = json_decode($result[$i]['images']);

						$img1 = [];
						if(!empty($images))
						{
							foreach($images as $val)
							{
								//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
								$img1[] = $this->general->getImageUrl('profile_image', $val);							
							}
						}
						$result[$i]['images'] = $img1;
					}
					if(!empty($result[$i]['w9form']))
					{
						//$result[$i]['w9form'] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/w9_form/".$result[$i]['w9form'];
						$result[$i]['w9form'] = $this->general->getImageUrl('w9_form', $result[$i]['w9form']);
					}
				}
			}
			else
			{
				if(!empty($result[0]['images']))
				{
					$images = json_decode($result[0]['images']);
					$img1 = [];
					if(!empty($images))
					{
						foreach($images as $val)
						{
							//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
							// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
							$img1[] = $this->general->getImageUrl('profile_image', $val);
						}
					}
					$result[0]['images'] = $img1;
				}
				if(!empty($result[0]['w9form']))
				{
					//$result[0]['w9form'] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/w9_form/".$result[0]['w9form'];
					$result[0]['w9form'] = $this->general->getImageUrl('w9_form', $result[0]['w9form']);
				}				
			}

			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Celebrity details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Celebrity details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_all_celebrities_get(){
		try{
			$result = $this->CelebrityModel->get_all_celebrities();
			if($result)
			{
				$data = SUCCESS( 1, 'Celebrities details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
		
	}

	public function delete_celebrity_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$celebrity_id = $this->input->post('celebrity_id');

			$result = $this->CelebrityModel->delete_celebrity($celebrity_id);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Celebrity deleted successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function add_music_creator_post()
	{
		try{
			$step = $this->input->post('step');

			if(empty($step))
			{
				$data = ERROR( 0, 'Please enter the step');
				$this->response($data);
			}

			if($step == 'register')
			{
				// register
				$first_name 	   = $this->input->post('first_name');
				$last_name 	   	   = $this->input->post('last_name');
				$email             = $this->input->post('email');
				$phone             = $this->input->post('phone');
				$role_id           = '2';
				$registration_type = 'Other';

				// validation
				if(empty($first_name))
				{
					$data = ERROR( 0, 'Please enter the first_name');
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

				$is_exist = $this->UserModel->email_exist($email);

				if(!empty($is_exist)){
					$data = ERROR( 0, 'User already exist this email');
					$this->response($data);
				}

				if(empty($phone)){
					$data = ERROR( 0, 'Please enter the phone');
					$this->response($data);
				}
				$user_data['vFirstName']= $first_name;
				$user_data['vLastName'] = $last_name;
				$user_data['vEmail'] 	= $email;
				$user_data['vPhone'] 	= $phone;
				$user_data['iRoleId'] 	= $role_id;
				$user_data['eRegistrationType'] = $registration_type;

				$last_id = $this->UserModel->register_user($user_data);
				
				if(!empty($last_id))
				{	
					$res = $this->UserModel->get_user($last_id);
					$token['user_id'] = $res[0]['user_id'];
					$token['name'] = $res[0]['first_name'];
					$token['email'] = $res[0]['email'];
					$enc_token = $this->authorization_token->generateToken($token);
					$this->UserModel->update_token($enc_token,$token['user_id']);

					$res = $this->UserModel->get_user($last_id);
					$data = SUCCESS(1, 'Music Creator Added successfully.',$res);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0,  'Something went wrong...please try again.');
					$this->response($data);
				}
			}
			else if($step == 'artist')
			{
				$artist = $this->input->post('artist_name');
				$user_id = $this->input->post('user_id');
				$categories = $this->input->post('categories');

				$category_arr = explode(",",$categories);

				if(in_array(7, $category_arr))
				{
					$other_category = $this->input->post('other_category');
					$category_data['vCategoryName']   = $other_category;
					$category_data['vSlug'] 	  	  = strtolower($other_category);
					$category_data['vCategoryParent'] = '7';
					$category_data['dtAddedDate']  	  = date('Y-m-d H:i:s');

					$result = $this->CategoryModel->add_category($category_data);
					$category_arr[] = $result;
					
					$categories = implode(",",$category_arr);
				}
				
				if(empty($artist))
				{
					$data = ERROR( 0, 'Please enter the artist');
					$this->response($data);
				}
				$music_creator_data['vArtistName'] = $artist;
				$music_creator_data['iUsersId']    = $user_id;
				$music_creator_data['dtAddedDate'] = date('Y-m-d H:i:s');
				$music_creator_data['vCategories'] = $categories;
				$result = $this->MusicCreatorModel->add_artist($music_creator_data);
				if(!empty($result))
				{	
					$res = $this->MusicCreatorModel->get_artist($result);
					$data = SUCCESS(1, 'Artist Added successfully.',$res);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0,  'Something went wrong...please try again.');
					$this->response($data);
				}
			}
			else if($step == 'upload')
			{
				$music_creator_id = $this->input->post('music_creator_id');
				$category_id      = $this->input->post('category_id');
				$music_name       = $this->input->post('music_name');
				if(empty($music_creator_id))
				{
					$data = ERROR( 0, 'Please enter the music_creator_id');
					$this->response($data);
				}

				// Music Upload
				$data = [];  
	      		$config['upload_path'] 		= './public/uploads/music';
				$config['allowed_types'] 	= 'mp3|mpeg|mpg|mpeg3';
				
				$errors = [];
				$files = $_FILES;				
				
				if (!empty($files["music"]["name"]))
            	{
	                $file_path = "music";
	                $file_name = str_replace(' ', '_', $files["music"]["name"]).'_'.time();
	                $file_tmp_path = $_FILES["music"]["tmp_name"];
	                // print_r($file_tmp_path);die;
	                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
	                if (!$response)
	                {
	                    //file upload failed

	                }
	            }
				
				$music_creator_data['dtAddedDate'] 	= date('Y-m-d H:i:s');
				$music_creator_data['vMusic']		= str_replace(' ', '_', $files["music"]["name"]).'_'.time();
				$music_creator_data['iCreatorId']	= $music_creator_id;
				$music_creator_data['iCategoryId']	= $category_id;
				$music_creator_data['vMusicName']	= $music_name;
				$result = $this->MusicCreatorModel->upload_music($music_creator_data);
				
				if(!empty($result))
				{
					$data = SUCCESS(1, 'Music uploaded successfully.',[]);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0,  'Something went wrong...please try again.');
					$this->response($data);
				}
			}			
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}


	public function get_music_creator_get()
	{
		try{

			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$music_creator_id = $this->input->get('music_creator_id');

			$result = $this->MusicCreatorModel->get_music_creator_details($music_creator_id);

			//print_r($result);

			//print_r($result);

			if(!empty($music_creator_id))
			{
				$result[0]['celeb_data'] = $result;
				$result[0]['musics'] = $this->MusicCreatorModel->get_musics($result[0]['music_creator_id']);
				
				for ($i=0; $i < count($result[0]['musics']) ; $i++) 
				{
					if(!empty($result[0]['musics'][$i]['musics']))
					{	
						$result[0]['musics'][$i]['musics'] = $this->general->getImageUrl('music', $result[0]['musics'][$i]['musics']);
					}
				}
			}

			if(empty($music_creator_id))
			{
				for ($i=0; $i < count($result) ; $i++) 
				{
					if(!empty($result[$i]['images']))
					{
						$images = json_decode($result[$i]['images']);

						$img1 = [];
						if(!empty($images))
						{
							foreach($images as $val)
							{
								//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
								// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
								$img1[] = $this->general->getImageUrl('profile_image', $val);
							}
						}
						$result[$i]['images'] = $img1;
					}
				}
			}
			else
			{
				if(!empty($result[0]['images']))
				{
					$images = json_decode($result[0]['images']);
					$img1 = [];
					if(!empty($images))
					{
						foreach($images as $val)
						{
							//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
							// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
							$img1[] = $this->general->getImageUrl('profile_image', $val);
						}
					}
					$result[0]['images'] = $img1;
				}
			}
			
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Music_creator details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Celebrity details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_all_music_creators_get(){
		try{
			$result = $this->MusicCreatorModel->get_all_music_creators();
			if($result)
			{
				$data = SUCCESS( 1, 'Music Creators details found successfully.', $result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		} catch(Exception $e) {
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function delete_music_creator_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$music_creator_id = $this->input->post('music_creator_id');

			$result = $this->MusicCreatorModel->delete_music_creator($music_creator_id);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Music Creator deleted successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function update_music_creator_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$music_creator_id  = $this->input->post('music_creator_id');
			$first_name 	   = $this->input->post('first_name');
			$last_name 		   = $this->input->post('last_name');
			$email             = $this->input->post('email');
			$phone             = $this->input->post('phone');
			$country		   = $this->input->post('country');
			$update_date	   = date('Y-m-d H:i:s');

			// profile_picture
			$data = [];  
      		$config['upload_path'] 		= './public/uploads/profile';
			$config['allowed_types'] 	= 'jpeg|gif|jpg|png|mp3|mpeg|mpg|mpeg3';
			
			$imgData = [];
			$errors = [];
			$files = $_FILES;
			$upload_count = count($_FILES['profile_picture']['name']);

			for( $i = 0; $i < $upload_count; $i++ )
			{
				$imgData[] = str_replace(' ', '_', $files['profile_picture']['name'][$i]).'_'.time();

			    $_FILES['profile_picture'] = [
			        'name'     => $files['profile_picture']['name'][$i],
			        'type'     => $files['profile_picture']['type'][$i],
			        'tmp_name' => $files['profile_picture']['tmp_name'][$i],
			        'error'    => $files['profile_picture']['error'][$i],
			        'size'     => $files['profile_picture']['size'][$i]
			    ];
			    
			   

			    if (!empty($files["profile_picture"]["name"]))
            	{
	                $file_path = "profile_image";
	                $file_name = str_replace(' ', '_', $files["profile_picture"]["name"][$i]).'_'.time();
	                $file_tmp_path = $_FILES["profile_picture"]["tmp_name"];
	                // print_r($file_tmp_path);die;
	                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
	                if (!$response)
	                {
	                    //file upload failed

	                }
	            }
			}

			$user_data['vFirstName']    = $first_name;
			$user_data['vLastName']     = $last_name;
			$user_data['vEmail'] 		= $email;
			$user_data['vPhone'] 		= $phone;
			$user_data['vCountry'] 		= $country;
			$user_data['vImage'] 		= json_encode($imgData);
			$user_data['dtUpdatedDate'] = $update_date;

			$res = $this->UserModel->update_user_data($user_data,$music_creator_id);

			$artist_name       = $this->input->post('artist_name');
			$categories        = $this->input->post('categories');
			$social_media_links= $this->input->post('social_media_links');
			// music
			
			$data1 = [];  
      		$config1['upload_path'] 		= './public/uploads/music';
			$config1['allowed_types'] 	= 'mp3|mpeg|mpg|mpeg3';
			
			$errors1 = [];
			$files1 = $_FILES;

			if (!empty($files["music"]["name"]))
        	{
                $file_path = "music";
                $file_name = str_replace(' ', '_', $files["music"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["music"]["tmp_name"];
                // print_r($file_tmp_path);die;
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
            }

			$music_creator_data['vArtistName'] 		= $artist_name;
			$music_creator_data['vCategories'] 		= $categories;
			$music_creator_data['vSocialMediaLinks']= json_encode($social_media_links);
			$music_creator_data['vUploadMusic']		= str_replace(' ', '_', $files["music"]["name"]).'_'.time();
			$music_creator_data['dtUpdatedDate'] 	= date('Y-m-d H:i:s');

			$result = $this->MusicCreatorModel->update_music_creator($music_creator_data,$music_creator_id);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Music Creator updated successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}


	public function add_contact_us_post()
	{
		try{
			$name 		= $this->input->post('name');
			$email      = $this->input->post('email');
			$phone      = $this->input->post('phone');
			$subject	= $this->input->post('subject');
			$message	= $this->input->post('message');
			$added_date	= date('Y-m-d H:i:s');

			// validation
			if(empty($name)){
				$data = ERROR( 0, 'Please enter the name');
				$this->response($data);
			}

			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email');
				$this->response($data);
			}

			if(empty($phone)){
				$data = ERROR( 0, 'Please enter the phone');
				$this->response($data);
			}

			if(empty($subject)){
				$data = ERROR( 0, 'Please enter the subject');
				$this->response($data);
			}

			if(empty($message)){
				$data = ERROR( 0, 'Please enter the message');
				$this->response($data);
			}

			$contact_data['vName'] 	  = $name;
			$contact_data['vEmail'] 	  = $email;
			$contact_data['vPhone'] 	  = $phone;
			$contact_data['vSubject'] 	  = $subject;
			$contact_data['vMessage'] 	  = $message;
			$contact_data['dtAddedDate'] = $added_date;

			$result = $this->ContactModel->add_contact_us($contact_data);

			if($result)
			{
				$data = SUCCESS( 1, 'Your message posted successfully.Team reacted will contact you soon!',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_contact_us_get()
	{
		try{
			$contact_us_id = $this->input->get('contact_us_id');

			$result = $this->ContactModel->get_contact_us($contact_us_id);
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Contact us details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Contact us details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_celebrities_by_category_get()
	{
		try{
			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$category_id = $this->input->get('category_id');

			$result = $this->CelebrityModel->get_celebrities_by_category($category_id);
			
			for ($i=0; $i < count($result) ; $i++) 
			{
				if(!empty($result[$i]['images']))
				{
					$images = json_decode($result[$i]['images']);

					$img1 = [];
					if(!empty($images))
					{
						foreach($images as $val)
						{
							//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
							// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
							$img1[] = $this->general->getImageUrl('profile_image', $val);
						}
					}
					$result[$i]['images'] = $img1;
				}
			}

			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Celebrity details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Contact us details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_similar_celebrities_get()
	{
		try{
			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$category_id = $this->input->get('category_id');
			//$celebrity_id = $this->input->get('celebrity_id');

			$result = $this->CelebrityModel->get_celebrities_by_category($category_id, $celebrity_id);
			for ($i=0; $i < count($result) ; $i++) 
			{
				if(!empty($result[$i]['images']))
				{
					$images = json_decode($result[$i]['images']);

					$img1 = [];
					if(!empty($images))
					{
						foreach($images as $val)
						{
							//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
							// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
							$img1[] = $this->general->getImageUrl('profile_image', $val);
						}
					}
					$result[$i]['images'] = $img1;
				}
			}

			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Similar Celebrities found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Contact us details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function add_social_media_post()
	{
		try{
			$platform_name = $this->input->post('platform_name');
			$link          = $this->input->post('link');
			$icon          = $this->input->post('icon');
			$added_date	   = date('Y-m-d H:i:s');

			// validation
			if(empty($platform_name)){
				$data = ERROR( 0, 'Please enter the platform_name');
				$this->response($data);
			}

			$platform_data['vPlatformName'] = $platform_name;
			$platform_data['vLink'] 	  	= $link;
			$platform_data['vIcon'] 	  	= $icon;
			$platform_data['dtAddedDate']  	= $added_date;

			$result = $this->PlatformModel->add_platform($platform_data);

			if($result)
			{
				$data = SUCCESS( 1, 'Social media platform added successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_all_social_media_platforms_get()
	{
		try {
			$result = $this->PlatformModel->get_all_social_media_platform();
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Social media platform details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Contact us details not found.');
				$this->response($data);
			}
		} catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_social_media_platform_get()
	{
		try{
			$platform_id = $this->input->get('platform_id');

			// if(empty($platform_id))
			// {
			// 	$data = ERROR( 0, 'Please enter the platform_id');
			// 	$this->response($data);
			// }

			$result = $this->PlatformModel->get_social_media_platform($platform_id);
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Social media platform details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Social media platform details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function delete_social_media_platform_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$platform_id = $this->input->post('platform_id');

			if(empty($platform_id))
			{
				$data = ERROR( 0, 'Please enter the platform_id');
				$this->response($data);
			}
			$result = $this->PlatformModel->delete_social_media_platform($platform_id);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Social media platform deleted successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}
	
	public function update_social_media_platform_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$platform_id   = $this->input->post('platform_id');
			$platform_name = $this->input->post('platform_name');
			$link          = $this->input->post('link');
			$icon          = $this->input->post('icon');
			$updated_date	   = date('Y-m-d H:i:s');

			if(empty($platform_id))
			{
				$data = ERROR( 0, 'Please enter the platform_id');
				$this->response($data);
			}
			if(empty($platform_name))
			{
				$data = ERROR( 0, 'Please enter the platform_name');
				$this->response($data);
			}
			$data['vPlatformName'] = $platform_name;
			$data['vLink'] 		   = $link;
			$data['vIcon'] 	  	   = $icon;
			$data['dtUpdatedDate'] = $updated_date;

			$result = $this->PlatformModel->update_social_media_platform($platform_id,$data);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Social media platform updated successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function category_get($category)
	{
		try{

			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$slug = $category;

			$title      = $this->input->get('title');
			if(!$title){
				$title = '';
			}
			$price      = $this->input->get('price');
			if(!$price){
				$price = '';
			}
			$price_from = $this->input->get('price_from');
			if(!$price_from){
				$price_from = '0';
			}
			$price_to   = $this->input->get('price_to');
			if(!$price_to){
				$price_to = '';
			}
			
			$category1 = $this->CategoryModel->get_category_id($slug);
			
			if(!empty($category1)){
				$result = $this->CelebrityModel->get_celebrities_by_category($category1[0]['iCategoryMasterId'],$title,$price,$price_from,$price_to);
				for ($i=0; $i < count($result) ; $i++) 
				{
					if(!empty($result[$i]['images']))
					{
						$images = json_decode($result[$i]['images']);

						$img1 = [];
						if(!empty($images))
						{
							foreach($images as $val)
							{
								//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
								// $img1[] = $this->config->item('base_url').'public/uploads/profile/'.$val;
								$img1[] = $this->general->getImageUrl('profile_image', $val);
							}
						}
						$result[$i]['images'] = $img1;
					}
				}

				if(!empty($result))
				{
					$data = SUCCESS( 1, 'Celebrities found successfully.',$result);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0, 'Celebrities not found.');
					$this->response($data);
				}
			}
			else
			{
				$data = ERROR( 0, 'Celebrities not found.');
				$this->response($data);
			}

			
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function add_category_post()
	{
		try{
			$headers = $this->input->request_headers();
			$token = $this->validate_access_token($headers);

			$category_name = $this->input->post('category_name');
			$slug          = $this->input->post('slug');
			$description   = $this->input->post('description');			
			$added_date	   = date('Y-m-d H:i:s');

			// validation
			if(empty($category_name))
			{
				$data = ERROR( 0, 'Please enter the category_name');
				$this->response($data);
			}

			if(empty($slug))
			{
				$data = ERROR( 0, 'Please enter the slug');
				$this->response($data);
			}

			if (!empty($_FILES["image"]["name"]))
        	{
                $file_path = "category";
                $file_name = str_replace(' ', '_', $_FILES["image"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["image"]["tmp_name"];
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
                if (!$response)
                {
                    //file upload failed

                }
            }

			$category_data['vCategoryName'] = $category_name;
			$category_data['vSlug'] 	  	= $slug;
			$category_data['vImage'] 	  	= str_replace(' ', '_', $_FILES["image"]["name"]).'_'.time();
			$category_data['vDescription'] 	= $description;
			$category_data['dtAddedDate']  	= $added_date;

			$result = $this->CategoryModel->add_category($category_data);

			if($result)
			{
				$data = SUCCESS( 1, 'Category added successfully.',['file_response' => $response]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_all_categories_get()
	{
		try{
			$result = $this->CategoryModel->get_all_categories();

			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Category details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Category details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_category_get()
	{
		try{
			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$category_id = $this->input->get('category_id');

			$result = $this->CategoryModel->get_category($category_id);

			if(!empty($result[0]['image']))
			{
				//$result[0]['image'] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/category/".$result[0]['image'];
				$result[0]['image'] = $this->general->getImageUrl('category', $result[0]['image']);
			}


			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Category details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Category details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function delete_category_post()
	{
		try{
			$headers = $this->input->request_headers();
			$token = $this->validate_access_token($headers);

			$category_id = $this->input->post('category_id');

			if(empty($category_id))
			{
				$data = ERROR( 0, 'Please enter the category_id');
				$this->response($data);
			}
			$result = $this->CategoryModel->delete_category($category_id);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Category deleted successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function update_category_post()
	{
		try{
			$headers = $this->input->request_headers();
			$token = $this->validate_access_token($headers);

			$category_id   = $this->input->post('category_id');
			$category_name = $this->input->post('category_name');
			$slug          = $this->input->post('slug');
			$description   = $this->input->post('description');			
			$updated_date  = date('Y-m-d H:i:s');

			if(empty($category_id))
			{
				$data = ERROR( 0, 'Please enter the category_id');
				$this->response($data);
			}
			if(empty($category_name))
			{
				$data = ERROR( 0, 'Please enter the category_name');
				$this->response($data);
			}
			if(empty($slug))
			{
				$data = ERROR( 0, 'Please enter the slug');
				$this->response($data);
			}

			if (!empty($_FILES["image"]["name"]))
        	{
                $file_path = "category";
                $file_name = str_replace(' ', '_', $_FILES["image"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["image"]["tmp_name"];
                // print_r($file_tmp_path);die;
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
                if (!$response)
                {
                    //file upload failed

                }
            }

			$category_data['vCategoryName'] = $category_name;
			$category_data['vSlug'] 	  	= $slug;
			$category_data['vImage'] 	  	= str_replace(' ', '_', $_FILES["image"]["name"]).'_'.time();
			$category_data['vDescription'] 	= $description;
			$category_data['dtUpdatedDate'] = $updated_date;

			$result = $this->CategoryModel->update_category($category_id,$category_data);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Category updated successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function add_coupon_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$coupon_title = $this->input->post('coupon_title');
			$coupon_code  = $this->input->post('coupon_code');
			$description  = $this->input->post('description');

			$celebrity_id = $this->input->post('celebrity_id');
			$start_date  = $this->input->post('start_date');
			$end_date  = $this->input->post('end_date');

			$coupon_limit  = $this->input->post('coupon_limit');
			$status  = $this->input->post('status');


			$added_date	   = date('Y-m-d H:i:s');

			// validation
			if(empty($coupon_title))
			{
				$data = ERROR( 0, 'Please enter the coupon_title');
				$this->response($data);
			}

			if(empty($coupon_code))
			{
				$data = ERROR( 0, 'Please enter the coupon_code');
				$this->response($data);
			}

			if(empty($celebrity_id))
			{
				$data = ERROR( 0, 'Please enter the celebrity_id');
				$this->response($data);
			}

			if(empty($start_date))
			{
				$data = ERROR( 0, 'Please enter the start_date');
				$this->response($data);
			}

			if(empty($end_date))
			{
				$data = ERROR( 0, 'Please enter the end_date');
				$this->response($data);
			}

			if(empty($coupon_limit))
			{
				$data = ERROR( 0, 'Please enter the coupon_limit');
				$this->response($data);
			}

			if(empty($status))
			{
				$data = ERROR( 0, 'Please enter the coupon_limit');
				$this->response($status);
			}

			$coupon_data['vCouponTitle'] = $coupon_title;
			$coupon_data['vCouponCode']  = $coupon_code;
			$coupon_data['vDescription'] = $description;

			$coupon_data['iCelebrityId'] = $celebrity_id;
			$coupon_data['dStartDate'] 	 = $start_date;
			$coupon_data['dEndDate'] 	 = $end_date;
			$coupon_data['iCouponLimit'] = $coupon_limit;
			$coupon_data['eStatus'] 	 = $status;
			$coupon_data['dtAddedDate']  = $added_date;

			$result = $this->CouponModel->add_coupon($coupon_data);

			if($result)
			{
				$data = SUCCESS( 1, 'Coupon added successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function get_coupon_get()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$coupon_id = $this->input->get('coupon_id');

			$result = $this->CouponModel->get_coupon($coupon_id);
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Coupon details found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Coupon details not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function delete_coupon_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$coupon_id = $this->input->post('coupon_id');

			if(empty($coupon_id))
			{
				$data = ERROR( 0, 'Please enter the coupon_id');
				$this->response($data);
			}
			$result = $this->CouponModel->delete_coupon($coupon_id);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Coupon deleted successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function update_coupon_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$coupon_id     = $this->input->post('coupon_id');
			$coupon_title  = $this->input->post('coupon_title');
			$coupon_code   = $this->input->post('coupon_code');
			$description   = $this->input->post('description');
			$celebrity_id  = $this->input->post('celebrity_id');
			$start_date    = $this->input->post('start_date');
			$end_date      = $this->input->post('end_date');
			$coupon_limit  = $this->input->post('coupon_limit');
			$status        = $this->input->post('status');
			$updated_date  = date('Y-m-d H:i:s');

			// validation
			if(empty($coupon_id))
			{
				$data = ERROR( 0, 'Please enter the coupon_id');
				$this->response($data);
			}

			if(empty($coupon_title))
			{
				$data = ERROR( 0, 'Please enter the coupon_title');
				$this->response($data);
			}

			if(empty($coupon_code))
			{
				$data = ERROR( 0, 'Please enter the coupon_code');
				$this->response($data);
			}

			if(empty($celebrity_id))
			{
				$data = ERROR( 0, 'Please enter the celebrity_id');
				$this->response($data);
			}

			if(empty($start_date))
			{
				$data = ERROR( 0, 'Please enter the start_date');
				$this->response($data);
			}

			if(empty($end_date))
			{
				$data = ERROR( 0, 'Please enter the end_date');
				$this->response($data);
			}

			if(empty($coupon_limit))
			{
				$data = ERROR( 0, 'Please enter the coupon_limit');
				$this->response($data);
			}

			if(empty($status))
			{
				$data = ERROR( 0, 'Please enter the coupon_limit');
				$this->response($status);
			}

			$coupon_data['vCouponTitle'] = $coupon_title;
			$coupon_data['vCouponCode']  = $coupon_code;
			$coupon_data['vDescription'] = $description;

			$coupon_data['iCelebrityId'] = $celebrity_id;
			$coupon_data['dStartDate'] 	 = $start_date;
			$coupon_data['dEndDate'] 	 = $end_date;
			$coupon_data['iCouponLimit'] = $coupon_limit;
			$coupon_data['eStatus'] 	 = $status;
			$coupon_data['dtUpdatedDate']= $updated_date;

			$result = $this->CouponModel->update_coupon($coupon_id,$coupon_data);
			
			if($result)
			{
				$data = SUCCESS( 1, 'Coupon updated successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function add_music_creator_by_admin_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$first_name 	   = $this->input->post('first_name');
			$last_name 		   = $this->input->post('last_name');
			$email             = $this->input->post('email');
			$phone             = $this->input->post('phone');
			$country		   = $this->input->post('country');
			

			if(empty($first_name))
			{
				$data = ERROR( 0, 'Please enter the first_name');
				$this->response($data);
			}
			if(empty($last_name))
			{
				$data = ERROR( 0, 'Please enter the last_name');
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

			$is_exist = $this->UserModel->email_exist($email);

			if(!empty($is_exist)){
				$data = ERROR( 0, 'User already exist this email');
				$this->response($data);
			}
			if(empty($phone))
			{
				$data = ERROR( 0, 'Please enter the phone');
				$this->response($data);
			}
			if(empty($country))
			{
				$data = ERROR( 0, 'Please enter the country');
				$this->response($data);
			}
			
			
			// Profile picture
			$data = [];  
	  		$config['upload_path'] 		= './public/uploads/profile';
			$config['allowed_types'] 	= 'jpeg|gif|jpg|png|mp3|mpeg|mpg|mpeg3';
			
			$imgData = [];
			$errors = [];
			$files = $_FILES;
			$upload_count = count($_FILES['profile_picture']['name']);

			for( $i = 0; $i < $upload_count; $i++ )
			{
				$imgData[] = str_replace(' ', '_', $files['profile_picture']['name'][$i]).'_'.time();

			    $_FILES['profile_picture'] = [
			        'name'     => $files['profile_picture']['name'][$i],
			        'type'     => $files['profile_picture']['type'][$i],
			        'tmp_name' => $files['profile_picture']['tmp_name'][$i],
			        'error'    => $files['profile_picture']['error'][$i],
			        'size'     => $files['profile_picture']['size'][$i]
			    ];
			    
			   	if (!empty($files["profile_picture"]["name"]))
            	{
	                $file_path = "profile_image";
	                $file_name = str_replace(' ', '_', $files["profile_picture"]["name"][$i]).'_'.time();
	                $file_tmp_path = $_FILES["profile_picture"]["tmp_name"];
	                // print_r($file_tmp_path);die;
	                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
	                if (!$response)
	                {
	                    //file upload failed

	                }
	            }
			}
			$user_data['vFirstName']= $first_name;
			$user_data['vLastName'] = $last_name;
			$user_data['vEmail'] 	= $email;
			$user_data['vPhone'] 	= $phone;
			$user_data['vPhone'] 	= $phone;
			$user_data['iRoleId'] 	= '2';
			$user_data['eRegistrationType'] = 'Other';
			$user_data['vImage'] 		= json_encode($imgData);
			$last_id = $this->UserModel->register_user($user_data);

			$artist_name       = $this->input->post('artist_name');
			$categories        = $this->input->post('categories');
			$social_media_links= $this->input->post('social_media_links');
			$description       = $this->input->post('description');

			if(empty($artist_name))
			{
				$data = ERROR( 0, 'Please enter the artist_name');
				$this->response($data);
			}

			if(empty($categories))
			{
				$data = ERROR( 0, 'Please select the categories');
				$this->response($data);
			}

			if(empty($social_media_links))
			{
				$data = ERROR( 0, 'Please enter the social_media_links');
				$this->response($data);
			}

			if(empty($description))
			{
				$data = ERROR( 0, 'Please enter the description');
				$this->response($data);
			}
			
			$music_creator_data['vArtistName']       = $artist_name;
			$music_creator_data['iUsersId']          = $last_id;
			$music_creator_data['vCategories']       = $categories;
			$music_creator_data['vSocialMediaLinks'] = $social_media_links;
			$music_creator_data['vDescription'] 	 = $description;
			#$music_creator_data['vUploadMusic']		 = str_replace(' ', '_', $files["music"]["name"]).'_'.time();
			$music_creator_data['dtAddedDate']       = date('Y-m-d H:i:s');
			$result = $this->MusicCreatorModel->add_artist($music_creator_data);
			#print_r($result);die;
			// music
			$data1 = [];  
	  		$config1['upload_path'] 		= './public/uploads/music';
			$config1['allowed_types'] 	= 'mp3|mpeg|mpg|mpeg3';
			
			$errors1 = [];
			$files1 = $_FILES;

			if (!empty($files["music"]["name"]))
        	{
                $file_path = "music";
                $file_name = str_replace(' ', '_', $files["music"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["music"]["tmp_name"];
                // print_r($file_tmp_path);die;
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
                if (!$response)
                {
                    //file upload failed

                }
            }
			$music_creator_data1['dtAddedDate'] 	= date('Y-m-d H:i:s');
			$music_creator_data1['vMusic']		= str_replace(' ', '_', $files["music"]["name"]).'_'.time();
			$music_creator_data1['iCreatorId']	= $result;

			$result1 = $this->MusicCreatorModel->upload_music($music_creator_data1);
			#print_r($result1);die;
			if($result)
			{
				$data = SUCCESS( 1, 'Music Creator added successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function add_to_cart_post()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);
			$user_id 		= $this->input->post('user_id');
			$coupon_code    = $this->input->post('coupon_code');
			$cart_items    	= $this->input->post('cart_items');
			
			// $prod_id = $this->input->post('prod_id');
			// $qty 	 = $this->input->post('qty');
			// $price   = $this->input->post('price');
			// $name    = $this->input->post('name');
			
			if(empty($user_id))
			{
				$data = ERROR( 0, 'Please enter the user_id');
				$this->response($data);
			}

			
			$cart['iUsersId']    = $user_id;
			$cart['vCuponCode']  = $coupon_code;
			
			$res = $this->CartModel->check_cart($user_id);

			if(!empty($res))
			{
				$this->CartModel->update_cart($cart,$user_id);
				$last_id = $this->CartModel->get_cart_id($user_id);
			}else{
				$cart['dtAddedDate'] = date('Y-m-d H:i:s');
				$last_id = $this->CartModel->add_to_cart($cart);	
			}
			
			if(!empty($last_id))
			{
				$cart_data = json_decode($cart_items,true);
				
				$cart_items_arr = [];
				foreach ($cart_data as $key => $value) 
				{
					$is_cart_data_exist = $this->CartModel->is_cart_data_exist($last_id,$value['prod_id']);
					if(!$is_cart_data_exist)
					{
						$cart_items_arr[$key]['iCartId']    = $last_id;
						$cart_items_arr[$key]['iProductId'] = $value['prod_id'];
						$cart_items_arr[$key]['iQty'] 	    = $value['qty'];
						$cart_items_arr[$key]['dPrice']     = $value['price'];
						$cart_items_arr[$key]['vName']      = $value['name'];
						$cart_items_arr[$key]['dtAddedDate']= date('Y-m-d H:i:s');	
					}
					else
					{
						$data = ERROR( 0, 'Item(s) already exist in cart.');
						$this->response($data);
					}
				}

				$res = $this->CartModel->add_to_cart_items($cart_items_arr);	
				if(!empty($res))
				{	
					$result = $this->CartModel->get_cart_items($last_id);
					if(!empty($result))
					{
						$sub_total = 0;
						foreach ($result as $key => $value) {
							$sub_total += $value['dPrice'];
						}
						$cart_data['dSubTotal'] = $sub_total;
						$cart_data['dTotal']    = $sub_total;
						$this->db->where('iCartId ', $last_id);
						$this->db->update('cart', $cart_data);
					}
					$data = SUCCESS( 1, 'Item(s) added to cart successfully.',[]);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0, 'Something went wrong...please try again.');
					$this->response($data);
				}
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
			
			
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function delete_from_cart_post()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);
			$cart_item_id = $this->input->post('cart_item_id');
			
			$result = $this->CartModel->delete_form_cart($cart_item_id);
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Item deleted form cart successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}

		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}	
	}

	public function get_cart_get()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);
			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$user_id = $this->input->get('user_id');
			$result = $this->CartModel->get_cart_details($user_id);
			if(!empty($result))
			{
				$result[0]['cart_items'] = $this->CartModel->get_cart_items_details($result[0]['cart_id']);
				for ($i=0; $i < count($result[0]['cart_items']) ; $i++) 
				{
					if(!empty($result[0]['cart_items'][$i]['images']))
					{
						$images = json_decode($result[0]['cart_items'][$i]['images']);

						$img1 = [];
						if(!empty($images))
						{
							foreach($images as $val)
							{
								//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
								$img1[] = $this->general->getImageUrl('profile_image', $val);					
							}
						}
						$result[0]['cart_items'][$i]['images'] = $img1;
					}
				}
				$data = SUCCESS( 1, 'Cart Items fetched successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Cart data not found.');
				$this->response($data);
			}

				

		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function update_celebrity_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$celebrity_id 	   = $this->input->post('celebrity_id');
			$first_name 	   = $this->input->post('first_name');
			$last_name         = $this->input->post('last_name');
			$email             = $this->input->post('email');
			$phone             = $this->input->post('phone');
			$role_id           = '3';
			$registration_type = 'Other';

			$social_media_links = $this->input->post('social_media_links');
			

			$title 			   = $this->input->post('title');
			$tag_line 		   = $this->input->post('tag_line');
			$short_description = $this->input->post('short_description');
			$long_description  = $this->input->post('long_description');
			$categories 	   = $this->input->post('categories');
			$price 			   = $this->input->post('price');
			$is_featured 	   = $this->input->post('is_featured');
			$added_date        = date('Y-m-d H:i:s');
			$country           = $this->input->post('country');

			$account_name 	   = $this->input->post('account_name');
			$account_number    = $this->input->post('account_number');
			$bank_name 	       = $this->input->post('bank_name');
			$bank_code 	       = $this->input->post('bank_code');
			$bank_address 	   = $this->input->post('bank_address');
			$paypal_id 	       = $this->input->post('paypal_id');


			// validation
			if(empty($celebrity_id)){
				$data = ERROR( 0, 'Please enter the celebrity_id');
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

			/*$is_exist = $this->UserModel->email_exist($email,$id);

			if(!empty($is_exist)){
				$data = ERROR( 0, 'User already exist this email');
				$this->response($data);
			}*/

			if(empty($title))
			{
				$data = ERROR( 0, 'Please enter the title');
				$this->response($data);
			}

			if(empty($tag_line))
			{
				$data = ERROR( 0, 'Please enter the tag_line');
				$this->response($data);
			}

			if(empty($short_description))
			{
				$data = ERROR( 0, 'Please enter short_description');
				$this->response($data);
			}

			if(empty($long_description))
			{
				$data = ERROR( 0, 'Please enter the long_description');
				$this->response($data);
			}

			if(empty($categories))
			{
				$data = ERROR( 0, 'Please enter the categories');
				$this->response($data);
			}

			if(empty($price))
			{
				$data = ERROR( 0, 'Please enter the price');
				$this->response($data);
			}

			/*if(empty($is_featured))
			{
				$data = ERROR( 0, 'Please enter the is_featured');
				$this->response($data);
			}*/

			/*if(empty($account_name))
			{
				$data = ERROR( 0, 'Please enter account_name');
				$this->response($data);
			}

			if(empty($account_number))
			{
				$data = ERROR( 0, 'Please enter the account_number');
				$this->response($data);
			}

			if(empty($bank_name))
			{
				$data = ERROR( 0, 'Please enter the bank_name');
				$this->response($data);
			}

			if(empty($bank_code))
			{
				$data = ERROR( 0, 'Please enter the bank_code');
				$this->response($data);
			}

			if(empty($bank_address))
			{
				$data = ERROR( 0, 'Please enter the bank_address');
				$this->response($data);
			}*/

			$data = [];
			
			$imgData = [];
			$errors = [];
			$files = $_FILES;
			$upload_count = count($_FILES['profile_picture']['name']);

			for( $i = 0; $i < $upload_count; $i++ )
			{
				$imgData[] = str_replace(' ', '_', $files['profile_picture']['name'][$i]).'_'.time();

			    $_FILES['profile_picture'] = [
			        'name'     => $files['profile_picture']['name'][$i],
			        'type'     => $files['profile_picture']['type'][$i],
			        'tmp_name' => $files['profile_picture']['tmp_name'][$i],
			        'error'    => $files['profile_picture']['error'][$i],
			        'size'     => $files['profile_picture']['size'][$i]
			    ];
			    
			   	if (!empty($files["profile_picture"]["name"]))
            	{
	                $file_path = "profile_image";
	                $file_name = str_replace(' ', '_', $files["profile_picture"]["name"][$i]).'_'.time();
	                $file_tmp_path = $_FILES["profile_picture"]["tmp_name"];
	                
	                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
	                if (!$response)
	                {
	                    //file upload failed
	                }
	            }
			}
			
			$user_data['vFirstName']= $first_name;
			$user_data['vLastName'] = $last_name;
			$user_data['vEmail'] 	= $email;
			$user_data['vPhone'] 	= $phone;
			$user_data['iRoleId'] 	= $role_id;
			$user_data['vCountry'] 	= $country;
			$user_data['vImage'] 	= json_encode($imgData);
			$user_data['dtUpdatedDate'] = date('Y-m-d H:i:s');
			$user_data['eRegistrationType'] = $registration_type;

			$last_id = $this->UserModel->update_user($celebrity_id,$user_data);

			if (!empty($_FILES["w9_form"]["name"]))
        	{
                $file_path = "w9_form";
                $file_name = str_replace(' ', '_', $_FILES["w9_form"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["w9_form"]["tmp_name"];
                // print_r($file_tmp_path);die;
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
                if (!$response)
                {
                    //file upload failed

                }
            }

			$celebrity_data['iUsersId'] 			= $last_id;
			$celebrity_data['vTitle'] 				= $title;
			$celebrity_data['vTagLine'] 			= $tag_line;
			$celebrity_data['vShortDescription'] 	= $short_description;
			$celebrity_data['vLongDescription'] 	= $long_description;
			$celebrity_data['vSocialMediaLinks'] = $social_media_links;
			$category_arr = explode(",",$categories);
			
			if(in_array(7, $category_arr))
			{
				$other_category = $this->input->post('other_category');
				$category_data['vCategoryName']   = $other_category;
				$category_data['vSlug'] 	  	  = strtolower($other_category);
				$category_data['vCategoryParent'] = '7';
				$category_data['dtUpdatedDate']  	  = date('Y-m-d H:i:s');

				$result = $this->CategoryModel->add_category($category_data);
				$category_arr[] = $result;
				$categories = implode(",",$category_arr);
			}

			$celebrity_data['vCategories'] 			= $categories;

			$celebrity_data['dPrice'] 				= $price;
			$celebrity_data['eIsFeatured'] 			= $is_featured;
			$celebrity_data['dtAddedDate'] 			= $added_date;

			$celebrity_data['vAccountName'] 		= $account_name;
			$celebrity_data['vAccountNumber'] 		= $account_number;
			$celebrity_data['vBankName'] 			= $bank_name;
			$celebrity_data['vBankCode'] 			= $bank_code;
			$celebrity_data['vBankAddress'] 		= $bank_address;
			$celebrity_data['vPaypalId'] 		    = $paypal_id;
			$celebrity_data['vW9Form'] 		        = str_replace(' ', '_', $_FILES["w9_form"]["name"]).'_'.time();
			$celebrity_data['vSocialMediaLinks']    = json_encode($social_media_links);

			$result = $this->CelebrityModel->update_celebrity($celebrity_data);

			if(!empty($result))
			{
				$data = SUCCESS(1, 'Celebrity updated successfully.',[]);
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

	public function add_to_wishlist_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$user_id 	= $this->input->post('user_id');
			$product_id = $this->input->post('product_id');

			if(empty($user_id))
			{
				$data = ERROR( 0, 'Please enter the user_id');
				$this->response($data);
			}

			if(empty($product_id))
			{
				$data = ERROR( 0, 'Please enter the product_id');
				$this->response($data);
			}

			$check_wishlist = $this->WishlistModel->check_wishlist($user_id,$product_id);
			if(empty($check_wishlist))
			{
				$user_data['IUsersId'] 	= $user_id;
				$user_data['iProductId'] 	= $product_id;
				$user_data['dtAddedDate'] = date('Y-m-d H:i:s');
				$res_wishlist = $this->WishlistModel->insert_wishlist($user_data);
				if($res_wishlist)
				{
					$data = SUCCESS( 1, 'Item added to wishlist successfully.',[]);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0, 'Something went wrong...please try again.');
					$this->response($data);
				}
			}
			else
			{
				$data = ERROR( 0, 'Item already in wishlist.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function delete_from_wishlist_post()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$wishlist_id = $this->input->post('wishlist_id');
			
			$result = $this->WishlistModel->delete_form_wishlist($wishlist_id);
			
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Item deleted form wishlist successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}

		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}	
	}

	public function get_wishlist_get()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$AWS_BUCKET_NAME = $this->general->get_setting('AWS_BUCKET_NAME');
			$AWS_END_POINT   = $this->general->get_setting('AWS_END_POINT');
			$user_id = $this->input->get('user_id');

			$result = $this->WishlistModel->get_wishlist($user_id);
			
			for ($i=0; $i < count($result) ; $i++) 
			{
				if(!empty($result[$i]['images']))
				{
					$images = json_decode($result[$i]['images']);

					$img1 = [];
					if(!empty($images))
					{
						foreach($images as $val)
						{
							//$img1[] = "https://".$AWS_BUCKET_NAME.".s3.".$AWS_END_POINT.".amazonaws.com/profile_image/".$val;
							$img1[] = $this->general->getImageUrl('profile_image', $val);				
						}
					}
					$result[$i]['images'] = $img1;
				}
			}
			if(!empty($result))
			{
				$data = SUCCESS( 1, 'Wishlist found successfully.',$result);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}

		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}	
	}

	public function upload_test_image_post()
	{
		$file = $_FILES['files']['name'];
		$temp_name = $_FILES['files']['tmp_name'];

		$response = $this->general->uploadAWSData($temp_name, 'music', $file);

		echo $response;
		exit;
	}

	public function get_image_get()
	{
		$file = 'vision-exams.jpg';
		$response = $this->general->getImageUrl('profile_image', $file);

		echo $response;
		exit;
	}

	public function upload_music_from_dashboard_post()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$music_creator_id = $this->input->post('music_creator_id');
			$category_id      = $this->input->post('category_id');
			$music_name       = $this->input->post('music_name');
			if(empty($music_creator_id))
			{
				$data = ERROR( 0, 'Please enter the music_creator_id');
				$this->response($data);
			}

			// Music Upload
			$data = [];  
      		$config['upload_path'] 		= './public/uploads/music';
			$config['allowed_types'] 	= 'mp3|mpeg|mpg|mpeg3';
			
			$errors = [];
			$files = $_FILES;				
			
			if (!empty($files["music"]["name"]))
        	{
                $file_path = "music";
                $file_name = str_replace(' ', '_', $files["music"]["name"]).'_'.time();
                $file_tmp_path = $_FILES["music"]["tmp_name"];
                // print_r($file_tmp_path);die;
                $response = $this->general->uploadAWSData($file_tmp_path, $file_path, $file_name);
                if (!$response)
                {
                    //file upload failed

                }
            }
			
			$music_creator_data['dtAddedDate'] 	= date('Y-m-d H:i:s');
			$music_creator_data['vMusic']		= str_replace(' ', '_', $files["music"]["name"]).'_'.time();
			$music_creator_data['iCreatorId']	= $music_creator_id;
			$music_creator_data['iCategoryId']	= $category_id;
			$music_creator_data['vMusicName']	= $music_name;
			$result = $this->MusicCreatorModel->upload_music($music_creator_data);
			
			if(!empty($result))
			{
				$data = SUCCESS(1, 'Music uploaded successfully.',[]);
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

	public function checkout_post()
	{
		try
		{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);

			$billing_first_name 	= $this->input->post('billing_first_name');
			if(empty($billing_first_name))
			{
				$data = ERROR( 0, 'Please enter the billing_first_name');
				$this->response($data);
			}
			$billing_last_name 		= $this->input->post('billing_last_name');
			if(empty($billing_last_name))
			{
				$data = ERROR( 0, 'Please enter the billing_last_name');
				$this->response($data);
			}
			$billing_email			= $this->input->post('billing_email');	
			if(empty($billing_email))
			{
				$data = ERROR( 0, 'Please enter the billing_email');
				$this->response($data);
			}					
			$billing_phone			= $this->input->post('billing_phone');
			if(empty($billing_phone))
			{
				$data = ERROR( 0, 'Please enter the billing_phone');
				$this->response($data);
			}
			$billing_address_line1	= $this->input->post('billing_address_line1');
			if(empty($billing_address_line1))
			{
				$data = ERROR( 0, 'Please enter the billing_address_line1');
				$this->response($data);
			}
			$billing_address_line2	= $this->input->post('billing_address_line2');
			if(empty($billing_address_line2))
			{
				$data = ERROR( 0, 'Please enter the billing_address_line2');
				$this->response($data);
			}
			$billing_city			= $this->input->post('billing_city');
			if(empty($billing_city))
			{
				$data = ERROR( 0, 'Please enter the billing_city');
				$this->response($data);
			}
			$billing_state			= $this->input->post('billing_state');
			if(empty($billing_state))
			{
				$data = ERROR( 0, 'Please enter the billing_state');
				$this->response($data);
			}
			$billing_zip			= $this->input->post('billing_zip');
			if(empty($billing_zip))
			{
				$data = ERROR( 0, 'Please enter the billing_zip');
				$this->response($data);
			}
			$billing_country		= $this->input->post('billing_country');
			if(empty($billing_country))
			{
				$data = ERROR( 0, 'Please enter the billing_country');
				$this->response($data);
			}
			$order_sub_total		= $this->input->post('order_sub_total');
			if(empty($order_sub_total))
			{
				$data = ERROR( 0, 'Please enter the order_sub_total');
				$this->response($data);
			}
			$oder_tax				= $this->input->post('oder_tax');
			$order_coupon			= $this->input->post('order_coupon');
			$order_discount			= $this->input->post('order_discount');
			$order_total 			= $this->input->post('order_total');
			if(empty($order_total))
			{
				$data = ERROR( 0, 'Please enter the order_total');
				$this->response($data);
			}
			$user_id 				= $this->input->post('user_id');
			if(empty($user_id))
			{
				$data = ERROR( 0, 'Please enter the user_id');
				$this->response($data);
			}
			$order_items 			= $this->input->post('order_items');
			$music_upload_key       = $this->input->post('music_upload_key');
			if(empty($music_upload_key))
			{
				$data = ERROR( 0, 'Please enter the music_upload_key');
				$this->response($data);
			}

			$order['vBillingFirstName'] 	= $billing_first_name;
			$order['vBillingLastName'] 		= $billing_last_name;
			$order['vBillingEmail'] 		= $billing_email;
			$order['vBillingPhone'] 		= $billing_phone;
			$order['vBillingAddressLine1'] 	= $billing_address_line1;
			$order['vBillingAddressLine2'] 	= $billing_address_line2;
			$order['vBillingCity'] 			= $billing_city;
			$order['vBillingState'] 		= $billing_state;
			$order['vBillingZip'] 			= $billing_zip;
			$order['vBillingCountry'] 		= $billing_country;
			$order['eOrderSubTotal'] 		= $order_sub_total;
			$order['eOrderTax'] 			= $oder_tax;
			$order['eOrderCoupon'] 			= $order_coupon;
			$order['eOrderDiscount'] 		= $order_discount;
			$order['eOrderTotal'] 			= $order_total;
			$order['vOrderPaymentTransactionId'] = '';
			$order['vPaymentData'] 			= '';
			$order['eMusicCreatorId'] 		= $user_id;
			$order['eOrderStatus'] 			= 'Pending';
			$order['dtAddedDate'] 			= date('Y-m-d H:i:s');
			$result = $this->CartModel->add_order($order);
			if(!empty($result))
			{
				$order_item = json_decode($order_items,true);

				foreach ($order_item as $key => $value) 
				{
					$order_items_arr[$key]['iOrderId']    		= $result;
					$order_items_arr[$key]['iMusicCreatorId'] 	= $user_id;
					$order_items_arr[$key]['iCelebrityId'] 	    = $value['prod_id'];
					$order_items_arr[$key]['vItemPrice']     	= $value['price'];
					$order_items_arr[$key]['iMusicUploadKey']   = $music_upload_key;
					$order_items_arr[$key]['eItemReviewStatus']	= 'In Progress';		
					$order_items_arr[$key]['eCelebrityPaymentStatus'] = 'Pending';		
					$order_items_arr[$key]['dtAddedDate'] 			        = date('Y-m-d H:i:s');
				}
				
				$res = $this->CartModel->add_order_items($order_items_arr);
				if(!empty($res))
				{
					$data = SUCCESS( 1, 'Cart checkout successfully.',[]);
					$this->response($data);
				}
				else
				{
					$data = ERROR( 0, 'Something went wrong...please try again.');
					$this->response($data);
				}

			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}	
	}

	public function update_order_post()
	{
		try
		{
			$order_id       = $this->input->post('order_id');
			$payment_status = $this->input->post('payment_status');
			$transaction_id = $this->input->post('transaction_id');
			$payment_data   = $this->input->post('payment_data');
			if(empty($order_id))
			{
				$data = ERROR( 0, 'Please enter the order_id');
				$this->response($data);
			}
			if(empty($payment_status))
			{
				$data = ERROR( 0, 'Please enter the payment_status');
				$this->response($data);
			}
			if(empty($transaction_id))
			{
				$data = ERROR( 0, 'Please enter the transaction_id');
				$this->response($data);
			}
			if(empty($payment_data))
			{
				$data = ERROR( 0, 'Please enter the payment_data');
				$this->response($data);
			}

			$order1['eOrderStatus'] 			  = $payment_status;
			$order1['vPaymentData'] 			  = $payment_data;
			$order1['vOrderPaymentTransactionId'] = $transaction_id;

			$res = $this->CartModel->update_order_status($order_id,$order1);
			if(!empty($res))
			{
				$data = SUCCESS( 1, 'Order updated successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}	
	}
}