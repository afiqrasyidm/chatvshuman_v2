<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tebakkode_m extends CI_Model {

  function __construct(){
    parent::__construct();
    $this->load->database();
  }

  // Events Log
  function log_events($signature, $body)
  {
    $this->db->set('signature', $signature)
    ->set('events', $body)
    ->insert('eventlog');

    return $this->db->insert_id();
  }

  // Users
  function getUserState($user_id){
	  
    $data = $this->db->where('user_id', $user_id)->get('timestamp');
    if( $data -> num_rows()  > 0) 
	{	
		$data_state['timestamp_jawab'] = $data->row()->timestamp;
		$data_state['isSudahPernahSave'] = true;
	
		return $data_state;
    }
	return false;
	
  }

  //disimpan ke DB jika lagi udh menjawab 30 detik statenya 1, jika /selesai statenya 0
  function saveUserState($profile){
	//jika sudah pernah diupdate saja statenya
	
	$isSudahPernahSave = $this->getUserState($profile['source']['userId']);
	if($isSudahPernahSave['isSudahPernahSave']){
	
		//cek spam apa kagak 2017-12-01 06:42:52.177557
		if($isSudahPernahSave['timestamp_jawab']){
			$this->db->set('timestamp', $profile['timestamp_jawab'])
			->where('user_id', $profile['source']['userId'])
			->update('users_state');
		   
			return $this->db->affected_rows();
		}
		else{
			
		}
		
	}
	//jika belum maka state user itu dibuat
	else{
	
	  $this->db->set('user_id', $profile['source']['userId'])
      ->set('timestamp', $profile['timestamp_jawab'])
      ->insert('users_state');
	
	   return $this->db->insert_id();
  
	}
  
  }
  
  // Group
  function getGroupState($group_id){
	  
    $data = $this->db->where('group_id', $group_id)->get('group_state');
    if( $data -> num_rows()  > 0) 
	{	
		$data_state['state'] = $data->row()->group_state;
		$data_state['isSudahPernahSave'] = true;
		$data_state['pertanyaan_id'] = $data->row()->pertanyaan_id;;
		return $data_state;
    }
	return false;
	
  }


  function saveGroupState($profile){
	//jika sudah pernah diupdate saja statenya
	
	$isSudahPernahSave = $this->getGroupState($profile['source']['groupId']);
	if($isSudahPernahSave['isSudahPernahSave']){
	
		if($profile['pertanyaan_id']==NULL){
		
			$this->db->set('group_state', $profile['state'])
			->where('group_id', $profile['source']['groupId'])
			->update('group_state');
		}
		
		else{
			
			$this->db->set('pertanyaan_id', $profile['pertanyaan_id'])
			->where('group_id', $profile['source']['groupId'])
			->update('group_state');
		}
	   return $this->db->affected_rows();
	}
	//jika belum maka state user itu dibuat
	else{
	
	  $this->db->set('group_id', $profile['source']['groupId'])
      ->set('group_state', $profile['state'])
      ->insert('group_state');
	
	   return $this->db->insert_id();
  
	}
  
  }
  

  // get pertanyaan 
  function getQuestion($questionNum){
	  $data = $this->db->where('id', $questionNum)->get('pertanyaan')->row();
	  return $data;
}

  function isAnswerEqual($number, $answer){}

  function setUserProgress($user_id, $newNumber){}

  function setScore($user_id, $score){}

}
