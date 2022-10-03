<html>
    <head>
        <meta charset="UTF-8">
        <title>GCP Storage File Upload using PHP</title>
        <link rel="icon" type="image/x-icon" href="/asset/img/parser_favicon.png">
    </head>
    <body>
        <form id="fileUploadForm" method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".xlsx"/>  
            <input type="submit" name="upload" value="Upload"/>
            <span id="uploadingmsg"></span>
            <hr/>
                <strong>Response (JSON)</strong>
                <pre id="json">json response will be shown here</pre>
            <hr/>
            <strong>Public Link (Click to Download Original File):</strong>
            <br/>
            <div id="output"></div>
            <div id="ss"></div>
        </form>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script>
            $("#fileUploadForm").submit(function (e) {
                e.preventDefault();
                var action = "requests.php?action=upload";
                $("#uploadingmsg").html("Uploading...");
                $("ss").html("");
                var data = new FormData(e.target);
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: data,
                    contentType: false,
                    processData: false,
                }).done(function (response) {
                    $("#uploadingmsg").html("");
                    //$("spreadsheet_table").html("");
                    //$("#json").html(JSON.stringify(response, null, 4));
                    console.log(JSON.stringify(response, null, 4));
                    //https://storage.googleapis.com/[BUCKET_NAME]/[OBJECT_NAME]
                    $("#output").html('<a href="https://storage.googleapis.com/' + response.data.bucket + '/' + response.data.name + '"><i>https://storage.googleapis.com/' + response.data.bucket + '/' + response.data.name + '</i></a>');
                    if(response.data.contentType === 'image/jpeg' || response.data.contentType === 'image/jpg' || response.data.contentType === 'image/png') {
                        $("#output").append('<br/><img src="https://storage.googleapis.com/' + response.data.bucket + '/' + response.data.name + '"/>');
                    }
                    includeHTML("ss", response.spreadsheet_location);
                    
                }).fail(function (data) {
                    //TODO: create action on failed request
                    alert('ajax failed');
                });
            });  
        </script>
        <script>
            function includeHTML(id, location) {
                var z, i, elmnt, file, xhttp;
                /* Loop through a collection of all HTML elements: */
                z = document.getElementById(id);
                    elmnt = z;
                    /*search for elements with a certain atrribute:*/
                    file = location;
                    if (file) {
                    /* Make an HTTP request using the attribute value as the file name: */
                    xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4) {
                        if (this.status == 200) {elmnt.innerHTML = this.responseText;}
                        if (this.status == 404) {elmnt.innerHTML = "Page not found.";}
                        /* Remove the attribute, and call this function once more: */
                        elmnt.removeAttribute("w3-include-html");
                        includeHTML();
                        }
                    }
                    xhttp.open("GET", file, true);
                    xhttp.send();
                    /* Exit the function: */
                    return;
                    }
                
            }
            </script>
    </body>
</html>