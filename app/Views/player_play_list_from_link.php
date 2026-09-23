<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Player</title>
    <style>
        .media-container {
            max-width: 100%;
            text-align: center;
        }
        .media-container img, .media-container video {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <input type="hidden" id="loopCount">
    <input type="hidden" id="loopCounter">
    <div class="media-container" id="mediaContainer"></div>
    <div class="media-container" id="videoMediaContainer">
        <video   id="videoId" 
        
        autoplay
        muted="false"  >
            
            </video>
    </div>

    <script>
    
// Define the URL
const jsonData = {
            "status": "1",
            "message": "Details Found",
            "data": <?=json_encode($data)?>
        };


        let currentIndex = 0;
const data = jsonData.data;
const mediaContainer = document.getElementById('mediaContainer');
const videoId = document.getElementById('videoId');
 videoId.addEventListener('ended', function(){
     var loopCount = document.getElementById('loopCount').value;
     var loopCounter= document.getElementById('loopCounter').value;
     
                    if (loopCounter < loopCount - 2) {
                            loopCounter++;
                            this.play();
                            document.getElementById('loopCounter').value = loopCounter;
                        } else {
                            this.play();
                            loopCounter = 0; // Reset loop counter for future loops
                            document.getElementById('loopCount').value = loopCounter;
                            console.log("NEXT END");
                            setTimeout(nextMedia, this.duration * 1000);
                        }
                });

function showMedia() {
    mediaContainer.innerHTML = ''; // Clear previous content

    const currentMedia = data[currentIndex];

    if (currentMedia.media_type === 'jpg') {
        
        // Show Image
        const image = document.createElement('img');
        image.src = currentMedia.media_url;
        mediaContainer.appendChild(image);
        setTimeout(nextMedia, currentMedia.duration_sec * 1500);

    } else if (currentMedia.media_type === 'mp4') {
        videoId.src = currentMedia.media_url;
        document.getElementById('loopCount').value=currentMedia.loop_count;
    }
    sendData(currentIndex);


}

function nextMedia() {
    videoId.src="";
    currentIndex = (currentIndex + 1) % data.length;
    showMedia();
}

// Start the media loop
showMedia();

function sendData(index){
    const datasend = {
    device_id: '<?=$device_id?>',
    screen_id: '<?=$screen_id?>',
    customer_id: '<?=$customer_id?>',
    user_id: '<?=$user_id?>',
    media_id:data[index].media_id,
    device_type: '1',
    device_details: navigator.userAgent,
    report_date:"<?=$report_date?>",
    count:data[index].loop_count
};

// Convert the data to JSON
const jsonData = JSON.stringify(datasend);

// Create a new XMLHttpRequest object
const xhr = new XMLHttpRequest();

// Define the type of request, the URL, and whether it should be asynchronous
xhr.open('POST', '<?=$reporting_url?>', true);

// Set the content type of the request to JSON
xhr.setRequestHeader('Content-Type', 'application/json');

// Define what happens on successful data submission
xhr.onload = function () {
    if (xhr.status >= 200 && xhr.status < 400) {
        // The request was successful
        console.log(xhr.responseText);
    } else {
        // There was an error from the server
        console.error('Error:', xhr.responseText);
    }
};

// Define what happens in case of an error
xhr.onerror = function () {
    console.error('Request failed');
};

// Send the request
xhr.send(jsonData);

}
    </script>
</body>
</html>
