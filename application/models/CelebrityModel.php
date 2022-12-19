<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CelebrityModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function register_celebrity($data)
  	{
  		$this->db->insert('user_celebrity',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

    public function get_celebrity_details($celebrity_id = '')
	{
		$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line,user_celebrity.vShortDescription as short_description,user_celebrity.vLongDescription as long_description,user_celebrity.dPrice as price,user_celebrity.eIsFeatured as is_featured,user_celebrity.dtAddedDate as added_date,user_celebrity.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,");
		$this->db->from('users');
		$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
		$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
		$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_celebrity.vCategories)<> 0","left",false);
		if(!empty($celebrity_id))
		{
			$this->db->where('users.iUsersId',$celebrity_id);
		}
		$this->db->where('users.iRoleId',3);
		$this->db->group_by('users.iUsersId');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();

		// print_r($this->db->last_query());die;
		return $result;
	}

	public function delete_celebrity($celebrity_id = '')
  	{
  		$this->db->where('iUsersId', $celebrity_id);
    	$this->db->delete('user_celebrity');

    	$this->db->where('iUsersId', $celebrity_id);
    	$result = $this->db->delete('users');
    	return $result;
  	}

  	
  	public function get_celebrities_by_category($category_id = '', $title = '', $price = '', $price_form = '', $price_to = '')
	{
		$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line,user_celebrity.vShortDescription as short_description,user_celebrity.vLongDescription as long_description,user_celebrity.dPrice as price,user_celebrity.eIsFeatured as is_featured,user_celebrity.dtAddedDate as added_date,user_celebrity.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,");
		$this->db->from('users');
		$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
		$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
		$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_celebrity.vCategories)<> 0","left",false);
		if(!empty($category_id))
		{
			$this->db->where('find_in_set('.$category_id.',user_celebrity.vCategories)<> 0');
		}
		
		$this->db->where('users.iRoleId',3);

		if(!empty($title))
		{
			$this->db->order_by('user_celebrity.vTitle',$title);
		}

		if(!empty($price))
		{
			$this->db->order_by('user_celebrity.dPrice',$price);	
		}

		if(!empty($price_form) && !empty($price_to))
		{
			$this->db->where('dPrice >=', $price_form);
			$this->db->where('dPrice <=', $price_to);
		}

		$this->db->group_by('users.iUsersId');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		
		return $result;
	}

	public function get_category_id($slug)
	{
		$this->db->select('iCategoryMasterId');
		$this->db->from('category_master');
		$this->db->where('vSlug',$slug);
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}
}
