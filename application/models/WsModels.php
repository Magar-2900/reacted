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

	public function get_all_orders()
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

	public function get_order_details_admin($order_id){
		/*$this->db->select('order_items.iOrderId as order_id,order_items.iMusicCreatorId as music_creator_id,order_items.iCelebrityId as celebrity_id,order_items.iMusicUploadKey as music_key,order_items.vItemPrice as item_price,order_items.eItemReviewStatus as item_review_status,order_items.eCelebrityPaymentStatus as celebrity_payment_status,order_items.dtAddedDate as added_date,order_items.dtUpdatedDate as updated_date,order_items.dtExpiryDate as expiry_date, users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,users.vAccessToken as access_token,users.vImage as image,users.eStatus as status,user_music_creator.vArtistName as artist_name,user_music_creator.vDescription as description,user_music_creator.vSocialMediaLinks social_media_links,user_music_creator.vUploadMusic as upload_music,u1.vFirstName as celebrity_first_name,u1.vLastName as celebrity_last_name,u1.vImage as celebrity_image, mu.vMusic as music_name');
  		$this->db->from('order_items');
  		$this->db->join('users','users.iUsersId = order_items.iMusicCreatorId','left');
  		$this->db->join('users u1','u1.iUsersId = order_items.iCelebrityId','left');
		$this->db->join('music_uploads mu', 'order_items.iMusicUploadKey = mu.iMusicUploadId');
  		$this->db->join('user_music_creator','user_music_creator.iUsersId = users.iUsersId','left');
  		$this->db->where('order_items.iOrderItemId',$order_id);
		$this->db->order_by("order_items.iOrderItemId", "desc");*/


		$this->db->select('orders.*');
		$this->db->from('orders');
		$this->db->where('orders.iOrderId',$order_id);
		$order_details = $this->db->get();

		$this->db->select('order_items.*');
		$this->db->from('order_items');
		$this->db->where('order_items.iOrderItemId',$order_id);
		$order_items = $this->db->get();
		
		$result_arr = array(
			'order_details' => $order_details->result_array(),
			'order_items' => $order_items->result_array()
		);

  		//$query_obj = $this->db->get();
		//$result = is_object($result_arr) ? $result_arr->result_array() : array();
		return $result_arr;
	}

	public function update_order_item_review_status($status, $order_item_id){
		$data = array(
			'eItemReviewStatus' => $status,
		);
		$this->db->where('iOrderItemId', $order_item_id);
		$result = $this->db->update('order_items', $data);
		return $result;
	}
	
}