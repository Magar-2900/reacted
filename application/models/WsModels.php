<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WsModels extends CI_Model {
  
	public function getAllRecords()
	{    
		$this->db->select('iUsersId as user_id,vName as name,vEmail as email,vAccessToken as access_token');
		$this->db->from('users');
		$query = $this->db->get()->result();  
		return $query;
	}

	public function getRecord($user_id)
	{    
		$this->db->select('iUsersId as user_id,vName as name,vEmail as email,vAccessToken as access_token');
		$this->db->from('users');
		$this->db->where('iUsersId',$user_id);
		$query = $this->db->get()->result();  
		return $query;
	}

	public function insertRecord($data)
	{
		$result = $this->db->insert('users',$data);
		return $result;
	}

	public function isExist($email)
	{
		$this->db->select('iUsersId as user_id,vName as name,vEmail as email,vAccessToken as access_token');
		$this->db->from('users');
		$this->db->where('vEmail',$email);
		$query = $this->db->get()->result();  
		return $query;
	}
	
	public function loginAction($data)
	{
		$this->db->select('iUsersId as user_id,vName as name,vEmail as email,vAccessToken as access_token,vPassword as password');
		$this->db->from('users');
		$this->db->where('vEmail',$data['vEmail']);
		$query = $this->db->get()->result();  
		return $query;
	}

	public function updateToken($token,$user_id)
	{
		$data['vAccessToken'] = $token;
		$this->db->where('iUsersId', $user_id);
		$result = $this->db->update('users', $data);
		return $result;
	}
}
