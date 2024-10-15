<?php
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;

use App\Controllers\BaseController;

class piTool extends BaseController
{
	use ResponseTrait;
	public function __construct()
	{
		
	}
	public function index()
	{		
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/segmentation.json",true);	
		}
		if(!isset($fileSegmentation['manufacturer'])){$fileSegmentation['manufacturer']=[];}
		if(!isset($fileSegmentation['categories'])){$fileSegmentation['categories']=[];}
		if(!isset($fileSegmentation['product'])){$fileSegmentation['product']=[];}

		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';

		$data = array_merge($this->data, [
			'title' 	        => 'Products Segmentation',
			'Products'		    => $this->productsModel->getProducts($country),
			'ProductsSeg'		=> json_encode($fileSegmentation['product']),
			'ManufacturerSeg'	=> json_encode($fileSegmentation['manufacturer']),
			'ManufacturerCount' => $this->productsModel->countManufacturer($country,$fileSegmentation['manufacturer'],$fileSegmentation['product']),
			'CategorySeg'		=> json_encode($fileSegmentation['categories']),
			'CategoryCount'		=> $this->productsModel->countCategories($country,$fileSegmentation['categories'],$fileSegmentation['product']),
			'Manufacturers'		=> $this->productsModel->getManufacturer($country),
			'Categories'		=> $this->productsModel->getCategories($country),
		]);		
		/* echo json_encode($data);
		exit; */
		return view('piTool/piTool', $data);
	}
	public function Manufacturer()
	{		
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/segmentation.json",true);	
		}
		if(!isset($fileSegmentation['manufacturer'])){$fileSegmentation['manufacturer']=[];}
		if(!isset($fileSegmentation['categories'])){$fileSegmentation['categories']=[];}
		if(!isset($fileSegmentation['product'])){$fileSegmentation['product']=[];}

		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';

		$data = array_merge($this->data, [
			'title' 	        => 'Products Segmentation',
			'Products'		    => $this->productsModel->getProducts($country),
			'ProductsSeg'		=> json_encode($fileSegmentation['product']),
			'ManufacturerSeg'	=> json_encode($fileSegmentation['manufacturer']),
			'ManufacturerCount' => $this->productsModel->countManufacturer($country,$fileSegmentation['manufacturer'],$fileSegmentation['product']),
			'CategorySeg'		=> json_encode($fileSegmentation['categories']),
			'CategoryCount'		=> $this->productsModel->countCategories($country,$fileSegmentation['categories'],$fileSegmentation['product']),
			'Manufacturers'		=> $this->productsModel->getManufacturer($country),
			'Categories'		=> $this->productsModel->getCategories($country),
		]);		
		/* echo json_encode($data);
		exit; */
		return view('piTool/Manufacturer', $data);
	}
	public function Categories()
	{		
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/segmentation.json",true);	
		}
		if(!isset($fileSegmentation['manufacturer'])){$fileSegmentation['manufacturer']=[];}
		if(!isset($fileSegmentation['categories'])){$fileSegmentation['categories']=[];}
		if(!isset($fileSegmentation['product'])){$fileSegmentation['product']=[];}

		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';

		$data = array_merge($this->data, [
			'title' 	        => 'Products Segmentation',
			'Products'		    => $this->productsModel->getProducts($country),
			'ProductsSeg'		=> json_encode($fileSegmentation['product']),
			'ManufacturerSeg'	=> json_encode($fileSegmentation['manufacturer']),
			'ManufacturerCount' => $this->productsModel->countManufacturer($country,$fileSegmentation['manufacturer'],$fileSegmentation['product']),
			'CategorySeg'		=> json_encode($fileSegmentation['categories']),
			'CategoryCount'		=> $this->productsModel->countCategories($country,$fileSegmentation['categories'],$fileSegmentation['product']),
			'Manufacturers'		=> $this->productsModel->getManufacturer($country),
			'Categories'		=> $this->productsModel->getCategories($country),
			'CategoriesIngram'	=> $this->productsModel->getCategoriesIngram($country),
			
		]);		
		/* echo json_encode($data);
		exit; */
		return view('piTool/Categories', $data);
	}
	public function putStorage($fileSegmentation){
		$country = session()->get('country');
		if(!isset($fileSegmentation['manufacturer'])){$fileSegmentation['manufacturer']=null;}
		if(!isset($fileSegmentation['categories'])){$fileSegmentation['categories']=null;}
		if(!isset($fileSegmentation['product'])){$fileSegmentation['product']=null;}
		$data = [
			'Products'		    => $this->storageModel->getProducts($country,$fileSegmentation['manufacturer'],$fileSegmentation['categories'],$fileSegmentation['product']),
			'ProductsExclude'	=> $fileSegmentation['product'],
			'ManufacturerSeg'	=> $fileSegmentation['manufacturer'],
			'ManufacturerCount' => $this->storageModel->countManufacturer($country,$fileSegmentation['manufacturer'],$fileSegmentation['product']),
			'CategorySeg'		=> $fileSegmentation['categories'],
			'CategoryCount'		=> $this->storageModel->countCategories($country,$fileSegmentation['categories'],$fileSegmentation['product']),
			'Manufacturers'		=> $this->storageModel->getManufacturer($country,$fileSegmentation['manufacturer']),
			'Categories'		=> $this->storageModel->getCategories($country,$fileSegmentation['categories']),
		];
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'products.json')){
			$fileProducts = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/products.json",true);	
			if(isset($fileProducts["Products"]["products"])){
				///actualiza lo que ya esta///////////////////////
				if(count($fileProducts["Products"]["products"])>0){
					$arraySkuData = array_column($data["Products"]["products"],"sku");
					foreach($fileProducts["Products"]["products"] as $product){
						$indexProductData = array_search($product['sku'],$arraySkuData);
						if($indexProductData){
							$data["Products"]["products"][$indexProductData]['prices_reseller']=$product['prices_reseller'];
							$data["Products"]["products"][$indexProductData]['stock']=$product['stock'];
							$data["Products"]["products"][$indexProductData]['active_ingram']=$product['active_ingram'];
							$data["Products"]["products"][$indexProductData]['message']=$product['message'];
							$data["Products"]["products"][$indexProductData]['warehouse']=$product['warehouse'];					
							$data["Products"]["products"][$indexProductData]['updated_at']=$product['updated_at'];

							if(!is_null($product['title'])){
								$data["Products"]["products"][$indexProductData]['title']=$product['title'];	
							}

							$data["Products"]["products"][$indexProductData]['ProductWidth']=$product['ProductWidth'];
							$data["Products"]["products"][$indexProductData]['ProductHeight']=$product['ProductHeight'];
							$data["Products"]["products"][$indexProductData]['ProductWeight']=$product['ProductWeight'];
							$data["Products"]["products"][$indexProductData]['ProductLength']=$product['ProductLength'];
							$data["Products"]["products"][$indexProductData]['images']=$product['images'];							
							$data["Products"]["products"][$indexProductData]['stock_cost']=$product['stock_cost'];						
							$data["Products"]["products"][$indexProductData]['price']=$product['price'];

							$data["Products"]["products"][$indexProductData]['updated_at_image']=$product['updated_at_image'];
							$data["Products"]["products"][$indexProductData]['ficha_json']=$product['ficha_json'];
							$data["Products"]["products"][$indexProductData]['updated_at_json']=$product['updated_at_json'];
							$data["Products"]["products"][$indexProductData]['ficha_html']=$product['ficha_html'];
							$data["Products"]["products"][$indexProductData]['updated_at_html']=$product['updated_at_html'];

							$data["Products"]["products"][$indexProductData]['updated_at_rc']=$product['updated_at_rc'];						
							$data["Products"]["products"][$indexProductData]['shop_id']=$product['shop_id'];
						}
					}
				}
				/////////////////////////////
				///actualiza lo que no esta actualizado///////////////
				$arrayUpdate = [];
				$arraySkuData = array_column($data["Products"]["products"],"updated_at");
				foreach($arraySkuData as $key=>$value){
					if(is_null($value)){
						$arrayUpdate[]=$data["Products"]["products"][$key]['sku'];
					}
				}
				if(count($arrayUpdate)>0){

					$user = $this->userModel->getUser(false,session()->get('id'));
					$access = json_decode($user['access'],true);	

					putActive(session()->get('country'),session()->get('id'),'prices','start');	

					$functionCallBack = $access['default']."Match";
					$arrayUpdate__ = array_chunk($arrayUpdate,200);
					foreach($arrayUpdate__ as $arrayUpdate_){						
						$pricesProduct = json_decode($access['default']($arrayUpdate_,$access[$access['default']]),true);	
						if(isset($pricesProduct['status'])){
							break;
						}else{
							$pricesProduct_ = $functionCallBack($pricesProduct,$data["Products"]["products"],$arrayUpdate_);							
							foreach($pricesProduct_ as $itemUpdate){	
								$data["Products"]["products"][$itemUpdate['index']]['prices_reseller']=$itemUpdate['price'];
								$data["Products"]["products"][$itemUpdate['index']]['stock']=$itemUpdate['stock'];
								$data["Products"]["products"][$itemUpdate['index']]['active_ingram']=$itemUpdate['active'];
								$data["Products"]["products"][$itemUpdate['index']]['message']=$itemUpdate['message'];
								$data["Products"]["products"][$itemUpdate['index']]['warehouse']=$itemUpdate['warehouse'];
								$data["Products"]["products"][$itemUpdate['index']]['updated_at']=date('Y-m-d H:i');
								
								if(!is_null($itemUpdate['title'])){
									$data["Products"]["products"][$itemUpdate['index']]['title']=$itemUpdate['title'];	
								}
								
								$data["Products"]["products"][$indexProductData]['ProductWidth']=$product['ProductWidth'];
								$data["Products"]["products"][$indexProductData]['ProductHeight']=$product['ProductHeight'];
								$data["Products"]["products"][$indexProductData]['ProductWeight']=$product['ProductWeight'];
								$data["Products"]["products"][$indexProductData]['ProductLength']=$product['ProductLength'];
								$data["Products"]["products"][$indexProductData]['images']=$product['images'];							
								$data["Products"]["products"][$indexProductData]['stock_cost']=$product['stock_cost'];						
								$data["Products"]["products"][$indexProductData]['price']=$product['price'];

								$data["Products"]["products"][$indexProductData]['updated_at_image']=$product['updated_at_image'];
								$data["Products"]["products"][$indexProductData]['ficha_json']=$product['ficha_json'];
								$data["Products"]["products"][$indexProductData]['updated_at_json']=$product['updated_at_json'];
								$data["Products"]["products"][$indexProductData]['ficha_html']=$product['ficha_html'];
								$data["Products"]["products"][$indexProductData]['updated_at_html']=$product['updated_at_html'];
												
								$data["Products"]["products"][$indexProductData]['updated_at_rc']=$product['updated_at_rc'];						
								$data["Products"]["products"][$indexProductData]['shop_id']=$product['shop_id'];
							}							
						}
					}
					putActive(session()->get('country'),session()->get('id'),'prices','finish');
				}
				//////////////////////////////////////////////////////
			}			
		}
		write_file_root('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/','products.json',json_encode($data,JSON_UNESCAPED_UNICODE));
	}
	public function getManufacturer(){
		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';
		return  $this->respond(["data"=>$this->productsModel->getManufacturerPiTool($country)],200);
	}
	public function getCategories(){
		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';
		return  $this->respond(["data"=>$this->productsModel->getCategoriesPiTool($country)],200);
	}
	public function getCategoriesRC(){
		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';
		return  $this->respond(["data"=>$this->productsModel->getCategoriesPiToolRC($country)],200);
	}
	public function getProducts(){
		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';
		$manufacturer = (isset($request['manufacturer']))?$request['manufacturer']:null;
		$category = (isset($request['categories']))?$request['categories']:null;
		return  $this->respond(["data"=>$this->productsModel->getProducts($country,$manufacturer,$category)],200);
	}
	public function setRC(){
		$request = json_decode($this->request->getBody(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS),true);		
		$country = (isset($request['country']))?$request['country']:'mx';
		$manufacturer = (count($request['manufacturer'])>0)?$request['manufacturer']:null;
		$category = (count($request['categories'])>0)?$request['categories']:null;
		return  $this->respond(["data"=>$this->productsModel->setRC($country,$manufacturer,$category,$request['type'])],200);
	}
	public function segmentationManufacturerCategories()
	{
		$processCurrent = verify_process(session()->get('country'),session()->get('id'));
		
		if($processCurrent['status']){
			$segmentationMC = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$fileSegmentation=$segmentationMC;
			if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'segmentation.json')){
				$fileSegmentation = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/segmentation.json",true);			
			}
			$fileSegmentation['manufacturer']=(isset($segmentationMC['manufacturer']))?$segmentationMC['manufacturer']:null;
			$fileSegmentation['categories']=(isset($segmentationMC['categories']))?$segmentationMC['categories']:null;
			$fileSegmentation['product']=(isset($segmentationMC['product']))?$segmentationMC['product']:null;
			write_file_root('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/','segmentation.json',json_encode($fileSegmentation));
			$this->putStorage($fileSegmentation);
			$resturnMessage = ['success','notif_success', '<b>Successfully segmentation</b> '];
		}else{
			$resturnMessage = ['danger','notif_error', '<b>Progress '.$processCurrent['message'].'  , wait an minutes</b> '];
		}
		return  $this->respond($resturnMessage,200);
	}
	public function segmentationProductExclude()
	{
		$processCurrent = verify_process(session()->get('country'),session()->get('id'));
		if($processCurrent['status']){
			$segmentationP = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'segmentation.json')){
				$fileSegmentation = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/segmentation.json",true);	
				if(!isset($fileSegmentation['product'])){$fileSegmentation['product']=[];}	
				if(in_array($segmentationP['product'],$fileSegmentation['product'])){
					$newArray=[];
					foreach($fileSegmentation['product'] as $item){
						if($item!=$segmentationP['product']){
							$newArray[] = $item;
						}
					}
					$fileSegmentation['product']=$newArray;
				}else{
					$fileSegmentation['product'][]=$segmentationP['product'];
				}
			}else{$fileSegmentation['product'][]=$segmentationP['product'];}

			write_file_root('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/','segmentation.json',json_encode($fileSegmentation));
			$this->putStorage($fileSegmentation);
			$resturnMessage = ['success','notif_success', '<b>Successfully product exclude</b> '];
		}else{
			$resturnMessage = ['danger','notif_error', '<b>Progress '.$processCurrent['message'].'  , wait an minutes</b> '];
		}
		return  $this->respond($resturnMessage,200);
	}	
}
