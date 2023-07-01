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

	  	public function update_celebrity($user_id,$data)
	  	{	  		
	  		$this->db->where('iUsersId', $user_id);
			$result = $this->db->update('user_celebrity', $data);
			return $result;
	  	}

	    public function get_celebrity_details($celebrity_id = '')
		{
			$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line,user_celebrity.vShortDescription as short_description,user_celebrity.vLongDescription as long_description,user_celebrity.dPrice as price,user_celebrity.eIsFeatured as is_featured, user_celebrity.vAccountName as bank_account_name, user_celebrity.vAccountNumber as bank_account_number, user_celebrity.vBankName as bank_name, user_celebrity.vBankAddress as bank_address, user_celebrity.vPaypalID as paypal_id, user_celebrity.dtAddedDate as added_date,user_celebrity.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,vW9Form as w9form,vSocialMediaLinks as social_media_links");
			$this->db->from('users');
			$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
			$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
			$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_celebrity.vCategories)<> 0","left",false);
			$this->db->where('users.iIsDeleted ','0');
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

		public function get_all_celebrities($celebrity_id = '')
		{
			$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line,user_celebrity.vShortDescription as short_description,user_celebrity.vLongDescription as long_description,user_celebrity.dPrice as price,user_celebrity.eIsFeatured as is_featured,user_celebrity.dtAddedDate as added_date,user_celebrity.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,vW9Form as w9form,vSocialMediaLinks as social_media_links");
			$this->db->from('users');
			$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
			$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
			$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_celebrity.vCategories)<> 0","left",false);
			//$this->db->where('users.iIsDeleted ','0');
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
	  		// $data['iIsDeleted'] = '1';
	  		// $this->db->where('iUsersId', $celebrity_id);
	    	// $this->db->update('user_celebrity',$data);

	    	$data['iIsDeleted'] = '1';
	    	$this->db->where('iUsersId', $celebrity_id);
	    	$result = $this->db->update('users',$data);
	    	return $result;
	  	}

	  	
	  	public function get_celebrities_by_category($category_id = '', $title = '', $price = '', $price_form = '', $price_to = '')
		{
			$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role,user_celebrity.vTitle as title,user_celebrity.vTagLine as tag_line,user_celebrity.vShortDescription as short_description,user_celebrity.vLongDescription as long_description,user_celebrity.dPrice as price,user_celebrity.eIsFeatured as is_featured,user_celebrity.dtAddedDate as added_date,user_celebrity.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,vW9Form as w9form,vSocialMediaLinks as social_media_links");
			$this->db->from('users');
			$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
			$this->db->join('user_celebrity','user_celebrity.iUsersId = users.iUsersId','left');
			$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_celebrity.vCategories)<> 0","left",false);
			if($category_id !== '')
			{
				//echo 'hiii';
				$this->db->where('find_in_set('.$category_id.',user_celebrity.vCategories)<> 0');
			}

			if($celebrity_id !== ''){
				$this->db->where('users.iUsersId != ',$celebrity_id);
			}
			
			$this->db->where('users.iRoleId',3);
			$this->db->where('users.iIsDeleted ','0');	
			if($price_form != '' && $price_to != '')
			{
				$this->db->where('dPrice >=', $price_form);
				$this->db->where('dPrice <=', $price_to);
			}

			if(!empty($title))
			{
				$this->db->order_by('user_celebrity.vTitle',$title);
			}

			if(!empty($price))
			{
				$this->db->order_by('user_celebrity.dPrice',$price);	
			}

			$this->db->group_by('users.iUsersId');
			$query_obj = $this->db->get();
			$result = is_object($query_obj) ? $query_obj->result_array() : array();
			#print_r($this->db->last_query());die;
			return $result;
		}

		public function set_featured_status($id, $is_featured){
			if($is_featured == 1){
				$data['eIsFeatured'] = 'Yes';
			} else {
				$data['eIsFeatured'] = 'No';
			}
	    	$this->db->where('iUsersId', $id);
	    	$result = $this->db->update('user_celebrity',$data);
	    	return $result;
		}

		public function set_celebrity_as_special($data){
			$this->db->insert('special_celebrities',$data);
	  		$result = $this->db->insert_id();
	  		return $result;
		}

		
	}