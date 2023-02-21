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
  		$this->db->insert_batch('cart_items',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}
  		
  	public function delete_form_cart($cart_item_id)
  	{
  		$this->db->where('iCartItemId', $cart_item_id);
    	$result1 = $this->db->delete('cart_items');
    	return $result1;
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
  		$this->db->select('cart_items.iCartItemId as cart_item_id,cart_items.iCartId as cart_id,cart_items.iProductId as product_id,cart_items.vName as name,cart_items.iQty as qty,cart_items.dPrice as price,cart_items.dtAddedDate as added_date,users.vImage as images,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line');
  		$this->db->from('cart_items');
  		$this->db->join('users','users.iUsersId = cart_items.iProductId','left');
  		$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
  		$this->db->where('iCartId',$cart_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();		
		return $result;
  	}

  	public function is_cart_data_exist($cart_id,$product_id)
  	{
  		$this->db->select('iCartItemId as cart_item_id,iCartId as cart_id,iProductId as product_id,vName as name,iQty as qty,dPrice as price,dtAddedDate as added_date');
  		$this->db->from('cart_items');
  		$this->db->where('iCartId',$cart_id);
  		$this->db->where('iProductId',$product_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();		
		return $result;
  	}

  	public function add_order_items($data)
  	{
  		$this->db->insert_batch('order_items',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function add_order($data)
  	{
  		$this->db->insert('orders',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function update_order_status($order_id, $order)
  	{
		return $this->db->where('iOrderId', $order_id)->update("orders", $order);
  	}

	public function delete_cart_data($cart_id, $user_id){
		$res = $this->db->query(`DELETE cart, cart_items FROM cart INNER JOIN cart_items  
		WHERE cart.iCartId = cart_items.iCartId and cart.iCartId = $cart_id and cart.iUsersId = $user_id`);
		echo $this->db->last_query();
	}
}