<!-- Copyright 2022 Gabe Scott -->
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>GCP Storage File Upload using PHP</title>
        <link href="style.css" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="/asset/img/favicon.ico">
    </head>
    <body>
        <div id="option-form">
            <strong>Which Action would you like to take?</strong><br><br>
            <select id="category-select">
                <option disabled selected>select option</option>
                <option value="1">Upload Employee Spreadsheet to DB</option>
                <option value="2">Display Employee Data in Browser</option>
                <option disabled value="3">Query Database</option>
            </select>
            <select id="subcategory-select"></select>
            <select id="subsubcategory-select"></select>
            <button id="submit-btn" type="button">Submit</button>
            
        </div>
        <br>
        <!-- Database response form (table) -->
        <div id="db-response-form">
            <hr/>
                <strong>Response (Database Table):</strong>
                <br><br>
                <div id="db-out">Database data will populate here</div>
                <br>
            <hr/>
        </div>
        <br><br>
    <!--   Download csvfile link - called from button id=loadFileXml, href will be set from post request response  -->
        <a href="" id="get-csv"></a>
        <!-- File upload form (also holds response from post request) -->
        <form id="fileUploadForm" method="post" enctype="multipart/form-data"> <!-- change this below onclick to javascript or php get request -->
            <input disabled type="file" id="file-chooser" name="file" accept=".xlsx"/>
            <div class="space"></div>  
            <input disabled type="submit" name="upload" id="upload-btn" value="Upload"/>
            <input type="button" id="loadFileXml" value="Download As CSV" onclick="document.getElementById('get-csv').click();"/>

            <span id="uploadingmsg"></span>
            <br>
            <hr/>
            <strong>Uploaded File:</strong>
            <br><br>
            <div id="output"></div>
            <!-- spreadsheet   -->
            <div id="ss">Your Excel file will be displayed here in it's original format</div> 
            <br>
            <hr/>
            <div id="spinner">
                <img src="asset/img/spinner.gif" width="50" height="50" />
            </div>
            <!-- json output from post request return - moved it to console   -->
            
        </form>
        <script>
            function swapButtons()
            {
                document.getElementById('upload-btn').style.display = 'inline-block';
                // download as CSV button
                document.getElementById('loadFileXml').style.display = 'none';
            }
            function updateSubcategories() 
            {
                var cat_select = document.getElementById("category-select");
                var subcat_select = document.getElementById("subcategory-select");
                var subsubcat_select = document.getElementById("subsubcategory-select");
                var upload_form = document.getElementById("fileUploadForm");
                var cat_id = cat_select.options[cat_select.selectedIndex].value;
                var submit_btn = document.getElementById("submit-btn");
                submit_btn.style.display = 'none';
                var db_response = document.getElementById("db-response-form");
                
                var url = 'subcategories.php?category_id=' + cat_id;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.onreadystatechange = function () 
                {
                    if(xhr.readyState == 4 && xhr.status == 200) 
                    {
                        subcat_select.innerHTML = xhr.responseText;
                        if(cat_select.selectedIndex == 1)   //upload file selected
                        {
                            upload_form.style.display = 'inline';
                            subcat_select.style.display = 'none';
                            db_response.style.display = 'none';
                            subsubcat_select.style.display = 'none';
                        }
                        else   // pull from database selected (currently only other option)
                        {
                            upload_form.style.display = 'none';
                            subcat_select.style.display = 'inline';
                            db_response.style.display = 'inline';
                        }
                    }
                }
                xhr.send();
            }
            function updateSubSubcategories() 
            {
                // TODO: remove this from updatesubcategories() and have it just here
                var submit_btn =  document.getElementById("submit-btn");
                submit_btn.style.display = 'inline';

                var cat_select = document.getElementById("category-select");
                var cat_id = cat_select.options[cat_select.selectedIndex].value;

                var subcat_select = document.getElementById("subcategory-select");
                var subcat_id = subcat_select.options[subcat_select.selectedIndex].value;

                var db_response = document.getElementById("db-response-form");
                var subsubcat_select = document.getElementById("subsubcategory-select");

                var url = 'subcategories.php?subcategory_id=' + cat_id + ',' + subcat_id;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.onreadystatechange = function () {
                    if(xhr.readyState == 4 && xhr.status == 200) 
                    {
                        console.log(xhr.responseText);
                        subsubcat_select.innerHTML = xhr.responseText;
                        if(subcat_select.selectedIndex == 3)   //upcoming birthdays
                        {
                            subsubcat_select.style.display = 'inline';
                        }
                        else
                        {
                            subsubcat_select.style.display = 'none';
                        }
                    }
                }
                xhr.send();
            }
            var cat_select = document.getElementById("category-select");
            cat_select.addEventListener("change", updateSubcategories);
            var submit_btn = document.getElementById("submit-btn");
            var out = document.getElementById("db-out");

            var subcat_select = document.getElementById("subcategory-select");
            subcat_select.addEventListener("change", updateSubSubcategories);
            
            // Button that gets clicked to choose a file to upload
            file_choose_btn = document.getElementById("file-chooser");
            file_choose_btn.addEventListener("click", swapButtons);

            // Button at top that gets clicked to display results
            submit_btn.addEventListener("click", function(){
                var subcat_select = document.getElementById("subcategory-select");
                var subsubcat_select = document.getElementById("subsubcategory-select");

                var subcat_id = subcat_select.options[subcat_select.selectedIndex].value;
                var subsubcat_id = subsubcat_select.options[subsubcat_select.selectedIndex].value;

                var url = 'requests.php?action=' + subcat_id + ',' + subsubcat_id;
                var xhr = new XMLHttpRequest();
                var out = document.getElementById("db-out");
                xhr.open('GET', url, true);
                xhr.onreadystatechange = function () 
                {
                    if(xhr.readyState == 4 && xhr.status == 200) 
                    {
                        out.innerHTML = xhr.responseText;
                        console.log(xhr.responseText);
                    }
                }
                xhr.send();
            }); 

        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script>
            // flip whether an element is visible or not by it's id
            function flipVisible($id){
                var displayType = ( ! $id.is(':visible') ) ? 'inline-block' : 'none';
                $id.css('display', displayType);
            }
            function showSpinner(){
                var spinner = document.getElementById("spinner");
                spinner.style.display = 'block';
            }
            function hideSpinner(){
                var spinner = document.getElementById("spinner");
                spinner.style.display = 'none';
            }
            $("#fileUploadForm").submit(function (e) {
                e.preventDefault();
                var action = "requests.php?action=upload";
                $("#uploadingmsg").html("Uploading...");
                showSpinner();
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
                    hideSpinner();
                    $("#upload-btn").css('display', 'none');
                    $("#loadFileXml").css('display', 'inline-block');
                    // DEBUG OUTPUT
                    //console.log(JSON.stringify(response, null, 4));
                    $("#ss").html(response.spreadsheet_html);
                }).fail(function (data) {
                    hideSpinner();  // TODO: should be here as well as in .done ? 
                    alert('ajax failed. Likely the excel file is not correct format');
                });
            });  
        </script>
        
    </body>
</html>