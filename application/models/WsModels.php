<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WsModels extends CI_Model {
  
	public function getAllRecords()
	{    
		$this->db->select('iUsersId as user_id,vFirstName as first_name,vLastName as last_name,vEmail as email,vAccessToken as access_token');
		$this->db->from('users');
		$query = $this->db->get()->result();  
		return $query;
	}

	public function getRecord($user_id)
	{    
		$this->db->select('iUsersId as user_id,vFirstName as first_name,vLastName as last_name,vEmail as email,vAccessToken as access_token');
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
		$this->db->select('iUsersId as user_id,vFirstName as first_name,vLastName as last_name,vEmail as email,vAccessToken as access_token');
		$this->db->from('users');
		$this->db->where('vEmail',$email);
		$query = $this->db->get()->result();  
		return $query;
	}
	
	public function loginAction($data)
	{
		$this->db->select('iUsersId as user_id,vFirstName as first_name,vLastName as last_name,vEmail as email,vAccessToken as access_token,vPassword as password');
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

	public function get_all_orders(){
	{
		$this->db->select('or.*, oi.iMusicUploadKey, mu.vMusicName');
  		$this->db->from('orders as or');
		$this->db->join('order_items as oi', 'or.iOrderId = oi.iOrderId');
		$this->db->join('music_uploads as mu', 'oi.iMusicUploadKey = mu.iMusicUploadId');
		$this->db->order_by("or.iOrderId", "desc");
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		//print_r($this->db->last_query());die;
		return $result;
	}
	
}