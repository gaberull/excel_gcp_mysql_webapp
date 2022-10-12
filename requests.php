<?php
include_once 'config.php';
//include_once 'subcategories.php';

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
            // TEST: get object detail (filesize, contentType, updated [date], etc.)
            $response['data'] = getFileInfo($bucketName, $cloudPath);
            //$localpath = 'uploads/' . $_FILES['file']['name'];
            $localPath = $cloudPath;
            //downloadLocally($bucketName, $cloudPath, $localPath);
            $temp = downloadLocally($bucketName, $cloudPath, $localPath);
            if($temp != false)
            {
                $response['download_xlsx_msg'] = 'SUCCESS: File xlsx file saved locally';
                $response['download_xlsx_loc'] = $localPath;
                // Convert file to csv
                $name = 'employees';
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                //$reader->setReadDataOnly(false);
                //$reader->setReadDataOnly(false);
                $reader->setReadEmptyCells(false);

                //Get all sheets in file
                $sheets = $reader->listWorksheetNames($localPath);
                
                //Loop over each sheet and save an individual file
                $count = 0;
                foreach($sheets as $sheet)
                {
                    //trim(iconv("UTF-8","ISO-8859-1",$sheet->getCell('B'.$row )->getValue())," \t\n\r\0\x0B\xA0");
                    //trim(utf8_decode($sheet->getCell('B'.$row )->getValue())," \t\n\r\0\x0B\xA0");
                    //Load the file
                    $reader->setLoadSheetsOnly([$sheet]);
                    //$reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($localPath);

                    //trim(iconv("UTF-8","ISO-8859-1",$sheet->getCell('B'.$row )->getValue())," \t\n\r\0\x0B\xA0");
                    
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
                            //$cell->setValue(trim(iconv("UTF-8","ISO-8859-1",$temp)," \t\n\r\0\x0B\xA0"));
                            $cell->setValue($temp);
                            //trim(utf8_decode($temp)," \t\n\r\0\x0B\xA0");
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

                    //$csvPath = 'uploads/' . $sheet.'.csv';
                    $csvPath = 'uploads/' . $name.'_'.$count.'.csv';    // will look like employees_0.csv
                    //Write the CSV file
                    $writer->save($csvPath);
                    
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
                    $writer->setPreCalculateFormulas(false);

                    // TODO: possibly do a trim before save to html
                    $html_location = "uploads/recent_spreadsheet.html";
                    // TODO: delete this file
                    $writer->save($html_location);
                    $response['spreadsheet_location'] = $html_location;
                    //$response['spreadsheet'] = $writer->save('php://output');
                    $count++;
                }
                $response['csv_conversion_msg'] = 'SUCCESS Converted to .csv';
                $response['csv_path'] = $csvPath;
                //$response['html_path'] $htmlPath;

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
    $mysqli = connectToDB();

    switch ($subcat_id) 
    {
        case 2:     // only active employees
            echo get_active_employees($mysqli, true);
            break;

        case 3:     // only inactive employees
            echo get_active_employees($mysqli, false);
            break;

        case 4:         // upcoming birthdays
            $num_days = 7; // default choice of 7 days
            // bday is 14 days out
            if ($subsubcat_id == 2) $num_days = 14;
             // bday is 30 days out
            else if ($subsubcat_id == 3) $num_days = 30;
                // bday is 60 days out
            else if ($subsubcat_id == 4) $num_days = 60;

            echo get_birthdays($mysqli, $num_days);
            break;
        
        default:        // all employees
            echo pull_database($mysqli);

            break;
    }
    $mysqli->close();
    exit();
}