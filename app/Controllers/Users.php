<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Users extends BaseController
{
	public function __construct()
	{
	}
	public function index()
	{
		$data = array_merge($this->data, [
			'title' 	=> 'Users Page',
			'Access'	=> returnAccess(),
			'Users'		=> $this->userModel->getUser(),
			'UserRole'	=> $this->userModel->getUserRole()
		]);
		return view('users/userList', $data);
	}
	public function userRoleAccess()
	{
		$role 		= $this->request->getGet('role');
		$userRole 	= $this->userModel->getUserRole($role);
		if (!$userRole) {
			return redirect()->to(base_url('users'));
		}
		$data = array_merge($this->data, [
			'title' 			=> 'Users Page',
			'MenuCategories'	=> $this->userModel->getMenuCategory(),
			'Menus'				=> $this->userModel->getMenu(),
			'Submenus'			=> $this->userModel->getSubmenu(),
			'UserAccess'		=> $this->userModel->getAccessMenu($role),
			'role'				=> $this->userModel->getUserRole($role)
		]);
		return view('users/userAccessList', $data);
	}
	public function createRole()
	{
		$createRole = $this->userModel->createRole($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($createRole) {
			session()->setFlashdata('notif_success', '<b>Successfully added role data</b> ');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to add role data</b> ');
			return redirect()->to(base_url('users'));
		}
	}
	public function updateRole()
	{
		$updateRole = $this->userModel->updateRole($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($updateRole) {
			session()->setFlashdata('notif_success', '<b>Successfully update user data</b> ');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to update user data</b> ');
			return redirect()->to(base_url('users'));
		}
	}
	public function deleteRole($role)
	{
		if (!$role) {
			return redirect()->to(base_url('users'));
		}
		$deleteRole = $this->userModel->deleteRole($role);
		if ($deleteRole) {
			session()->setFlashdata('notif_success', '<b>Successfully added menu data</b> ');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to add menu data</b> ');
			return redirect()->to(base_url('users'));
		}
	}
	public function createMenuCategory()
	{
		$createMenuCategory = $this->userModel->createMenuCategory($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($createMenuCategory) {
			session()->setFlashdata('notif_success', '<b>Successfully create menu category</b>');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to create menu category</b>');
			return redirect()->to(base_url('users'));
		}
	}

	public function createMenu()
	{
		$createController 	= $this->_createController();
		$createView			= $this->_createView();
		if ($createController && $createView) {
			$createMenu = $this->userModel->createMenu($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			if ($createMenu) {
				session()->setFlashdata('notif_success', '<b>Successfully create menu </b> ');
				return redirect()->to(base_url('users'));
			} else {
				session()->setFlashdata('notif_error', '<b>Failed to create menu </b> ');
				return redirect()->to(base_url('users'));
			}
		} else {
			session()->setFlashdata('notif_error', "<b>Failed to create menu </b>Cannot create file ");
			return redirect()->to(base_url('users'));
		}
	}
	public function createSubMenu()
	{
		$createSubMenu = $this->userModel->createSubMenu($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($createSubMenu) {
			session()->setFlashdata('notif_success', '<b>Successfully create submenu </b> ');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to create submenu </b> ');
			return redirect()->to(base_url('users'));
		}
	}
	

	public function createUser()
	{
		if (!$this->validate(['inputUsername' => ['rules' => 'is_unique[users.username]']])) {
			session()->setFlashdata('notif_error', '<b>Failed to add new user</b> The user already exists! ');
			return redirect()->to(base_url('users'));
		}		

		$dataUser=$this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country=$dataUser['access']['zonedefault'];	
		$createUser = $this->userModel->createUser($dataUser);
		
		if ($createUser) {
			/* $getPost = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS); */
			$userFind = $this->userModel->getUser($dataUser['inputUsername']);
			//$this->createControllerCron($userFind['country'],$userFind['id'],$userFind['access']);
			//$this->createControllerCron($createUser['country'],$createUser['id']);

			$file = $this->request->getFile('avatar');
			if($file->isValid()){
				
				$file->move('getSegmentation/'.$country."/".$userFind['userID'], "avatar.jpg",true);
			}

			session()->setFlashdata('notif_success', '<b>Successfully added new user</b> '.$file->getName());
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to add new user</b> ');
			return redirect()->to(base_url('users'));
		}
	}
	public function updateUser()
	{
		
		$dataUser=$this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country=$dataUser['access']['zonedefault'];
		$updateUser = $this->userModel->updateUser($dataUser);

		if ($updateUser) {
			//$getPost = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			//$this->createControllerCron($userFind['country'],$userFind['id'],$userFind['access']);
			//$this->createControllerCron($updateUser['country'],$updateUser['id']);

			$file = $this->request->getFile('avatar');
			
			if($file->isValid()){
				$file->move('getSegmentation/'.$country."/".$dataUser['userID'], "avatar.jpg",true);
			}

			session()->setFlashdata('notif_success', '<b>Successfully update user data</b> ');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to update user data</b> ');
			return redirect()->to(base_url('users'));
		}
	}
	public function deleteUser($userID)
	{
		if (!$userID) {
			return redirect()->to(base_url('users'));
		}
		$deleteUser = $this->userModel->deleteUser($userID);
		if ($deleteUser) {
			session()->setFlashdata('notif_success', '<b>Successfully delete user</b> ');
			return redirect()->to(base_url('users'));
		} else {
			session()->setFlashdata('notif_error', '<b>Failed to delete user</b> ');
			return redirect()->to(base_url('users'));
		}
	}

	public function changeMenuCategoryPermission()
	{
		$userAccess = $this->userModel->checkUserMenuCategoryAccess($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($userAccess > 0) {
			$this->userModel->deleteMenuCategoryPermission($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		} else {
			$this->userModel->insertMenuCategoryPermission($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		}
	}

	public function changeMenuPermission()
	{
		$userAccess = $this->userModel->checkUserAccess($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($userAccess > 0) {
			$this->userModel->deleteMenuPermission($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		} else {
			$this->userModel->insertMenuPermission($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		}
	}

	public function changeSubMenuPermission()
	{
		$userAccess = $this->userModel->checkUserSubmenuAccess($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		if ($userAccess > 0) {
			$this->userModel->deleteSubmenuPermission($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		} else {
			$this->userModel->insertSubmenuPermission($this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		}
	}

	public function _createController()
	{
		$menuTitle		= $this->request->getPost('inputMenuTitle');
		$controllerName = url_title(ucwords($menuTitle), '', false);
		$viewName 		= url_title($menuTitle, '', true);
		$controllerPath	= APPPATH . 'Controllers/' . $controllerName . ".php";
		$controllerContent = "<?php
		namespace App\Controllers;
		use App\Controllers\BaseController;
		class $controllerName extends BaseController
		{
			public function index()
			{
				$|data = array_merge($|this->data, [
					'title'         => '$menuTitle'
				]);
				return view('$viewName', $|data);
			}
		}
		";
		$renderFile = str_replace("|", "", $controllerContent);
		if (file_put_contents($controllerPath, $renderFile) !== false) {
			return true;
		} else {
			return false;
		}
	}
	public function _createView()
	{
		$viewName 		= url_title($this->request->getPost('inputMenuTitle'), '', true);
		$viewPath		= APPPATH . 'Views/' . $viewName . ".php";
		$viewContent 	= "<?= $|this->extend('layouts/main'); ?>
		<?= $|this->section('content'); ?>
		<h1 class=\"h3 mb-3\"><strong><?= $|title; ?></strong> Menu </h1>
		<?= $|this->endSection(); ?>
		";
		$renderFile = str_replace("|", "", $viewContent);
		if (file_put_contents($viewPath, $renderFile) !== false) {
			return true;
		} else {
			return false;
		}
	}
	public function createControllerCron($country,$id,$access)
	{
		$controllerName = 'Cron_' . $country."_". $id;
		$controllerPath	= APPPATH . 'Controllers/Cron/Timer/'.$controllerName. ".php";
		$accessCountry = returnAccess();
		foreach($accessCountry['zone'] as $country_=>$value){
			$controllerName_ = 'Cron_' . $country_."_". $id;
			if(file_exists(APPPATH . 'Controllers/Cron/Timer/'.$controllerName_. ".php")){
				unlink(APPPATH . 'Controllers/Cron/Timer/'.$controllerName_. ".php");
			}
		}
		if(!file_exists($controllerPath)){		
			helper('segmentation');			
			$controllerContent = "<?php
				namespace App\Controllers\Cron\Timer;
				use App\Controllers\Storage;
				use App\Models\Users;
				class $controllerName extends Storage
				{
					public function index()
					{
						
					}

					public function actionCron(){

						$|country = '".$country."';
						$|id = ".$id.";
						$|rootCron = \"getSegmentation/\".$|country.\"/\".$|id;
						set_time_limit(0);		
						$|loop = true;
						if(file_exists($|rootCron.\"loop-exit.txt\")){
							$|loop = false;
							exit;
						}

						$|timerJson = [];
						if(file_exists($|rootCron.\"timer.json\")){
							$|timerJson = json_decode(file_get_contents($|rootCron.\"timer.json\"),true);
							if(isset($|timerJson[date(\"dmY\")])){
								$|hourMax = (int)max($|timerJson[date(\"dmY\")]);
								$|hourMaxCurrent = (int)date(\"H\");
								$|diffHour = $|hourMaxCurrent-$|hourMax;
								if($|diffHour<2){
									file_put_contents($|rootCron.\"timer-exit.txt\",date(\"Y-m-d_H\"));
									exit;
								}
							}
						}else{
							file_put_contents($|rootCron.\"timer.json\",json_encode($|timerJson));
						}

						while($|loop){
							$|model = new Users();
							$|userFind = $|model->getUser(false,$|id);
							$|access = json_decode($|userFind['access'],true);

							$|filters = $|access[$|access['default']];

							$|timerJson[date(\"dmY\")][] = (int)date(\"H\");
						
							file_put_contents($|rootCron.\"timer.json\",json_encode($|timerJson));
							$|filtro=[];
							
							if(file_exists($|rootCron.\"loop-exit.txt\")){
								$|loop = false;
								file_put_contents($|rootCron.\"log-exit.txt\",\"exit-\".date(\"Y-m-d-H\"));
								unlink($|rootCron.\"timer.txt\");
								exit;
							}    

							$|categories=((!is_null($|filters[\"categories\"]))? explode(\",\",$|filters[\"categories\"]) :[]);
							$|manufacturer=((!is_null($|filters[\"manufacturer\"]))? explode(\",\",$|filters[\"manufacturer\"]) :[]);
							$|products=((!is_null($|filters[\"products\"]))? explode(\",\",$|filters[\"products\"]) :[]);
							$|prices=((!is_null($|filters[\"prices\"]))? explode(\",\",$|filters[\"prices\"]) :[]);
							$|contents=((!is_null($|filters[\"contents\"]))? explode(\",\",$|filters[\"contents\"]) :[]);
							$|productsrc=((!is_null($|filters[\"productsrc\"]))? explode(\",\",$|filters[\"productsrc\"]) :[]);
							$|WEEK=((!is_null($|filters[\"WEEK\"]))? explode(\",\",$|filters[\"WEEK\"]) :[]);

						
							$|hora = (int)date(\"H\");
							$|week = (int)date(\"w\");
							if(in_array($|hora,$|categories)){ $|filtro[]=\"categories\"; }
							if(in_array($|hora,$|manufacturer)){ $|filtro[]=\"manufacturer\"; }
							if(in_array($|hora,$|products)){ $|filtro[]=\"products\"; }
							if(in_array($|hora,$|prices)){ $|filtro[]=\"prices\"; }
							if(in_array($|hora,$|contents)){ $|filtro[]=\"contents\"; }
							if(in_array($|hora,$|productsrc)){ $|filtro[]=\"productsrc\"; }
							if(count($|filtro)>0&&in_array($|week,$|WEEK)){
									/*$|f=\"&filtro=\".implode(\",\",$|filtro);
						
									$|get_url = pathUrl().\"cron-bdi.php?user_id=\".INGRAM_ID.$|f;
									
									$|response = curl($|get_url);*/ 									

									foreach($|filtro as $|item){
										$|response[]  = $|this->storageUpdateByCron($|item,$|access,$|country,$|id);
									}	
							}				
							if (!file_exists($|rootCron.\"logs_get_url\")) {
								mkdir($|rootCron.\"logs_get_url\", 0775, true);	
							}
							file_put_contents($|rootCron.\"logs_get_url/log-\".date(\"Y-m-d-H\").\".txt\",json_encode($|response));
							$|segundos = (60-(int)date(\"i\"))*60; 
							sleep($|segundos);
						}
					} 
				}
			?>
			";
			$renderFile = str_replace("|", "", $controllerContent);
			write_file_root(APPPATH . 'Controllers/Cron/Timer/',$controllerName.".php",$renderFile);
		}
	}
}
