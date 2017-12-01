<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Webhook extends CI_Controller {

  private $bot;
  private $events;
  private $signature;
  private $user;

  function __construct()
  {
    parent::__construct();
    $this->load->model('tebakkode_m');
	
    // create bot object
    $httpClient = new CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
    $this->bot  = new LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);
  }

  public function index()
  {
     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo "Hello Coders!";
      header('HTTP/1.1 400 Only POST method allowed');
      exit;
    }
 
    // get request
    $body = file_get_contents('php://input');
    $this->signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : "-";
    $this->events = json_decode($body, true);
 
    // save log every event requests
    $this->tebakkode_m->log_events($this->signature, $body);
 
    // debuging data
    file_put_contents('php://stderr', 'Body: '.$body);
  
  
  
  
  
  
  
  
		if(is_array($this->events['events'])){
		  foreach ($this->events['events'] as $event){
	 
			//untuk group
			if( $event['source']['type'] == 'group' or
				$event['source']['type'] == 'room') 
			{
			
				$message = GroupChat($event, $this->bot);
				
				$this->bot->replyMessage($event['replyToken'], $message);
			}
			//untuk chat personal
			else{
				
				$message = $this->GroupChat($event, $this->bot);
				
				
				$this->bot->replyMessage($event['replyToken'], $textMessageBuilder);
				
			}
			
	 
			
		  } // end of foreach
		}
	  
  
  
  
  
  
  } // end of index.php

  private function followCallback($event){}

  private function textMessage($event){
	  
	
  }

  private function stickerMessage($event){}

  public function sendQuestion($replyToken, $questionNum=1){}

  private function checkAnswer($message, $replyToken){}
  
  
  
  //fungsi untuk group chat
  function GroupChat($event, $bot) {
			// route action code here
			  $userId     = $event['source']['userId'];
			  $getprofile = $bot->getProfile($userId);
			  $profile    = $getprofile->getJSONDecodedBody();
			  
			 //cek apakah state sudah /main atau tidak 
			 //jika sudah menekan /main
			if($this->tebakkode_m->getUserState($event['source']['userId']) 
				
			){
				$cekCommand  = new TextMessageBuilder($this->cekCommandMain($event));						
			}
			//jika belum menekan /main
			else{
	
				$cekCommand  = new TextMessageBuilder($this->cekCommand($event));
				
			}
			
			 
				 
			 
			
			
			 $result = $bot->replyMessage($event['replyToken'], $cekCommand );
			 
			 return $result;
			        
	}
	//fungsi untuk User Chat
	function UserChat($event, $bot){
		
			
			
			$result = $bot->replyText($event['replyToken'],  $event['replyToken']);
			
			
			
			return $result;
	}
	//fungsi untuk get pertanyaan 
	function getPertanyaan(){
		
		$game_arr=array("[1]... Kakek tidak suka",
						"Mobil[1]... tidak berjalan di",
						"Toyota adalah sebuah ... [2]",
						);
						
			
		return $game_arr[rand(0 , sizeof($game_arr)-1)];
	}
	
	//fungsi untuk mengecek command user awal
	function cekCommand($event){
		if( $event['message']['text'] === "/main"){
			
			 $profile = $event;
			//disimpan ke DB jika lagi /main statenya 1, jika /selesai statenya 0
			$profile['state'] = 1; 
			
			$this->tebakkode_m->saveUserState($profile);
 
			
			return $this->getPertanyaan();
			
			
		}
		else if($event['message']['text'] === "/help"){
			return "Bentar yaa";
			
		}
		else if($event['message']['text'] === "/selesai"){
			return "Permainan Berakhir, silahkan ketik /main lagi untuk bermain";
		}
		else{
			return "Silahkan ketik /main untuk main dan /help untuk bantuan";
		}
	}
	
	//fungsi untuk mengecek command user setelah /main
	function cekCommandMain($event){
		if( $event['message']['text'] === "/skip"){
			
			
			return $this->getPertanyaan();
			
			
		}
		
		else if($event['message']['text'] === "/selesai"){
		
			$profile = $event;
			//disimpan ke DB jika lagi /main statenya 1, jika /selesai statenya 0
			$profile['state'] = 0; 
			
			$this->tebakkode_m->saveUserState($profile);
		
			return "Permainan Berakhir, silahkan ketik /main untuk bermain lagi";
		}
		else{
			return "Silahkan ketik /skip untuk ganti kalimat dan /selesai untuk selesai";
		}
	}
  

}
