<?php
namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class Integration extends BaseController
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
		return view('integration/integrationProducts', $data);
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
		return view('integration/integrationManufacturer', $data);
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
		]);		
		/* echo json_encode($data);
		exit; */
		return view('integration/integrationCategories', $data);
	}
	public function getManufacturer(){
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'products.json')){
			$fileProducts = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/products.json",true);	
		}
		return  $this->respond(["data"=>$fileProducts['Manufacturers']],200);
	}
	public function getCategories(){
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'products.json')){
			$fileProducts = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/products.json",true);	
		}
		$returnCategories=[];
		foreach($fileProducts["Categories"] as $item){			
			$item["parent"]["parent_group"]=$item["parent"]["nombre"];
			$returnCategories[] = $item["parent"];
			foreach($item["child"] as $itemChild){
				$itemChild["parent_group"]=$item["parent"]["nombre"];
				$returnCategories[] = $itemChild;
			}
		}
		return  $this->respond(["data"=>$returnCategories],200);
	}
	public function putStorage($fileSegmentation){
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
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'products.json')){
			$fileProducts = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/products.json",true);	
		}

		$country = (isset($request['country']))?$request['country']:'mx';
		$manufacturer = (isset($request['manufacturer']))?$request['manufacturer']:null;
		$category = (isset($request['categories']))?$request['categories']:null;
		
		$products = array_filter($fileProducts['Products']['products'], function($item) use($manufacturer,$category){
			if(!is_null($manufacturer)){
				$validManufacturer = in_array($item['manufacturer_id'],$manufacturer);
			}else{$validManufacturer=true;}
			if(!is_null($category)){
				$validCategories = in_array($item['category_id'],$category);
			}else{$validCategories=true;}
			return $validCategories && $validManufacturer;
		});
		//$products=[];
		/*if(!is_null($manufacturer)){
			foreach($manufacturer as $id){
				if(isset($fileProducts['Products']['manufacturer'][$id])){
					$products=array_merge($products,$fileProducts['Products']['manufacturer'][$id]);
				}
			}
		}
		if(!is_null($category)){
			foreach($category as $id){
				if(isset($fileProducts['Products']['categories'][$id])){
					$products=array_merge($products,$fileProducts['Products']['categories'][$id]);
				}
			}
		}
		$products=array_unique($products);*/

		$productResponse=[];
		if(isset($fileProducts['Products']['products'])){
			/*$arrayColumn = array_column($fileProducts['Products']['products'],"sku");
			if(count($products)<=0){$products=$arrayColumn;}*/		
			//foreach($products as $product){	
			foreach($products as $productGet){			
				//$indexProduct = array_search($product,$arrayColumn);	

				//$productGet = $fileProducts['Products']['products'][$indexProduct];

				$seed['sku']=$productGet['sku'];
				$seed['partNumber']=$productGet['partNumber'];
				$seed['title']=$productGet['title'];
				$seed['manufacturer']=$productGet['manufacturer'];
				$seed['category']=$productGet['category'];
				$seed['stock']=$productGet['stock'];
				$seed['prices']="P.R.: ".$productGet['price_retail']."<br> P.C.: ".$productGet['price_retail'];
				$seed['dimension']="Wid: ".$productGet['ProductWidth'].", Hei: ".$productGet['ProductHeight']."<br>"."Wei: ".$productGet['ProductWeight'].", Len: ".$productGet['ProductLength'];
				$seed['images']=$productGet['images'];
				$seed['ficha_json']=$productGet['ficha_json'];
				$seed['ficha_html']=$productGet['ficha_html'];

				$productResponse[]=$seed;
			}
		}

		return $this->respond(["data"=>$productResponse],200);
	}
	public function segmentationManufacturerCategories()
	{
		$segmentationMC = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$fileSegmentation=$segmentationMC;
		if(file_exists('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/'.'segmentation.json')){
			$fileSegmentation = read_file_json("getSegmentation/".session()->get('country').'/'.session()->get('id')."/segmentation.json",true);
			$fileSegmentation['manufacturer']=(isset($segmentationMC['manufacturer']))?$segmentationMC['manufacturer']:[];
			$fileSegmentation['categories']=(isset($segmentationMC['categories']))?$segmentationMC['categories']:[];
		}
		write_file_root('./getSegmentation/'.session()->get('country').'/'.session()->get('id').'/','segmentation.json',json_encode($fileSegmentation));
		$this->putStorage($fileSegmentation);
		return  $this->respond(['success','notif_success', '<b>Successfully segmentation</b> '],200);
	}
	public function segmentationProductExclude()
	{
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
		return  $this->respond(['success','notif_success', '<b>Successfully Exclude Product</b> '],200);
	}
	
	
}
