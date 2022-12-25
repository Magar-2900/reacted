<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CouponModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_coupon($data)
  	{
  		$this->db->insert('coupons',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function get_coupon($id = '')
  	{
  		$this->db->select('vCouponTitle  as coupon_name,vCouponCode as coupon_code,vDescription as description,iCelebrityId as celebrity_id,dStartDate as start_date,dEndDate as end_date,iCouponLimit as coupon_limit,,dtAddedDate as added_date,dtUpdatedDate as updated_date,eStatus as status');
		$this->db->from('coupons');
		if(!empty($id))
		{
			$this->db->where('iCouponId  ',$id);
		}
		$this->db->where('iIsDeleted ','0');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	} 
  	
  	public function update_coupon($coupon_id,$data)
  	{
  		$this->db->where('iCouponId  ', $coupon_id);
		$result = $this->db->update('coupons', $data);
		return $result;
  	}

  	public function delete_coupon($coupon_id = '')
  	{
  		$data['iIsDeleted'] = '1';
    	$this->db->where('iCouponId ', $coupon_id);
		$result = $this->db->update('coupons', $data);
    	return $result;
  	}
}
