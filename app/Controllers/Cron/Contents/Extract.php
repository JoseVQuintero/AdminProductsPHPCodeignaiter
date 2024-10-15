<?php
namespace App\Controllers\Cron\Contents;
use App\Controllers\Storage;
class Extract extends Storage
{
	public function index()
	{		
		if(!isset($_GET_['country'])){
			$resturnMessage = ['danger','notif_error', '<b>country null</b> '];
			return  $this->respond($resturnMessage,200);
		}
		$_GET_ = $this->request->getGet(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		helper('contents');
	} 
}
?>
			