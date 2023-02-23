<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MusicCreatorModel extends CI_Model 
{
  	public function __construct()
  	{
  		parent::__construct();
  	}

  	public function add_artist($data)
  	{
  		$this->db->insert('user_music_creator',$data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function get_artist($music_creator_id = '')
  	{
  		$this->db->select('umc.iMusicCreatorid  as music_creator_id,umc.iUsersId as user_id,umc.vArtistName as artist_name,umc.dtAddedDate as added_date');
		$this->db->from('user_music_creator as umc');
		if(!empty($music_creator_id))
		{
			$this->db->where('umc.iMusicCreatorid ',$music_creator_id);	
		}
		$this->db->where('users.iIsDeleted ','0');	
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}   

  	public function upload_music($music_creator_data)
  	{
		$this->db->insert('music_uploads',$music_creator_data);
  		$result = $this->db->insert_id();
  		return $result;
  	}

  	public function get_music_creator_details($music_creator_id = '')
  	{
  		$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role, vAddressLine1 as address_line_1, vAddressLine2 as address_line_2, vCity as user_city, vState as user_state, vCountry as user_country, vZipCode as user_zip_code, user_music_creator.vArtistName as artist_name,user_music_creator.vDescription as description,user_music_creator.vUploadMusic as music,user_music_creator.dtAddedDate as added_date,user_music_creator.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,user_music_creator.iMusicCreatorid as music_creator_id,user_music_creator.vSocialMediaLinks as social_media_links,users.vCountry as country");
		$this->db->from('users');
		$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
		$this->db->join('user_music_creator','user_music_creator.iUsersId = users.iUsersId','left');
		$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_music_creator.vCategories)<> 0","left",false);
		if(!empty($music_creator_id))
		{
			$this->db->where('users.iUsersId',$music_creator_id);
		}
		$this->db->where('users.iIsDeleted ','0');	
		$this->db->where('users.iRoleId',2);
		$this->db->group_by('users.iUsersId');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		//print_r($this->db->last_query());
		return $result;
		
  	}

	  public function get_all_music_creators($music_creator_id = '')
  	{
  		$this->db->select("users.iUsersId as user_id,users.vFirstName as first_name,users.vLastName as last_name,users.vEmail as email,users.vPhone as phone,user_roles.vRole as role, vAddressLine1 as address_line_1, vAddressLine2 as address_line_2, vCity as user_city, vState as user_state, vCountry as user_country, vZipCode as user_zip_code, user_music_creator.vArtistName as artist_name,user_music_creator.vDescription as description,user_music_creator.vUploadMusic as music,user_music_creator.dtAddedDate as added_date,user_music_creator.dtUpdatedDate as updated_date,GROUP_CONCAT(category_master.vCategoryName SEPARATOR ',') as categories,users.vImage as images,user_music_creator.iMusicCreatorid as music_creator_id,user_music_creator.vSocialMediaLinks as social_media_links,users.vCountry as country");
		$this->db->from('users');
		$this->db->join('user_roles','user_roles.iRoleId = users.iRoleId','left');
		$this->db->join('user_music_creator','user_music_creator.iUsersId = users.iUsersId','left');
		$this->db->join("category_master","find_in_set(category_master.iCategoryMasterId,user_music_creator.vCategories)<> 0","left",false);
		if(!empty($music_creator_id))
		{
			$this->db->where('users.iUsersId',$music_creator_id);
		}
		//$this->db->where('users.iIsDeleted ','0');	
		$this->db->where('users.iRoleId',2);
		$this->db->group_by('users.iUsersId');
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		//print_r($this->db->last_query());
		return $result;
		
  	}

  	public function delete_music_creator($music_creator_id = '')
  	{
  		// $data['iIsDeleted'] = '1';
  		// $this->db->where('iUsersId', $music_creator_id);
    	// $this->db->update('user_music_creator',$data);

    	$data['iIsDeleted'] = '1';
    	$this->db->where('iUsersId', $music_creator_id);
    	$result = $this->db->update('users',$data);

    	return $result;
  	}

  	public function update_music_creator($music_creator_data,$music_creator_id)
  	{
  		
  		$this->db->where('iUsersId', $music_creator_id);
		$result = $this->db->update('user_music_creator', $music_creator_data);

		return $result;
  	}

  	public function get_musics($music_creator_id)
  	{
  		$this->db->select('mu.iMusicUploadId   as music_upload_id,mu.iCreatorId as creator_id, mu.vMusicName as music_name, mu.vMusic as musics,mu.dtAddedDate as added_date,mu.eStatus as status');
		$this->db->from('music_uploads as mu');

		if(!empty($music_creator_id))
		{
			$this->db->where('mu.iCreatorId ',$music_creator_id);	
		}

		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}


	public function get_music_creator_reviews($user_id){
		$this->db->select('mr.iOrderItemId as order_item_id, mr.iOrderId as order_id, mr.vMusicReviewKey as music_key, mr.iCelebrityId as celebrity_id, mr.eItemReviewStatus as review_status, cr.vTitle as celebrity_name');
		$this->db->from('order_items as mr');
		$this->db->join('user_celebrity as cr', 'mr.iCelebrityId = cr.iUserCelebrityId', 'left');
		$this->db->where(array('mr.iMusicCreatorId'=> $user_id, 'mr.eItemReviewStatus' => 'Completed'));

		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
	}




}