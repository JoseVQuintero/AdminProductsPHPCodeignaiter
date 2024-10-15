<?php
function returnDate($zone,$format="Y-m-d H:i:s"){
    $date_ = new DateTime("now",new DateTimeZone($zone));
    return $date_->format($format);
}

function returnDiffDate($type,$zone,$date1,$date2="now"){
    $date1_ = new DateTime($date1,new DateTimeZone($zone));
    $date2_ = new DateTime($date2,new DateTimeZone($zone));
    return $date1_->diff($date2_)->$type;
}

function setBoolean($value){
    return ((in_array($value, ["1", "true", "True"])) ? true : ((in_array($value, ["0", "false", "False"])) ? false : $value));
}

function setNumeric($value){
    return ((is_float($value)) ? (float)$value : ((is_int($value)) ? (int)$value : $value));
}

function money_format_($value) { return '$' . number_format($value, 2); }

function initInstance($country, $id){

    $instanceCurrent = ((file_exists("getSegmentation/" . $country . '/' . $id . "/instancecurrent.json")) ? read_file_json("getSegmentation/" . $country . '/' . $id . "/instancecurrent.json", true) : ["instance" => returnDate(returnAccess()['zone'][$country], "Y-m-d H:i")]);
    $diffIntance = returnDiffDate("i", returnAccess()['zone'][$country], $instanceCurrent['instance']);
    if ($diffIntance <= 1) {
        if (!file_exists("getSegmentation/" . $country . '/' . $id . "/instancecurrent.json")) {
            write_file_root("getSegmentation/" . $country . '/' . $id . "/", "instancecurrent.json", json_encode($instanceCurrent, JSON_UNESCAPED_UNICODE));
        } else {
            return ["error" => true, "msg" => "Instance active"];
        }
    }
    $instanceCurrent['instance'] = returnDate(returnAccess()['zone'][$country], "Y-m-d H:i");
    write_file_root("getSegmentation/" . $country . '/' . $id . "/", "instancecurrent.json", json_encode($instanceCurrent, JSON_UNESCAPED_UNICODE));
    return ["success" => true, "msg" => null];
}

function updateInstance($country, $id)
{
    $instanceCurrent['instance'] = returnDate(returnAccess()['zone'][$country], "Y-m-d H:i");
    write_file_root("getSegmentation/" . $country . '/' . $id . "/", "instancecurrent.json", json_encode($instanceCurrent, JSON_UNESCAPED_UNICODE));
}

function progressProcess($country, $id, $totalProucts, $updateCount){
    //$diffIntance = returnDiffDate("i", returnAccess()['zone'][$country], $instanceCurrent['instance']);
    $processprogress = $updateCount . ' to ' . $totalProucts . ' Update Prices ' . (($updateCount / $totalProucts) * 100) /*. ' % init: '..',current: '..',time elapsed'. $diffIntance.' minute'*/;
    write_file_root("progress/" . $country . '/' . $id . "/", "processprogress.txt", $processprogress);
}

function outInstance($country, $id)
{
    $instanceCurrent['instance'] = returnDate(returnAccess()['zone'][$country], "Y-m-d H:i");
    write_file_root("getSegmentation/" . $country . '/' . $id . "/", "instancecurrent.json", json_encode($instanceCurrent, JSON_UNESCAPED_UNICODE));
}
