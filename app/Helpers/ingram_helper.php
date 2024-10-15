<?php
function returnAccess(){
    return  
    /*[
        'default'=>'getIngramPriceV5',
        'getIngramPriceXML'=>['sender'=>null,'login'=>null,'pass'=>null,'ReservedInventory'=>'Y',"products"=>null,"prices"=>null,"contents"=>null,"productsrc"=>null,"categories"=>null,"manufacturer"=>null,"WEEK"=>null],
        'getIngramPriceV5'=>['client_id'=>null,'client_secret'=>null,'customernumber'=>null,'isocountrycode'=>null,"products"=>null,"prices"=>null,"contents"=>null,"productsrc"=>null,"categories"=>null,"manufacturer"=>null,"WEEK"=>null],
        'getIngramPriceV6'=>['client_id'=>null,'client_secret'=>null,'IM-CustomerNumber'=>null,'IM-CorrelationID'=>null,'IM-CountryCode'=>null,'IM-SenderID'=>null,'showReserveInventoryDetails'=>'true','showAvailableDiscounts'=>'false','availabilityForAllLocation'=>'true','includeAvailability'=>'true','includePricing'=>'false','includeProductAttributes'=>'false',"products"=>null,"prices"=>null,"contents"=>null,"productsrc"=>null,"categories"=>null,"manufacturer"=>null,"WEEK"=>null]
    ];*/
    [
    'default'=>'IngramV5',
    'shopdefault'=>null,
    'zonedefault'=>null,
    'zone'=>['mx'=>'America/Mexico_City','co'=>'America/Bogota','ca'=>'America/Vancouver','pe'=>'America/Lima'],
    'shop'=>['opencart'=>['url'=>null,'key'=>null,'clientIdBDI' => null, 'clientBDICountry' => null,'username' =>null, 'storeutility' =>null,'specialutility' => null, 'iva' => null, 'emails'=>null, 'emailto'=>null,'emailfrom'=>null,'host'=>null,'hostuser'=>null,'hostpass'=>null],'woocommerce'=>['url'=>null,'ck'=>null,'cs'=>null, 'clientIdBDI' => null, 'clientBDICountry' => null, 'urlInt'=>null,'userInt'=>null,'passInt'=>null,'storeutility'=>null, 'specialutility' => null, 'iva' =>null,'emails' => null, 'emailto' => null, 'emailfrom' => null, 'host' => null, 'hostuser' => null, 'hostpass' => null],'integration'=>['name'=>null,'emails' => null, 'emailto' => null, 'emailfrom' => null, 'host' => null, 'hostuser' => null, 'hostpass' => null]],
    'IngramXML'=>['sender'=>null,'login'=>null,'pass'=>null,'ReservedInventory'=>'Y',"products"=>null,"prices"=>null,"contents"=>null,"productsrc"=>null,"categories"=>null,"manufacturer"=>null,"WEEK"=>null],
    'IngramV5'=>['client_id'=>null,'client_secret'=>null,'customernumber'=>null,'isocountrycode'=>null,"extendedspecs"=>[["attributename"=>"placeoncustomerhold","attributevalue"=>true]],"linetype"=>"P","carriercode"=>"E1","products"=>null,"prices"=>null,"contents"=>null,"productsrc"=>null,"categories"=>null,"manufacturer"=>null,"WEEK"=>null],
    'IngramV6'=>['client_id'=>null,'client_secret'=>null,'IM-CustomerNumber'=>null,'IM-CorrelationID'=>null,'IM-CountryCode'=>null,'IM-SenderID'=>null,'showReserveInventoryDetails'=>'true','showAvailableDiscounts'=>'false','availabilityForAllLocation'=>'true','includeAvailability'=>'true','includePricing'=>'false','includeProductAttributes'=>'false',"products"=>null,"prices"=>null,"contents"=>null,"productsrc"=>null,"categories"=>null,"manufacturer"=>null,"WEEK"=>null],
    ];
}
function returnUtility($country,$id)
{
    return (file_exists("getSegmentation/" . $country . '/' . $id . 'utilityspecial.json')) ?
        json_decode(file_get_contents("getSegmentation/". $country . '/' . $id . 'utilityspecial.json'), true):null;
}

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function httpsPost($Url, $strRequest)
{
	// Initialisation
	$ch=curl_init();
	// Set parameters
	curl_setopt($ch, CURLOPT_URL, $Url);
	// Return a variable instead of posting it directly
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// Active the POST method
	curl_setopt($ch, CURLOPT_POST, 1) ;
	// Request
	curl_setopt($ch, CURLOPT_POSTFIELDS, $strRequest);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$headers = array("Content-type: text/xml", "Content-length: " . strlen($strRequest), "Connection: close");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	// execute the connexion
	$result = curl_exec($ch);
	// Close it
	curl_close($ch);
	return $result;
}



function IngramXML($access=null, $skus = NULL)
{
    global $bdliniomx_test;
    global $bdliniomx;	 

    $string = '<?xml version="1.0" encoding="ISO-8859-1"?><PNARequest><Version>2.0</Version>';
    $string.= '<TransactionHeader><SenderID>'.$access['sender'].'</SenderID>';
    $string.= '<ReceiverID>INGRAM MICRO</ReceiverID>';
    $string.= '<CountryCode>MX</CountryCode><LoginID>'.$access['login'].'</LoginID><Password>'.$access['pass'].'</Password>';
    $string.= '<TransactionID>{A0DEA52B-341F-40C3-9C80-77A4A84F9EB7}</TransactionID></TransactionHeader>';

    foreach ($skus as $sk) {
        $string.= '<PNAInformation SKU="'.$sk.'" Quantity="1" ReservedInventory="'.$access['ReservedInventory'].'"/>';
    }		
    
    $string.= '<ShowDetail>0</ShowDetail></PNARequest>';
    $url = 'https://newport.ingrammicro.com/MUSTANG';

    $doc0 = new DOMDocument();
    $doc0->loadXML($string);
    $pnaReq = "xml/PNARequest-multiple-request.xml";
    $doc0->save("$pnaReq");
    

    $strRequest = utf8_encode($string);
    $Response = httpsPost($url, $strRequest);


    $doc = new DOMDocument();
    $doc->loadXML($Response);

    $pnaResp = "xml/PNARequest-muñtiple-response.xml";
    $doc->save("$pnaResp");
    
    $ErrorStatus = $doc->getElementsByTagName("ErrorStatus");
    $ErrorNumber = $ErrorStatus->item(0)->getAttribute("ErrorNumber");
    $TotAvail    = 0;
    if(strlen($ErrorNumber)<=0){
        $PriceAndAvailability = $doc->getElementsByTagName( "PriceAndAvailability" );
        foreach( $PriceAndAvailability as $PriceAndAvailability ){
            $List_Branchs = [];
            $Prices = @$PriceAndAvailability->getElementsByTagName( "Price" );				  
            $Price = @$Prices->item(0)->nodeValue;
            $Parts = $PriceAndAvailability->getElementsByTagName( "ManufacturerPartNumber" );					  
            $Branchs = $PriceAndAvailability->getElementsByTagName( "Branch" );
            $Avails = $PriceAndAvailability->getElementsByTagName( "Availability" );	 

            if(!isset($bdliniomx[$PriceAndAvailability->getAttribute('SKU')])){
                $bdliniomx[$PriceAndAvailability->getAttribute('SKU')]=0;
                $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["valid"]["stock"]=0;
            }

            if(!isset($bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["pna"])){
                $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["pna"]=null;
            }

            if(!isset($bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch"])){
                $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch"]=[];
            }

            if(!isset($bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch-add"])){
                $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch-add"]=[];
            }

            for ($i = 0; $i < $Branchs->length; $i++) {
                                        
                if((int)$Branchs->item($i)->getAttribute('ID')==5||(int)$Branchs->item($i)->getAttribute('ID')==73){
                    $List_Branchs[] = [$Branchs->item($i)->getAttribute('ID')=>(($Avails->item($i)->nodeValue>=0)?$Avails->item($i)->nodeValue:0)];
                    if($Avails->item($i)->nodeValue>=0){
                        $TotAvail += $Avails->item($i)->nodeValue;
                    }							
                }						
                $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch"]["test"][$Branchs->item($i)->getAttribute('ID')] = [in_array($Branchs->item($i)->getAttribute('ID'),$bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch"])=>$Avails->item($i)->nodeValue];
                if(!in_array($Branchs->item($i)->getAttribute('ID'),$bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch"])){
                    $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch"][] = $Branchs->item($i)->getAttribute('ID');
                    $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["branch-add"][] = (int)$Avails->item($i)->nodeValue;
                    $bdliniomx[$PriceAndAvailability->getAttribute('SKU')]+=(int)$Avails->item($i)->nodeValue;	
                    $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["valid"]["stock"]+=(int)$Avails->item($i)->nodeValue;
                    if((int)$Avails->item($i)->nodeValue>0){
                        $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["valid"]["values"][]=[$Branchs->item($i)->getAttribute('ID')=>(int)$Avails->item($i)->nodeValue];
                    }
                }
                $bdliniomx_test[$PriceAndAvailability->getAttribute('SKU')]["pna"][$sender][$Branchs->item($i)->getAttribute('ID')]=(int)$Avails->item($i)->nodeValue;
            }				  				  		  
        }
    }
}	

function IngramV5Match($pricesProduct,$products_,$product){
    $products = array_column($products_,"sku");
    $itemUpdate_=[];
    
    if(isset($pricesProduct['serviceresponse']['responsepreamble'])){
        
        if(in_array($pricesProduct['serviceresponse']['responsepreamble']['responsestatus'],['SUCCESS','SUCCESSWITHERROR'])){
            $sku_success = [];
           
            foreach($pricesProduct['serviceresponse']['priceandstockresponse']['details'] as $itemPrices){
              
                $itemUpdate__=[];

                if(!in_array($itemPrices['itemstatus'],['FAILED'])){
                    if(isset($itemPrices['ingrampartnumber'])){

                        $stock =0;
                        $warehouse=[];

                        if(isset($itemPrices['warehousedetails'])){
                            
                            if(count($itemPrices['warehousedetails'])>0){              
 
                                foreach($itemPrices['warehousedetails'] as $st){
                                    $stock = $stock+((isset($st['availablequantity']))?$st['availablequantity']:0);
                                    if($st['availablequantity']>0){
                                        $warehouse[]=["warehousedescription"=>(isset($st['warehousedescription']))?$st['warehousedescription']:null,"warehouseid"=>((isset($st['warehouseid']))?$st['warehouseid']:-1),"availablequantity"=>((isset($st['availablequantity']))?$st['availablequantity']:-1)];
                                    }
                                }                              
                            }
                        }

                     
                        
                                         
                        $itemUpdate__['index'] = array_search($itemPrices['ingrampartnumber'],$products);
                        $itemUpdate__['price']=$itemPrices['customerprice'];
                        

                        if (isset($itemPrices['skuauthorized'])) {
                            if ($itemPrices['skuauthorized'] != "Y") {
                                $stock = 0;
                            }
                        }

                        
                        $itemUpdate__['stock']=$stock;
                        $itemUpdate__['active']=(isset($itemPrices['skuauthorized'])? $itemPrices['skuauthorized']: $itemPrices['isavailable']);
                        $itemUpdate__['warehouse']=(count($warehouse)>0)?$warehouse:null;                        
                        $itemUpdate__['message']=$itemPrices['itemstatus'];
                        $itemUpdate__['title']=((isset($itemPrices['partdescription1']))?$itemPrices['partdescription1']:'')." ".((isset($itemPrices['partdescription2']))?$itemPrices['partdescription2']:'');
                        $itemUpdate_[]=$itemUpdate__;
                        $sku_success[]=$itemPrices['ingrampartnumber'];
                       
                        
                    }
                }else{
                    $sku = explode('Number: ',$itemPrices['statusmessage']);
                    if(isset($sku[1])){
                        $itemUpdate__['index'] = array_search($sku[1],$products);
                        $itemUpdate__['price']=null;
                        $itemUpdate__['stock']=null;
                        $itemUpdate__['warehouse']=null; 
                        $itemUpdate__['active']=null;
                        $itemUpdate__['message']=(isset($itemPrices['statusmessage']))?$itemPrices['statusmessage']:'return null';
                        $itemUpdate__['title']=null;
                        $itemUpdate_[]=$itemUpdate__;
                        $sku_success[]=$sku[1];
                    }
                }
               
            }

           
 
            foreach($product as $item){
                $itemUpdate__=[];
                if(!in_array($item,$sku_success)){
                    $itemUpdate__['index'] = array_search($item,$products);
                    $itemUpdate__['price']=null;
                    $itemUpdate__['stock']=null;
                    $itemUpdate__['warehouse']=null; 
                    $itemUpdate__['active']=null;
                    $itemUpdate__['message']='return null';
                    $itemUpdate__['title']=null;
                    $itemUpdate_[]=$itemUpdate__;
                }
            }
        }
    }
    
    return $itemUpdate_;
}

function IngramV5($muchos,$access=null)
{
    try{
        $muchos_v5 = [];
        foreach($muchos as $item) {
            array_push($muchos_v5,array("ingrampartnumber"=>$item,"quantity"=>1));
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.ingrammicro.com:443/oauth/oauth20/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=".$access['client_id']."&client_secret=".$access['client_secret']);
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);    
        if (curl_errno($ch)) {
            $message =  'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $a = json_decode($result);
        $token = $a->access_token;
    
        $productos = json_encode($muchos_v5);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ingrammicro.com:443/resellers/v5/catalog/priceandavailability",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"servicerequest\":{\"requestpreamble\":{\"customernumber\":\"".$access['customernumber']."\",\"isocountrycode\":\"".$access['isocountrycode']."\"},\"priceandstockrequest\":{\"showwarehouseavailability\":\"True\",\"extravailabilityflag\":\"Y\",\"item\":" . $productos . ",\"includeallsystems\":false}}}",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $token
            ),
        ));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $message =  'Error:' . curl_error($curl);
        }
        
        curl_close($curl);
        return $response;
    } catch (Exception $e) {
        return json_encode(["status"=>'error','message'=> $message.' '.$e->getMessage()]);
    }
}

function IngramV6($muchos,$access=null)
{
    try{
        $ch = curl_init();

        $muchos_v6 = [];
        foreach($muchos as $item) {
            array_push($muchos_v6,array("ingramPartNumber"=>$item));
        }

        curl_setopt($ch, CURLOPT_URL, 'https://api.ingrammicro.com:443/oauth/oauth20/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=".$access['client_id']."&client_secret=".$access['client_secret']);
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $message =  'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $a = json_decode($result);
        $token = $a->access_token;
        $curl = curl_init();

        $sku = json_encode($muchos_v6);

        $post_field = "{\"showReserveInventoryDetails\": ".$access['showReserveInventoryDetails'].", \"showAvailableDiscounts\": ".$access['showAvailableDiscounts'].", \"availabilityByWarehouse\": [{\"availabilityForAllLocation\": ".$access['availabilityForAllLocation']."}],\"products\": ".$sku."}";
        //var_dump($post_field);
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.ingrammicro.com:443/resellers/v6/catalog/priceandavailability?includeAvailability=".$access['includeAvailability']."&includePricing=".$access['includePricing']."&includeProductAttributes=".$access['includeProductAttributes']."",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>$post_field,
        CURLOPT_HTTPHEADER => array(
            "Accept: application/json",
            "Content-Type: application/json",
            "IM-CustomerNumber: ".$access['IM-CustomerNumber'],
            "IM-CorrelationID: ".$access['IM-CorrelationID'],
            "IM-CountryCode: ".$access['IM-CountryCode'],
            "IM-SenderID: ".$access['IM-SenderID'],
            "Authorization: Bearer ".$token
        ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $message =  'Error:' . curl_error($curl);
        }


        return $response;
    } catch (Exception $e) {
        return json_encode(["status"=>'error','message'=> $message.' '.$e->getMessage()]);
    }
}
