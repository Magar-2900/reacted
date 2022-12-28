<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ContactModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_contact_us($data)
  	{
  		$this->db->insert('contact_us',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function get_contact_us($id)
  	{
  		$this->db->select('iContactUsId  as contact_us_id,vName as name,vEmail as email,vPhone as phone,vSubject as subject,vMessage as message,dtAddedDate as added_date,eStatus as status');
		$this->db->from('contact_us');
		if(!empty($id)){
		$this->db->where('iContactUsId ',$id);
		}
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	} 
}