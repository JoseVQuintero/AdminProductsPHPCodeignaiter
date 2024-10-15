<?php

function tokenClient($access)
{
    
    $ip_ = str_replace('http://','',str_replace('https://','',$access['url']));
    
    if(isset(explode('/', $ip_)[0])){
        
        
        $ip = gethostbyname(explode('/', $ip_)[0]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://bdicentralserver.com/api/set_htaccess/bdi2021tresdosunokwxz/". $access['clientBDICountry']."/contents/" . $ip);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $access['urlInt'] . 'token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "ip=" . $ip . "&username=" . $access['userInt'] . "&password=" . $access['passInt']);
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

    }
   
   
}

function productsClientBdi($client)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://mx.bdicentralserver.com/utils.php?updateIPLast=null");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://mx.bdicentralserver.com/api/productos_per_client/" . $client);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function tokenBdi($access){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $access['urlInt'] . 'token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $access['userInt'] . "&password=" . $access['passInt']);
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}

function bdi($access,$sku)
{
   
    tokenClient($access);

    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $access['urlInt'].'products',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{"skus":"'.$sku.'"}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
/* var_dump($sku);
    var_dump($response);
exit;
     */
    curl_close($curl);

    $dataResponse = json_decode($response,true);
    $contentResponse = [];

    foreach($dataResponse as $data){

        if(isset($data['sku'])){
            $contents=null;

            $contents['productDetail']['sku']= $data['sku'];
            $contents['productDetail']['category'] = $data['categoria_padre'];
            $contents['productDetail']['subCategory'] = $data['categoria_nombre'];
            $contents['productDetail']['productLine'] = $data['categoria_nombre'];
            $contents['productDetail']['vendor'] = $data['fabricante_nombre'];
            $contents['productDetail']['title'] = $data['titulo'];
            $contents['productDetail']['description'] = null;
            $contents['productDetail']['vpn'] = $data['codigo_fabricante'];
            $contents['productDetail']['productImage']['imageGalleryUrlHigh'] = implode(',',(!is_null($data['imagenes']))?$data['imagenes']:[]);
            $contents['productDetail']['productImage']['imageGalleryUrlLow'] = implode(',',(!is_null($data['imagenes']))?$data['imagenes']:[]);
            $contents['productDetail']['productImage']['imageGalleryUrlMedium'] = implode(',',(!is_null($data['imagenes']))?$data['imagenes']:[]);
            $contents['productDetail']['productMeasurement']['productWeight'] = null;
            $contents['productDetail']['productMeasurement']['isBulkFreight'] = false;
            $contents['productDetail']['productMeasurement']['pMeasureHeight'] = $data['height'];
            $contents['productDetail']['productMeasurement']['pMeasureWidth'] = $data['width'];
            $contents['productDetail']['productMeasurement']['pMeasureLength'] = $data['length'];
            $contents['productDetail']['productMeasurement']['pMeasureWeight'] = $data['weight'];
            $contents['productDetail']['imageGalleryURLLow'] = $data['imagenes'];
            $contents['productDetail']['imageGalleryURLMedium'] = $data['imagenes'];
            $contents['productDetail']['imageGalleryUrlHigh'] = $data['imagenes'];
            $contents['productDetail']['basicSpecifications'] = $data['json'];
            $contents['productDetail']['basicSpecificationsHTML'] = $data['ficha'];

            $contentResponse[]= $contents;
        }
    }


    

    return json_decode(json_encode($contentResponse));
}
