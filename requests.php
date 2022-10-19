<?php
include_once 'config.php';

if(!isset($_REQUEST['action']))
{
    // include error msg
    header('Location: ./index.php');
    exit();
}

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
        
        // Add date to filename in order to save changes over time in GCS bucket
        $path = $_FILES['file']['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $base = pathinfo($path, PATHINFO_FILENAME);
        $name = $base. '_' . date("Y-m-d"). '.' . $ext;

        $cloudPath = 'uploads/' . $name;
        $isSucceed = uploadFile($bucketName, $fileContent, $cloudPath);

        if ($isSucceed == true) 
        {
            $response['uploadmsg'] = 'SUCCESS: to upload ' . $cloudPath;
            $response['data'] = getFileInfo($bucketName, $cloudPath);
            $localPath = '../../uploads/recent_excel.xlsx';
            $temp = downloadLocally($bucketName, $cloudPath, $localPath);
            if($temp != false)
            {
                $response['download_xlsx_msg'] = 'SUCCESS: File xlsx file saved locally';
                $response['download_xlsx_loc'] = $localPath;
                // Convert file to csv
                $name = 'employees';
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadEmptyCells(false);

                //Get all sheets in file
                $sheets = $reader->listWorksheetNames($localPath);
                
                //Loop over each sheet and save an individual file
                $count = 0;
                foreach($sheets as $sheet)
                {
                    $reader->setLoadSheetsOnly([$sheet]);
                    $spreadsheet = $reader->load($localPath);
                    
                    $worksheet = $spreadsheet->getSheet(0);
                    foreach ($worksheet->getRowIterator() as $row) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(TRUE); // This loops through all cells,
                                                                        //    even if a cell value is not set.
                                                                        // For 'TRUE', we loop through cells
                                                                        //    only when their value is set.
                                                                        // If this method is not called,
                                                                        //    the default value is 'false'.
                        foreach ($cellIterator as $cell) {
                            $temp = $cell->getValue();
                            $temp = trim($temp, " \x20\x2A\n\r\t\v\x00");
                            $cell->setValue($temp);
                        }
                    }

                    $spreadsheet->getActiveSheet()->getStyle('C:C')
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $spreadsheet->getActiveSheet()->getStyle('D:D')
                        ->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                    $writer->setEnclosure("'");
                    $writer->setDelimiter(';'); // comma was causing addresses to split up into new cells
                    $writer->setLineEnding("\r\n");
                    $csvPath = '../../uploads/' . $name.'_'.$count.'.csv';     // will look like employees_0.csv
                    //Write the CSV file
                    $writer->save($csvPath);
                    
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
                    $writer->setPreCalculateFormulas(false);

                    ob_start();
                    $writer->save('php://output');
                    $html = ob_get_clean();
                    $response['spreadsheet_html'] = $html;
                    $count++;
                }
                $response['csv_conversion_msg'] = 'SUCCESS Converted to .csv';
                $response['csv_path'] = $csvPath;

                // Function call to Connect to mysql database
                $mysqli = connectToDB();
                if ($mysqli->connect_errno) {
                    $response['db_connection'] = 'MySQL DB Connection failed: Error ' .$mysqli->connect_error;
                } 
                else
                {
                    $response['db_connection_host'] = 'MySQL DB Connection Successful: '. $mysqli->host_info;
                    $response['db_connection_client'] = 'MySQL DB Connection Successful: '. $mysqli->client_info;
                 }

                /* check if server is alive */
                if ($mysqli->ping()) {
                    $response['alive'] = 'Our connection is ok!';
                } else {
                    $response['alive'] = 'Ping Error: '. $mysqli->error;
                }

                $query_str_arr = get_insert_queries($csvPath, $mysqli);
                $size_arr = count($query_str_arr);
                for($i=0; $i<$size_arr; $i++)
                {
                    $a = 'query_' .$i;
                    $b = 'query_' .$i . '_SUCCESS';
                    $response[$a] = $query_str_arr[$i];
                    $response[$b] = $mysqli->query($query_str_arr[$i]);
                }
                $mysqli->close();
            }
            else
            {
                $response['download_xlsx_msg'] = 'Failed: to download to ' . $localpath;
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
else  // $action == <subcategory>,<sub-subcategory>
{
    $input_exploded = explode(',', $action);
    $subcat_id = $input_exploded[0];
    $subsubcat_id = $input_exploded[1];
    //$mysqli = connectToDB();

    switch ($subcat_id) 
    {
        case 2:     // only active employees
            $mysqli = connectToDB();
            echo get_active_employees($mysqli, true, true);
            $mysqli->close();
            break;

        case 3:     // only inactive employees
            $mysqli = connectToDB();
            echo get_active_employees($mysqli, false, true);
            $mysqli->close();
            break;

        case 4:         // upcoming birthdays
            $mysqli = connectToDB();
            $num_days = 7; // default choice of 7 daysy
            // bday is 14 days out
            if ($subsubcat_id == 2) $num_days = 14;
             // bday is 30 days out
            else if ($subsubcat_id == 3) $num_days = 30;
                // bday is 60 days out
            else if ($subsubcat_id == 4) $num_days = 60;

            echo get_birthdays($mysqli, $num_days, true);
            $mysqli->close();
            break;
        
        case 5:        // all employees
            $mysqli = connectToDB();
            echo pull_database($mysqli, true);
            $mysqli->close();
            break;
            
        default:        // do nothing

            break;
    }
    exit();
}