<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CartModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_to_cart($data)
  	{

  		$this->db->insert('cart_items',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}
  		
  	public function delete_form_cart($row_id)
  	{
  		$this->db->where('vRowId ', $row_id);
    	$result = $this->db->delete('cart_items');
    	return $result;
  	}
}
