<?php
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;

use App\Controllers\BaseController;

class Storage extends BaseController
{
	use ResponseTrait;
	public function __construct()
	{
	}
	public function index()
	{				
		if(file_exists('./getSegmentation/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('id')."/segmentation.json",true);	
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
		return view('segmentation/segmentationProducts', $data);
	}
	public function putOptimized($fileSegmentation){
		$country = session()->get('country');
		if(!isset($fileSegmentation['manufacturer'])){$fileSegmentation['manufacturer']=[];}
		if(!isset($fileSegmentation['categories'])){$fileSegmentation['categories']=[];}
		if(!isset($fileSegmentation['product'])){$fileSegmentation['product']=[];}
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
		write_file_root('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/','products.json',json_encode($data,JSON_UNESCAPED_UNICODE));
	}
	public function getProducts(){
		$request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$country = (isset($request['country']))?$request['country']:'mx';
		$manufacturer = (isset($request['manufacturer']))?$request['manufacturer']:null;
		$category = (isset($request['categories']))?$request['categories']:null;

		return json_encode(["data"=>$this->productsModel->getProducts($country,$manufacturer,$category)]);
	}
	public function segmentationManufacturerCategories()
	{
		$segmentationMC = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$fileSegmentation=$segmentationMC;
		if(file_exists('./getSegmentation/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('id')."/segmentation.json",true);
			$fileSegmentation['manufacturer']=(isset($segmentationMC['manufacturer']))?$segmentationMC['manufacturer']:[];
			$fileSegmentation['categories']=(isset($segmentationMC['categories']))?$segmentationMC['categories']:[];
		}
		write_file_root('./getSegmentation/'.session()->get('id').'/','segmentation.json',json_encode($fileSegmentation));

		return json_encode(['success','notif_success', '<b>Successfully segmentation</b> ']);
	}
	public function segmentationProductExclude()
	{
		$segmentationP = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		if(file_exists('./getSegmentation/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('id')."/segmentation.json",true);	
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

		write_file_root('./getSegmentation/'.session()->get('id').'/','segmentation.json',json_encode($fileSegmentation));
		return json_encode(['success','notif_success', '<b>Successfully Exclude Product</b> ']);
	}

	public function putStorage($fileSegmentation,$country,$id){
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

		if(!file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
			$fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
			if(isset($fileProducts["Products"]["products"])){
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
							$data["Products"]["products"][$indexProductData]['prices_reseller']=$product['prices_reseller'];						
							$data["Products"]["products"][$indexProductData]['updated_at']=$product['updated_at'];
						}
					}
				}
			}			
		}

		write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($data,JSON_UNESCAPED_UNICODE));
	}
	
	public function putPrices($id,$cron=false)
	{
		$user = $this->userModel->getUser(false,$id);
		$access = json_decode($user['access'],true);	
		if(!file_exists('./getSegmentation/'.$user['country'].'/'.$id.'/'.'products.json')){
			if(file_exists('./getSegmentation/'.$user['country'].'/'.$id.'/'.'segmentation.json')){
				$fileSegmentation = read_file_json("getSegmentation/".$user['country'].'/'.$id."/segmentation.json",true);	
				$this->putStorage($fileSegmentation,$user['country'],$id);		
				while(!file_exists('./getSegmentation/'.$user['country'].'/'.$id.'/','products.json')){
					sleep(1);
				}
			}		
		}
		
		if(file_exists('./getSegmentation/'.$user['country'].'/'.$id.'/'.'products.json')){
			$fileProducts = read_file_json("getSegmentation/".$user['country'].'/'.$id."/products.json",true);	

			putActive($user['country'],$id,'prices-api','start');

			$products = array_column($fileProducts["Products"]["products"],"sku");
			$productChunk = array_chunk($products,200);
			$functionCallBack = $access['default']."Match";

			foreach($productChunk as $product){	
						
				$pricesProduct = json_decode($access['default']($product,$access[$access['default']]),true);

				if(isset($pricesProduct['status'])){
					putActive($user['country'],$id,'prices-api','finish');
					return ((!$cron)?$this->respond(['danger','notif_error', $pricesProduct['message']]):['danger','notif_error', $pricesProduct['message']]); 
				}

				/* echo json_encode($pricesProduct,JSON_UNESCAPED_UNICODE);
				exit; */

				$pricesProduct_ = $functionCallBack($pricesProduct,$fileProducts["Products"]["products"],$product);
				foreach($pricesProduct_ as $itemUpdate){	
					$fileProducts["Products"]["products"][$itemUpdate['index']]['prices_reseller']=$itemUpdate['price'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['stock']=$itemUpdate['stock'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['active_ingram']=$itemUpdate['active'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['message']=$itemUpdate['message'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['warehouse']=$itemUpdate['warehouse'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['updated_at']=returnDate($access['zone'][$access['zonedefault']]);
					if(!is_null($itemUpdate['title'])){
						$fileProducts["Products"]["products"][$itemUpdate['index']]['title']=$itemUpdate['title'];	
					}
				}
			}
		}

		write_file_root("getSegmentation/".$user['country'].'/'.$id,"/products.json",json_encode($fileProducts,JSON_UNESCAPED_UNICODE));

		putActive($user['country'],$id,'prices-api','finish');

		return ((!$cron)?$this->respond(['success','notif_success', 'update prices: '.count($fileProducts["Products"]["products"])]):['success','notif_success', 'update prices: '.count($fileProducts["Products"]["products"])]);
	}
	
	public function storageUpdateByCron($item,$access,$country,$id){
		$response_=[];
		switch ($item) {
			case 'prices':
				putActive($country,$id,'prices-cron','start');
				$response = $this->putPrices($id,$country,true);
				$response_['prices']['get']=$response;
				if($response){					
					$products = $this->getProductsStorage($id,$country,true);
					$response_['prices']['send']=$this->sendShop($products,$access,$id,$country,'prices');
				}
				putActive($country,$id,'prices-cron','finish');
				break;	
			case 'categories':
				putActive($country,$id,'categories-cron','start');
				$response = $this->getCategories($id,$country,true);
				
				$response_['categories']['get']=$response;
				if($response){					
					$response_['categories']['send']=$this->sendShop($response,$access,$id,$country,'categories');
				}
				putActive($country,$id,'categories-cron','finish');
				break;
			case 'manufacturer':	
				putActive($country,$id,'manufacturer-cron','start');			
				$response = $this->getManufacturer($id,$country,true);
				$response_['manufacturer']['get']=$response;
				if($response){					
					$response_['manufacturer']['send']=$this->sendShop($response,$access,$id,$country,'manufacturer');
				}
				putActive($country,$id,'manufacturer-cron','finish');
				break;
			case 'products':
				putActive($country,$id,'products-cron','start');
				$response = $this->getProductsStorage($id,$country,true);
				$response_['products']['get']=$response;
				if($response){
					$response__ = $this->mapShopIdResponse($response,['shop_id']);
					$response_['products']['send']=$this->sendShop($response__,$access,$id,$country,'products');
				}
				putActive($country,$id,'products-cron','finish');
				break;
			case 'contents':
				putActive($country,$id,'contents-cron','start');
				$response = $this->getContents($id,$country,true);
				$response_['contents']['get']=$response;
				if($response){	
					$response__ = $this->mapDateResponse($response,['updated_at_image','updated_at_html'],$access);
					$response_['contents']['send']=$this->sendShop($response__,$access,$id,$country,'contents');
				}
				putActive($country,$id,'contents-cron','finish');
				break;		
			case 'productsrc':
				putActive($country,$id,'productsrc-cron','start');
				$response = $this->getProductsRC($id,$country,true);
				$response_['productsrc']['get']=$response;
				if($response){					
					$response__ = $this->mapDateResponse($response,['updated_at_rc'],$access);
					$response_['productsrc']['send']=$this->sendShop($response__,$access,$id,$country,'productsrc');
				}
				putActive($country,$id,'productsrc-cron','finish');
				break;	
		}
		return $response_;
	}		

	
	function mapShopIdResponse($catalog,$need){
		$search_item = array_filter($catalog, 
			function($item) use($need){
				return is_null($item[$need[0]]);
			}
		);
		return $search_item;
	}

	function mapDateResponse($catalog,$need,$access,$days=1){
		if(count($need)>1){
			$search_item = array_filter($catalog, 
				function($item) use($need,$access,$days){
					return returnDiffDate('d',$access['zone'][$access['zonedefault']],$item[$need[0]]) <= $days || returnDiffDate('d',$access['zone'][$access['zonedefault']],$item[$need[1]]) <= $days;
				}
			);
		}
		if(count($need)==1){
			$search_item = array_filter($catalog, 
				function($item) use($need,$access,$days){
					return returnDiffDate('d',$access['zone'][$access['zonedefault']],$item[$need[0]]) <= $days;
				}
			);
		}
		return $search_item;
	}

	function sendShop($response,$access,$id,$country,$type){
		if(in_array($access['shopdefault'],['opencart'])){
			return $this->opencartSend($response,$access,$id,$country,$type);
		}

		if(in_array($access['shopdefault'],['woocommerce'])){
			return $this->woocommerceSend($response,$access,$id,$country,$type);
		}

		/*if(in_array($access['shopdefault'],['woocommerce'])){
			return $this->woocommerceSend($response,$access,$type);
		}*/
	}

	function opencartSend($response,$access,$id,$country,$type){
		helper("opencart");
		$functionSend = 'opencart'.$type;
		return $functionSend($response,$access['shop']['opencart'],$id,$country);
	}

	function woocommerceSend($response,$access,$id,$country,$type){
		helper("woocommerce");
		$functionSend = 'woocommerce'.$type;
		return $functionSend($response,$access['shop']['woocommerce'],$id,$country);
	}

	/*function woocommerceSend($response,$access,$type){
		$functionSend = 'woocommerce'.$type;
		return $functionSend($response,$access['shop']['woocommerce']);
	}*/

	function getCategories($id,$country,$cron=false,$access=null){
		if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
			$fileSegmentation = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);		
		}
		if(!$cron){
			return $this->sendShop($fileSegmentation['Categories'],$access,$id,$country,'categories');
		}
		return $fileSegmentation['Categories'];
	}

	function getManufacturer($id,$country,$cron=false,$access=null){
		if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
			$fileSegmentation = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);		
		}
		if(!$cron){
			return $this->sendShop($fileSegmentation['Manufacturers'],$access,$id,$country,'manufacturers');
		}
		return $fileSegmentation['Manufacturers'];
	}

	function getProductsStorage($id,$country,$cron=false,$access=null){
		if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
			$fileSegmentation = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);		
		}
		if(!$cron){
			return $this->sendShop($fileSegmentation['Products']['products'],$access,$id,$country,'products');
		}
		return $fileSegmentation['Products']['products'];
	}

	function getContents($id,$country,$cron=false,$access=null){
		if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
			$fileSegmentation = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);		
		}
		if(!$cron){
			return $this->sendShop($fileSegmentation['Products']['products'],$access,$id,$country,'contents');
		}
		return $fileSegmentation['Products']['products'];
	}

	function getProductsRC($id,$country,$cron=false,$access=null){
		if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
			$fileSegmentation = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);		
		}
		if(!$cron){
			return $this->sendShop($fileSegmentation['Products']['products'],$access,$id,$country,'productsrc');
		}
		return $fileSegmentation['Products']['products'];
	}
}
