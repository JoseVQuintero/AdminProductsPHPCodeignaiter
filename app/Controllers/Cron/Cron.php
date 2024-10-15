<?php
namespace App\Controllers\Cron;

use App\Controllers\BaseControllerCron;
use App\Models\Users;

use CodeIgniter\API\ResponseTrait;
class Cron extends BaseControllerCron
{
	use ResponseTrait;
	public function __construct()
	{
	}

	public function execute()
	{
		try{
			/*$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$country = $request['country'];
			$id = $request['user_id'];*/
			$response=[];
			$userModel = new Users();
			$users = $userModel->getUser(false,false);	
			foreach($users as $user){
				if(!empty($user['access'])){
					helper('cron');
					index($user['country'],$user['userID']);
					$response[]=[$user['country'],$user['userID']];
				}
			}			
			return $this->respond(["status"=>'success','message'=> "success","response"=>$response],200);
		} catch (Exception $e) {
			return $this->respond(["status"=>'success','message'=> "Error".' - '.$e->getMessage()],200);
		}
		/* echo "si";
		exit; */
		/* $ch = curl_init('http://localhost:8080/cron2');
		$data = json_encode(
			["country"=>"mx","user_id"=>"3"],
								JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
							);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded',"Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6IkFQUF9Vc2VyX1h4c3dYRSMkJSZURlJERWZnU1dEUnhjNDU2Njc3UlRZVFJWJSZcLz0yMjMzZSIsImlhdCI6MTY0OTYzNjk3OCwiZXhwIjoxNjQ5NjQwNTc4fQ.xnDUE6UUR8f9b3KzMIGCtLM4KueoCHoKZ6cgl8ck50c"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		var_dump($response); */
		
		//try{
			
			/*$ch = curl_init();			
			curl_setopt($ch, CURLOPT_URL, base_url().'/authcron/login');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "username=APP_User_XxswXE#$%&TFRDEfgSWDRxc456677RTYTRV%&/=2233e&password=APP_Pass_bdicronWE\"#!\"#$&/\$GDERDFTRE");
			$headers = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
			$result = curl_exec($ch);    
			var_dump($result);
			exit;
			if (curl_errno($ch)) {
				$message =  'Error:' . curl_error($ch);
			}
			curl_close($ch);
			$a = json_decode($result);
			$token = $a->access_token;*/
			//return $this->respond(["status"=>'success','message'=> 'Complete - Timer'." - ".base_url().'/authcron/login'],200);
			/* $request = $this->request->header('Authorization');
			var_dump($request);
			exit; 
			$userModel = new Users();
			$users = $userModel->getUser(false,false);	
			$response=[];	
			$message='';
			foreach($users as $user){
				if(!empty($user['access'])){
					$data = json_encode(["country"=>$user['country'],"user_id"=>$user['id']]);
					$response[]=$data;
					$curl = curl_init();

					curl_setopt_array($curl, array(
						CURLOPT_URL => base_url().'/cron/cron',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_TIMEOUT => 2,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => $data,
						CURLOPT_HTTPHEADER => array(
							"Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6IkFQUF9Vc2VyX1h4c3dYRSMkJSZURlJERWZnU1dEUnhjNDU2Njc3UlRZVFJWJSZcLz0yMjMzZSIsImlhdCI6MTY0OTYzNjk3OCwiZXhwIjoxNjQ5NjQwNTc4fQ.xnDUE6UUR8f9b3KzMIGCtLM4KueoCHoKZ6cgl8ck50c"
						),
					));
					
					$response[] = curl_exec($curl);
					echo "si";
					echo base_url().'/cron/cron';
					exit;
					if (curl_errno($curl)) {
						$message =  'Error:' . curl_error($curl);
						$response[]='Error:' . curl_error($curl);
					}
					
					curl_close($curl);
				}
			}
			return $this->respond(["status"=>'success','message'=> $message,"response"=>$response],200);
		} catch (Exception $e) {
			return $this->respond(["status"=>'success','message'=> $message.' '.$e->getMessage()],200);
		}*/
	}
	
}
