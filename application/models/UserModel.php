<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function email_exist($email,$id='')
  	{
  		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('vEmail',$email);
		if(!empty($id))
		{
			$this->db->where('iUsersId',$id);
		}
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
		$this->db->select('iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,vEmail as email,vPassword as password, iRoleId as role_id');
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
		$this->db->select('users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role_id,users.vAccessToken as access_token,users.iRoleId as role_id1, vAddressLine1 as address_line_1, vAddressLine2 as address_line_2, vCity as user_city, vState as user_state, vCountry as user_country, vZipCode as user_zip_code');
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

	public function check_password($user_id)
    {
		$this->db->select('iUsersId as user_id,vFirstName as first_name,vLastName as last_name,vEmail as email,vPassword as password');
		$this->db->from('users');
		$this->db->where('iUsersId',$user_id);
		$query_obj = $this->db->get();  
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
    }

    public function update_user_otp($email, $data)
	{
		$this->db->where('vEmail', $email);
		$result = $this->db->update('users', $data);
		return $result;
	}

	public function check_security_code($email,$security_code)
	{
		$this->db->select('iUsersId as user_id,vFirstName as first_name,vLastName as last_name,vEmail as email,vPassword as password');
		$this->db->from('users');
		$this->db->where('vEmail',$email);
		$this->db->where('iEmailVerifyOtp',$security_code);
		$query_obj = $this->db->get();  
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}

	public function update_password($email,$data)
	{
		$this->db->where('vEmail', $email);
		$result = $this->db->update('users', $data);
		return $result;
	}

	public function update_user_data($data, $id)
	{
		$this->db->where('iUsersId', $id);
		$result = $this->db->update('users', $data);
		return $result;
	}

	public function get_music_creator_individual_orders($user_id)
	{
		$this->db->select('or.*, oi.iMusicUploadKey, mu.vMusicName');
  		$this->db->from('orders as or');
		$this->db->join('order_items as oi', 'or.iOrderId = oi.OrderId');
		$this->db->join('music_uploads as mu', 'oi.iMusicUploadKey = mu.iMusicUploadId');
   		$this->db->where('or.eMusicCreatorId',$user_id);
		$this->db->order_by("or.iOrderId", "desc");
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		//print_r($this->db->last_query());die;
		return $result;
	}

	public function get_music_creator_orders($user_id, $order_id)
	{
		$this->db->select('order_items.iOrderItemId as order_item_id, order_items.iOrderId as order_id, order_items.iMusicCreatorId as music_creator_id,order_items.iCelebrityId as celebrity_id,order_items.iMusicUploadKey as music_key,order_items.vItemPrice as item_price,order_items.eItemReviewStatus as item_review_status,order_items.eCelebrityPaymentStatus as celebrity_payment_status,order_items.dtAddedDate as added_date,order_items.dtUpdatedDate as updated_date,order_items.dtExpiryDate as expiry_date, orders.iOrderId as order_id, orders.vBillingFirstName as billing_first_name, orders.vBillingLastName as billing_last_name, orders.vBillingPhone as billing_phone, orders.vBillingAddressLine1 as billing_address_line_1, orders.vBillingAddressLine2 as address_line_2, orders.vBillingCity as billing_city, orders.vBillingCity as billing_city, orders.vBillingState as billing_state, orders.vBillingZip as billing_zip_code, orders.VBillingCountry as billing_country, orders.eOrderSubTotal as order_sub_total, orders.eOrderTax as order_tax, orders.eOrderCoupon as order_coupon, orders.eOrderDiscount as order_discount, orders.eOrderTotal as order_total, orders.vOrderPaymentTransactionId as payment_intent_id, orders.eOrderStatus as order_status, .users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,users.vAccessToken as access_token,users.vImage as image,users.eStatus as status,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line,user_celebrity.vShortDescription as short_description ,user_celebrity.vLongDescription as long_description,user_celebrity.vCategories as categories,user_celebrity.vSocialMediaLinks as social_media_links,user_celebrity.dPrice as price,user_celebrity.vAccountName as account_name,user_celebrity.vAccountNumber as account_number,user_celebrity.vBankName as bank_name,user_celebrity.vBankCode as bank_code,user_celebrity.vBankAddress as bank_address,user_celebrity.vPaypalId as paypal_id,user_celebrity.eIsFeatured as is_featured');
  		$this->db->from('order_items');
		  $this->db->join('orders','orders.iOrderId = order_items.iOrderId','left');
  		$this->db->join('users','users.iUsersId = order_items.iCelebrityId','left');
  		$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
   		$this->db->where(array('order_items.iMusicCreatorId' => $user_id, 'order_items.iOrderId' => $order_id));
		$this->db->order_by("order_items.iOrderItemId", "desc");
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		//echo $this->db->last_query();
		//exit;
		return $result;
	}

	public function get_celebrity_orders($user_id)
	{
		$this->db->select('order_items.iOrderId as order_id,order_items.iMusicCreatorId as music_creator_id,order_items.iCelebrityId as celebrity_id,order_items.iMusicUploadKey as music_key,order_items.vItemPrice as item_price,order_items.eItemReviewStatus as item_review_status,order_items.eCelebrityPaymentStatus as celebrity_payment_status,order_items.dtAddedDate as added_date,order_items.dtUpdatedDate as updated_date,order_items.dtExpiryDate as expiry_date,users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,users.vAccessToken as access_token,users.vImage as image,users.eStatus as status,user_music_creator.vArtistName as artist_name,user_music_creator.vDescription as description,user_music_creator.vSocialMediaLinks social_media_links,user_music_creator.vUploadMusic as upload_music');
  		$this->db->from('order_items');
  		$this->db->join('users','users.iUsersId = order_items.iMusicCreatorId','left');
  		$this->db->join('user_music_creator','user_music_creator.iUsersId = users.iUsersId','left');
  		$this->db->where('order_items.iCelebrityId',$user_id);
		$this->db->order_by("order_items.iOrderItemId", "desc");
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}

	public function get_order_details($order_id)
	{
		$this->db->select('order_items.iOrderId as order_id,order_items.iMusicCreatorId as music_creator_id,order_items.iCelebrityId as celebrity_id,order_items.iMusicUploadKey as music_key,order_items.vItemPrice as item_price,order_items.eItemReviewStatus as item_review_status,order_items.eCelebrityPaymentStatus as celebrity_payment_status,order_items.dtAddedDate as added_date,order_items.dtUpdatedDate as updated_date,order_items.dtExpiryDate as expiry_date,users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,users.vAccessToken as access_token,users.vImage as image,users.eStatus as status,user_music_creator.vArtistName as artist_name,user_music_creator.vDescription as description,user_music_creator.vSocialMediaLinks social_media_links,user_music_creator.vUploadMusic as upload_music,u1.vFirstName as celebrity_first_name,u1.vLastName as celebrity_last_name,u1.vImage as celebrity_image');
  		$this->db->from('order_items');
  		$this->db->join('users','users.iUsersId = order_items.iMusicCreatorId','left');
  		$this->db->join('users u1','u1.iUsersId = order_items.iCelebrityId','left');
  		$this->db->join('user_music_creator','user_music_creator.iUsersId = users.iUsersId','left');
  		$this->db->where('order_items.iOrderItemId',$order_id);
		$this->db->order_by("order_items.iOrderItemId", "desc");
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}
}