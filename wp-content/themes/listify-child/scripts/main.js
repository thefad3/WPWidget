var count=0;
function getDirections() {
    if(count>0){
        document.getElementById("directions").innerHTML = '';
        document.getElementById("distance").innerHTML = '';
    }
    count++;
    var data = document.getElementById('address').value,
        url = "/wp-content/themes/listify/inc/integrations/wp-job-manager/widgets/class-widget-job_listing-directions.php?q=" + data + "&loc=<?php echo $l ?>";
    xhr = new XMLHttpRequest();
    xhr.open("GET", url , false);
    xhr.send();
    var apiData = JSON.parse(xhr.response),
        htmlSteps = apiData.data.routes[0].legs[0].steps,
        distance = apiData.data.routes[0].legs[0].distance.text,
        totalMiles = document.getElementById("distance");

    dirDiv = document.createElement("div");
    dirDiv.innerHTML = distance;
    totalMiles.appendChild(dirDiv);


    for(i=0; i<htmlSteps.length;i++){
        var directions = document.getElementById("directions"),
            content = document.createElement("div");
        content.innerHTML = apiData.data.routes[0].legs[0].steps[i].html_instructions + apiData.data.routes[0].legs[0].steps[i].distance.text;
        directions.appendChild(content);
    }
}

function gpsDirections(){

    navigator.geolocation.getCurrentPosition(success);
    function success(position) {
        if(count>0){
            document.getElementById("directions").innerHTML = '';
            document.getElementById("distance").innerHTML = '';
        }
        count++;
        var location = position.coords.latitude+','+position.coords.longitude,
            url = "/wp-content/themes/listify/inc/integrations/wp-job-manager/widgets/class-widget-job_listing-directions.php?q=" + location + "&loc=<?php echo $l ?>";
        xhr = new XMLHttpRequest();
        xhr.open("GET", url , false);
        xhr.send();
        var apiData = JSON.parse(xhr.response),
            htmlSteps = apiData.data.routes[0].legs[0].steps,
            distance = apiData.data.routes[0].legs[0].distance.text,
            totalMiles = document.getElementById("distance");

        dirDiv = document.createElement("div");
        dirDiv.innerHTML = distance;
        totalMiles.appendChild(dirDiv);

        for(i=0; i<htmlSteps.length;i++){
            var directions = document.getElementById("directions"),
                content = document.createElement("div");
            content.innerHTML = apiData.data.routes[0].legs[0].steps[i].html_instructions + apiData.data.routes[0].legs[0].steps[i].distance.text;
            directions.appendChild(content);
        }
    }
}