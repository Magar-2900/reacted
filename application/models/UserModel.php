<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function email_exist($email)
  	{
  		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('vEmail',$email);
		$query_obj = $this->db->get();  
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}

  	public function register_user($data)
  	{
  		$this->db->insert('users',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function login_action($data)
    {
		$this->db->select('iUsersId as user_id,vName as name,vEmail as email,vPassword as password');
		$this->db->from('users');
		$this->db->where('vEmail',$data['vEmail']);
		$query_obj = $this->db->get();  
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
    }

	public function update_token($token, $user_id)
	{
		$data['vAccessToken'] = $token;
		$this->db->where('iUsersId', $user_id);
		$result = $this->db->update('users', $data);
		return $result;
	}

  	public function get_user($user_id = '')
	{
		$this->db->select('users.iUsersId as user_id,users.vName as name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role_id,users.vAccessToken as access_token');
		$this->db->from('users');
		$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
		if(!empty($user_id))
		{
			$this->db->where('users.iUsersId',$user_id);	
		}
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}

	public function update_user($user_id, $data)
	{
		$this->db->where('iUsersId', $user_id);
		$result = $this->db->update('users', $data);
		return $result;
	}

	public function user_roles()
	{
		$this->db->select('user_roles.iRoleId as role_id,user_roles.vRole as role_name');
		$this->db->from('user_roles');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}
}
