<?php
if(!function_exists('curl')){
    function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
if(!function_exists('httpsPost')){
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
}

function stateColombia($state){
    $states = [
        "91" => ["CO-AMA", "Amazonas", "AMZ", "Amazonas"],
        "08" => ["CO-ANT", "Antioquia", "ANT", "Antioquia"],
        "81" => ["CO-ARA", "Arauca", "ARU", "Arauca"],
        "08" => ["CO-ATL", "Atlántico", "ATL", "Atlántico"],
        "13" => ["CO-BOL", "Bolívar", "BOL", "Bolívar"],
        "15" => ["CO-BOY", "Boyacá", "BOY", "Boyacá"],
        "17" => ["CO-CAL", "Caldas", "CAL", "Caldas"],
        "18" => ["CO-CAQ", "Caquetá", "CAQ", "Caquetá"],
        "85" => ["CO-CAS", "Casanare", "CAS", "Casanare"],
        "19" => ["CO-CAU", "Cauca", "CAU", "Cauca"],
        "20" => ["CO-CES", "Cesar", "CES", "Cesar"],
        "27" => ["CO-CHO", "Chocó", "CHO", "Chocó"],
        "23" => ["CO-COR", "Córdoba", "COR", "Córdoba"],
        "25" => ["CO-CUN", "Cundinamarca", "CUN", "Cundinamarca"],
        "11" => ["CO-DC", "Distrito Capital", "BOG", "Bogotá"],
        "94" => ["CO-GUA", "Guainía", "GUA", "Guainía"],
        "95" => ["CO-GUV", "Guaviare", "GUV", "Guaviare"],
        "41" => ["CO-HUI", "Huila", "HUI", "Huila"],
        "44" => ["CO-LAG", "La Guajira", "GUJ", "La Guajira"],
        "47" => ["CO-MAG", "Magdalena", "MAG", "Magdalena"],
        "50" => ["CO-MET", "Meta", "MET", "Meta"],
        "52" => ["CO-NAR", "Nariño", "NAR", "Nariño"],
        "54" => ["CO-NSA", "Norte de Santander", "NOR", "Norte de Santander"],
        "86" => ["CO-PUT", "Putumayo", "PUT", "Putumayo"],
        "63" => ["CO-QUI", "Quindío", "QUI", "Quindío"],
        "66" => ["CO-RIS", "Risaralda", "RIS", "Risaralda"],
        "68" => ["CO-SAN", "Santander", "SAN", "Santander"],
        "88" => ["CO-SAP", "San Andrés y Providencia", "SAP", "San Andrés y Providencia"],
        "70" => ["CO-SUC", "Sucre", "SUC", "Sucre"],
        "73" => ["CO-TOL", "Tolima", "TOL", "Tolima"],
        "76" => ["CO-VAC", "Valle del Cauca", "VAC", "Valle del Cauca"],
        "97" => ["CO-VAU", "Vaupés", "VAU", "Vaupés"],
        "99" => ["CO-VID", "Vichada", "VIC", "Vichada"]
    ];
    $resState = $state;
    foreach($states as $key=>$values){
        if(in_array($state,$values)){
            $resState=$key;
        }
    }
    return $resState;
    /* $estado=$state;
    switch ($state) {
				case "Antioquia"   : $estado = '05';  	break;
				case "Atlantico"   : $estado = '08'; 	break;
				case "Bogota" :     $estado = '11'; 	break;
				case "Bolivar"	    : $estado = '13';  break;
				case "Boyaca"	     : $estado = '15';     break;
				case "Caldas"	     : $estado = '17';     break;
				case "Caqueta"	    : $estado = '18';      break;
				case "Cauca"	      : $estado = '19';    break;
				case "Cesar"	      : $estado = '20';    break;
				case "Cordoba"	    : $estado = '23';      break;
				case "Cundinamarca" : $estado = '25'; 	        break;
				case "Choco"	      : $estado = '27';    break;
				case "Huila"	      : $estado = '41';    break;
				case "Guajira"	    : $estado = '44';      break;
				case "Magdalena"	  : $estado = '47';        break;
				case "Meta"	       : $estado = '50';   break;
				case "Narino"	     : $estado = '52';     break;
				case "Norte de Santander" : $estado = '54'; 	        break;
				case "Quindio"	    : $estado = '63';      break;
				case "Risaralda"	  : $estado = '66';        break;
				case "Santander"	  : $estado = '68';        break;
				case "Sucre"	      : $estado = '70';    break;
				case "Tolima"	     : $estado = '73';     break;
				case "Valle del Cauca" : $estado = '76'; 	        break;
				case "Arauca"	     : $estado = '81';     break;
				case "Casanare"	   : $estado = '85';       break;
				case "Putumayo"	   : $estado = '86';       break;
				case "San Andres y Providencia" : $estado = '88'; 	        break;
				case "Amazonas"	   : $estado = '91';       break;
				case "Guainia"	    : $estado = '94';      break;
				case "Guaviare"	   : $estado = '95';       break;
				case "Vaupes"	     : $estado = '97';     break;
				case "Vichada"	    : $estado = '99';      break;
    } */    
}

function mapPOSTFIELDSV5($order_data,$user){
    try {
        $country=$user['country'];
        $access_=json_decode($user['access'],true);
        $access=$access_[$access_['default']];
        //## BILLING INFORMATION:
        $order_billing_first_name = $order_data['billing']['first_name'];
        $order_billing_last_name = $order_data['billing']['last_name'];
        $order_billing_company = $order_data['billing']['company'];
        $order_billing_address_1 = $order_data['billing']['address_1'];
        $order_billing_address_2 = $order_data['billing']['address_2'];
        $order_billing_city = $order_data['billing']['city'];
        $order_billing_state = $order_data['billing']['state'];
        $order_billing_postcode = $order_data['billing']['postcode'];
        $order_billing_country = $order_data['billing']['country'];
        $order_billing_email = $order_data['billing']['email'];
        $order_billing_phone = $order_data['billing']['phone'];
        
        //## SHIPPING INFORMATION:
        $order_shipping_first_name = $order_data['shipping']['first_name'];
        $order_shipping_last_name = $order_data['shipping']['last_name'];
        $order_shipping_company = $order_data['shipping']['company'];
        $order_shipping_address_1 = $order_data['shipping']['address_1'];
        $order_shipping_address_2 = $order_data['shipping']['address_2'];
        $order_shipping_city = $order_data['shipping']['city'];
        $order_shipping_state = $order_data['shipping']['state'];
        $order_shipping_postcode = $order_data['shipping']['postcode'];
        $order_shipping_country = $order_data['shipping']['country'];
        
        $orden = $order_data['id'];
        
        $nombre = $order_shipping_first_name . " " . $order_shipping_last_name ;
        $calle = (!empty($order_billing_address_1))?$order_billing_address_1:"calle sin nombre";
        $colonia = (!empty($order_billing_address_2))?$order_billing_address_2:"colonia sin nombre";

        $telefono = (!empty($order_billing_phone))?$order_billing_phone:"000 0000";
        $ciudad = (!empty($order_shipping_city ))?$order_shipping_city :"sin ciudad";
        $estado = (!empty($order_shipping_state))?$order_shipping_state:"sin estado";
        $cp = (!empty($order_shipping_postcode))?$order_shipping_postcode:"000000";

        $atention_to  = substr($nombre,0,34);
        $address1     = substr($calle,0,34);
        $address2     = substr($colonia,0,34);
        $address3     = substr($telefono,0,34);									
        $city         = substr($ciudad,0,34);
        $state        = $estado;
        $postal_code  = $cp;
        $OBJ_product = array();   

        if($order_shipping_country=='CO'){
            $state=stateColombia($state);
        }

        $i=1;

        foreach($order_data['line_items'] as $producto)
        {
            $linenumber=str_pad($i, 3, "0", STR_PAD_LEFT);
            $OBJ_product[] = array(
                "linetype"=> $access['linetype'],
                "linenumber"=> $linenumber,
                "quantity"=> $producto["quantity"],
                "ingrampartnumber"=> $producto["sku"]
            );
            $i++;
        }

        $requestData["ordercreaterequest"]["requestpreamble"]["isocountrycode"]=$access['isocountrycode'];
        $requestData["ordercreaterequest"]["requestpreamble"]["customernumber"]=$access['customernumber'];


        $requestData["ordercreaterequest"]["ordercreatedetails"]["customerponumber"]='RSF_'.$orden;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["ordertype"]="";

        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["attention"]=$atention_to;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["addressline1"]=$address1;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["addressline2"]=$address2;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["addressline3"]=$address3;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["city"]=$city;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["state"]=$state;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["postalcode"]=$postal_code;
        $requestData["ordercreaterequest"]["ordercreatedetails"]["shiptoaddress"]["countrycode"]=strtoupper($country);

        $requestData["ordercreaterequest"]["ordercreatedetails"]["carriercode"]=$access['carriercode'];
        $requestData["ordercreaterequest"]["ordercreatedetails"]["lines"]=$OBJ_product;
        
        if(is_array($access['extendedspecs'])){       
            foreach($access['extendedspecs'] as $key=>$elemt){
                $access['extendedspecs'][$key] = (($key== 'attributename')?$elemt:setBoolean($elemt));
            }
        }else{
            if(isset($access['extendedspecs']['attributevalue'])){
                $access['extendedspecs']['attributevalue'] = setBoolean($access['extendedspecs']['attributevalue']);
            }
        }
        $requestData["ordercreaterequest"]["ordercreatedetails"]["extendedspecs"][]=$access['extendedspecs'];

        return ["data"=>json_encode($requestData,JSON_UNESCAPED_UNICODE),"order_id"=>$orden];
    } catch (Exception $e) {
        var_dump($e->getMessage());
        return ["status" => 'error', 'message' => $e->getMessage()];
    }

}

function logOrdersV5($responseData,$requestData,$order_id,$user){
    $country=$user['country'];
    $userId=$user['id'];
    $urlOrder =  "getSegmentation/".$country."/".$userId."/orders/".$order_id;
	if(!file_exists($urlOrder."/wc_order_SUCCESS_".$order_id.".json")){        
		$Response = json_decode($responseData);
    
		if(!file_exists($urlOrder."/")){
			mkdir($urlOrder."/", 0775, true);
		}	
	
		if(isset($Response->serviceresponse)){
			$ErrorStatus = $Response->serviceresponse->responsepreamble->responsestatus;    
			
			if($ErrorStatus!='FAILED'){        
				$order_from_ingram = $Response->serviceresponse->ordersummary->ordercreateresponse[0]->globalorderid;		

                $status=["message"=>"success","mensaje_error_id"=>14,"mensaje_error"=>"ORDEN GRABADA CON EXITO - globalId: ". $order_from_ingram];	
                
				file_put_contents($urlOrder."/wc_order_SUCCESS_".$order_id.".json", $requestData);
				
				$OResp = $urlOrder."/OResponse - ".$order_id.' - '.$order_from_ingram.".json";
				file_put_contents($OResp,json_encode($Response));
                $OResp = $urlOrder . "/OResponse - " . $order_id . ".json";
                file_put_contents($OResp, ["globalorderid"=>json_encode($Response->serviceresponse->ordersummary->ordercreateresponse[0]->globalorderid)]);
			}else{
                $status=["message"=>"error","mensaje_error_id"=>15,"mensaje_error"=>"ERROR FATAL registrando orden de compras, contactar a administradores","response"=>$Response];

				file_put_contents($urlOrder."/wc_order_FAILED_".$order_id.".json", json_encode($Response));
				file_put_contents($urlOrder."/wc_order_items_FAILED_".$order_id.".json", $requestData);						
			}
			
		}else{
            $status=["message"=>"ingram, responsn't","mensaje_error_id"=>16,"mensaje_error"=>$Response];
			file_put_contents($urlOrder."/wc_order-FAILED-".$order_id.".json", json_encode($Response));
			file_put_contents($urlOrder."/wc_order_items-FAILED-".$order_id.".json", $requestData);
		}	
	}
    return $status;
}

function OrdersIngramV5($POSTFIELDSdata,$user)
{

    try{
        $access_=json_decode($user['access'],true);
        $access=$access_[$access_['default']];
        $POSTFIELDS=mapPOSTFIELDSV5($POSTFIELDSdata,$user);
      
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
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $a = json_decode($result);
        $token = $a->access_token;
    
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ingrammicro.com:443/resellers/v5/orders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$POSTFIELDS['data'],
            CURLOPT_HTTPHEADER => array(
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer ".$token
            )
        ));
    
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $message =  'Error:' . curl_error($curl);
            return ["status"=>'error','message'=> $message];
        }    
        curl_close($curl);

        $message=logOrdersV5($response,$POSTFIELDS['data'],$POSTFIELDS['order_id'],$user);

        return ["status"=>'success','message'=> $message];

    } catch (Exception $e) {
        return ["status"=>'error','message'=> $e->getMessage()];
    }

}