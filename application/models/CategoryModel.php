<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CategoryModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_category($data)
  	{
  		$this->db->insert('category_master',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

	public function get_all_categories(){
		$this->db->select('iCategoryMasterId as category_id,vCategoryName  as category_name,vSlug as slug,vDescription as description,vImage as image,dtAddedDate as added_date,dtUpdatedDate as updated_date,eStatus as status, isInNav as in_nav');
		$this->db->from('category_master');
		$this->db->where('iIsDeleted ','0');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}

  	public function get_category($id = '')
  	{
  		$this->db->select('iCategoryMasterId as category_id,vCategoryName  as category_name,vSlug as slug,vDescription as description,vImage as image,dtAddedDate as added_date,dtUpdatedDate as updated_date,eStatus as status');
		$this->db->from('category_master');
		if(!empty($id))
		{
			$this->db->where('iCategoryMasterId ',$id);
		}
		$this->db->where('iIsDeleted ','0');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		#print_r($this->db->last_query());die;
		return $result;
  	} 
  	
  	public function update_category($category_id,$data)
  	{
  		$this->db->where('iCategoryMasterId ', $category_id);
		$result = $this->db->update('category_master', $data);
		return $result;
  	}

  	public function delete_category($category_id = '')
  	{
  		$data['iIsDeleted'] = '1';
    	$this->db->where('iCategoryMasterId ', $category_id);
		$result = $this->db->update('category_master', $data);
    	return $result;
  	}


  	public function get_category_id($slug = '')
	{
		$this->db->select('iCategoryMasterId');
	    $this->db->from('category_master');
	    $this->db->where('vSlug',$slug);
	    $this->db->where('iIsDeleted ','0');
	    $dataArr = $this->db->get();
	    $result = is_object($dataArr) ? $dataArr->result_array() : array();
		return $result;
	}
}