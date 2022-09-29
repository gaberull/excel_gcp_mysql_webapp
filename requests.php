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
                $response['download_xlsx_msg'] = 'SUCCESS: File saved to ' . $localPath;

                // Convert file to csv
                $name = 'employees';
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                //$reader->setReadDataOnly(false);
                $reader->setReadDataOnly(false);

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
/*
                    //trim(iconv("UTF-8","ISO-8859-1",$sheet->getCell('B'.$row )->getValue())," \t\n\r\0\x0B\xA0");
                    $worksheet = $spreadsheet->getActiveSheet();
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
*/

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
                    /*
                    $mysqli = csv_to_db($csvPath, $mysqli);
                    if($mysqli==null)
                    {
                        $response['db_write_error'] = $mysqli->error;
                    }
                    else
                    {
                        $sql = "SELECT FROM employees(name,email,address);";
                        $response['db_content'] = mysqli_query($con, $sql);
                    }
                    */
                 }

                /* check if server is alive */
                if ($mysqli->ping()) {
                    $response['alive'] = 'Our connection is ok!';
                } else {
                    $response['alive'] = 'Ping Error: '. $mysqli->error;
                }

                $query_str_arr = get_insert_queries($csvPath, $mysqli);
                $size_arr = count($query_str_arr);
                // loop through return array of query strings, add to database, add to response if successful or not and the query itself
                $db_entry_results = array("query_string_0" => 'placeholder', "success_0" => 'placeholder');
                //$db_entry_results["query_string_0"] = $query_str_arr[0];
                //$db_entry_results["success_0"] = $temp);
                for($i=0; $i<$size_arr; $i++)
                {
                    //$ret = $mysqli->query($query_str_arr[$i]);  // returns TRUE / FALSE if not requesting data from db
                    //$db_entry_results["query_string_$i"] = $query_str_arr[$i];
                    //$db_entry_results["success_$i"] = $ret;
                    $a = 'query_' .$i . '_string';
                    $b = 'query_' .$i . '_SUCCESS';
                    $response[$a] = $query_str_arr[$i];
                    $response[$b] = $mysqli->query($query_str_arr[$i]);
                    //array_push($db_entry_results, "query_string_$i" => $query_str_arr[$i], "success_$i" => $temp);
                    //$db_entry_results = array("query_string" => $query_str_arr[$i], "success" => $temp);
                    //$response["query_$i"] = json_encode($db_entry_results);
                }
/*
                $db_entry_results["query_string_11"] = $query_str_arr[11];
                $a = 'query_11_str';
                $response[$a] = $query_str_arr[$i];
                $response['query_11_SUCCESS'] = "FAILLLLLLLLLLLL";
*/
                //if(!mysqli>real_query( $query_str_arr[11]))
                //{
                    //$response['query_11_ERROR'] = $mysqli->error;
                //}
                //$out = array_values($db_entry_results);
                //$response["all_queries"] = json_encode($out);
                //$response["all_queries"] = json_encode($db_entry_results);
                //$response["all_queries"] = json_encode($db_entry_results);
                // Close DB connection
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