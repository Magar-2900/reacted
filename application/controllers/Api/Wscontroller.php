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
		$this->load->model('WsModels');
	}

	public function user_get()
	{
		$result = $this->WsModels->getAllRecords();		
		if(!empty($result))
		{
			$ret_arr = SUCCESS(1,'Data found successfully.',$result,'','','');
			$this->response($ret_arr); 
		}
		else
		{
			$ret_arr = ERROR(0,'Data not found.');
			$this->response($ret_arr,404); 
		}
	}

	public function register_user_post()
	{
		$data['vName'] = $this->input->post('name');
		$data['vEmail'] = $this->input->post('email');
		$data['vPassword'] = password_hash($this->input->post('password'),PASSWORD_DEFAULT);

		$is_exist = $this->WsModels->isExist($data['vEmail']);
		if(empty($is_exist))
		{
			$result = $this->WsModels->insertRecord($data);
			if(!empty($result))
			{
				$ret_arr = SUCCESS(1,'User registered successfully.',$data,'','','');
				$this->response($ret_arr); 
			}
			else
			{
				$ret_arr = ERROR(0,'User not registered.');
				$this->response($ret_arr); 
			}
		}
		else
		{
			$ret_arr = ERROR(0,'User already exist with this email.');
			$this->response($ret_arr); 
		}
	}
	
	public function login_post()
	{

		$data['vEmail'] = $this->input->post('email');
		$data['vPassword'] = $this->input->post('password');

		$is_match = $this->WsModels->loginAction($data);
		$record = json_decode(json_encode($is_match), true);

		if(!empty($is_match))
		{
			if (password_verify($data['vPassword'], $record[0]['password'])) 
			{
				$token['user_id'] = $record[0]['user_id'];
				$token['name'] = $record[0]['name'];
				$token['email'] = $record[0]['email'];
				$enc_token = $this->authorization_token->generateToken($token);
				$this->WsModels->updateToken($enc_token,$token['user_id']);
				$user_details = $this->WsModels->getRecord($token['user_id']);
				$ret_arr = SUCCESS(1,'You have logged in successfully.',$user_details,'','','');
			} 
			else 
			{
			    $ret_arr = ERROR(0,'Please enter valid password.');
			}
			$this->response($ret_arr); 
		}
		else
		{
			$ret_arr = ERROR(0,'User not found with this email.');
			$this->response($ret_arr); 
		}
	}

	public function verify_post()
	{  
		$headers = $this->input->request_headers(); 
		$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);

		$this->response($decodedToken);  
	}

	public function my_profile_get()
	{  
		$headers = $this->input->request_headers(); 
		if(array_key_exists('Authorization', $headers))
		{
			$token = validateAccessToken($headers);

			if($token['status'] == 1)
			{
				$record = json_decode(json_encode($token['data']), true);
				$user_details = $this->WsModels->getRecord($record['user_id']);
				$ret_arr = SUCCESS(1,'Details found successfully.',$user_details,'','','');
			}
			else
			{
				$ret_arr = ERROR(-1,$token['message']);
			}
		}
		else
		{
			$ret_arr = ERROR(-1,'Token Not found.');
		}
		
		$this->response($ret_arr);
	}
}
