<?php
use App\Models\Users;

use CodeIgniter\API\ResponseTrait;
helper('datespecial');

function index($country,$id)
{
    /*echo json_encode(["message"=>"Loop, Exit"]);
    
    exit;	
    
        $request = $this->request->getPost(null, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $country = $request['country'];
        $id = $request['user_id'];*/

    $rootCron = "../public/getSegmentation/".$country."/".$id."/";
    
    set_time_limit(0);		
    $zoneDefault=returnAccess()['zone'][$country];
    
    
    $loop = true;
    if(file_exists($rootCron."loop-exit.txt")){
        $loop = false;
        return json_encode(["message"=>"Loop, Exit"]);
        exit;
    }		

    $timerJson = [];
    if(file_exists($rootCron."timer.json")){
        $timerJson = json_decode(file_get_contents($rootCron."timer.json"),true);
        if(isset($timerJson[returnDate($zoneDefault,"dmY")])){
            $hourMax = (int)max($timerJson[returnDate($zoneDefault,"dmY")]);
            $hourMaxCurrent = (int)returnDate($zoneDefault,"H");
            $diffHour = $hourMaxCurrent-$hourMax;
            if($diffHour<2){
                file_put_contents($rootCron."timer-exit.txt",returnDate($zoneDefault,"Y-m-d_H"));
                /* exec("tasklist 2>NUL",$task_list);
                print_r($task_list); */
                return json_encode(["message"=>"Exit, Proccess Exist - diffHour:".$diffHour]);
                exit;
            }
        }
    }else{
        file_put_contents($rootCron."timer.json",json_encode($timerJson));
    }
    
    
    while($loop){
        $zoneDefault=returnAccess()['zone'][$country];
        $model = new Users();
        $userFind = $model->getUser(false,$id);
        $access = json_decode($userFind['access'],true);

        $filters = $access[$access['default']];

        $timerJson[returnDate($zoneDefault,"dmY")][] = (int)returnDate($zoneDefault,"H");
    
        file_put_contents($rootCron."timer.json",json_encode($timerJson));
        $filtro=[];
        
        if(file_exists($rootCron."loop-exit.txt")){
            $loop = false;
            file_put_contents($rootCron."log-exit.txt","exit-".returnDate($zoneDefault,"Y-m-d-H"));
            unlink($rootCron."timer.txt");
            //return json_encode(["message"=>"Loop, Exit"]);
            exit;
        }    

        $categories=((!is_null($filters["categories"]))? explode(",",$filters["categories"]) :[]);
        $manufacturer=((!is_null($filters["manufacturer"]))? explode(",",$filters["manufacturer"]) :[]);
        $products=((!is_null($filters["products"]))? explode(",",$filters["products"]) :[]);
        $prices=((!is_null($filters["prices"]))? explode(",",$filters["prices"]) :[]);
        $contents=((!is_null($filters["contents"]))? explode(",",$filters["contents"]) :[]);
        $productsrc=((!is_null($filters["productsrc"]))? explode(",",$filters["productsrc"]) :[]);
        $WEEK=((!is_null($filters["WEEK"]))? explode(",",$filters["WEEK"]) :[]);

    
        $hora = (int)returnDate($zoneDefault,"H");
        $week = (int)returnDate($zoneDefault,"w");
        if(in_array($hora,$categories)){ $filtro[]="categories"; }
        if(in_array($hora,$manufacturer)){ $filtro[]="manufacturer"; }
        if(in_array($hora,$products)){ $filtro[]="products"; }
        if(in_array($hora,$prices)){ $filtro[]="prices"; }
        if(in_array($hora,$contents)){ $filtro[]="contents"; }
        if(in_array($hora,$productsrc)){ $filtro[]="productsrc"; }
        /* var_dump($filtro);
        var_dump(returnDate($zoneDefault,"H"));
        exit; */
        $response=[];
        if(count($filtro)>0&&in_array($week,$WEEK)){
                foreach($filtro as $item){
                    //$response[]  = $this->storageUpdateByCron($item,$access,$country,$id);
                }	
        }				
        if (!file_exists($rootCron."logs_get_url")) {
            mkdir($rootCron."logs_get_url", 0775, true);	
        }
        file_put_contents($rootCron."logs_get_url/log-".returnDate($zoneDefault,"Y-m-d-H").".txt",json_encode($response));
        $segundos = (60-(int)returnDate($zoneDefault,"i"))*60; 
        /* echo "si";
        exit;
        return "si";
        exit; */
        //sleep($segundos);
    }
} 
?>