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
            $localpath = 'temp/' . $_FILES['file']['name'];
            downloadLocally($bucketName, $cloudPath, $localpath);
            $temp = downloadLocally($bucketName, $cloudPath, $localpath);
            if($temp != false)
            {
                $response['downloadmsg'] = 'SUCCESS: to download to ' . $localpath;
            }
            else
            {
                $response['downloadmsg'] = 'Failed: to download to ' . $localpath;
            }

            // convert to csv - below
            //$reader = new Xlsx();
            //$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            //$spreadsheet = $reader->load($localpath);
            //$loadedSheetNames = $spreadsheet->getSheetNames();
            //$writer = new Csv($spreadsheet);
            //TODO: figure out how to return this $writer in $response OR just add to db
            // and echo
            //echo($writer);
            //foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) 
            //{
            //    $writer->setSheetIndex($sheetIndex);
            //    $writer->save($loadedSheetName.'.csv');
            //}
    
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