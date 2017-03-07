<?php
namespace App\Library;

class Sms {
	protected $authKey;
	protected $mobile;
	protected $sender_id;
	protected $message;
	protected $route;
	
	public function __construct() {
		$this->authKey = MSG91_AUTH_KEY;
	}

	public function setMobile($mobile) {
		$this->mobile = $mobile;
	}

	public function setSender($sender_id) {
		$this->sender_id = $sender_id;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setRoute($route) {
		$this->route = $route;
	}
	
	public function sendSMS() {
		if (!$this->authKey) {
			trigger_error('Error: Authentication Key required!');
			exit();			
		}

		if (!$this->sender_id) {
			trigger_error('Error: Sender ID required!');
			exit();					
		}

		if (!$this->mobile) {
			trigger_error('Error: Mobile Number required!');
			exit();					
		}

		if (!$this->message) {
			trigger_error('Error: Message required!');
			exit();					
		}

		if (is_array($this->mobile)) {
			$mobile = implode(',', $this->mobile);
		} else {
			$mobile = $this->mobile;
		}
		
		if (!$this->route) {
			trigger_error('Error: Route required!');
			exit();					
		}

		
		$postData = array(
			'authkey' => $this->authKey,
			'mobiles' => $mobile,
			'message' => $this->message,
			'sender' => $this->sender_id,
			'route' => $this->route
		);
		
		$url="http://api.msg91.com/sendhttp.php";
		
		if(SMS_SERVICE === true) {
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $postData
			));
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$output = curl_exec($ch);
			if(curl_errno($ch))
			{
				trigger_error('Error: ' . curl_error($ch));
				exit();
			}
			
			curl_close($ch);
		}
	}
}