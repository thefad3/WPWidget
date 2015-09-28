<?php
header('Content-type: application/json');

$q = $_GET['q'];
$loc = $_GET['loc'];


$new = str_replace(' ', '%20', $q);
$l = str_replace(' ', '%20', $loc);

$service_url = 'https://maps.googleapis.com/maps/api/directions/json?origin='.$new.'&destination='.$l.'&key=AIzaSyBLzancw8yODD8ssXBNUJ-C0R0OsMyUvoo';

$apiData = file_get_contents($service_url);
$json = json_decode($apiData);

//Takes API data
    $dataArray = array(
        'data'=> $json
    );

echo json_encode($dataArray);

?>