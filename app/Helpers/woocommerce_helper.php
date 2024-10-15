<?php
require 'vendor/autoload.php';
use Automattic\WooCommerce\Client;

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

function woocommercemanufacturer($manufacturer,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);    
    $woocommerce = createWoo($access);
    $updateListManufacturer=[];
    foreach($manufacturer as $brand){    
        if(is_null($brand['shop_id'])){    
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
                }
            } 
            
            try{
                $object_response_data = $woocommerce->post( 'brands' , $data_productos["create"][0] );                
                if(isset($object_response_data->id)){                        
                    $manufacturer_shop_id=($object_response_data->id==0)?$object_response_data->error->data->resource_id:$object_response_data->id;  
                    $updateListManufacturer["brands"][$brand['id']]=$manufacturer_shop_id;
                }
            } catch (\Exception $e) {
                
            }

            if(isset($updateListManufacturer["tags"])){
                updateShopManufacturerId($id,$country,$updateListManufacturer);
            }

            $data_productos["create"] = [];
        }
    }       
}

function woocommercecategories($categories,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);
    $woocommerce = createWoo($access);
    $updateListCategories=[];
    foreach($categories as $category){  
        $data_category=[];
        $parent_shop_id=$category['parent']['shop_id'];
        if(is_null($parent_shop_id)){      
            $data_category["create"][] =        
            [
                'name' => $category['parent']['nombre']
            ];
            
            $object_response_data = $woocommerce->post('products/categories/batch', $data_category);       
            
            if(count($object_response_data->create)>0){
                foreach($object_response_data->create as $elem){                    
                    $parent_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                    $updateListCategories['parent'][$category['parent']['id']]=$parent_shop_id;
                }
            }                      
        }
        
        foreach($category['child'] as $chield){
            $data_category=[];
            if(is_null($chield['shop_id'])){
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
                    }
                }                                                             
            }
        }

        if(isset($updateListCategories['chield'])||isset($updateListCategories['parent'])){
            updateShopCategoriesId($id,$country,$updateListCategories);
        }        
    }
}

function woocommerceprices($products,$access,$id=null,$country=null,$operation=null){
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
            /* echo json_encode($data_productos);  
            exit;     */ 
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
}



function woocommerceproductsrc($products,$access,$id=null,$country=null,$operation="update"){
    set_time_limit(0);
    $woocommerce = createWoo($access);
    $catalogs = getCatalog($id,$country);
    $products=shopIdIsNotNull($products);
    $x=0;

    $products=shopSkus($products,['U82594','U82577','U82569','4964DV','139KKC','3372DT','8375DH','4151CZ','8376DH','70026Z','7170FC','33671T','607XXA','26893Y','72080U','6530DK','46407K','07022M','80661Y','94120K','81182P','55017Z','5145CH','24581L','152LLD','960KKA','23994V','158LLD','19472T','80875Y','7125EA','04027X','748063','985423','985819','38239C','20165E','985010','7925EC','10076M','65970X','9306DE','5722DU','8478CF','65065P','55048W','1673CK','35241P','1230CK','24469L','54951W','19452Q','6980DX','85944U','82170Y','564NNL','82782Y','65742M','81752L','53754V','40420T','21559Z','28084W','5640DK','2616XD','24443L','5637DK','905XXW','4724DU','5338DU','72836T','5636DK','2757ZY','5635DK','82344K','83568P','55020Z','294AAP','634WWV','28297W','1231CK','31459T','60082U','8796CY','55558Q','5643DK','66180M','85941U','82349K','72834T','5638DK','7920EC','247CCU','7M3628','575NNK','831PPK','6124FA','511PPK','862DDM','701A1G','7839EA','3935DX','718AAP','813CCQ','8164ZS','037XXM','512PPK','543CCQ','1555CK','475XXA','861DDM','5596ZC','227A1S','31061Y','30909Y','8281CP','91158L','91131L','94462U','94461U','91234L','77313L','91132L','90635L','679JJJ','3349DV','84591T','91086L','91143L','91212L','77295L','94453U','91211L','70435N','02907P','19425M','77302L','94456U','90247L','90386L','674JJJ','481DDN','38522T','319NNL','70436N','1713CK','90540L','781VVV','922A9E','2430RD','297ZZF','293ZZF','294ZZF','1325ZW','606A1P','590A8F','317A2E','357A8S','362MMD','897CCX','906CCX','353A8S','350A8S','582VVU','583VVU','869AAB','649VVU','554HHF','2575FA','790VVU','557HHF','995ZZF','88937V','78421Y','4638ZC','662MMG','79885T','52544G','47801Q','35315R','150WWZ','652A9F','382AAN','651A9F','848A8V','564A8U','7332DQ','91135L','70334N','91200L','516DDX','649VVK','70590N','565DDV','70433N','91127L','91156L','1079CL','118CCQ','7040ZB','62690U','468CCW','D84386','326A1X','5842ZF','782JJJ','D85147','D85146','VS3438','2022DY','586AAU','787A5R','788A5R','789A5R','3914ZR','3916ZR','6309CT','799A5R','145A2G','279XXZ','9485ZD','282XXZ','765A2H','971A2L','28827Z','28826Z','304KKF','152C1A','151C1A','242A7K','241A7K','243A7K','150C1A','26105Z','942VVZ','686PPJ','115A2B','981A2A','966A2A','910A2A','302MMH','685A5Z','689A5Z','687A5Z','197KKG','684A5Z','97009Y','97008Y','928FFP']);
    
    foreach($products as $product){

            if(is_null($product['shop_id'])&&$operation=="update"){continue;}

            if(is_null($product['ProductWeight'])){
                
                    $contentsSet = extractContents($product['sku'],$country);
                    
                    if(!is_null($contentsSet)){
                        

                        if(!is_null($contentsSet['dim']['ProductWeight'])){
                            $product['ProductWeight']=$contentsSet['dim']['ProductWeight'];           
                            $product['ProductLength']=$contentsSet['dim']['ProductLength'];
                            $product['ProductWidth']=$contentsSet['dim']['ProductWidth'];
                            $product['ProductHeight']=$contentsSet['dim']['ProductHeight'];
                        }
                    } 

            }

            if(is_null($product['ProductWeight'])){$dataNotFound[]=$product['sku'];file_put_contents('datanotfound.json',json_encode($dataNotFound));continue;}
            
            

            $data_productos[$operation][] = 
            [                   
                'id' =>($operation=="update")?$product['shop_id']:null,
                //'name' => $product['title'],
                //'type' => 'simple',     
                //'manage_stock' => true,
                //"status" => 'publish',            
                /*'categories' => [
                    [
                        'id' => setIdShop($product["category_id"],$catalogs['Categories']),
                    ]
                ],
                'tags'=>[['id'=> setIdShop($product["manufacturer_id"],$catalogs['Manufacturers'])]],     
                'brands'=>['id'=> setIdShop($product["manufacturer_id"],$catalogs['Manufacturers'],"shop_id_brands")],   */    
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
                //exit;    
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

function woocommercecontents($products,$access,$id=null,$country=null,$operation="update"){
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

function woocommerceproducts($products,$access,$id=null,$country=null,$operation="create"){    
    set_time_limit(0);
    $woocommerce = createWoo($access);
    
    $catalogs = getCatalog($id,$country);
    
    $skus=[];
    $updateListProducts=[];

    if(in_array($operation,['create'])){$products=shopIdIsNull($products);/*$products=shopSkus($products,['8478CF']);*/}
    $x=0;
    foreach($products as $product){ 
        
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
        helper("storage");
        $productPrices = array_values(putPrices($id,true,[$product['sku']]));
        $product['stock'] = $productPrices['stock'];
        $product['price'] = $productPrices['price'];
        /////////////////////////////////////////////
        

        $data_productos[$operation][] = 
        [                   
            'id' =>($operation=="update")?$product['shop_id']:null,
            'sku' => $product['sku'],
            'stock_quantity' => $product['stock'],
            'regular_price' =>  $product['price'],
            'name' => $product['title'],
            'type' => 'simple',     
            'stock_status' => ((intval($product['price'])>0 && intval($product['stock'])>0)?"instock":"outofstock"),
            'manage_stock' => true,   
            'images' =>  $array_img,
            'description' => $info,
            "status" => 'publish',            
            'categories' => [
                [
                    'id' => setIdShop($product["category_id"],$catalogs['Categories']),
                ]
            ],
            'tags'=>[['id'=> setIdShop($product["manufacturer_id"],$catalogs['Manufacturers'])]],     
            'brands'=>['id'=> setIdShop($product["manufacturer_id"],$catalogs['Manufacturers'],"shop_id_brands")],       
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
            $x++;
            if($x>100){exit;}
            if(count($object_response_data->create)>0){                
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
                if($operation=="create"){updateShopProductsId($id,$country,$updateListProducts,$access);}
                $objct_response_data[] = $object_response_data;                          
            }
            $data_productos=[];
            $updateListProducts=[];        
            exit;    
        }  
    }
   /*  var_dump($data_productos);
    exit; */
    if(isset($data_productos[$operation])){               
        $object_response_data = $woocommerce->post('products/batch', $data_productos);
        foreach($object_response_data->create as $elem){    
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
            if($operation=="create"){updateShopProductsId($id,$country,$updateListProducts,$access);}
            $objct_response_data[] = $object_response_data;
        }
        $data_productos=[];
        $updateListProducts=[];
    } 
    return $skus;
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
                foreach($fileProducts["Categories"] as $key=>$category){                    
                    if(is_null($category['parent']['shop_id'])){
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
                    if(is_null($manufacturer['shop_id'])){
                        if(isset($list["tags"][$manufacturer['id']])){                            
                            $manufacturer['shop_id'] = $list["tags"][$manufacturer['id']];
                            if(isset($list["brands"][$manufacturer['id']])){
                                $manufacturer['shop_id_brands'] = $list["brands"][$manufacturer['id']];
                            }
                            $fileProducts["Manufacturers"][$key]=$manufacturer;
                        }
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
        foreach($elem["child"] as $elem_){
            $listCategories[]=$elem_;
        }
    }
    
    return ["Categories"=>$listCategories,"Manufacturers"=>$fileSegmentation['Manufacturers']];
}

function setIdShop($id,$catalog,$idLabel='shop_id'){
    $search_item = array_filter($catalog, 
        function($item) use($id){
            if(isset($item['id'])){
            return $item['id'] == $id;
            }
        }
    );
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