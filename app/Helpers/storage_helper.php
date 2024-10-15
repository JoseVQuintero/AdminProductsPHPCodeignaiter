<?php

if (!function_exists('slugify')){
	function slugify($text)
	{
	// replace non letter or digits by -
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	// trim
	$text = trim($text, '-');

	// remove duplicate -
	$text = preg_replace('~-+~', '-', $text);

	// lowercase
	$text = strtolower($text);

	if (empty($text)) {
		return utf8_encode($text);
	}

	return $text;
	}
}

if (!function_exists('url_exists')) {
    function url_exists($url) { 
        $ch = @curl_init($url); 
        @curl_setopt($ch, CURLOPT_HEADER, TRUE); 
        @curl_setopt($ch, CURLOPT_NOBODY, TRUE); 
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $status = array(); preg_match('/HTTP\/.* ([0-9]+) .*/', @curl_exec($ch) , $status);
        return (isset($status[1]))?($status[1] == 200):false;		
    }
}

if (!function_exists('shopIdIsNotNull')) {
    function shopIdIsNotNull($catalog){
        $search_item = array_filter($catalog, 
            function($item) {
                if(isset($item['shop_id'])){
                    return !is_null($item['shop_id']);
                }
            }
        );
        return $search_item;
    }
}

function putPrices($id,$cron=false,$skus=null)
	{
        $db       = \Config\Database::connect();
        $user = $db->table('users')
				->select('*,users.password,users.usertoken,users.id AS userID,user_role.id AS role_id')
				->join('user_role', 'users.role = user_role.id')
				->where(['users.id' => $id])
				->get()->getRowArray();
        
		$access = json_decode($user['access'],true);	
		
		if(file_exists('./getSegmentation/'.$user['country'].'/'.$id.'/'.'products.json')){

			////que se actualiza//////////
			$fileProducts = read_file_json("getSegmentation/".$user['country'].'/'.$id."/products.json",true,true);
			putActive($user['country'],$id,'prices-api','start');

			$productsFilters = [];
			if(is_null($skus)){
				if(in_array($access['shopdefault'],['woocommerce','opencart'])){
					$productsFilters = shopIdIsNotNull($fileProducts["Products"]["products"]);
				}else{
					$productsFilters = $fileProducts;
				}				
			}else{
				$search_item = array_filter($fileProducts["Products"]["products"], 
					function($item) use($skus){       
						return in_array($item['sku'],$skus);
					}
				);
				$productsFilters = $search_item;
			}

			$products = array_column($productsFilters,"sku");
			$productChunk = array_chunk($products,200);
			//////////////////////////////
		
			$functionCallBack = $access['default']."Match";
			
			foreach($productChunk as $product){	
						
				$pricesProduct = json_decode($access['default']($product,$access[$access['default']]),true);

				if(isset($pricesProduct['status'])){
					return ((!$cron)?$this->respond(['danger','notif_error', $pricesProduct['message']]):['danger','notif_error', $pricesProduct['message']]); 
				}
			
				$pricesProduct_ = $functionCallBack($pricesProduct,$fileProducts["Products"]["products"],$product);
			
				foreach($pricesProduct_ as $itemUpdate){
					$fileProducts["Products"]["products"][$itemUpdate['index']]['price']=number_format($itemUpdate['price'], 2, '.', '');
					$fileProducts["Products"]["products"][$itemUpdate['index']]['stock']=$itemUpdate['stock'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['active_ingram']=$itemUpdate['active'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['message']=$itemUpdate['message'];
					$fileProducts["Products"]["products"][$itemUpdate['index']]['warehouse']=$itemUpdate['warehouse'];
                    $fileProducts["Products"]["products"][$itemUpdate['index']]['updated_at']=returnDate(returnAccess()['zone'][$access['zonedefault']]);
                    if(!is_null($itemUpdate['title'])){
                        $fileProducts["Products"]["products"][$itemUpdate['index']]['title']=$itemUpdate['title'];	
                    }
                }

			
        }
    }

    write_file_root("getSegmentation/".$user['country'].'/'.$id,"/products.json",json_encode($fileProducts,JSON_UNESCAPED_UNICODE));

    if(!is_null($skus)){
        $search_item = array_filter($fileProducts["Products"]["products"], 
            function($item) use($skus){       
                return in_array($item['sku'],$skus);
            }
        );
		
        return $search_item;
    }
	

    return ((!$cron)?$this->respond(['success','notif_success', 'update prices: '.count($fileProducts["Products"]["products"])]):['success','notif_success', 'update prices: '.count($fileProducts["Products"]["products"])]);
}