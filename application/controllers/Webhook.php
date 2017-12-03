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
			
				$message = $this->GroupChat($event, $this->bot);
				
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
			  $groupId     = $event['source']['groupId'];
			  $getprofile = $bot->getProfile($userId);
			  $profile    = $getprofile->getJSONDecodedBody();
			  
			 //cek apakah state sudah /main atau tidak 
			 //jika sudah menekan /main
			 
			 $isSudahPernahSave =$this->tebakkode_m->getGroupState($event['source']['groupId']);
				
			if($isSudahPernahSave['isSudahPernahSave'] && $isSudahPernahSave['state'] == 1) 
				
			{
				$cekCommand  = $this->cekCommandMain($event);						
			
			
			}
			//jika belum menekan /main atau telah menekan /selesai
			else{
	
				$cekCommand  = $this->cekCommand($event);
				
			
			
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
	function getPertanyaan($event, $isBenar){
		 $profile = $event;
		 
		 //random pertanyaan 
		$random_id = rand(1 , 15);
			

		 $pertanyaan =$this->tebakkode_m->getQuestion($random_id );	
		$textMessage_jikaBenar = new TextMessageBuilder("Selamat Kamu Benar. Berikut pertanyaan selanjutnya");		
		$textMessageBuilder_pertanyaan = new TextMessageBuilder($pertanyaan->pertanyaan);
		$textMessageBuilder_opsi = new TextMessageBuilder(
									"A. ".$pertanyaan->opsi_a
									."\n".
									"B. ".$pertanyaan->opsi_b
									."\n".
									"C. ".$pertanyaan->opsi_c
									."\n".
									"D. ".$pertanyaan->opsi_d
									);
		

	
		
		$multiMessageBuilder = new MultiMessageBuilder();
		if($isBenar){
			
			//kasih keterangan
			$group_state = $this->tebakkode_m->getGroupState($profile['source']['groupId']);
			
			$keterangan = $this->tebakkode_m->getQuestion($group_state ['pertanyaan_id'])->keterangan;
			//jika keterangan ada
			if($keterangan != NULL ){
				$textMessage_keterangan = new TextMessageBuilder("Ya.". $keterangan);
				$multiMessageBuilder->add($textMessage_keterangan);
			}
			
			
			
		
			$multiMessageBuilder->add($textMessage_jikaBenar);
		
			
		}
	
		$multiMessageBuilder->add($textMessageBuilder_pertanyaan);
		$multiMessageBuilder->add($textMessageBuilder_opsi);
		
		$profile["pertanyaan_id"] = $random_id;
		
		$this->tebakkode_m->saveGroupState($profile);
 
		
		
		return $multiMessageBuilder;
	}
	
	//fungsi untuk mengecek command user awal
	function cekCommand($event){
		if( $event['message']['text'] === "/main"){
			
			 $profile = $event;
			//disimpan ke DB jika lagi atau sudah /main statenya 1, jika /selesai statenya 0
			$profile['state'] = 1; 
			
			$this->tebakkode_m->saveGroupState($profile);
 
			
			return $this->getPertanyaan($event, false);
			
			
		}
		else if($event['message']['text'] === "/help"){
		
			$textMessageBuilder1 = new TextMessageBuilder("Bentar yaa masih bingung nulis apa");
	
			return $textMessageBuilder1;
			
		}
		
		
	}
	
	//fungsi untuk mengecek command user setelah /main
	function cekCommandMain($event){
		if( $event['message']['text'] === "/skip"){
			
			
			return $this->getPertanyaan($event, false);
			
			
		}
		
		else if($event['message']['text'] === "/selesai"){
		
			$profile = $event;
			//disimpan ke DB jika lagi /main statenya 1, jika /selesai statenya 0
			$profile['state'] = 0; 
			
			$this->tebakkode_m->saveGroupState($profile);
			
			$textMessageBuilder1 = new TextMessageBuilder("Permainan Berakhir, silahkan ketik /main untuk bermain lagi dan /ranking untuk melihat ranking kamu group ini");
	
			return $textMessageBuilder1;
		}
		//user menjawab
		else if( (strcasecmp($event['message']['text'], "A") == 0 )
			OR (strcasecmp($event['message']['text'], "B") == 0)
			OR (strcasecmp($event['message']['text'], "C") == 0)
			OR (strcasecmp($event['message']['text'], "D") == 0)
			){
			
			//cek apakah jawaban benar
			$answer = 0;
			if(strcasecmp($event['message']['text'], "A") == 0 ){
				$answer = 1;
			}
			else if(strcasecmp($event['message']['text'], "B") == 0){
				$answer = 2;
			}
			else if (strcasecmp($event['message']['text'], "C") == 0){
				$answer = 3;
			}
			else{
				$answer = 4;
			}
			$isBenar = $this->tebakkode_m->isAnswerEqual($event, $answer) ;
			
			$profile = $event;
			$date = new DateTime();
			$profile['timestamp_jawab'] = $date ->format('Y-m-d H:i:s');
			//Save Use State 
			$is_spam = $this->tebakkode_m->saveUserState($profile);
	
	
	
	
			//cek spam apa engga
			if(!$is_spam){
				$textMessageBuilder1 = new TextMessageBuilder("Jangan SPAM OI, kasih kesempatan yang lain");
				return $textMessageBuilder1;
	
			}
			//cek benar apa engga
			else if($isBenar){
				
				//jika benar ke pertanyaan selanjutnya
					 $this->tebakkode_m->setScoreGroup($event) ;
		
				
				return $this->getPertanyaan($event, $isBenar);
			
				
			}
			else{
				$textMessageBuilder1 = new TextMessageBuilder("Masih kurang beruntung gan");
	
				return $textMessageBuilder1;
	
			}
			
			
		}
		//cek score
		else if($event['message']['text'] === "/ranking"){
			
			
			$isSudahPernahSave =$this->tebakkode_m->getRanking($event) ;
			if($isSudahPernahSave['isSudahPernahSave']){
			
				$datas  = $isSudahPernahSave['data'];
				
		
				$pemenang = "Berikut adalah rangking-rangking anggota dari group ini :";
				 foreach($datas as $data)
				{
					$res = $this->bot->getProfile($data->user_id);
					$index = 1;
					if($res->isSucceeded()){
					
						 $profile_user = $res->getJSONDecodedBody();
						 
						 	$pemenang = $pemenang
										."\n"
										.$index
										.". "
										. $profile_user['displayName']
										." dengan jumlah score ="
										.$data->score;
										
							$index++;
					}
				
				
				}
				
				$textMessageBuilder1 = new TextMessageBuilder($pemenang);
		

				
				
				return $textMessageBuilder1;
	
			}
			else{
				$textMessageBuilder1 = new TextMessageBuilder("Kalian Belum Pernah main Oi");
				return $textMessageBuilder1;
				
				
			}
				
		}
		
	
		else{
				$textMessageBuilder1 = new TextMessageBuilder("Silahkan ketik jawaban(ex : A) untuk menjawab, atau ketik /selesai untuk selesai, /skip untuk skip pertanyaan ,/pertanyaan untuk melihat pertanyaan sekarang, /ranking untuk melihat ranking kamu group ini");
	
				
				 
		
				return $textMessageBuilder1;
			

		}
	}
  

}
