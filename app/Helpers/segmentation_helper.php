<?php
function read_file_json($root,$jsonDecode=false,$write=false)
{        
    if($write){
        $rootDoc=explode('/',$root);
        $lastItem = count($rootDoc);
        unset($rootDoc[$lastItem-1]);
        $rootDir = implode('/',$rootDoc);
        while(file_exists($rootDir.'/write.json')){
            sleep(1);
        }
        file_put_contents($rootDir.'/write.json',json_encode([$root])); 
    }
    try {
        $returnContents = ($jsonDecode)?json_decode(file_get_contents($root),true):file_get_contents($root);
    } catch (Exception $e) {
        $returnContents = 'Excepción capturada: '.  $e->getMessage(). "\n";
    }
    return $returnContents;
}

function write_file_root($root,$file_name,$contents){
    if(!is_dir($root))
    {
        mkdir($root,0755,TRUE);
    } 
    if(write_file($root.$file_name, $contents))
    {
        if(file_exists($root.'/write.json')){
            unlink($root.'/write.json');
        }
    }
}

function verify_process($country,$id){
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'active.json')){
        $active = json_decode(file_get_contents('./getSegmentation/'.$country.'/'.$id.'/'.'active.json'),true);
        if($active['active']=='price-api'){
            return ["status"=>false,"message"=>"update -".$active['active']."-"];
        }
    }
    return ["status"=>true,"message"=>null];
}

function putActive($country,$id,$message,$type){
    if(file_exists('./getSegmentation/'.$country.'/'.$id.'/'.'active.json')){
        $active = json_decode(file_get_contents('./getSegmentation/'.$country.'/'.$id.'/'.'active.json'),true);
        if($type=='start'){
            $active['sync'][]=['message'=>$message,'datestart'=>date('Y-m-d H:i')];
            $active['active']=$message;
        }
        if($type=='finish'){
            $active['sync'][]=['message'=>$message,'datefinish'=>date('Y-m-d H:i')];
            $active['active']=null;
        }
    }else{
        $active=['active'=>(($type=='start')?$message:null),'sync'=>[['message'=>$message,(($type=='start')?'datestart':'datefinish')=>date('Y-m-d H:i')]]];
    }    
    write_file_root('./getSegmentation/'.$country.'/'.$id.'/','active.json',json_encode($active,JSON_UNESCAPED_UNICODE));
}
