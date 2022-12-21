<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PlatformModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_platform($data)
  	{
  		$this->db->insert('platform_master',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

	public function get_all_social_media_platform(){
		$this->db->select('iPlatformMasterId as id, vPlatformName  as platform_name,vLink as platform_link,dtAddedDate as added_date,dtUpdatedDate as updated_date,eStatus as status');
		$this->db->from('platform_master');
		$this->db->where('eStatus','Active');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}

  	public function get_social_media_platform($id = '')
  	{
  		$this->db->select('vPlatformName  as platform_name,vLink as platform_link,dtAddedDate as added_date,dtUpdatedDate as updated_date,eStatus as status');
		$this->db->from('platform_master');
		$this->db->where('iPlatformMasterId',$id);
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	} 
  	
  	public function delete_social_media_platform($platform_id = '')
  	{

    	$this->db->where('iPlatformMasterId', $platform_id);
    	$result = $this->db->delete('platform_master');
    	return $result;
  	}

  	public function update_social_media_platform($platform_id,$data)
  	{
  		$this->db->where('iPlatformMasterId', $platform_id);
		$result = $this->db->update('platform_master', $data);
		return $result;
  	}
}
