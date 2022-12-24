<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CartModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function check_cart($user_id)
  	{
  		$this->db->select('iUsersId');
  		$this->db->from('cart');
  		$this->db->where('iUsersId',$user_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}

  	public function update_cart($data, $user_id)
  	{
  		$this->db->where('iUsersId ', $user_id);
		$result = $this->db->update('cart', $data);
		return $result;
  	}

  	public function add_to_cart($data)
  	{
  		$this->db->insert('cart',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function add_to_cart_items($data)
  	{
  		$this->db->insert('cart_items',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}
  		
  	public function delete_form_cart($cart_item_id)
  	{
  		$this->db->where('iCartItemId', $cart_item_id);
    	$result1 = $this->db->delete('cart_items');
    	return $result;
  	}

  	public function get_cart_items($cart_item_id)
  	{
  		$this->db->select('*');
  		$this->db->from('cart_items');
  		$this->db->where('iCartId',$cart_item_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}

  	public function get_cart_id($user_id)
  	{
  		$this->db->select('iCartId');
  		$this->db->from('cart');
  		$this->db->where('iUsersId',$user_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result[0]['iCartId'];
  	}

  	public function get_cart_details($user_id)
  	{
  		$this->db->select('iCartId as cart_id,iUsersId as user_id,dSubTotal as sub_total,vTax as tax,dTotal as total,vCuponCode as coupon_code,dtAddedDate as added_date');
  		$this->db->from('cart');
  		$this->db->where('iUsersId',$user_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}
  	
  	public function get_cart_items_details($cart_id)
  	{
  		$this->db->select('iCartItemId as cart_item_id,iCartId as cart_id,iProductId as product_id,vName as name,iQty as qty,dPrice as price,dtAddedDate as added_date');
  		$this->db->from('cart_items');
  		$this->db->where('iCartId',$cart_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();		
		return $result;
  	}
}
