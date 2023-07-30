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

	public function get_categorywise_count($from_date,$to_date)
	{
		$this->db->select('CASE WHEN SUM(`oi`.`vItemPrice`) IS NULL THEN "0" ELSE SUM(`oi`.`vItemPrice`) END as category_count,`cm`.`vCategoryName` as category_name');
	    $this->db->from('category_master cm');
	    $this->db->join('music_uploads mu','cm.iCategoryMasterId = mu.iCategoryId','left');
	    $this->db->join('order_items oi','mu.iMusicUploadId = oi.iMusicUploadKey AND oi.dtAddedDate >= "'.$from_date.'" AND oi.dtAddedDate <= "'.$to_date.'"','left');
	    $this->db->where('cm.eStatus','Active');
	    $this->db->group_by('cm.iCategoryMasterId');
	    $dataArr = $this->db->get();
	    $result = is_object($dataArr) ? $dataArr->result_array() : array();
		return $result;
	}

	public function status_wise_amount_in_escrow($from_date,$to_date)
	{
		$this->db->select('CASE WHEN SUM(`oi`.`vItemPrice`) IS NULL THEN "0" ELSE SUM(`oi`.`vItemPrice`) END as category_count,OI.eStatus as status');
	    $this->db->from('order_items oi');
	    $this->db->where('oi.dtAddedDate >= ',$from_date);
	    $this->db->where('oi.dtAddedDate <= ',$to_date);
	    $this->db->group_by('oi.eStatus');
	    $dataArr = $this->db->get();
	    $result = is_object($dataArr) ? $dataArr->result_array() : array();
		return $result;
	}

	public function update_payment_status($id,$status)
	{
		$data['eStatus'] = $status;
		$this->db->where('iOrderItemId ', $id);
		$result = $this->db->update('order_items', $data);
		return $result;
	}
}
