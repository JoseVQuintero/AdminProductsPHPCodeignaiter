<?php
/* require 'vendor/autoload.php';
use Automattic\opencart\Client; */

function token_shop($access)
{
   
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $access["url"]."/index.php?route=api/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "ip=addip534xx43267&key=".$access["key"]."&username=".$access["username"],
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return "{'error:''" . $err . "'}";
    } else {
        return $response;
    }
}

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

class OpenCart
{
    public function __construct($access, $token)
    {
        $this->token = $token;
        $this->access = $access;
    }

    public function post($segment,$data=[])
    {
        $arraySegment = explode("/",$segment);
        if($arraySegment[0]== 'brands'){return null;}
        $Segment[] = str_replace($arraySegment[0],"products", "shoptransaction");
        unset($arraySegment[0]);
        $Segment[] = implode("_",$arraySegment);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->access["url"] . "/index.php?route=api/".implode("/", $Segment)."&api_token=" . $this->token,
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_ENCODING => "",
            //CURLOPT_MAXREDIRS => 10,
            //CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        var_dump($response);
        exit;
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return json_decode("{'error:''" . $err . "'}");
        } else {
            return json_decode($response);
        }
    }
    public function get($segment, $data=[])
    {
        $arraySegment = explode("/", $segment);
        $Segment[] = str_replace($arraySegment[0], "products", "product");
        unset($arraySegment[0]);
        
        if(count($arraySegment)>0){
            if(is_numeric($arraySegment[0])){
                $data[]=["product_id"=> $arraySegment[0]];
            }else{
                if(!is_numeric($arraySegment[0])){
                    $data[] = ["sku" => $arraySegment[0]];
                }
            }
            $Segment[] = "get_product";
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->access["url"] . "/index.php?route=api/" . implode("/", $Segment) . "&api_token=" . $this->token,
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_ENCODING => "",
            //CURLOPT_MAXREDIRS => 10,
            //CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "{'error:''" . $err . "'}";
        } else {
            return $response;
        }
    }
    public function put($segment, $data = [])
    {
        $arraySegment = explode("/", $segment);
        if(in_array($arraySegment[0],['orders'])){
            $Segment[] = $arraySegment[0];      
            if (is_numeric($arraySegment[1])) {
                $data[] = ["product_id" => $arraySegment[1]];
            } else {
                if (!is_numeric($arraySegment[1])) {
                    $data[] = ["sku" => $arraySegment[1]];
                }
            }
            $Segment[] = "put_order";
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->access["url"] . "/index.php?route=api/" . implode("/", $Segment) . "&api_token=" . $this->token,
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_ENCODING => "",
            //CURLOPT_MAXREDIRS => 10,
            //CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "{'error:''" . $err . "'}";
        } else {
            return $response;
        }
    }
}

function createOpenCart($access){
    $token=json_decode(token_shop($access), true);    
    $opencarttoken = (isset($token['api_token'])) ? $token['api_token'] : $token;
    $opencart = new OpenCart($access, $opencarttoken);
    return $opencart;
}

function opencartstatuscomplete($order,$access){
    set_time_limit(0);    
    $opencart = createOpenCart($access);
    $update_order = [
        'status' => 'completed'
    ];
    return $opencart->put('orders/' . $order, $update_order);
}

function opencartmanufacturer($manufacturer,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);    
    $opencart = createOpenCart($access);
    $updateListManufacturer=[];
    foreach($manufacturer as $brand){    
        if(is_null($brand['shop_id'])){   

            $data_productos["create"][] = 
            [                   
                'name' =>$brand['manufacturer'],                
                'slug' => slugify($brand['manufacturer'])                    
            ];

            $object_response_data = $opencart->post('products/tags/batch', $data_productos); 
            
            if(count($object_response_data->create)>0){
                foreach($object_response_data->create as $elem){                    
                    $manufacturer_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                    $updateListManufacturer["tags"][$brand['id']]=$manufacturer_shop_id;
                    $updateListManufacturer["tags-name"][$brand['id']]=$brand['manufacturer'];
                    $updateListManufacturer["brands"][$brand['id']] = $manufacturer_shop_id;
                }
            }            
            
            try{
                $object_response_data = $opencart->post( 'brands' , $data_productos["create"][0] );                
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
    return $updateListManufacturer;  
}

function opencarttags($tags,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);    
    $opencart = createOpenCart($access);
    $updateListTags=[];
    foreach($tags as $tag){    
        //if(is_null($tag['shop_id'])){    
            $data_productos["create"][] = 
            [                   
                'name' =>$tag['name'],                
                'slug' => slugify($tag['name'])                    
            ];
            

            $object_response_data = $opencart->post('products/tags/batch', $data_productos); 
            
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

function opencartcategories($categories,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);
    $opencart = createOpenCart($access);
    $updateListCategories=[];
    $updateListCategories['parent']=null;
    $updateListCategories['chield']=null;
    foreach($categories as $category){  
       
        $data_category=[];

        $parent_shop_id=$category['parent']['shop_id'];
        if(is_null($parent_shop_id)){      
            $data_category["create"][] =        
            [
                'name' => $category['parent']['nombre']
            ];
            
            $object_response_data = $opencart->post('products/categories/batch', $data_category);       
            
            if(count($object_response_data->create)>0){
                foreach($object_response_data->create as $elem){                    
                    $parent_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                    $updateListCategories['parent'][$category['parent']['id']]=$parent_shop_id;
                    $updateListCategories['parent-name'][$category['parent']['id']]=$category['parent']['nombre'];
                }
            }                      
        }
        //echo json_encode($updateListCategories);
        //echo json_encode($category);
       // exit;

        if(isset($category['child'])){
            foreach($category['child'] as $chield){
                $data_category=[];
                if(is_null($chield['shop_id'])){
                    $data_category["create"][] =        
                        [
                            'parent' => $parent_shop_id,
                            'name' => $chield['nombre']
                        ];          
                    $object_response_data = $opencart->post('products/categories/batch', $data_category);  
                    if(count($object_response_data->create)>0){
                        foreach($object_response_data->create as $elem){                    
                            $chield_shop_id=($elem->id==0)?$elem->error->data->resource_id:$elem->id;  
                            $updateListCategories['chield'][$chield['id']]=["parent"=>$parent_shop_id,"chield"=>$chield_shop_id];
                            $updateListCategories['chield-name'][$chield['id']]=$chield['nombre'];
                            $updateListCategories['chield-parent'][$chield['id']]=$chield['parent_id'];
                        }
                    }                                                             
                }
            }
        }

        //echo json_encode($updateListCategories);
        //echo json_encode($category);
        //exit;
        

        if(isset($updateListCategories['chield'])||isset($updateListCategories['parent'])){
            updateShopCategoriesId($id,$country,$updateListCategories);   
            if(is_null($operation)){
                $updateListCategories['parent'] = null;
                $updateListCategories['chield'] = null;
            }         
        }           

    }

    return $updateListCategories;

}

function opencartprices($products,$access,$id=null,$country=null,$operation=null){
    set_time_limit(0);
    $opencart = createOpenCart($access);
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
            $object_response_data = $opencart->post('products/batch', $data_productos);
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
        $object_response_data = $opencart->post('products/batch', $data_productos);
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


$access_=null;
$parent_id_new=null;
$catalogs_ = null;
function opencartproductsrc($products,$access,$id=null,$country=null,$operation="update"){
    global $access_;
    global $parent_id_new;
    global $catalogs_;
    
    set_time_limit(0);

    $access_= $access;
    $opencart = createOpenCart($access);
    $catalogs_ = getCatalog($id,$country);

    $products=shopIdIsNotNull($products);
    $x=0;

    //$products=shopSkus($products,['U82594','U82577','U82569','4964DV','139KKC','3372DT','8375DH','4151CZ','8376DH','70026Z','7170FC','33671T','607XXA','26893Y','72080U','6530DK','46407K','07022M','80661Y','94120K','81182P','55017Z','5145CH','24581L','152LLD','960KKA','23994V','158LLD','19472T','80875Y','7125EA','04027X','748063','985423','985819','38239C','20165E','985010','7925EC','10076M','65970X','9306DE','5722DU','8478CF','65065P','55048W','1673CK','35241P','1230CK','24469L','54951W','19452Q','6980DX','85944U','82170Y','564NNL','82782Y','65742M','81752L','53754V','40420T','21559Z','28084W','5640DK','2616XD','24443L','5637DK','905XXW','4724DU','5338DU','72836T','5636DK','2757ZY','5635DK','82344K','83568P','55020Z','294AAP','634WWV','28297W','1231CK','31459T','60082U','8796CY','55558Q','5643DK','66180M','85941U','82349K','72834T','5638DK','7920EC','247CCU','7M3628','575NNK','831PPK','6124FA','511PPK','862DDM','701A1G','7839EA','3935DX','718AAP','813CCQ','8164ZS','037XXM','512PPK','543CCQ','1555CK','475XXA','861DDM','5596ZC','227A1S','31061Y','30909Y','8281CP','91158L','91131L','94462U','94461U','91234L','77313L','91132L','90635L','679JJJ','3349DV','84591T','91086L','91143L','91212L','77295L','94453U','91211L','70435N','02907P','19425M','77302L','94456U','90247L','90386L','674JJJ','481DDN','38522T','319NNL','70436N','1713CK','90540L','781VVV','922A9E','2430RD','297ZZF','293ZZF','294ZZF','1325ZW','606A1P','590A8F','317A2E','357A8S','362MMD','897CCX','906CCX','353A8S','350A8S','582VVU','583VVU','869AAB','649VVU','554HHF','2575FA','790VVU','557HHF','995ZZF','88937V','78421Y','4638ZC','662MMG','79885T','52544G','47801Q','35315R','150WWZ','652A9F','382AAN','651A9F','848A8V','564A8U','7332DQ','91135L','70334N','91200L','516DDX','649VVK','70590N','565DDV','70433N','91127L','91156L','1079CL','118CCQ','7040ZB','62690U','468CCW','D84386','326A1X','5842ZF','782JJJ','D85147','D85146','VS3438','2022DY','586AAU','787A5R','788A5R','789A5R','3914ZR','3916ZR','6309CT','799A5R','145A2G','279XXZ','9485ZD','282XXZ','765A2H','971A2L','28827Z','28826Z','304KKF','152C1A','151C1A','242A7K','241A7K','243A7K','150C1A','26105Z','942VVZ','686PPJ','115A2B','981A2A','966A2A','910A2A','302MMH','685A5Z','689A5Z','687A5Z','197KKG','684A5Z','97009Y','97008Y','928FFP']);
    $products=shopSkus($products,['70027Y', '1992ZT']);
    
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
            /* echo json_encode($data_productos);
            exit; */

            $skus['skus'][]=$product['sku'];
            if(count($data_productos[$operation])>0){               
                $object_response_data = $opencart->post('products/batch', $data_productos);
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
                                    $productDuplicate = $opencart->get('products/'.$product_id);                            
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
        $object_response_data = $opencart->post('products/batch', $data_productos);
        foreach($object_response_data->update as $elem){    
            if(isset($elem->id)){                
                if(isset($elem->id)){     
                    $product_id=($elem->id==0)?((isset($elem->error->data->resource_id))?$elem->error->data->resource_id:0):$elem->id;  
                    if($elem->id==0){     
                        if($product_id>0){                       
                            $productDuplicate = $opencart->get('products/'.$product_id);                            
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

function opencartcontents($products,$access,$id=null,$country=null,$operation="update"){
    set_time_limit(0);
    $opencart = createOpenCart($access);

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
                $object_response_data = $opencart->post('products/batch', $data_productos);
                
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
            $object_response_data = $opencart->post('products/batch', $data_productos);
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

function opencartproducts($products,$access,$id=null,$country=null,$operation="create"){     

    /* $instanceCurrent = ((file_exists("getSegmentation/".$country.'/'.$id."/instancecurrent.json"))?read_file_json("getSegmentation/".$country.'/'.$id."/instancecurrent.json",true):["wooproducts"=>returnDate(returnAccess()['zone'][$country],"Y-m-d h:i")]);
    $diffIntance = returnDiffDate("i",returnAccess()['zone'][$country],$instanceCurrent['wooproducts']);

    if($diffIntance<=10){
        if(!file_exists("getSegmentation/".$country.'/'.$id."/instancecurrent.json")){
            write_file_root("getSegmentation/".$country.'/'.$id."/","instancecurrent.json",json_encode($instanceCurrent,JSON_UNESCAPED_UNICODE));
        }else{
            return ["status"=>"error","msg"=>"Instance active"];
        }        
    } */   

    set_time_limit(0);
    $cuantos = 10;
    $opencart = createOpenCart($access);
    
    $catalogs = getCatalog($id,$country);
    
    $skus=[];
    $updateListProducts=[];

    //if(in_array($operation,['create'])){$products=shopSkus($products,['198A7L','603VVS','200A7L','186WWL','433VVC','893VVV','501A1P','821PPU']);}
    //if(in_array($operation,['create'])){$products=shopIdIsNull($products);/* $products=shopSkus($products,['198A7L','603VVS','200A7L','186WWL','433VVC','893VVV','501A1P','821PPU']); */}
    //$skusFilters = ((file_exists("getSegmentation/".$country.'/'.$id."/skusfilters.json"))?read_file_json("getSegmentation/".$country.'/'.$id."/skusfilters.json",true):[]);
    if(in_array($operation,['create'])){$products=shopIdIsNull($products); /* $products=shopSkus($products,$skusFilters); */}
    /* echo count($products);
    exit; */
    $x=0;

    /* var_dump($products);
    exit; */
   
    $productsSeg=array_chunk($products,$cuantos);
    
    helper("storage");

    foreach($productsSeg as $products_){
        
        //getPrices///////////////////////////////////        
        $productsSKU = array_column($products_,'sku');
        $productPrices = putPrices($id,true,$productsSKU);
        /////////////////////////////////////////////
       
        try{
            //foreach ($products_ as $product) {
            foreach($productPrices as $product){ 
                
                    if(is_null($product['shop_id'])&&$operation=="update"){continue;}            
                    if(!is_null($product['shop_id'])&&$operation=="create"){continue;}
                    
                    $info = $product['ficha_html'];
                    $array_img=null;

                    if (!is_null($product['images']) && !empty($product['images'])) {
                        $imagesList = json_decode($product['images'], true);
                        $imagesList = (isset($imagesList['imageGalleryUrlHigh'])) ? $imagesList['imageGalleryUrlHigh'] : $imagesList;
                        foreach ($imagesList as $item) {
                            if (url_exists($item)) {
                                $array_img[] = ["src" => $item];
                            }
                        }
                    }

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
                    $skus['skus'][]=$product['sku'];
            } 
                
           
            //if(count($data_productos[$operation])>= $cuantos){      
                
                $object_response_data = $opencart->post('products/batch', $data_productos);
                

                //$x++;
                //if($x>100){exit;}
                if(count($object_response_data->create)>0){  
                    //getPrices///////////////////////////////////
                    /*helper("storage");
                    $product_ = array_column($data_productos[$operation],'sku');
                    $productPrices = putPrices($id,true,$product_);
                    opencartprices($productPrices,$access,$id,$country);*/
                    /////////////////////////////////////////////         
                    foreach($object_response_data->create as $elem){                     
                        if(isset($elem->id)){     
                            $product_id=($elem->id==0)?((isset($elem->error->data->resource_id))?$elem->error->data->resource_id:0):$elem->id;  
                            if($elem->id==0){     
                                if($product_id>0){                       
                                    $productDuplicate = $opencart->get('products/'.$product_id);                            
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

                $putproducts = ((file_exists("getSegmentation/".$country.'/'.$id."/put-products-add.json"))?read_file_json("getSegmentation/".$country.'/'.$id."/put-products-add.json",true):[]);
                $putproducts[]=[returnDate(returnAccess()['zone'][$country],"Y-m-d_H")=>$updateListProducts];
                write_file_root("getSegmentation/".$country.'/'.$id."/","put-products-add.json",json_encode($putproducts,JSON_UNESCAPED_UNICODE));
                $data_productos[$operation]=[];
                $updateListProducts=[]; 
                
            //}  
               
        }catch (Exception $e) {
            $log = ((file_exists("getSegmentation/".$country.'/'.$id."/log.json"))?read_file_json("getSegmentation/".$country.'/'.$id."/log.json",true):[]);
            $log[]=[returnDate(returnAccess()['zone'][$country],"Y-m-d_H"),$e->getMessage(),$object_response_data];
            write_file_root("getSegmentation/".$country.'/'.$id."/","log.json",json_encode($log,JSON_UNESCAPED_UNICODE));            
        }        

        $instanceCurrent['wooproducts'] = returnDate(returnAccess()['zone'][$country],"Y-m-d h:i");
        write_file_root("getSegmentation/".$country.'/'.$id."/","instancecurrent.json",json_encode($instanceCurrent,JSON_UNESCAPED_UNICODE));
        
    }
   /*  var_dump($data_productos);
    exit; */
    try{
        if(count($data_productos[$operation])>0){               
            $object_response_data = $opencart->post('products/batch', $data_productos);
            foreach($object_response_data->create as $elem){    
                if(isset($elem->id)){                
                    if(isset($elem->id)){     
                        $product_id=($elem->id==0)?((isset($elem->error->data->resource_id))?$elem->error->data->resource_id:0):$elem->id;  
                        if($elem->id==0){     
                            if($product_id>0){                       
                                $productDuplicate = $opencart->get('products/'.$product_id);                            
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
            $data_productos[$operation]=[];
            $updateListProducts=[];
        } 
    }catch (Exception $e) {
        $log = ((file_exists("getSegmentation/".$country.'/'.$id."/log.json"))?read_file_json("getSegmentation/".$country.'/'.$id."/log.json",true):[]);
        $log[]=[returnDate(returnAccess()['zone'][$country],"Y-m-d_H"),$e->getMessage(),$object_response_data];
        write_file_root("getSegmentation/".$country.'/'.$id."/","log.json",json_encode($log,JSON_UNESCAPED_UNICODE));
    } 

    $instanceCurrent['wooproducts'] = returnDate(returnAccess()['zone'][$country],"Y-m-d h:i");
    write_file_root("getSegmentation/".$country.'/'.$id."/","instancecurrent.json",json_encode($instanceCurrent,JSON_UNESCAPED_UNICODE));

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

                $arrayData=[];
                foreach($fileProducts["Categories"] as $key=>$category){         
                    if(isset($category['parent'])){         
                        if(is_null($category['parent']['shop_id'])){
                            if(isset($list['parent'][$category['parent']['id']])){
                                $category['parent']['shop_id'] = $list['parent'][$category['parent']['id']];
                                $fileProducts["Categories"][$key]['parent']=$category['parent'];   
                                unset($list['parent'][$category['parent']['id']]);
                            }
                        }
                    }
                    if(isset($category['child'])){ 
                        foreach($category['child'] as  $keyC=>$chield){
                            if(isset($list['chield'][$chield['id']])){                            
                                $chield['shop_id'] = $list['chield'][$chield['id']]['chield'];
                                $fileProducts["Categories"][$key]['child'][$keyC]=$chield;
                                unset($list['chield'][$chield['id']]['chield']);
                                unset($list['chield'][$chield['id']]['parent']);
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
                    if (count($list["parent"]) > 0) {                        
                        $fileProducts["Categories"][]['parent']=[
                            "shop_id"=> $list["parent"][key($list["parent"])],
                            "id"=> key($list["parent"]),
                            "parent_id"=> 0,
                            "nombre"=> $list["parent-name"][key($list["parent"])]
                        ];
                    }
                }

                if(!empty($list["chield"])){
                    /* echo json_encode($list);
                    exit; */
                    foreach($list["chield"] as $key=>$item){  
                        if(count($item)>0){
                            foreach($arrayData as $key_=>$value){
                                if(in_array($list["chield-parent"][$key],$value)){
                                    $indexData = $key_;
                                }
                            }
                            if(!is_null($indexData)){
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
                        if(!is_null($indexData)){
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
                    if(is_null($manufacturer['shop_id'])){
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
                /*if(!empty($list["tags"])){
                    foreach($list["tags"] as $key=>$item){
                        $fileProducts["Manufacturers"][]=[
                            "shop_id"=> $item,
                            "id"=> $key,
                            "manufacturer"=> $list["tags-name"][$key],
                            "shop_id_brands"=> $list["brands"][$key]
                        ];
                    }
                }*/
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
        return ["shop_id"=>array_values(opencartmanufacturer($manufacturer,$access_,$user_id,$country)['tags'])];
    }
    if($type=='category'){
        $category = $db->table('categorias_'.$country)->where(['id' => $id, 'type' => 'category-product'])->get()->getResultArray();
        $category_[]= ["parent"=>[
            "shop_id"=> null,
            "id"=> $category[0]['id'],
            "parent_id"=> 0,
            "nombre"=> $category[0]['name'],"child"=>[]]
        ];
        $response = opencartcategories($category_,$access_,$user_id,$country,"request")['parent'];
        
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
        $response = opencartcategories($subCategory_,$access_,$user_id,$country, "request")['chield'];       
        return ["shop_id"=>$response[key($response)]['chield']];
    }
    if($type=='tags'){
        $productLine = $db->table('categorias_'.$country)->where(['id' =>$id, 'type' => 'line-product'])->get()->getResultArray();
        return ["shop_id"=>array_values(opencarttags($productLine,$access_,$user_id,$country)['tags'])];
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