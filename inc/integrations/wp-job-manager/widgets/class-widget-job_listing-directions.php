<?php
header('Content-type: application/json');

$q = $_GET['q'];


$new = str_replace(' ', '%20', $q);

$service_url = 'https://maps.googleapis.com/maps/api/directions/json?origin=2351%20Nautical%20Way%20Orlando%20Florida&destination='.$new.'&key=AIzaSyBLzancw8yODD8ssXBNUJ-C0R0OsMyUvoo';

$apiData = file_get_contents($service_url);
$json = json_decode($apiData);

//Takes API data
    $dataArray = array(
        'data'=> $json
    );

echo json_encode($dataArray);

?>