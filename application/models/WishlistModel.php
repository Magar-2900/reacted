<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WishlistModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function check_wishlist($user_id,$product_id)
  	{

  		$this->db->select('*');
  		$this->db->from('wishlist');
  		$this->db->where('IUsersId',$user_id);
  		$this->db->where('iProductId',$product_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}

  	public function insert_wishlist($data)
  	{
  		$this->db->insert('wishlist',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function delete_form_wishlist($wishlist_id = '')
  	{    	
    	$this->db->where('iWishListId', $wishlist_id);
    	$result = $this->db->delete('wishlist');
    	return $result;
  	}

  	public function get_wishlist($user_id)
  	{   
  		$this->db->select('wishlist.iWishListId as wishlist_id,wishlist.IUsersId as user_id,wishlist.iProductId as product_id,wishlist.dtAddedDate as added_date,users.vImage as images,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line');
  		$this->db->from('wishlist');
  		$this->db->join('users','users.iUsersId = wishlist.iProductId','left');
  		$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
  		$this->db->where('wishlist.IUsersId',$user_id);
  		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		#print_r($this->db->last_query());die;
		return $result;
  	}
}
