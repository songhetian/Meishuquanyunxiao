<?php

namespace components;

use Yii;
use JPush\Client as Client;
use common\models\Format;

class Jpush {

	private $client;
    public $campus_id;
    public function __construct($campus_id = 0)
    {
        $this->campus_id = $campus_id;
        $this->client = new Client(
            Format::getStudio('jpush_app_key', $this->campus_id),
            Format::getStudio('jpush_master_secret', $this->campus_id),
            null
        );
    }

    public function sendPrivateNotification($device_token, $alert, $extras)
    {	
    	$push = $this->client->push();
    	$os = $this->device_os($device_token);
    	$push->setPlatform($os);
    	$push->addRegistrationId($device_token);
    	if($os == 'ios'){
    		$push->iosNotification($alert, [
			  'sound' => 'sound',
			  'badge' => '+1',
              'extras' => $extras
			]);
    	}else{
    		$push->androidNotification($alert,[
                'extras' => $extras
            ]);
    	}
        $push->options([
            'apns_production' => true
        ]);
    	$push->send();
    }
 
    public function sendAllNotification($alert, $extras){
        $this->client->push()
        ->setPlatform(['ios', 'android'])
        ->addAllAudience()
        ->iosNotification($alert, [
            'sound' => 'sound',
            'badge' => '+1',
            'extras' => $extras
        ])
        ->androidNotification($alert, [
            'extras' => $extras
        ])
        ->options([
            'apns_production' => true
        ])
        ->send();
    }
    
    public function device_os($device_token) 
    {  
    	$os = substr($device_token, 2,1);
    	return ($os) ? 'ios' : 'android';
    }
}