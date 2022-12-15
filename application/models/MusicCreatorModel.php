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
		$query_obj = $this->db->get();
		$result = is_object($query_obj) ? $query_obj->result_array() : array();
		return $result;
  	}   

  	public function upload_music($music_creator_data,$music_creator_id)
  	{
  		$this->db->where('iMusicCreatorid', $music_creator_id);
		$result = $this->db->update('user_music_creator', $music_creator_data);
		return $result;
  	}
}
