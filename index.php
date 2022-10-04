<html>
    <head>
        <meta charset="UTF-8">
        <title>GCP Storage File Upload using PHP</title>
        <style>
            #subcategory-select {
                display: none;
            }
            #fileUploadForm {
                display: none;
            }
            
             
        </style>
        <link rel="icon" type="image/x-icon" href="/asset/img/parser_favicon.png">
    </head>
    <body>
        <div id="form">
            <strong>Which Action would you like to take?</strong><br><br>
            <select id="category-select">
                <option disabled selected>select option</option>
                <option value="1">Upload Employee Spreadsheet to DB</option>
                <option value="2">Display Employee Data in Browser</option>
                <option disabled value="3">Query Database</option>
            </select>
            <select id="subcategory-select">
            </select>
        </div>
        <br><br><br>
        <form id="fileUploadForm" method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".xlsx"/>  
            <input type="submit" name="upload" value="Upload"/>
            <span id="uploadingmsg"></span>
            <hr/>
                <strong>Response (JSON)</strong>
                <pre id="json">json response will be shown here. If not, look in console.</pre>
            <hr/>
            <strong>Public Link (Click to Download Original File):</strong>
            <br/>
            <!-- json output from post request return - moved it to console   -->
            <div id="output"></div>
            <!-- spreadsheet   -->
            <div id="ss"></div> 
        </form>
        <script>
            function updateSubcategories() 
            {
                var cat_select = document.getElementById("category-select");
                var subcat_select = document.getElementById("subcategory-select");
                var upload_form = document.getElementById("fileUploadForm");

                var cat_id = cat_select.options[cat_select.selectedIndex].value;
                //if (cat_select.selectedIndex == 1)
                //{
                    // display upload form if on right selection
                //    upload_form.style.display = 'inline';
                //}
                //else
                //{
                //    upload_form.style.display = 'none';
                //}

                var url = 'subcategories.php?category_id=' + cat_id;

                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.onreadystatechange = function () 
                {
                    if(xhr.readyState == 4 && xhr.status == 200) 
                    {
                        subcat_select.innerHTML = xhr.responseText;
                        if(cat_select.selectedIndex == 1)
                        {
                            upload_form.style.display = 'inline';
                            subcat_select.style.display = 'none';
                        }
                        else
                        {
                            upload_form.style.display = 'none';
                            subcat_select.style.display = 'inline';
                        }
                        
                    }
                }
                xhr.send();
            }

            var cat_select = document.getElementById("category-select");
            cat_select.addEventListener("change", updateSubcategories);

        </script>
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
                    //$("#json").html(JSON.stringify(response, null, 4));
                    console.log(JSON.stringify(response, null, 4));
                    //https://storage.googleapis.com/[BUCKET_NAME]/[OBJECT_NAME]
                    $("#output").html('<a href="https://storage.googleapis.com/' + response.data.bucket + '/' + response.data.name + '"><i>https://storage.googleapis.com/' + response.data.bucket + '/' + response.data.name + '</i></a>');
                    if(response.data.contentType === 'image/jpeg' || response.data.contentType === 'image/jpg' || response.data.contentType === 'image/png') {
                        $("#output").append('<br/><img src="https://storage.googleapis.com/' + response.data.bucket + '/' + response.data.name + '"/>');
                    }
                    includeHTML("ss", response.spreadsheet_location);
                }).fail(function (data) {
                    alert('ajax failed. Likely the excel file is not correct format');
                });
            });  
        </script>
        <script>
            function includeHTML(id, location)  // include the html from spreadsheet file
            {
                var elmnt, file, xhttp;
                elmnt = document.getElementById(id);
                file = location;
                if (file) 
                {
                    /* Make an HTTP request using the attribute value as the file name: */
                    xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() 
                    {
                        if (this.readyState == 4) 
                        {
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