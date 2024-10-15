<?php
require 'vendor/autoload.php';
use Automattic\WooCommerce\Client;

$access_ = null;
$parent_id_new = null;
$catalogs_ = null;

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

function clear_space($string)
{
    $array = explode(' ',$string);  // convierte en array separa por espacios;
    $out =[];
    // quita los campos vacios y pone un solo espacio
    //for ($i=0; $i < count($array); $i++) { 
    foreach($array as $item){
        if(strlen(trim($item))>0) {
            $out[]= trim($item);
        }
    }
    //}
  return  implode(' ',$out);
}

function array_where($catalog,$key,$value){
    $search_item = array_filter($catalog, 
        function($item) use($key,$value){
            return $item[$key] == $value;
        }
    );
    return $search_item;
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

function createWoo($access){    
    $woocommerce = new Client(
        $access['url'], 
        $access['ck'], 
        $access['cs'],
        [
            'wp_api' => true, // Enable the WP REST API integration
            'version' => 'wc/v3', // WooCommerce WP REST API version        
            'verify_ssl' => false,
            'timeout'=> 36000,
        ]
    );
    return $woocommerce;
}

function woocommercestatuscomplete($order,$access){
    set_time_limit(0);    
    $woocommerce = createWoo($access);
    $update_order = [
        'status' => 'completed'
    ];
    return $woocommerce->put('orders/' . $order, $update_order);
}

function woocommercenote($order, $access,$response)
{
    set_time_limit(0);
    $woocommerce = createWoo($access);
    $update_order = [
        'note' => json_encode($response)
    ];

    return $woocommerce->post('orders/' . $order . '/notes', $update_order);
}

function woocommercemanufacturer($manufacturer,$access,$id=null,$country=null,$operation=null,$skus=null,$update=null){
    set_time_limit(0);    
    $woocommerce = createWoo($access);
    $errorBrand=null;
    $updateListManufacturer=[];
    foreach($manufacturer as $brand){    

        if(empty($brand['shop_id'])){$brand['shop_id']=null;}
        if(empty($brand['shop_id_brands'])){$brand['shop_id_brands']=null;}

        if(is_null($brand['shop_id'])|| is_null($brand['shop_id_brands'])){    

            $data_productos["create"][] = 
            [                   
                'name' =>$brand['manufacturer'],                
                'slug' => slugify($brand['manufacturer'])                    
            ];
            
            $object_response_data = $woocommerce->post('products/tags/batch', $data_productos);
            
            if(count($object_response_data->create)>0){                
                foreach($object_response_data->create as $elem){                    
                    $manufacturer_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                    $updateListManufacturer["tags"][$brand['id']]=$manufacturer_shop_id;
                    $updateListManufacturer["tags-name"][$brand['id']]=$brand['manufacturer'];
                }
            }
            
            try{
                
                $errorBrand = $data_productos["create"][0];
                
                $object_response_data_ = $woocommerce->post( 'brands' , $data_productos["create"][0] );

                if(isset($object_response_data_->id)){                        
                    $manufacturer_shop_id=($object_response_data_->id==0)?$object_response_data->error->data->resource_id:$object_response_data_->id;  
                    $updateListManufacturer["brands"][$brand['id']]=$manufacturer_shop_id;
                }

            } catch (Exception $e) {
                //var_dump($e->getMessage());
                /* $bandGet =  $woocommerce->get('brands', ['page' => 3] );
                var_dump($bandGet);
                exit; */

                $page = 1;
                $bandGet= [];
                $exit = false;

                while(!$exit){

                    $tags = $woocommerce->get('brands', ['page' => $page]);
                           
                    if(!empty($tags)){
                        $bandGet = array_merge($bandGet, $tags);
                    }else{
                        $exit = true;
                    }

                    $page++;
                }

                $brandObj = array_values(array_filter(
                    $bandGet,
                    function($item) use($errorBrand){
                        return (strtolower($item->name) == strtolower($errorBrand['name']));
                    }
                ));   

                if(isset($brandObj[0])){
                    $updateListManufacturer["brands"][$brand['id']] = $brandObj[0]->term_id;    
                }    
                   
            }
            
            try {
                
                if(isset($updateListManufacturer["tags"])){
                   /*  if(!isset($updateListManufacturer["brands"][$brand['id']])){
                        $updateListManufacturer["brands"][$brand['id']] = $manufacturer_shop_id;
                    } */
                    updateShopManufacturerId($id,$country,$updateListManufacturer);
                }
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }

            $data_productos["create"] = [];

        }
    }     
    return $updateListManufacturer;  
}

function woocommercetags($tags,$access,$id=null,$country=null,$operation=null, $skus = null, $update = 'all'){
    set_time_limit(0);    
    
    $woocommerce = createWoo($access);
    
    $updateListTags=[];
    foreach($tags as $tag){    
        //if(is_null($tag['shop_id'])){    
            $data_productos["create"][] = 
            [                   
                'name' =>$tag['name'],                
                'slug' => slugify($tag['name'])                    
            ];
        
            $object_response_data = $woocommerce->post('products/tags/batch', $data_productos); 
            
            if(count($object_response_data->create)>0){
                foreach($object_response_data->create as $elem){                    
                    $tag_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                    $updateListTags['tags'][$tag['id']]=$tag_shop_id;
                    $updateListTags['tags-name'][$tag['id']]=$tag['name'];
                    $updateListTags['tags-parent'][$tag['id']]=$tag['parent_id'];
                }
            }           

            if(count($updateListTags)>0){
                updateShopTagsId($id,$country,$updateListTags);
            }

            $data_productos["create"] = [];
        //}
    } 
    
    return $updateListTags;  
}

function woocommercecategories($categories,$access,$id=null,$country=null,$operation=null, $skus = null, $update = 'all'){
    set_time_limit(0);
    
    $woocommerce = createWoo($access);
   
    $updateListCategories=[];
    $updateListCategories['parent']=null;
    $updateListCategories['chield']=null;
    
    
    foreach($categories as $category){  

        if(empty($category['parent']['shop_id'])){$category['parent']['shop_id']=null;}
       
        $data_category=[];

        $parent_shop_id=$category['parent']['shop_id'];
        
        if(is_null($parent_shop_id) || empty($parent_shop_id)){      
            
            $data_category["create"][] =        
            [
                'name' => $category['parent']['nombre']
            ];
          
            $object_response_data = $woocommerce->post('products/categories/batch', $data_category);
            
            if(count($object_response_data->create)>0){
                foreach($object_response_data->create as $elem){                    
                    $parent_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                    $updateListCategories['parent'][$category['parent']['id']]=$parent_shop_id;
                    $updateListCategories['parent-name'][$category['parent']['id']]=$category['parent']['nombre'];
                }
            }                      
        }else{
            /*if(!isset($category['parent']['id'])){
                var_dump($category);
                var_dump($parent_shop_id);
                echo json_encode($categories);
                exit;
            }*/
            if (isset($category['parent']['id'])) {
                $updateListCategories['parent'][$category['parent']['id']] = $parent_shop_id;
                $updateListCategories['parent-name'][$category['parent']['id']] = $category['parent']['nombre'];
            }
        }
        /* echo json_encode($updateListCategories); */
       /*  echo json_encode($category);
        exit; */

        if(isset($category['child'])){
            
            foreach($category['child'] as $chield){

                $data_category=[];
                if(is_null($chield['shop_id']) || empty($chield['shop_id'])){
                    
                    $data_category["create"][] =        
                    [
                        'parent' => $parent_shop_id,
                        'name' => $chield['nombre']
                    ];    

                    $object_response_data = $woocommerce->post('products/categories/batch', $data_category);

                    if(count($object_response_data->create)>0){
                        foreach($object_response_data->create as $elem){                    
                            $chield_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                            $updateListCategories['chield'][$chield['id']]=["parent"=>$parent_shop_id,"chield"=>$chield_shop_id];
                            $updateListCategories['chield-name'][$chield['id']]=$chield['nombre'];
                            $updateListCategories['chield-parent'][$chield['id']]=$chield['parent_id'];
                        }
                         echo json_encode($updateListCategories);
                    }            
                                                                
                }
            }
        }

        if(isset($updateListCategories['chield'])||isset($updateListCategories['parent'])){
            
            updateShopCategoriesId($id,$country,$updateListCategories);

        }   

    }

    return $updateListCategories;

}

/* function woocommerceprices($products, $access, $id = null, $country = null, $operation = null)
{
    set_time_limit(0);
    $woocommerce = createwoocommerce($access);
    $data_productos["update"] = [];

    $cuantos = 100;

    $products = shopIdIsNotNull($products);

    $productsSeg = array_chunk($products, $cuantos);

    helper("storage");

    try {

        foreach ($productsSeg as $products_) {
            //getPrices///////////////////////////////////        
            $productsSKU = array_column($products_, 'sku');
            $productPrices = putPrices($id, true, $productsSKU);
            /////////////////////////////////////////////       
            foreach ($productPrices as $product) {

                if (!is_null($product['shop_id'])) {
                    $data_productos_ = [
                        'id' => $product['shop_id'],
                        'stock_quantity' => $product['stock'],
                        'regular_price' =>  $product['price'],
                        "status" => 'publish',
                        'sale_price' => $product['price'],
                        'stock_status' => (intval($product['price']) > 0 && intval($product['stock']) > 0) ? "instock" : "outofstock",
                        'manage_stock' => true
                    ];
                    $data_productos_['sku'] = $product['sku'];
                    $data_productos["update"][] = $data_productos_;
                }
            }


            $object_response_data = $woocommerce->post('products/batch', $data_productos);

            if (count($object_response_data->update) > 0) {
                foreach ($object_response_data->update as $elem) {
                    if (isset($elem->id)) {
                        if ($elem->id > 0) {
                            $updateListProducts[] = $elem->id;
                        }
                    }
                }
            }

            if (count($updateListProducts) > 0) {
                updateShopProductsPrices($id, $country, $updateListProducts, $access);
                $objct_response_data[] = $object_response_data;
            }
            $data_productos["update"] = [];
            $updateListProducts = [];
        }
    } catch (Exception $e) {
        $log = ((file_exists("getSegmentation/" . $country . '/' . $id . "/log.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/log.json", true) : []);
        $log[] = [returnDate(returnAccess()['zone'][$country], "Y-m-d_H"), $e->getMessage(), $object_response_data];
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log.json", json_encode($log, JSON_UNESCAPED_UNICODE));
    }
} */

if (!function_exists('curl')) {
    function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch,
            CURLOPT_URL,
            $url
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

function activeIngram($access){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $access['urlInt'].'token?username='. $access['userInt'].'&password='. $access['passInt'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode(curl('http://co.bdicentralserver.com/api/productos_active_ingram'), true);
}

function woocommerceprices($products, $access, $id = null, $country = null, $operation =null, $skus = null, $update = 'all')
{   
    set_time_limit(0);

    /*
    $statusInstance = initInstance($country, $id);
    if (isset($statusInstance['error'])) { return $statusInstance;  }
    */

    //$skus=['5346628'];


    $woocommerce = createWoo($access);

    $data_productos["update"] = [];

    $cuantos = 100;

    $storeutility__=1;
    if(!is_null($access['storeutility'])){
        $storeutility__ = is_int((int)$access['storeutility'])? 1+((int)$access['storeutility']/100):1;
    }

    $iva = 1;
    if (!is_null($access['iva'])) {
        $iva = is_int((int)$access['iva']) ? 1 + ((int)$access['iva'] / 100) : 1;
    }

    $utilityspecial = null;
    if (file_exists("getSegmentation/" . $country . '/' . $id . '/utilityspecial.json')) {
        $utilityspecial = json_decode(file_get_contents("getSegmentation/" . $country . '/' . $id . '/utilityspecial.json'), true);
    }

    /* if($country=='co'){
        $ingramActive = activeIngram($access);
    } */
    
    if (is_array($update)) {
        $arraySkuData = array_column($products, "sku");
        foreach ($update as $key=>$product) {    
            $indexProductData = array_search($product['sku'], $arraySkuData);           
            if ($indexProductData) {
                $update[$key]['shop_id'] = $products[$indexProductData]['shop_id'];
                $update[$key]['active'] = true;                
            }else{
                $update[$key]['shop_id'] = null;
                $update[$key]['active'] = false;
            }
        }
        
        $products = $update;
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log_update_woo.json", json_encode($products, JSON_UNESCAPED_UNICODE));
    }else{
        $products = ($update=='all'||is_null($update))?shopIdIsNotNull($products): shopIdIsNotNullAndIsActive($products);
        if(!is_null($skus)){$skus=((is_array($skus))?$skus:explode(",",$skus)); $products =shopSkus($products, $skus);}
    }


    $totalProucts = count($products);
  
    $updateCount = 0;
    $totalActives = 0;   
    

    $productsSeg = array_chunk($products, $cuantos);

    helper("storage");

    try {
        $countUpdate=0;
        
        foreach ($productsSeg as $products_) {
            $countUpdate += count($products_);
            
            //getPrices///////////////////////////////////            
            if(is_array($update)){
                $productPrices = $products_;                
            }else{
                $productsSKU = array_column($products_, 'sku');
                $productPrices = putPrices($id, true, $productsSKU);
                
            }
            
            /////////////////////////////////////////////            
            foreach ($productPrices as $product) {              

                if (!is_null($product['shop_id'])) {

                    ///////////////////////////////
                    //$product['active']=true;
                    //////////////////////////////

                    /////////////////////////////
                    /* if ($country == 'co') {
                        if (!in_array($product['sku'], $ingramActive)) {
                            $product['stock'] = -1;
                        }
                    } */
                    ////////////////////////////
                    $storeutility = $storeutility__;
                    if (!is_null($utilityspecial)) {

                        $storeutility_ = null;
                        foreach ($utilityspecial as $util) {
                            if (isset($util['>='])) {
                                
                                if(!is_null($util['skus'])){

                                    if (in_array($product['sku'], $util['skus']) && (float)$product['price'] >=(float)$util['>=']['value'] ) {
                                        //var_dump('>='.(float)$util['>=']['value']);
                                        $storeutility_ = is_int((int)$util['>=']['utility']) ? 1 + ((int)$util['>=']['utility'] / 100) : 1;
                                        break;
                                    }

                                }else{

                                    $banManufacturer = true;
                                    if (!is_null($util['idmanufacturer'])) {
                                        if ((int)$product['manufacturer_id'] != (int)$util['idmanufacturer']) {
                                            $banManufacturer = false;
                                        }
                                    }

                                    $banCategory = true;
                                    if (!is_null($util['idcategory'])) {
                                        if ((int)$product['category_id'] != (int)$util['idcategory']) {
                                            $banCategory = false;
                                        }
                                    }

                                    if($banCategory && $banManufacturer && (float)$product['price'] >= (float)$util['>=']['value']){
                                        $storeutility_ = is_int((int)$util['>=']['utility']) ? 1 + ((int)$util['>=']['utility'] / 100) : 1;
                                        break;
                                    }

                                }


                            }
                            if (isset($util['<'])) {

                                if (!is_null($util['skus'])){

                                    if (in_array($product['sku'], $util['skus']) && (float)$product['price'] < (float)$util['<']['value']) {
                                        //var_dump('<'.(float)$util['<']['value'].'---'. (float)$product['price']);
                                        $storeutility_ = is_int((int)$util['<']['utility']) ? 1 + ((int)$util['<']['utility'] / 100) : 1;
                                        break;
                                    }

                                }else{

                                    $banManufacturer = true;
                                    if (!is_null($util['idmanufacturer'])) {
                                        if ((int)$product['manufacturer_id'] != (int)$util['idmanufacturer']) {
                                            $banManufacturer = false;
                                        }
                                    }

                                    $banCategory = true;
                                    if (!is_null($util['idcategory'])) {
                                        if ((int)$product['category_id'] != (int)$util['idcategory']) {
                                            $banCategory = false;
                                        }
                                    }

                                    if ($banCategory && $banManufacturer && (float)$product['price'] < (float)$util['<']['value']) {
                                        $storeutility_ = is_int((int)$util['<']['utility']) ? 1 + ((int)$util['<']['utility'] / 100) : 1;
                                        break;
                                    }


                                }


                            }
                        }

                        if (!is_null($storeutility_)) {
                            $storeutility = $storeutility_;
                        }

                    }

                    
                   

                    if (is_array($update)) {
                        $data_productos_ = [
                            'id' => $product['shop_id'],
                            'stock_quantity' => ((setBoolean($product['active'])) ? $product['stock'] : -1),
                            'regular_price' => /*((setBoolean($product['active'])) ?*/ number_format(((float)$product['price'] * (float)$storeutility * (float)$iva), 2, '.', '') /*: 0)*/,
                            "status" => 'publish',
                            'sale_price' => /*((setBoolean($product['active'])) ?*/ number_format(((float)$product['price'] * (float)$storeutility * (float)$iva), 2, '.', '') /*: 0)*/,
                            'stock_status' => ((setBoolean($product['active'])) ? ((intval($product['price']) > 0 && intval($product['stock']) > 0) ? "instock" : "outofstock") : "outofstock"),
                            'manage_stock' => true
                        ];
                    }else{
                        $data_productos_ = [
                            'id' => $product['shop_id'],
                            'stock_quantity' => $product['stock'],
                            'regular_price' => /*((setBoolean($product['active'])) ?*/ number_format(((float)$product['price'] * (float)$storeutility * (float)$iva), 2, '.', '') /*: 0)*/,
                            "status" => 'publish',
                            'sale_price' => /*((setBoolean($product['active'])) ?*/ number_format(((float)$product['price'] * (float)$storeutility * (float)$iva), 2, '.', '') /*: 0)*/,
                            'stock_status' =>((intval($product['price']) > 0 && intval($product['stock']) > 0) ? "instock" : "outofstock"),
                            'manage_stock' => true
                        ];
                    }

                  
                    /* var_dump($product['price'].' -- '.$storeutility.' -- '.(float)$iva);
                    var_dump($data_productos_['regular_price']);
                    exit; */

                    $data_productos_['sku'] = $product['sku'];
                    $data_productos["update"][] = $data_productos_;

                    if(setBoolean($product['active'])){ $totalActives+=1; var_dump($product['sku']); }

                    /* if(file_exists("getSegmentation/" . $country . '/' . $id . "/"."update_prices_response.json")){
                        $update_prices_response=json_decode(file_get_contents("getSegmentation/" . $country . '/' . $id . "/" . "update_prices_response.json"),true);
                        $update_prices_response[] = $data_productos;
                    }else{
                        $update_prices_response=[];
                        $update_prices_response[] = $data_productos;
                    }

                    file_put_contents("getSegmentation/" . $country . '/' . $id . "/" . "update_prices_response.json", json_encode($update_prices_response)); */
                    //exit;
                }

            }
            
            
            if(isset($data_productos["update"])){
                if (count($data_productos["update"])>0) {
                    //var_dump($data_productos);
                    $object_response_data = $woocommerce->post('products/batch', $data_productos);

                }
            }           

            if (count($object_response_data->update) > 0) {
                foreach ($object_response_data->update as $elem) {
                    if (isset($elem->id)) {
                        if ($elem->id > 0) {
                            $updateListProducts[] = $elem->id;
                        }
                    }
                }
            }

            if (count($updateListProducts) > 0) {
                updateShopProductsPrices($id, $country, $updateListProducts, $access);
                $updateCount += count($updateListProducts);               
                updateInstance($country, $id);
                progressProcess($country, $id, $totalProucts, $updateCount);

                
                $responseList=[];
                foreach($object_response_data->update as $element_){
                    $element=(array)$element_;
                    $responseList[]=[
                        "id"=> $element['id'],
                        "date_modified"=> $element['date_modified'],
                        "sku" => $element['sku'],
                        "price" => $element['price'],
                        "stock_quantity" => $element['stock_quantity'],
                        "stock_status" => $element['stock_status'],
                    ];
                }
                write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log_update_woo_response.json", json_encode($responseList, JSON_UNESCAPED_UNICODE));
                $objct_response_data[] = $responseList;
            }
            $data_productos["update"] = [];
            $updateListProducts = [];
        }

        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log_update_woo_response_success_" . date("Ymd_Hi") . ".json", json_encode($objct_response_data, JSON_UNESCAPED_UNICODE));
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log_update_woo_response_success_count_" . date("Ymd_Hi") . ".json", json_encode([count($objct_response_data)], JSON_UNESCAPED_UNICODE));
        if (isset($access['emailto'])) {
            if (!empty($access['emailto'])) {

                helper('email');
                $utilityIva = json_encode(["utilidadespecial" => $utilityspecial ,"utilidad"=> ((isset($access['storeutility']))?$access['storeutility']:'Sin'),"iva"=> ((isset($access['iva']))?$access['iva']:'Sin')], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $html = json_encode((is_array($update)? $update: $objct_response_data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                //var_dump($html);
                getSend($access, $utilityIva.' - '. $html,"Update Prices");

            }
        }

       
    } catch (Exception $e) {
        $log = ((file_exists("getSegmentation/" . $country . '/' . $id . "/log.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/log.json", true) : []);
        $log[] = [returnDate(returnAccess()['zone'][$country], "Y-m-d_H") => $e];
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log.json", json_encode($log, JSON_UNESCAPED_UNICODE));
        write_file_root("progress/" . $country . '/' . $id . "/", "processprogress.txt", $e->getMessage());
        outInstance($country, $id);
        return ["status" => "error", "msg" => $e];
    }
    outInstance($country,$id );
    return  ["success" => true, "msg" => "Price Update Finish", "countActives"=> $totalActives,"update"=> $updateCount];
}


function woocommerceproductsrc($products, $access, $id = null, $country = null, $operation = 'create', $skus = null, $update = 'all')
{
    global $access_;
    global $parent_id_new;
    global $catalogs_;
    $catalogs_ = getCatalog($id, $country);
    $access_ = $access;
    set_time_limit(0);

    /*$statusInstance = initInstance($country, $id);
    if (isset($statusInstance['error'])) {return $statusInstance;}*/

    $woocommerce = createWoo($access);

    $data_productos = [];

    $cuantos = 20;

    if (!is_null($skus)) {
        if (!is_array($skus)) {
            $skus = explode(',', $skus);
        }

        $products = shopSkus($products, $skus);
        /* if(count($products)>0){
            $operation='update';
        } */
    } else {
        $products = shopIdIsNotNullAndIsActive($products);
        /* if(in_array($operation,['create'])){$products = shopIdIsNullAndIsActive($products);}
        if(in_array($operation,['update'])){$products = shopIdIsNotNullAndIsActive($products);} */
    }

    //$products = shopSkus($products, ['725PPV']);
    /* echo json_encode(array_column($products,'sku'));
    exit; */
    $totalProucts = count($products);
    $updateCount = 0;

    $productsSeg = array_chunk($products, $cuantos);

    //helper("storage");
    $failAdd = [];
    $successAdd = [];
    try {



    foreach ($productsSeg as $products_) {
        //getPrices///////////////////////////////////        
        //$productsSKU = array_column($products_, 'sku');
        //$productPrices = putPrices($id, true, $productsSKU);
        /////////////////////////////////////////////  
        foreach ($products_ as $product) {

            //if (is_null($product['shop_id']) && $operation == "update") { continue; }
            //if (!is_null($product['shop_id']) && $operation == "create") { continue; }

            /*$info = $product['ficha_html'];
            $array_img = null;*/



            /*if (is_null($product['ficha_html']) || empty($product['ficha_html']) || is_null($product['images']) || empty($product['images']) ||
                    is_null($product['ProductWeight']) || is_null($product['ProductLength']) || is_null($product['ProductWidth']) || is_null($product['ProductHeight'])) {*/

            $contentsSet = extractContents($product['sku'], $country);

            if (!is_null($contentsSet)) {

                if (!is_null($contentsSet['dim']['ProductWeight'])) {
                    $product['ProductWeight'] = $contentsSet['dim']['ProductWeight'];
                    $product['ProductLength'] = $contentsSet['dim']['ProductLength'];
                    $product['ProductWidth'] = $contentsSet['dim']['ProductWidth'];
                    $product['ProductHeight'] = $contentsSet['dim']['ProductHeight'];
                }

                if (!is_null($contentsSet['contentsTitle'])) {
                    $product['title'] = $contentsSet['contentsTitle'];
                }

                /*$info = $contentsSet['contentsHTML'];
                if (!is_null($contentsSet['contentsImages']['imageGalleryUrlHigh'])) {
                    $imagesList = $contentsSet['contentsImages']['imageGalleryUrlHigh'];

                    foreach ($imagesList as $item) {
                        if (url_exists($item)) {
                            $array_img[] = ["src" => $item];
                        }
                    }
                }*/
            }
            //}

            /*if (!is_null($product['images']) && !empty($product['images']) && is_null($array_img)) {
                $imagesList = json_decode($product['images'], true);
                $imagesList = (isset($imagesList['imageGalleryUrlHigh'])) ? $imagesList['imageGalleryUrlHigh'] : $imagesList;
                foreach ($imagesList as $item) {
                    if (url_exists($item)) {
                        $array_img[] = ["src" => $item];
                    }
                }
            }*/
            $categories = [];
            $tags = [];
            $functionCallBack = "RC" . $country;
            $rcSet = $functionCallBack($product['sku'], true);



            if (!is_null($rcSet)) {
                $product["category_id"] = $rcSet[$product['sku']]['id_subcategory'];
                $product["manufacturer_id"] = $rcSet[$product['sku']]['id_manufacturer'];

                $categories[] = ['id' => setIdShop($country, $rcSet[$product['sku']], $catalogs_['Categories'], 'category', $id, 'id_category')];
                $parent_id_new = $categories[0]['id'];

                $categories[] = ['id' => setIdShop($country, $product["category_id"], $catalogs_['Categories'], 'subcategory', $id)];
                $tags[] = ['id' => setIdShop($country, $rcSet[$product['sku']], $catalogs_['Categories'], 'tags', $id, 'id_line')];
            }

            $tags[] = ['id' => setIdShop($country, $product["manufacturer_id"], $catalogs_['Manufacturers'], 'manufacturer', $id)];

            if (empty($categories)) {
                $updateCount += 1;
                updateInstance($country, $id);
                progressProcess($country, $id, $totalProucts, $updateCount);
                $failAdd[] = ['CategoryFail' => [$product['sku'], $categories, $tags]];
                continue;
            }
            if (empty($tags)) {
                $updateCount += 1;
                updateInstance($country, $id);
                progressProcess($country, $id, $totalProucts, $updateCount);
                $failAdd[] = ['TagsFail' => $product['sku'], $categories, $tags];
                continue;
            }



            //if (is_null($product['shop_id'])) {
            $data_productos_ = [
                'name' => $product['title'],
                //'stock_quantity' => $product['stock'],
                //'regular_price' =>  $product['price'],
                //"status" => 'publish',
                //'sale_price' => $product['price'],
                //'stock_status' => (intval($product['price']) > 0 && intval($product['stock']) > 0) ? "instock" : "outofstock",
                //'manage_stock' => true,
                'categories' => $categories,
                //'images' =>  $array_img,
                //'description' => $info,
                'tags' => $tags,
                'brands' => ['id' => setIdShop($country, $product["manufacturer_id"], $catalogs_['Manufacturers'], 'manufacturer', null, null, "shop_id_brands")],
                'weight' => $product['ProductWeight'],
                'dimensions' => (object)[
                    'length' => $product['ProductLength'],
                    'width' => $product['ProductWidth'],
                    'height' => $product['ProductHeight']
                ]
            ];
            if (!is_null($product['shop_id'])) {
                $data_productos_['shop_id'] = $product['shop_id'];
                $data_productos_['id'] = $product['shop_id'];
                //$data_productos_['sku'] = $product['sku'];
                $data_productos['update'][] = $data_productos_;
            } else {
                //$data_productos_['sku'] = $product['sku'];
                //$data_productos['create'][] = $data_productos_;
            }
            //}
        }



        $updateListProducts = [];
        //$createListProducts = [];
        if (count($data_productos) > 0) {

            $object_response_data = $woocommerce->post('products/batch', $data_productos);


            /*if (isset($object_response_data->create)) {
                foreach ($object_response_data->create as $elem) {
                    if (isset($elem->id)) {
                        $product_id = ($elem->id == 0) ? ((isset($elem->error->data->resource_id)) ? $elem->error->data->resource_id : 0) : $elem->id;
                        if ($elem->id == 0) {
                            if ($product_id > 0) {
                                $productDuplicate = $woocommerce->get('products/' . $product_id);
                                $createListProducts[$productDuplicate->sku] = $product_id;
                            }
                        } else {
                            $createListProducts[$elem->sku] = $elem->id;
                        }
                    }
                }
            }*/

            if (isset($object_response_data->update)) {
                foreach ($object_response_data->update as $elem) {
                    if (isset($elem->id)) {
                        $product_id = ($elem->id == 0) ? ((isset($elem->error->data->resource_id)) ? $elem->error->data->resource_id : 0) : $elem->id;
                        if ($elem->id == 0) {
                            if ($product_id > 0) {
                                $productDuplicate = $woocommerce->get('products/' . $product_id);
                                $updateListProducts[$productDuplicate->sku] = $product_id;
                            }
                        } else {
                            $updateListProducts[$elem->sku] = $elem->id;
                        }
                    }
                }
            }

            if (count($updateListProducts) > 0) {

                updateShopProductsRC($id, $country, $updateListProducts, $access);

                $updateCount += count($updateListProducts);
                updateInstance($country, $id);
                progressProcess($country, $id, $totalProucts, $updateCount);

                $objct_response_data[] = $object_response_data;
                $successAdd[] = $updateListProducts;
            }

            /*if (count($createListProducts) > 0) {

                updateShopProductsId($id, $country, $createListProducts, $access);

                $updateCount += count($createListProducts);
                updateInstance($country, $id);
                progressProcess($country, $id, $totalProucts, $updateCount);

                $objct_response_data[] = $object_response_data;
                $successAdd[] = $createListProducts;
            }*/
        }
        $data_productos = [];
        $updateListProducts = [];
    }
    } catch (Exception $e) {
        $log = ((file_exists("getSegmentation/" . $country . '/' . $id . "/log.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/log.json", true) : []);
        $log[] = [returnDate(returnAccess()['zone'][$country],"Y-m-d_H")=>$e];
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log.json", json_encode($log, JSON_UNESCAPED_UNICODE));
        write_file_root("progress/" . $country . '/' . $id . "/", "processprogress.txt", $e->getTraceAsString());
        outInstance($country, $id);
        return ["status" =>"error" , "msg" =>  $e->getTraceAsString()];
    }
    outInstance($country, $id);
    return  ["status" => "success", "msg" => "Product Add Finish", "add" => $successAdd, "fail" => $failAdd];
}

function woocommerceproducts($products, $access, $id = null, $country = null, $operation = 'create', $skus = null, $update = 'all'){
    
    global $access_;
    global $parent_id_new;
    global $catalogs_;
    $catalogs_ = getCatalog($id, $country);
    $access_ = $access;
    
    set_time_limit(0);

    tokenBdi($access);
    if(isset($access['urlInt'])){    
        tokenClient($access);
    }
    
    $storeutility__ = 1;
    if (!is_null($access['storeutility'])) {
        $storeutility__ = is_int((int)$access['storeutility']) ? 1 + ((int)$access['storeutility'] / 100) : 1;
    }

    $iva = 1;
    if (!is_null($access['iva'])) {
        $iva = is_int((int)$access['iva']) ? 1 + ((int)$access['iva'] / 100) : 1;
    }

    $utilityspecial=null;
    if (file_exists("getSegmentation/" . $country . '/' . $id . '/utilityspecial.json')) {
        $utilityspecial = json_decode(file_get_contents("getSegmentation/" . $country . '/' . $id . '/utilityspecial.json'), true);
    }
    
    /*$statusInstance = initInstance($country, $id);
    if (isset($statusInstance['error'])) {return $statusInstance;}*/

    $woocommerce = createWoo($access);

    $data_productos = [];

    $cuantos =1;

    $contentsSetBatch=[];
    
    if(!is_null($skus)){

        if(!is_array($skus)){$skus=explode(',', $skus);}
        $products = shopSkus($products, $skus);

        /* if(count($products)>0){
            $operation='update';
        } */     

    }else{

        //$products = shopIdIsNullAndIsActive($products);
     
        //$products = shopIdIsNull($products);

        $products = shopIdIsNullDayOut($products);

  
        $skusContents = array_column($products,'sku');
        $contentsSetBatch = extractContentsBatch($skusContents, $country, null, $access);
        /* 
            var_dump($contentsSetBatch);
            exit; 
        */
        
        //$contentsSetBatch = array_chunk($contentsSetBatch,10)[0];
        /* if(in_array($operation,['create'])){$products = shopIdIsNullAndIsActive($products);}
        if(in_array($operation,['update'])){$products = shopIdIsNotNullAndIsActive($products);} */
    }    
   
    $skusProducts = array_column($products,'sku');

    //$products = shopSkus($products, ['3086479', '4200134', '4835747', '5026953', '5026954', '5026966', '5026967', '5026968', '5026997', '5026998', '5026999', '5027001', '5027007', '5027008', '5027009', '5027010', '5027012', '4312898', '3086478', '4173214', '4610247', '4610252', '4610258', '4866900', '4866902', '4866904', '4866975', '4866976', '5046143', '5046146', '5199176', '5199177', '4312890', '4312891', '4312892', '4954306', '3682856', '4831090', '4831093', '4461906', '4461909', '4461912', '4461918', '4461921', '4610172', '4610173', '4610174', '4610215', '4831197', '4831199', '4831201', '4831203', '4831205', '4831207', '4831209', '4831211', '4831213', '4831215', '4831217', '4831219', '4831226', '4831228', '4831230', '4831232', '5027025', '5027026', '5027027', '5027028', '5027029', '5027033', '5027045', '5027046', '5027047', '5027048', '5027049', '5027050', '5027051', '4681714', '4682125', '4880975', '4880978', '4880979', '5114202', '4831221', '4972273', '4972274', '4972455', '4972456', '4972457', '4972458', '4972459', '2839996', '2837864', '4682129', '4348223', '4374462', '4461963', '4616183', '4312893', '2840015', '3966276', '3966277', '3966278', '4878504', '3411132', '4682126', '5114201', '4512846', '4688100', '4507930', '4641700']);
/* echo json_encode(array_column($products,'sku'));
    exit; */
    
    $totalProucts = count($products);
    $updateCount = 0;

    

    

    $productsSeg = array_chunk($products, $cuantos);

    helper("storage");
    $failAdd=[];
    $successAdd=[];
    try {
        
        foreach ($productsSeg as $products_) {
            if (!in_array($update, ['imagenes','fichas', 'contents'])) {
                //getPrices///////////////////////////////////        
                $productsSKU = array_column($products_, 'sku');
               
                $productPrices = putPrices($id, true, $productsSKU);
                 
                /////////////////////////////////////////////  


                
            }else{
                $productPrices= $products_;
            }


            
            
            foreach ($productPrices as $product) {
                //if (is_null($product['shop_id']) && $operation == "update") { continue; }
                //if (!is_null($product['shop_id']) && $operation == "create") { continue; }
                 
                $info = $product['ficha_html'];
                $array_img = null;

                $contentsSet=null;

                /*if (is_null($product['ficha_html']) || empty($product['ficha_html']) || is_null($product['images']) || empty($product['images']) ||
                    is_null($product['ProductWeight']) || is_null($product['ProductLength']) || is_null($product['ProductWidth']) || is_null($product['ProductHeight'])) {*/
                
                    if(empty($contentsSetBatch)){               

                        $contentsSet = extractContents($product['sku'], $country,null, $access);
                   
                        if (is_null($contentsSet)|| in_array($update, ['reset'])) {                        
                            if(isset($access['urlInt']) && isset($access['userInt'])){
                                if (!empty($access['urlInt']) && !empty($access['userInt'])) {
                                    $contentsSet = extractContentsBDI($product['sku'], $country,null,$access);
                                }
                            }
                        }
                        
                    }else{

                        $skusUse = $product['sku'];

                        /* $contentsSet = $contentsSet[$skusUse];

                        $contentsSet = array_values(array_filter($contentsSetBatch, function($item) use($skusUse) {
                            return $item['productDetail']['sku'] == $skusUse;
                        })); */

                        if(isset($contentsSetBatch[$skusUse])){ $contentsSet = $contentsSetBatch[$skusUse];}else{$contentsSet=null; }

                        

                    }

                


                    if (!is_null($contentsSet)) {

                        
                        if (!is_null($contentsSet['dim']['ProductWeight'])) {
                            $product['ProductWeight'] = $contentsSet['dim']['ProductWeight'];
                            $product['ProductLength'] = $contentsSet['dim']['ProductLength'];
                            $product['ProductWidth'] = $contentsSet['dim']['ProductWidth'];
                            $product['ProductHeight'] = $contentsSet['dim']['ProductHeight'];
                        }

                        if (!is_null($contentsSet['contentsTitle'])) {
                            $product['title'] = (strpos('X-CUSTOMER NOT', $contentsSet['contentsTitle'])!==false)?$contentsSet['contentsTitle']: $product['manufacturer'].' '.$product['title'].' '. ((!is_null($product['partNumber']))? $product['partNumber']: $contentsSet['vpn']);
                        }

                        $info = $contentsSet['contentsHTML'];
                        if (!is_null($contentsSet['contentsImages']['imageGalleryUrlHigh'])) {
                            $imagesList = $contentsSet['contentsImages']['imageGalleryUrlHigh'];

                            foreach ($imagesList as $item) {
                                if (url_exists($item)) {
                                    $array_img[] = ["src" => $item];
                                }
                            }
                        }

                    }
                //}

                
               
                if (!is_null($product['images']) && !empty($product['images']) && is_null($array_img)) {
                     
                    $imagesList = json_decode($product['images'], true);                    
                    $imagesList = (isset($imagesList['imageGalleryUrlHigh'])) ? $imagesList['imageGalleryUrlHigh'] : $imagesList;
                    
                    if(is_array($imagesList)){                        
                        foreach ($imagesList as $item) {      
                            if(is_string($item)){          
                                if (url_exists($item)) {
                                    $array_img[] = ["src" => $item];
                                }                        
                            }
                        }
                    }                   

                }

                

                if (!in_array($update, ['imagenes', 'fichas', 'contents'])) {
                
                    $categories = [];
                    $tags = [];
                    $functionCallBack = "RC" . $country;
                    $rcSet = $functionCallBack($product['sku'], true);
                    
                    if (!is_null($rcSet)) {
                        $product["category_id"] = $rcSet[$product['sku']]['id_subcategory'];
                        $product["manufacturer_id"] = $rcSet[$product['sku']]['id_manufacturer'];

                       

                        $categories[] = ['id' => setIdShop($country, $rcSet[$product['sku']], $catalogs_['Categories'], 'category', $id, 'id_category')];
                    
                        
                        $parent_id_new = $categories[0]['id'];                        
 
                        $categories[] = ['id' => setIdShop($country, $product["category_id"], $catalogs_['Categories'], 'subcategory', $id)];

                        $tags[] = ['id' => setIdShop($country, $rcSet[$product['sku']], $catalogs_['Categories'], 'tags', $id, 'id_line')];


                    }
                    
                    $tags[] = ['id' => setIdShop($country, $product["manufacturer_id"], $catalogs_['Manufacturers'], 'manufacturer', $id)];


                    if(empty($categories)){
                        $updateCount += 1;
                        updateInstance($country, $id);
                        progressProcess($country, $id, $totalProucts, $updateCount);
                        $failAdd[]= ['CategoryFail'=>[$product['sku'], $categories, $tags]];
                        var_dump($failAdd);
                        continue;
                    }
                    if(empty($tags)){
                        $updateCount += 1;
                        updateInstance($country, $id);
                        progressProcess($country, $id, $totalProucts, $updateCount);
                        $failAdd[]= ['TagsFail' => $product['sku'], $categories, $tags];
                        var_dump($failAdd);
                        continue;
                    }

                    $storeutility = $storeutility__;
                    if(!is_null($utilityspecial)){

                        $storeutility_=null;
                        foreach($utilityspecial as $util){
                            if (isset($util['>='])) {

                                if (!is_null($util['skus'])) {

                                    if (in_array($product['sku'], $util['skus']) && (float)$product['price'] >= (float)$util['>=']['value']) {
                                        //var_dump('>='.(float)$util['>=']['value']);
                                        $storeutility_ = is_int((int)$util['>=']['utility']) ? 1 + ((int)$util['>=']['utility'] / 100) : 1;
                                        break;
                                    }
                                } else {

                                    $banManufacturer = true;
                                    if (!is_null($util['idmanufacturer'])) {
                                        if ((int)$product['manufacturer_id'] != (int)$util['idmanufacturer']) {
                                            $banManufacturer = false;
                                        }
                                    }

                                    $banCategory = true;
                                    if (!is_null($util['idcategory'])) {
                                        if ((int)$product['category_id'] != (int)$util['idcategory']) {
                                            $banCategory = false;
                                        }
                                    }

                                    if ($banCategory && $banManufacturer && (float)$product['price'] >= (float)$util['>=']['value']) {
                                        $storeutility_ = is_int((int)$util['>=']['utility']) ? 1 + ((int)$util['>=']['utility'] / 100) : 1;
                                        break;
                                    }
                                }
                            }
                            if (isset($util['<'])) {

                                if (!is_null($util['skus'])) {

                                    if (in_array($product['sku'], $util['skus']) && (float)$product['price'] < (float)$util['<']['value']) {
                                        //var_dump('<'.(float)$util['<']['value'].'---'. (float)$product['price']);
                                        $storeutility_ = is_int((int)$util['<']['utility']) ? 1 + ((int)$util['<']['utility'] / 100) : 1;
                                        break;
                                    }
                                } else {

                                    $banManufacturer = true;
                                    if (!is_null($util['idmanufacturer'])) {
                                        if ((int)$product['manufacturer_id'] != (int)$util['idmanufacturer']) {
                                            $banManufacturer = false;
                                        }
                                    }

                                    $banCategory = true;
                                    if (!is_null($util['idcategory'])) {
                                        if ((int)$product['category_id'] != (int)$util['idcategory']) {
                                            $banCategory = false;
                                        }
                                    }

                                    if ($banCategory && $banManufacturer && (float)$product['price'] < (float)$util['<']['value']) {
                                        $storeutility_ = is_int((int)$util['<']['utility']) ? 1 + ((int)$util['<']['utility'] / 100) : 1;
                                        break;
                                    }
                                }
                            }
                        }

                        if(!is_null($storeutility_)){
                            $storeutility = $storeutility_;
                        }

                    }

                   
                    $data_productos_ = [
                        'name' => $product['title'],
                        'stock_quantity' => $product['stock'],
                        'regular_price' =>  /*((setBoolean($product['active'])) ?*/ number_format(((float)$product['price'] * (float)$storeutility * (float)$iva), 2, '.', '') /*: 0)*/,
                        "status" => 'publish',
                        'sale_price' => /*((setBoolean($product['active'])) ?*/ number_format(((float)$product['price'] * (float)$storeutility * (float)$iva), 2, '.', '') /*: 0)*/,
                        'stock_status' => (intval($product['price']) > 0 && intval($product['stock']) > 0) ? "instock" : "outofstock",
                        'manage_stock' => true,
                        'categories' => $categories,
                        'images' =>  $array_img,
                        'description' => $info,
                        'tags' => $tags,
                        'brands' => ['id' => setIdShop($country, $product["manufacturer_id"], $catalogs_['Manufacturers'], 'manufacturer', null, null, "shop_id_brands")],
                        'weight' => $product['ProductWeight'],
                        'dimensions' => (object)[
                            'length' => $product['ProductLength'],
                            'width' => $product['ProductWidth'],
                            'height' => $product['ProductHeight']
                        ]
                    ];
                    

                    if(!is_null($product['shop_id'])){
                        $data_productos_['shop_id'] = $product['shop_id'];
                        $data_productos_['id'] = $product['shop_id'];
                        //$data_productos_['sku'] = $product['sku'];
                        $data_productos['update'][] = $data_productos_;
                    }else{
                        $data_productos_['sku'] = $product['sku'];
                        $data_productos['create'][] = $data_productos_;
                    }

                    
                
                }else{
                    

                    if (!is_null($product['shop_id'])) {

                        $data_productos_ = [                            
                            "status" => 'publish',                     
                        ];

                        if (in_array($update, ['imagenes', 'contents'])) {
                            $data_productos_['images'] = $array_img;
                        }
                       
                        if (in_array($update, ['fichas', 'contents'])) {
                            $data_productos_['description'] = $info;
                        }

                        $data_productos_['shop_id'] = $product['shop_id'];
                        $data_productos_['id'] = $product['shop_id'];
                        //$data_productos_['sku'] = $product['sku'];
                        $data_productos['update'][] = $data_productos_;

                    }

                }
            }

            /* var_dump($data_productos);
            exit; */

            $updateListProducts=[];
            $createListProducts = [];
            if(count($data_productos)>0){


                
                
                $object_response_data = $woocommerce->post('products/batch', $data_productos);
                
                
                if (!in_array($update, ['imagenes', 'fichas', 'contents'])) {

                    if(isset($object_response_data->create)){
                        foreach ($object_response_data->create as $elem) {
                            if (isset($elem->id)) {
                                $product_id = ($elem->id == 0) ? ((isset($elem->error->data->resource_id)) ? $elem->error->data->resource_id : 0) : $elem->id;
                                if ($elem->id == 0) {
                                    if ($product_id > 0) {
                                        $productDuplicate = $woocommerce->get('products/' . $product_id);
                                        $createListProducts[$productDuplicate->sku] = $product_id;
                                    }
                                } else {
                                    $createListProducts[$elem->sku] = $elem->id;
                                }
                            }
                        }
                    }
    
                    if (isset($object_response_data->update)) {
                        foreach ($object_response_data->update as $elem) {
                            if (isset($elem->id)) {
                                $product_id = ($elem->id == 0) ? ((isset($elem->error->data->resource_id)) ? $elem->error->data->resource_id : 0) : $elem->id;
                                if ($elem->id == 0) {
                                    if ($product_id > 0) {
                                        $productDuplicate = $woocommerce->get('products/' . $product_id);
                                        $updateListProducts[$productDuplicate->sku] = $product_id;
                                    }
                                } else {
                                    $updateListProducts[$elem->sku] = $elem->id;
                                }
                            }
                        }
                    }               

                    if (count($updateListProducts) > 0) {                    
                        
                        updateShopProductsRC($id, $country, $updateListProducts, $access);

                        $updateCount += count($updateListProducts);
                        updateInstance($country, $id);
                        progressProcess($country, $id, $totalProucts, $updateCount);

                        $objct_response_data[] = $object_response_data;
                        $successAdd[] = $updateListProducts;
                    }

                    if (count($createListProducts) > 0) {

                        updateShopProductsId($id, $country, $createListProducts, $access);

                        $updateCount += count($createListProducts);
                        updateInstance($country, $id);
                        progressProcess($country, $id, $totalProucts, $updateCount);

                        $objct_response_data[] = $object_response_data;
                        $successAdd[] = $createListProducts;
                    }
                }else{
                    $skusproductPrices=array_column($productPrices,'sku');
                    $skusProducts=array_filter($skusProducts, function($sku) use($skusproductPrices){
                        return !in_array($sku,$skusproductPrices);
                    });
                    write_file_root("getSegmentation/" . $country . '/' . $id . "/", "skuscontentspass_array.txt", implode(',', $skusproductPrices));
                    write_file_root("getSegmentation/" . $country . '/' . $id . "/", "skuscontentspass.txt", implode(',', $skusProducts));                    
                }

            }
            //exit;
            $data_productos = [];
            $updateListProducts = [];
            $createListProducts=[];
        }
    } catch (Exception $e) {
        $log = ((file_exists("getSegmentation/" . $country . '/' . $id . "/log.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/log.json", true) : []);
        $log[] = [returnDate(returnAccess()['zone'][$country],"Y-m-d_H")=>$e];
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log.json", json_encode($log, JSON_UNESCAPED_UNICODE));
        write_file_root("progress/" . $country . '/' . $id . "/", "processprogress.txt", $e->getMessage());
        outInstance($country, $id);
        return ["status" =>"error" , "msg" =>  $e->getMessage(). $e->getTraceAsString()];
    }
    outInstance($country, $id);
    return  ["status" => "success", "msg" => "Product Add Finish","add"=> $successAdd,"fail"=> $failAdd];
}

/*function woocommerceprices($products, $access, $id = null, $country = null, $operation = null)
{
    set_time_limit(0);

    initInstance($country, $id);

    $woocommerce = createWoo($access);


    $data_productos["update"] = [];

    $cuantos = 100;

    $products = shopIdIsNotNull($products);

    $totalProucts = count($products);
    $updateCount = 0;

    $productsSeg = array_chunk($products, $cuantos);

    helper("storage");

    try {

        foreach ($productsSeg as $products_) {
            //getPrices///////////////////////////////////        
            $productsSKU = array_column($products_, 'sku');
            $productPrices = putPrices($id, true, $productsSKU);
            /////////////////////////////////////////////       
            foreach ($productPrices as $product) {
                if (!is_null($product['shop_id'])) {
                    $data_productos_ = [
                        'id' => $product['shop_id'],
                        'stock_quantity' => $product['stock'],
                        'regular_price' =>  $product['price'],
                        "status" => 'publish',
                        'sale_price' => $product['price'],
                        'stock_status' => (intval($product['price']) > 0 && intval($product['stock']) > 0) ? "instock" : "outofstock",
                        'manage_stock' => true
                    ];
                    //$data_productos_['sku'] = $product['sku'];
                    $data_productos["update"][] = $data_productos_;
                }
            }


            $object_response_data = $woocommerce->post('products/batch', $data_productos);

            if (count($object_response_data->update) > 0) {
                foreach ($object_response_data->update as $elem) {
                    if (isset($elem->id)) {
                        if ($elem->id > 0) {
                            $updateListProducts[] = $elem->id;
                        }
                    }
                }
            }

            if (count($updateListProducts) > 0) {
                $updateCount += count($updateListProducts);
                updateShopProductsPrices($id, $country, $updateListProducts, $access);
                updateInstance($country, $id);
                progressProcess($country, $id, $totalProucts, $updateCount);

                $objct_response_data[] = $object_response_data;
            }
            $data_productos["update"] = [];
            $updateListProducts = [];
        }
    } catch (Exception $e) {
        $log = ((file_exists("getSegmentation/" . $country . '/' . $id . "/log.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/log.json", true) : []);
        $log[] = [returnDate(returnAccess()['zone'][$country], "Y-m-d_H"), $e->getMessage(), $object_response_data];
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log.json", json_encode($log, JSON_UNESCAPED_UNICODE));
        write_file_root("progress/" . $country . '/' . $id . "/", "processprogress.txt", $e->getMessage());
        outInstance($country, $id);
    }
    outInstance($country, $id);
}*/

/*function woocommerceprices($products,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);
    $woocommerce = createWoo($access);
    $data_productos["update"]=[];

    $products = shopIdIsNotNull($products);
    
    foreach($products as $product){  
        
        if(!is_null($product['shop_id'])){       
            $data_productos["update"][] = 
                [                   
                    'id'=>$product['shop_id'],
                    'stock_quantity' => $product['stock'],            
                    'regular_price'=>  $product['price'],
                    "status"=> 'publish',  
                    'sale_price'=> $product['price'],        
                    'stock_status'=> (intval($product['price'])>0 && intval($product['stock'])>0)?"instock":"outofstock",
                    'manage_stock'=> true                    
                ];  
        }
        
        if(count($data_productos["update"])>=100){        
            
            $object_response_data = $woocommerce->post('products/batch', $data_productos);
            //var_dump($object_response_data);
    
            if(count($object_response_data->update)>0){
                foreach($object_response_data->update as $elem){    
                    if(isset($elem->id)){ 
                        if($elem->id>0){ 
                            $updateListProducts[]=$elem->id;
                        }
                    }
                }
            }    
            
            if(count($updateListProducts)>0){
                updateShopProductsPrices($id,$country,$updateListProducts,$access);
                $objct_response_data[] = $object_response_data;                    
            }
            $data_productos["update"]=[];
            $updateListProducts=[];
        }
    }
   
    if(count($data_productos["update"])>0){               
        $object_response_data = $woocommerce->post('products/batch', $data_productos);
        if(count($object_response_data->update)>0){
            foreach($object_response_data->update as $elem){      
                if(isset($elem->id)){ 
                    if($elem->id>0){ 
                        $updateListProducts[]=$elem->id;
                    }
                }
            }
        }    
        
        if(count($updateListProducts)>0){
            updateShopProductsPrices($id,$country,$updateListProducts,$access);
            $objct_response_data[] = $object_response_data;                    
        }
        $data_productos["update"]=[];
        $updateListProducts=[];
    }
}*/



function woocommerceproductsrc____($products,$access,$id=null,$country=null,$operation="update", $skus = null, $update = 'all'){
    global $access_;
    global $parent_id_new;
    global $catalogs_;
    $access_= $access;

    $catalogs_ = getCatalog($id,$country);
    set_time_limit(0);
    
    $woocommerce = createWoo($access);    

    $products=shopIdIsNotNull($products);
    $x=0;

    //$products=shopSkus($products,['U82594','U82577','U82569','4964DV','139KKC','3372DT','8375DH','4151CZ','8376DH','70026Z','7170FC','33671T','607XXA','26893Y','72080U','6530DK','46407K','07022M','80661Y','94120K','81182P','55017Z','5145CH','24581L','152LLD','960KKA','23994V','158LLD','19472T','80875Y','7125EA','04027X','748063','985423','985819','38239C','20165E','985010','7925EC','10076M','65970X','9306DE','5722DU','8478CF','65065P','55048W','1673CK','35241P','1230CK','24469L','54951W','19452Q','6980DX','85944U','82170Y','564NNL','82782Y','65742M','81752L','53754V','40420T','21559Z','28084W','5640DK','2616XD','24443L','5637DK','905XXW','4724DU','5338DU','72836T','5636DK','2757ZY','5635DK','82344K','83568P','55020Z','294AAP','634WWV','28297W','1231CK','31459T','60082U','8796CY','55558Q','5643DK','66180M','85941U','82349K','72834T','5638DK','7920EC','247CCU','7M3628','575NNK','831PPK','6124FA','511PPK','862DDM','701A1G','7839EA','3935DX','718AAP','813CCQ','8164ZS','037XXM','512PPK','543CCQ','1555CK','475XXA','861DDM','5596ZC','227A1S','31061Y','30909Y','8281CP','91158L','91131L','94462U','94461U','91234L','77313L','91132L','90635L','679JJJ','3349DV','84591T','91086L','91143L','91212L','77295L','94453U','91211L','70435N','02907P','19425M','77302L','94456U','90247L','90386L','674JJJ','481DDN','38522T','319NNL','70436N','1713CK','90540L','781VVV','922A9E','2430RD','297ZZF','293ZZF','294ZZF','1325ZW','606A1P','590A8F','317A2E','357A8S','362MMD','897CCX','906CCX','353A8S','350A8S','582VVU','583VVU','869AAB','649VVU','554HHF','2575FA','790VVU','557HHF','995ZZF','88937V','78421Y','4638ZC','662MMG','79885T','52544G','47801Q','35315R','150WWZ','652A9F','382AAN','651A9F','848A8V','564A8U','7332DQ','91135L','70334N','91200L','516DDX','649VVK','70590N','565DDV','70433N','91127L','91156L','1079CL','118CCQ','7040ZB','62690U','468CCW','D84386','326A1X','5842ZF','782JJJ','D85147','D85146','VS3438','2022DY','586AAU','787A5R','788A5R','789A5R','3914ZR','3916ZR','6309CT','799A5R','145A2G','279XXZ','9485ZD','282XXZ','765A2H','971A2L','28827Z','28826Z','304KKF','152C1A','151C1A','242A7K','241A7K','243A7K','150C1A','26105Z','942VVZ','686PPJ','115A2B','981A2A','966A2A','910A2A','302MMH','685A5Z','689A5Z','687A5Z','197KKG','684A5Z','97009Y','97008Y','928FFP']);
    //$products=shopSkus($products,['990LLE']);
    /* var_dump($products);
    exit; */

    foreach($products as $product){

            if(is_null($product['shop_id'])&&$operation=="update"){continue;}

            //if(is_null($product['ProductWeight'])){
                
                    $contentsSet = extractContents($product['sku'],$country);
                    
                    if(!is_null($contentsSet)){                       

                        if(!is_null($contentsSet['dim']['ProductWeight'])){
                            $product['ProductWeight']=$contentsSet['dim']['ProductWeight'];           
                            $product['ProductLength']=$contentsSet['dim']['ProductLength'];
                            $product['ProductWidth']=$contentsSet['dim']['ProductWidth'];
                            $product['ProductHeight']=$contentsSet['dim']['ProductHeight'];
                        }

                        if(!is_null($contentsSet['contentsTitle'])){
                            $product['title']=$contentsSet['contentsTitle'];
                        }
                    } 

                    $categories=[];
                    $tags=[];
                    $functionCallBack = "RC".$country;
                    $rcSet =$functionCallBack($product['sku'],true);
                   
                    if(!is_null($rcSet)){
                        $product["category_id"]=$rcSet[$product['sku']]['id_subcategory'];
                        $product["manufacturer_id"]=$rcSet[$product['sku']]['id_manufacturer'];

                        $categories[] = ['id' => setIdShop($country,$rcSet[$product['sku']],$catalogs_['Categories'],'category',$id,'id_category')];
                        $parent_id_new=$categories[0]['id'];
                        $categories[] = ['id' => setIdShop($country,$product["category_id"],$catalogs_['Categories'],'subcategory',$id)];                        
                        
                        $tags[]=['id'=> setIdShop($country,$rcSet[$product['sku']],$catalogs_['Categories'],'tags',$id,'id_line')];                        
                    }
                        
                    $tags[]=['id'=> setIdShop($country,$product["manufacturer_id"],$catalogs_['Manufacturers'],'manufacturer',$id)];                                              

            //}

            if(is_null($product['ProductWeight'])){$dataNotFound[]=$product['sku'];file_put_contents('datanotfound.json',json_encode($dataNotFound));continue;}           

            $data_productos[$operation][] = 
            [                   
                'id' =>($operation=="update")?$product['shop_id']:null,
                'name' => $product['title'],
                //'type' => 'simple',     
                //'manage_stock' => true,
                //"status" => 'publish',            
                /*'categories' => [
                    [
                        'id' => setIdShop($product["category_id"],$catalogs['Categories']),
                    ]
                ],
                'tags'=>[['id'=> setIdShop($product["manufacturer_id"],$catalogs['Manufacturers'])]],*/ 
                'categories' => $categories,
                'tags'=>$tags,  
                'brands'=>['id'=> setIdShop($country,$product["manufacturer_id"],$catalogs_['Manufacturers'],'manufacturer',null,null,"shop_id_brands")],
                'weight'=> $product['ProductWeight'],
                'dimensions'=> (object)[
                    'length'=>$product['ProductLength'],
                    'width'=>$product['ProductWidth'],
                    'height'=>$product['ProductHeight']
                ]                         
            ];     
           

            $skus['skus'][]=$product['sku'];
            if(count($data_productos[$operation])>0){               
                $object_response_data = $woocommerce->post('products/batch', $data_productos);
                /* var_dump($object_response_data);
                exit; */
                $x++;
                //if($x>100){exit;}
                if(count($object_response_data->update)>0){                
                    foreach($object_response_data->update as $elem){                     
                        if(isset($elem->id)){     
                            $product_id=($elem->id==0)?((isset($elem->error->data->resource_id))?$elem->error->data->resource_id:0):$elem->id;  
                            if($elem->id==0){     
                                if($product_id>0){                       
                                    $productDuplicate = $woocommerce->get('products/'.$product_id);                            
                                    $updateListProducts[$productDuplicate->sku]=$product_id;
                                }
                            }else{
                                $updateListProducts[$elem->sku]=$elem->id;
                            }
                        }
                    }
                }             
                
                if(count($updateListProducts)>0){
                    
                    if($operation=="update"){updateShopProductsRC($id,$country,$updateListProducts,$access);}
                    $objct_response_data[] = $object_response_data;                          
                }
                $data_productos=[];
                $updateListProducts=[];        
                   
            }  
            
    }
   /*  var_dump($data_productos);
    exit; */
    if(isset($data_productos[$operation])){               
        $object_response_data = $woocommerce->post('products/batch', $data_productos);
        foreach($object_response_data->update as $elem){    
            if(isset($elem->id)){                
                if(isset($elem->id)){     
                    $product_id=($elem->id==0)?((isset($elem->error->data->resource_id))?$elem->error->data->resource_id:0):$elem->id;  
                    if($elem->id==0){     
                        if($product_id>0){                       
                            $productDuplicate = $woocommerce->get('products/'.$product_id);                            
                            $updateListProducts[$productDuplicate->sku]=$product_id;
                        }
                    }else{
                        $updateListProducts[$elem->sku]=$elem->id;
                    }
                }
            }
        }    
        
        if(count($updateListProducts)>0){
            if($operation=="update"){updateShopProductsRC($id,$country,$updateListProducts,$access);}
            $objct_response_data[] = $object_response_data;
        }
        $data_productos=[];
        $updateListProducts=[];
    } 
    return $skus;

}

function woocommercecontents($products,$access,$id=null,$country=null,$operation="update", $skus = null, $update = 'all'){
    set_time_limit(0);
    $woocommerce = createWoo($access);

    $products = shopIdIsNotNullAndContentsNull($products);

    foreach($products as $product){

            if(is_null($product['shop_id'])&&$operation=="update"){continue;}

            $info = $product['ficha_html'];
            $array_img=null;
            if(is_null($product['ficha_html']) || empty($product['ficha_html']) || is_null($product['images']) || empty($product['images'])){
                $contentsSet = extractContents($product['sku'],$country);
                
                $info = $contentsSet['contentsHTML'];
                if(!is_null($contentsSet['contentsImages']['imageGalleryUrlHigh'])){                
                    $imagesList = $contentsSet['contentsImages']['imageGalleryUrlHigh'];
                    
                    foreach($imagesList as $item){
                        if(url_exists($item)){
                            $array_img[] = ["src"=>$item];
                        }
                    }            
                }
            }else{
                if(!is_null($product['images'])&&!empty($product['images'])){                
                    $imagesList = json_decode($product['images'],true);
                    $imagesList = (isset($imagesList['imageGalleryUrlHigh']))?$imagesList['imageGalleryUrlHigh']:$imagesList;
                    foreach($imagesList as $item){
                        if(url_exists($item)){
                            $array_img[] = ["src"=>$item];
                        }
                    }            
                }
            }                        

            $data_productos[$operation][] = 
            [                   
                'id' =>($operation=="update")?$product['shop_id']:null,                
                'type' => 'simple',     
                'images' =>  $array_img,
                'description' => $info,
                "status" => 'publish',                             
            ];        

            if(count($data_productos[$operation])>3){               
                $object_response_data = $woocommerce->post('products/batch', $data_productos);
                
                if(count($object_response_data->update)>0){
                    foreach($object_response_data->update as $elem){      
                        if(isset($elem->id)){ 
                            if($elem->id>0){ 
                                try{
                                    $updateListProducts[$elem->sku]=$elem->id;
                                } catch (\Exception $e) {
                                    
                                }
                            }
                        }
                    }
                }    
                
                if(count($updateListProducts)>0){
                    if($operation=="update"){updateShopProductsRC($id,$country,$updateListProducts,$access);}
                    $objct_response_data[] = $object_response_data;                    
                }
                $data_productos=[];
                $updateListProducts=[];
            }
    }

    if(isset($data_productos[$operation])){  
        if(count($data_productos[$operation])>0){
            $object_response_data = $woocommerce->post('products/batch', $data_productos);
            if(count($object_response_data->update)>0){
                foreach($object_response_data->update as $elem){      
                    if(isset($elem->id)){ 
                        if($elem->id>0){ 
                            $updateListProducts[$elem->sku]=$elem->id;
                        }
                    }
                }
            }    
            
            if(count($updateListProducts)>0){
                if($operation=="update"){updateShopProductsRC($id,$country,$updateListProducts,$access);}
                $objct_response_data[] = $object_response_data;                    
            }
            $data_productos=[];
            $updateListProducts=[];
        }
    }

}

function woocommerceproducts____($products,$access,$id=null,$country=null,$operation="create", $skus = null, $update = 'all'){
    set_time_limit(0);

    /* $statusInstance = initInstance($country, $id);
    if(isset($statusInstance['error'])){return $statusInstance;} */

   

    $cuantos = 2;
    $woocommerce = createWoo($access);
    
    $catalogs = getCatalog($id,$country);
    
    $skus=[];
    $updateListProducts=[];

    //if(in_array($operation,['create'])){$products=shopSkus($products,['198A7L','603VVS','200A7L','186WWL','433VVC','893VVV','501A1P','821PPU']);}
    //if(in_array($operation,['create'])){$products=shopIdIsNull($products);/* $products=shopSkus($products,['198A7L','603VVS','200A7L','186WWL','433VVC','893VVV','501A1P','821PPU']); */}
    //$skusFilters = ((file_exists("getSegmentation/".$country.'/'.$id."/skusfilters.json"))?read_file_json("getSegmentation/".$country.'/'.$id."/skusfilters.json",true):[]);
    if(in_array($operation,['create'])){$products= shopIdIsNullAndIsActive($products); /*$products= shopSkus($products,$skusFilters);*/}
     $totalProucts = count($products);
    $updateCount = 0;
    /* echo count($products);
    //echo json_encode(array_column($products,'sku'));
    exit;
    $x=0; */

    /* var_dump($products);
    exit; */
    try{
        $productsSeg=array_chunk($products,$cuantos);
        
        helper("storage");

        foreach($productsSeg as $products_){
            
            //getPrices///////////////////////////////////        
            $productsSKU = array_column($products_,'sku');
            $productPrices = putPrices($id,true,$productsSKU);
       
            /* var_dump($productPrices);
            exit; */
            /////////////////////////////////////////////
            
                foreach($productPrices as $product){ 
                    
                        if(is_null($product['shop_id'])&&$operation=="update"){continue;}            
                        if(!is_null($product['shop_id'])&&$operation=="create"){continue;}
                        
                        $info = $product['ficha_html'];
                        $array_img=null;

                        if(is_null($product['ficha_html']) || empty($product['ficha_html']) || is_null($product['images']) || empty($product['images']) ||
                        is_null($product['ProductWeight']) || is_null($product['ProductLength']) || is_null($product['ProductWidth']) || is_null($product['ProductHeight'])
                        ){                
                            $contentsSet = extractContents($product['sku'],$country);
                            
                            if(!is_null($contentsSet)){
                                $info = $contentsSet['contentsHTML'];
                                if(!is_null($contentsSet['contentsImages']['imageGalleryUrlHigh'])){                
                                    $imagesList = $contentsSet['contentsImages']['imageGalleryUrlHigh'];
                                    
                                    foreach($imagesList as $item){
                                        if(url_exists($item)){
                                            $array_img[] = ["src"=>$item];
                                        }
                                    }            
                                }

                                if(!is_null($contentsSet['dim']['ProductWeight'])){
                                    $product['ProductWeight']=$contentsSet['dim']['ProductWeight'];           
                                    $product['ProductLength']=$contentsSet['dim']['ProductLength'];
                                    $product['ProductWidth']=$contentsSet['dim']['ProductWidth'];
                                    $product['ProductHeight']=$contentsSet['dim']['ProductHeight'];
                                }
                            }            
                        }else{
                            if(!is_null($product['images'])&&!empty($product['images'])){                
                                $imagesList = json_decode($product['images'],true);
                                $imagesList = (isset($imagesList['imageGalleryUrlHigh']))?$imagesList['imageGalleryUrlHigh']:$imagesList;
                                foreach($imagesList as $item){
                                    if(url_exists($item)){
                                        $array_img[] = ["src"=>$item];
                                    }
                                }            
                            }
                        }       

                        //getPrices///////////////////////////////////
                        /*helper("storage");
                        $productPrices = array_values(putPrices($id,true,[$product['sku']]));
                        if(isset($productPrices[0]['stock'])){
                            $product['stock'] = $productPrices[0]['stock'];
                            $product['price'] = $productPrices[0]['price'];
                        }*/
                        /////////////////////////////////////////////
                        

                        $data_productos[$operation][] = 
                        [                   
                            'id' =>($operation=="update")?$product['shop_id']:null,
                            'sku' => $product['sku'],
                            'stock_quantity' => $product['stock'],
                            'regular_price' =>  $product['price'],
                            'name' => clear_space($product['title']),
                            'type' => 'simple',     
                            'stock_status' => ((intval($product['price'])>0 && intval($product['stock'])>0)?"instock":"outofstock"),
                            'manage_stock' => true,   
                            'images' =>  $array_img,
                            'description' => $info,
                            "status" => 'publish',            
                            'categories' => [
                                [
                                    'id' => setIdShop($country,$product["category_id"],$catalogs['Categories'],'category'),
                                ]
                            ],
                            'tags'=>[['id'=> setIdShop($country,$product["manufacturer_id"],$catalogs['Manufacturers'],'manufacturer')]],     
                            'brands'=>['id'=> setIdShop($country,$product["manufacturer_id"],$catalogs['Manufacturers'],'manufacturer',null,null,"shop_id_brands")],       
                            'weight'=> $product['ProductWeight'],
                            'dimensions'=> (object)[
                                'length'=>$product['ProductLength'],
                                'width'=>$product['ProductWidth'],
                                'height'=>$product['ProductHeight']
                            ]                         
                        ];

            
            
                }



            //if(count($data_productos[$operation])>= $cuantos){   

            
                    
                    $object_response_data = $woocommerce->post('products/batch', $data_productos);
                     
                 

                    //$x++;
                    //if($x>100){exit;}
                    if(count($object_response_data->create)>0){  
                        //getPrices///////////////////////////////////
                        /*helper("storage");
                        $product_ = array_column($data_productos[$operation],'sku');
                        $productPrices = putPrices($id,true,$product_);
                        woocommerceprices($productPrices,$access,$id,$country);*/
                        /////////////////////////////////////////////         
                        foreach($object_response_data->create as $elem){                     
                            if(isset($elem->id)){     
                                $product_id=($elem->id==0)?((isset($elem->error->data->resource_id))?$elem->error->data->resource_id:0):$elem->id;  
                                if($elem->id==0){     
                                    if($product_id>0){                       
                                        $productDuplicate = $woocommerce->get('products/'.$product_id);                            
                                        $updateListProducts[$productDuplicate->sku]=$product_id;
                                    }
                                }else{
                                    $updateListProducts[$elem->sku]=$elem->id;
                                }
                            }
                        }
                    }             
                    
                    if(count($updateListProducts)>0){
                        if($operation=="create"){
                            updateShopProductsId($id,$country,$updateListProducts,$access);
                            $updateCount+=count($updateListProducts);
                            
                            updateInstance($country, $id);
                            progressProcess($country, $id, $totalProucts, $updateCount);
                        }
                        $objct_response_data[] = $object_response_data;                          
                    }
                    $data_productos[$operation]=[];
                    $updateListProducts=[]; 
                    var_dump($object_response_data);exit;
                //}  
        }
        outInstance($country, $id);
        return  ["success" => true, "msg" => "Product Add Finish"];
    } catch (Exception $e) {
        $log = ((file_exists("getSegmentation/" . $country . '/' . $id . "/log.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/log.json", true) : []);
        $log[] = [returnDate(returnAccess()['zone'][$country], "Y-m-d_H"), $e->getMessage()];
        write_file_root("getSegmentation/" . $country . '/' . $id . "/", "log.json", json_encode($log, JSON_UNESCAPED_UNICODE));
        write_file_root("progress/" . $country . '/' . $id . "/", "processprogress.txt", $e->getMessage());
        outInstance($country, $id);
    }     
}

function updateShopProductsPrices($id,$country,$list){   
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
        if(isset($fileProducts["Products"]["products"])){
            if(count($fileProducts["Products"]["products"])>0){
                $arraySkuData = array_column($fileProducts["Products"]["products"],"shop_id");
                foreach($list as $product){
                    $indexProductData = array_search($product,$arraySkuData);
                    if($indexProductData!==false){
                        $fileProducts["Products"]["products"][$indexProductData]['shop_update_at'][returnDate(returnAccess()['zone'][$country],"Y-m-d_H")][]="product-prices";
                        $fileProducts["Products"]["products"][$indexProductData]['status'] = true;  
                    }
                }
            }
        }			
    }
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($fileProducts,JSON_UNESCAPED_UNICODE));    
}

function updateShopProductsId($id,$country,$list){
   
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
        if(isset($fileProducts["Products"]["products"])){
            if(count($fileProducts["Products"]["products"])>0){
                $arraySkuData = array_column($fileProducts["Products"]["products"],"sku");
                foreach($list as $key=>$product){
                    $indexProductData = array_search($key,$arraySkuData);
                    if($indexProductData!==false){
                        $fileProducts["Products"]["products"][$indexProductData]['shop_id']=$product;
                        $fileProducts["Products"]["products"][$indexProductData]['shop_update_at'][returnDate(returnAccess()['zone'][$country],"Y-m-d_H")][]="product-insert";
                    }
                }
            }
        }			
    }
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($fileProducts,JSON_UNESCAPED_UNICODE));    
}

function updateShopProductsRC($id,$country,$list){
   
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
        if(isset($fileProducts["Products"]["products"])){
            if(count($fileProducts["Products"]["products"])>0){
                $arraySkuData = array_column($fileProducts["Products"]["products"],"sku");
                foreach($list as $key=>$product){
                    $indexProductData = array_search($key,$arraySkuData);
                    if($indexProductData!==false){
                        $fileProducts["Products"]["products"][$indexProductData]['shop_update_at'][returnDate(returnAccess()['zone'][$country],"Y-m-d_H")][]="product-rc";
                    }
                }
            }
        }			
    }
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($fileProducts,JSON_UNESCAPED_UNICODE));    
}

function updateShopCategoriesId($id,$country,$list){   
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
        if(isset($fileProducts["Categories"])){
            if(count($fileProducts["Categories"])>0){

                $arrayData=[];
                foreach($fileProducts["Categories"] as $key=>$category){         
                    if(isset($category['parent'])){         
                        if(is_null($category['parent']['shop_id'])){
                            if(isset($list['parent'][$category['parent']['id']])){
                                $category['parent']['shop_id'] = $list['parent'][$category['parent']['id']];
                                $fileProducts["Categories"][$key]['parent']=$category['parent'];
                                unset($list['parent'][$category['parent']['id']]);
                            }
                        }else{
                            unset($list['parent'][$category['parent']['id']]);
                        }
                    }
                    if(isset($category['child'])){ 
                        foreach($category['child'] as  $keyC=>$chield){
                            if(isset($list['chield'][$chield['id']])){                            
                                $chield['shop_id'] = $list['chield'][$chield['id']]['chield'];
                                $fileProducts["Categories"][$key]['child'][$keyC]=$chield;
                                unset($list['chield'][$chield['id']]['chield']);
                            }
                        }
                    }
                    $arrayData[$key]=array_merge(
                        ((isset($category['parent']))?[$category['parent']['id']]:[]),
                        ((isset($category['child']))?array_column($category['child'],'id'):[]),
                        ((isset($category['line']))?array_column($category['line'],'id'):[])
                    );
                }           
                     

                if(!empty($list["parent"])){   
                    $fileProducts["Categories"][]['parent']=[
                        "shop_id"=> $list["parent"][key($list["parent"])],
                        "id"=> key($list["parent"]),
                        "parent_id"=> 0,
                        "nombre"=> $list["parent-name"][key($list["parent"])]
                    ];
                }

                

                if(!empty($list["chield"])){
                    /* echo json_encode($list);
                    exit; */
                    foreach($list["chield"] as $key=>$item){      
                        foreach($arrayData as $key_=>$value){
                            if(in_array($list["chield-parent"][$key],$value)){
                                $indexData = $key_;
                            }
                        }
                        
                        if(isset($indexData)){
                            if(isset($item['chield'])){
                                $fileProducts["Categories"][$indexData]['child'][]=[
                                    "shop_id"=> $item['chield'],
                                    "id"=> $key,
                                    "parent_id"=> $list["chield-parent"][$key],
                                    "nombre"=> $list["chield-name"][$key]
                                ];
                            }
                        }
                    }
                }

            }
        }			
    }
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($fileProducts,JSON_UNESCAPED_UNICODE));   
}

function updateShopTagsId($id,$country,$list){   
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
        if(isset($fileProducts["Categories"])){
            if(count($fileProducts["Categories"])>0){ 
                $arrayData=[];
                foreach($fileProducts["Categories"] as $key=>$category){                    
                    /*if(is_null($category['parent']['shop_id'])){
                        if(isset($list['parent'][$category['parent']['id']])){
                            //$indexPData = array_search($category['parent']['id'],$arrayIdPData);                            
                            $category['parent']['shop_id'] = $list['parent'][$category['parent']['id']];
                            $fileProducts["Categories"][$key]['parent']=$category['parent'];
                        }
                    }
                    foreach($category['child'] as  $keyC=>$chield){
                        if(isset($list['chield'][$chield['id']])){                            
                            $chield['shop_id'] = $list['chield'][$chield['id']]['chield'];
                            $fileProducts["Categories"][$key]['child'][$keyC]=$chield;
                        }
                    }*/
                    if(isset($category['line'])){
                        foreach($category['line'] as  $keyL=>$line){
                            if(isset($list['tags'][$line['id']])){ 
                                $line['shop_id'] = $list['tags'][$line['id']];
                                $fileProducts["Categories"][$key]['line'][$keyL]=$line;
                                unset($list['tags'][$line['id']]);
                            }
                        }
                    }
                    $arrayData[$key]=array_merge(
                                                    ((isset($category['parent']))?[$category['parent']['id']]:[]),
                                                    ((isset($category['child']))?array_column($category['child'],'id'):[]),
                                                    ((isset($category['line']))?array_column($category['line'],'id'):[])
                                                );
                }
 
                if(!empty($list["tags"])){
                    foreach($list["tags"] as $key=>$item){                    
                        foreach($arrayData as $key_=>$value){
                            if(in_array($list["tags-parent"][$key],$value)){
                                $indexData = $key_;
                            }
                        }
                        if(isset($indexData)){
                            $fileProducts["Categories"][$indexData]['line'][]=[
                                "shop_id"=> $item,
                                "id"=> $key,
                                "parent_id"=> $list["tags-parent"][$key],
                                "nombre"=> $list["tags-name"][$key]
                            ];
                        }
                    }
                }
            }
        }			
    }
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($fileProducts,JSON_UNESCAPED_UNICODE));   
}

function updateShopManufacturerId($id,$country,$list){   
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileProducts = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);	
        if(isset($fileProducts["Manufacturers"])){
            if(count($fileProducts["Manufacturers"])>0){                
                foreach($fileProducts["Manufacturers"] as $key=>$manufacturer){                    
                    if(is_null($manufacturer['shop_id'])|| is_null($manufacturer['shop_id_brands'])){
                        if(isset($list["tags"][$manufacturer['id']])){                            
                            $manufacturer['shop_id'] = $list["tags"][$manufacturer['id']];
                            if(isset($list["brands"][$manufacturer['id']])){
                                $manufacturer['shop_id_brands'] = $list["brands"][$manufacturer['id']];
                            }
                            $fileProducts["Manufacturers"][$key]=$manufacturer;
                            unset($list["tags"][$manufacturer['id']]);
                        }
                    }
                }
                if(!empty($list["tags"])){
                    foreach($list["tags"] as $key=>$item){
                        $fileProducts["Manufacturers"][]=[
                            "shop_id"=> $item,
                            "id"=> $key,
                            "manufacturer"=> $list["tags-name"][$key],
                            "shop_id_brands"=> $list["brands"][$key]
                        ];
                    }
                }
            }
        }			
    }
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','products.json',json_encode($fileProducts,JSON_UNESCAPED_UNICODE));
}

function getCatalog($id,$country){
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'products.json')){
        $fileSegmentation = read_file_json("getSegmentation/".$country.'/'.$id."/products.json",true);		
    }
    $listCategories = [];
    foreach($fileSegmentation['Categories'] as $elem){
        $listCategories[]=$elem["parent"];
        if(isset($elem["child"])){
            foreach($elem["child"] as $elem_){
                $listCategories[]=$elem_;
            }
        }
        if(isset($elem["line"])){
            foreach($elem["line"] as $elem_){
                $listCategories[]=$elem_;
            }
        }
    }
    
    return ["Categories"=>$listCategories,"Manufacturers"=>$fileSegmentation['Manufacturers']];
}

function getShopId($country,$id,$type,$user_id){
    
    global $access_;
    global $parent_id_new;
   
    $db       = \Config\Database::connect();
    if($type=='manufacturer'){
	    $manufacturer = $db->table('manufacturer_'.$country)->where(['id' => $id])->get()->getResultArray();
        $manufacturer[0]['shop_id']=null;
        return ["shop_id"=>array_values(woocommercemanufacturer($manufacturer,$access_,$user_id,$country)['tags'])];
    }
    if($type=='category'){
        
        $category = $db->table('categorias_'.$country)->where(['id' => $id, 'type' => 'category-product'])->get()->getResultArray();
        
        $category_[]= ["parent"=>[
            "shop_id"=> null,
            "id"=> $category[0]['id'],
            "parent_id"=> 0,
            "nombre"=> $category[0]['name'],"child"=>[]]
        ];
        $response = woocommercecategories($category_,$access_,$user_id,$country)['parent'];
        
        return ["shop_id"=>$response[key($response)]];
    }
    if($type=='subcategory'){
        $subCategory = $db->table('categorias_'.$country)->where(['id' => $id, 'type' => 'sub-product'])->get()->getResultArray();
        
        $subCategory_[]= ["child"=>[[
            "shop_id"=> null,
            "id"=> $subCategory[0]['id'],
            "parent_id"=> $subCategory[0]['parent_id'],
            "nombre"=> $subCategory[0]['name']]],"parent"=>["shop_id"=>$parent_id_new]
        ];
       
        $response = woocommercecategories($subCategory_,$access_,$user_id,$country)['chield'];       
        return ["shop_id"=>$response[key($response)]['chield']];
    }
    
    if($type=='tags'){
        $productLine = $db->table('categorias_'.$country)->where(['id' =>$id, 'type' => 'line-product'])->get()->getResultArray();
      
        return ["shop_id"=>array_values(woocommercetags($productLine,$access_,$user_id,$country)['tags'])];
    }
}

function setIdShop($country,$id,$catalog,$type,$user_id=null,$keyLabel=null,$idLabel='shop_id'){
    global $catalogs_;
    
    $id = ((is_null($keyLabel))?$id:$id[$keyLabel]);   
    
    if(is_null($id)){return $id;}
    
    $search_item = array_filter($catalog, 
        function($item) use($id){
            if(isset($item['id'])){
                 return $item['id'] == $id;
            }
        }
    );
    
    if(count($search_item)<=0 || is_null(reset($search_item)[$idLabel])){
        
        $search_item[0] = getShopId($country,$id,$type,$user_id);
        
        $catalogs_ = getCatalog($user_id,$country);
    }
    

    return ((count($search_item)>0)?( (isset(reset($search_item)[$idLabel]))?reset($search_item)[$idLabel]:null ):null);
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

function shopIdIsNull($catalog){
    $search_item = array_filter($catalog, 
        function($item) {            
           
                    return is_null($item['shop_id']);
        }
    );
    return $search_item;
}

function shopIdIsNullDayOut($catalog)
{
    $search_item = array_filter(
        $catalog,
        function ($item) {
            try {
                if (isset($item['day_out'])) {
                    return (is_null($item['shop_id']) && intval($item['day_out']) >= 0 && intval($item['day_out']) <= 360);
                } else {
                    return false;
                }
            } catch (Exception $e) {
                var_dump([$item['sku'] => $e->getMessage()]);
            }
        }
    );
    return $search_item;
}

function shopIdIsNotNullAndContentsNull($catalog){
    $search_item = array_filter($catalog, 
        function($item) {
            if(isset($item['shop_id'])){
                return (!is_null($item['shop_id'])&&(is_null($item['ficha_html'])||is_null($item['images'])));
            }
        }
    );
    return $search_item;
}

function shopSkus($catalog,$sku){
    $search_item = array_filter($catalog, 
        function($item) use($sku){       
            return in_array($item['sku'],$sku);
        }
    );
    return $search_item;
}

function shopIdIsNullAndIsActive($catalog)
{
    $search_item = array_filter(
        $catalog,
        function ($item) {
            return is_null($item['shop_id']) && setBoolean($item['active']);
        }
    );
    return $search_item;
}

function shopIdIsNotNullAndIsActive($catalog)
{
    $search_item = array_filter(
        $catalog,
        function ($item) {
            return !is_null($item['shop_id']) && setBoolean($item['active']);
        }
    );
    return $search_item;
}


function shopSkusInvers($catalog, $sku)
{
    $filter = array_column($catalog, 'sku');
    $search_item = array_filter($sku,
        function ($item) use ($filter) {
            return in_array($item, $filter);
        }
    );
    return $search_item;
}