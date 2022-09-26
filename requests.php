<?php
include_once 'config.php';

$action = filter_var(trim($_REQUEST['action']), FILTER_SANITIZE_STRING);
if ($action == 'upload') 
{
    $response['code'] = "200";
    if ($_FILES['file']['error'] != 4) 
    {
        //set which bucket to work in
        $bucketName = "xlsx-uploads";
        // get local file for upload testing
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        
        // NOTE: if 'folder' or 'tree' is not exist then it will be automatically created !
        $cloudPath = 'uploads/' . $_FILES['file']['name'];

        $isSucceed = uploadFile($bucketName, $fileContent, $cloudPath);

        if ($isSucceed == true) 
        {
            $response['uploadmsg'] = 'SUCCESS: to upload ' . $cloudPath;
            // TEST: get object detail (filesize, contentType, updated [date], etc.)
            $response['data'] = getFileInfo($bucketName, $cloudPath);
            //$localpath = 'uploads/' . $_FILES['file']['name'];
            $localPath = $cloudPath;
            //downloadLocally($bucketName, $cloudPath, $localPath);
            $temp = downloadLocally($bucketName, $cloudPath, $localPath);
            if($temp != false)
            {
                $response['downloadmsg'] = 'SUCCESS: File saved to ' . $localPath;

                // Convert file to csv
                $name = 'employees';
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(true);

                //Get all sheets in file
                $sheets = $reader->listWorksheetNames($localPath);
                
                //Loop for each sheet and save an individual file
                foreach($sheets as $sheet)
                {
                    //Load the file
                    $reader->setLoadSheetsOnly([$sheet]);
                    $spreadsheet = $reader->load($localPath);

                    //Write the CSV file
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                    //$writer->setDelimiter(";");
                    $csvPath = 'uploads/' . $name.'_'.$sheet.'.csv';
                    $writer->save($csvPath);
                }
                $response['csvMsg'] = 'SUCCESS Converting to csv. Path: ' . $csvPath;
                $response['csvPath'] = $csvPath;

                //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                $json_credentials = file_get_contents('../keys/db_credentials.json');
                $response['json_credentials_type'] = 'type of json_cred is ' . gettype($json_credentials);
                $response['json_credentials_val'] = 'json credentials are ' . $json_credentials;
                $json_data = json_decode($json_credentials, true);
                
                // connect to database
                $mysqli = new mysqli(
                    $json_data['host'],
                    $json_data['user'],
                    $json_data['password'],
                    $json_data['database']
                );
                $response['mysqli_type'] = 'type of ' . gettype($mysqli);
                $mysqli->set_charset('utf8mb4');
                if($mysqli === false)
                {
                    $response['db_connection'] = 'Failed to connect to mysql db';
                }
                else
                {
                    $response['db_connection'] = $mysqli->client_info;
                }
                
            }
            else
            {
                $response['downloadmsg'] = 'Failed: to download to ' . $localpath;
            }
        } 
        else 
        {
            $response['code'] = "201";
            $response['msg'] = 'FAILED: to upload ' . $cloudPath . PHP_EOL;
        }
    }
    header("Content-Type:application/json");
    echo json_encode($response);
    exit();
}