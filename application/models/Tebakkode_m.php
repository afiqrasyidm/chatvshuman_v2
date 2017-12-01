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
	  
    $data = $this->db->where('user_id', $user_id)->get('users')->row_array();
    if(count($data) > 0) return $data;
    return false;
	
  }

  //disimpan ke DB jika lagi /main statenya 1, jika /selesai statenya 0
  function saveUserState($profile){
	//jika sudah pernah diupdate saja statenya
	if($this->getUserState($profile['userId'])){
	
	  $this->db->set('state', $profile['state'])
      ->where('user_id', $profile['userId'])
      ->update('users_state');
	   
	   return $this->db->affected_rows();
	}
	//jika belum maka state user itu dibuat
	else{
	
	  $this->db->set('user_id', $profile['userId'])
      ->set('state', $profile['state'])
      ->insert('users_state');
	
	   return $this->db->insert_id();
  
	}
  
  }

  // Question
  function getQuestion($questionNum){}

  function isAnswerEqual($number, $answer){}

  function setUserProgress($user_id, $newNumber){}

  function setScore($user_id, $score){}

}
