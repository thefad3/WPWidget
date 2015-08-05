<?php
header('Content-type: application/json');

$q = $_GET['q'];
$error = $q;

$service_url = 'https://maps.googleapis.com/maps/api/directions/json?origin=2351%20Nautical%20Way%20Orlando%20Florida&destination=Florida&key=AIzaSyBLzancw8yODD8ssXBNUJ-C0R0OsMyUvoo';


$apiData = file_get_contents($service_url);
$json = json_decode($apiData);
//Takes API data
$data = $json->routes[0];

    $duration = $data->legs[0]->duration->text;


    //Prints out Dirctions from API result.
    foreach($data->legs[0]->steps as $item){
        $directions = $item->html_instructions;
    }


    $dataArray = array(
        'directions'=> $directions,
        'duration'=>$duration,
        'error'=>$error
    );

echo json_encode($dataArray);

?>