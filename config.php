<?php
session_start();
// load composer generated files
require_once '../vendor/autoload.php'; 

// load GCS library
use Google\Cloud\Storage\StorageClient;
// load PHPSpreadsheet xlsx and csv tools
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;

// Path to private API key stored on GCP VM (not publicly)
// TODO: use PHP to get relative path. Encrypt file?
$privateKeyFilePath = '../keys/silken-reducer-359320-b3cecc9b17ca.json';

// Global variables for debugging, CENSOR   -------------------------
$CENSOR = false;
$DEBUG = false;
//   CENSOR ABOVE        --------------------------------------------
/**
 *  Upload file to Google Cloud Storage Bucket
 * 
 *  @return bool 
 *      true  - file is uploaded
 *      false - file is not uploaded
 */
function uploadFile($bucketName, $fileContent, $cloudPath) 
{
    $privateKeyFilePath = $GLOBALS['privateKeyFilePath'];
    // connect to Google Cloud Storage using private key as authentication
    try 
    {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } 
    catch (Exception $e) 
    {
        print $e;
        return false;
    }

    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);

    // upload/replace file 
    $storageObject = $bucket->upload(
            $fileContent,
            ['name' => $cloudPath]
            // if $cloudPath is existed then will be overwrite without confirmation
            // NOTE: 
            // a. do not put prefix '/', '/' is a separate folder name  !!
            // b. private key MUST have 'storage.objects.delete' permission if want to replace file !
    );
    return $storageObject != null;  // return if successful or not
}

/**
 *  Get File info for specific file in Google Cloud Storage Bucket
 */
function getFileInfo($bucketName, $cloudPath) {
    $privateKeyFilePath = $GLOBALS['privateKeyFilePath'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    return $object->info();
}

/**
 *  List all items in Google Cloud storage bucket
 * 
 *  @return null if no objects in GCS 
 *  @return array of object names otherwise
 */
function listFiles($bucket, $directory = null) {

    if ($directory == null) {
        // list all files
        $objects = $bucket->objects();
    } else {
        // list all files within a directory (sub-directory)
        $options = array('prefix' => $directory);
        $objects = $bucket->objects($options);
    }
    $file_array = array();
    $count = 0;
    foreach ($objects as $object) {
        $file_array [count] = $object->name();
        $count++;
        //print $object->name() . PHP_EOL;
        // NOTE: if $object->name() ends with '/' then it is a 'folder'
    }
    if($count === 0)
    {
        return null;
    }
    return $file_array;
}
/**
 *  From Google Cloud Storage API Documentation
 * 
 * @param $bucketName - name of bucket (xlsx-uploads)
 * @param $cloudPath - path of file on GCS
 * 
 * @return bool or storage object containing file info
 */
function downloadFileToMemory($bucketName, $cloudPath) {
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object->$bucket->object($cloudPath);
    $contents = $object->downloadAsString();
    printf(
        'Downloaded gs://%s/%s to %s' . PHP_EOL,
        $contents,
        $bucketName,
        $contents
    );
    return $contents;
}

/**
 *  Download file from GCS storage to local disk (on VM)
 * 
 *  @return true if object is downloaded correctly
 *  @return false if StorageClient fails to be created (Google Cloud Storage API call)
 */
function downloadLocally($bucketName, $cloudPath, $localpath)
{
    //$bucketName = 'xlsx-uploads'; $temp = downloadLocally($bucketName, $cloudPath, $localpath);
    // $objectName = 'my-object';
    // $destination = '/path/to/your/file';

    $privateKeyFilePath = $GLOBALS['privateKeyFilePath'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($privateKeyFilePath), true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        echo $e;
        return false;
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    $object->downloadToFile($localpath);
    return true; // return object (Psr\Http\Message\StreamInterface)
}

/**
 * Use mysqli extension to connect to MySQL database
 * 
 * @return null if json_decode() fails
 * @return null if mysqli object isn't created (connection to MySQL DB failed)
 * @return mysqli object if MySQL database connection was successful
 */
function connectToDB()
{
    $json_credentials = file_get_contents('../keys/db_credentials.json');
    $json_data = json_decode($json_credentials, true);
    if($json_data == null)
    {
        return null;
    }
    // connect to database
    $mysqli = new mysqli(
        $json_data['host'],
        $json_data['user'],
        $json_data['password'],
        $json_data['database']
    );
    if($mysqli !== null)
    {
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}

/**
 *  Builds strings for inserting csv items into DB to return to requests.php
 * 
 *  @param $mysqli - connection to mysql database
 *  @param $csvPath - string path to csv file in apache directory
 * 
 *  @return array of strings containing mysql queries to insert from csv
 */
function get_insert_queries($csvPath, $mysqli)
{
    // set all employees to not active, then iterate through and set ones in file to active
    $query = "UPDATE employees SET active=FALSE;";
    //$result_str = '';
    $result = $mysqli->query($query);
    $fields = array("first_name", "last_name", "start_date", "date_of_birth", "address", "email", "phone_number", "schedule", "position", "active");
    $fields_assoc = array("first_name" => null, "last_name" => null, "start_date" =>null , "date_of_birth" =>null, "address" =>null, "email" =>null, "phone_number" =>null, "schedule" =>null, "position" =>null, "active" =>1);
    //$stmt = mysqli_prepare($mysqli, "REPLACE INTO employees (first_name, last_name, start_date, date_of_birth, address, email, phone_number, schedule, position, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $row = 0;
    $query_str_arr = array();
    $fp = fopen($csvPath, "r");
    while (($data = fgetcsv($fp, 10000, ";")) !== FALSE) 
    {
        $num = count($data);
        if($row != 0)
        {
            //$stmt = $mysqli->prepare($mysqli, "REPLACE INTO employees (first_name, last_name, start_date, date_of_birth, address, email, phone_number, schedule, position, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
            //$stmt = $mysqli->prepare($mysqli, "REPLACE INTO employees(first_name,last_name,start_date,date_of_birth,address,email,phone_number,schedule,position,active) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $query = "REPLACE INTO employees (first_name, last_name, start_date, date_of_birth, address, email, phone_number, schedule, position, active) VALUES (";
            for ($c=0; $c < $num; $c++) 
            {
                $value = $data[$c];
                // IMPORTANT: trim() progresses from first char to last in the character string
                // trim leading/trailing single quotes first, then asterisks(\x2A), then whitespace and similar chars
                $value = trim($value, "'\x2A \n\r\t\v\x00");    // \x2A means 0x2A - hex code for asterisk on ascii table
                // re-add single quotes to start and end of string    
                $value = "'$value'"; 
                // change all multiple spaces and space characters to just one single space character 
                $value = trim(preg_replace('/[\t\n\r\s]+/', ' ', $value));
                $col = $fields[$c];
                
                // Make all name values uppercase if they're not already
                if($col === "first_name" || $col === "last_name")
                {
                    $value = strtoupper($value);
                }
                else if($col === "email")   // if we're in "email" column (primary key in mysql db), make value lowercase.
                {
                    $value = strtolower($value);
                }
                // preceding whitespace sometimes seemed to switch around the format of the date
                else if($col === "start_date" || $col === "date_of_birth")
                {
                    // if there are slashes in the date, will be like 01/09/1984 OR 1984/01/09
                    // find out which one it is and put it in the correct format
                    $pos = strpos($value, '/'); // Check if string contains a backslash at all
                    if($pos !== false)
                    {
                        $value = trim($value, "'\x2A \n\r\t\v\x00");    // trim single quotes
                        $tok = strtok($value, "/"); // from 01/09/1984 OR 1984/01/09 this gives '1984' OR '01' - never '09'
                        if(strlen($tok)>3)  // length of 4. First token is year
                        {
                            $year = $tok;
                            $day = strtok( "/");
                            $month = strtok("/");
                            $value = "$year-$day-$month";
                        }
                        else    // length of 2. First token is the day ('01)
                        {
                            $day = $tok;
                            $month = strtok( "/");
                            $year = strtok( "/");
                            $value = "$year-$day-$month";
                        }
                        $value = "'$value'";    // add single quotes back to string
                    }
                }
                $query .= "$value,";    // add cell and comma to query string
            } 
            // "active" is not in csv file. Adding it manually to update which employees are still active
            $query .= "TRUE);";     
            array_push($query_str_arr, $query);
            //$fields_assoc[$fields[$num]] = 1;
            //$stmt->bind_param('sssssssssi', $fields_assoc[$fields[0]], $fields_assoc[$fields[1]], $fields_assoc[$fields[2]], $fields_assoc[$fields[3]], $fields_assoc[$fields[4]], $fields_assoc[$fields[5]], $fields_assoc[$fields[6]], $fields_assoc[$fields[7]], $fields_assoc[$fields[8]], $fields_assoc[$fields[9]]);
            //$stmt->execute();
        }    
        $row++;
    }
    fclose($fp);
    return $query_str_arr;
}

/**
 *  Get Database Table Columns 
 * 
 *  @return - false on failure, array on success 
 */
function get_col_names($mysqli)
{
    $sql = 'SHOW COLUMNS FROM employees';
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()){
        $columns[] = $row['Field'];
    }
    return $columns;    //TODO: return false if !$columns
}

/**
 *  Format number to have dashes in it
 */
function format_phone_number($number, $censor=false)
{
    $censor = ($censor || $GLOBALS['CENSOR']);
    if (!preg_match("/^\d+$/", $number)) 
    {
        return false;
    }
    if($number == "")
    {
        return $number;
    } 
    // format phone number to hold dashes
    $phone_str = "";
    $n = strlen($number);
    for($j = $n-1; $j>=0; $j--) // handle 7, 9, 10, or 11 digits
    {
        $phone_str = $number[$j] . $phone_str;
        if($j==$n-4 || $j==$n-7 || ($n>10 && $j==$n-10))
        {
            $phone_str = '-'. $phone_str;
        }
    }
    if($censor)
    {
        return "XXX-XXX-XXXX";
    }
    else
    {
        return $phone_str;
    }
    
}

/**
 *  Get full html table for query for ACTIVE employees with upcoming bdays
 *  
 * @param $mysqli - mysql database connection
 * @param $len_time - number of days out to check (int)
 * @return html with results of query. Table holds: 
 *             - first_name
 *             - last_name
 *             - email
 *             - date_of_birth
 */
function get_birthdays($mysqli, $len_time, $censor=false)
{
    $censor = ($censor || $GLOBALS['CENSOR']);
    $sql = 
    "SELECT first_name, last_name, email, address, phone_number, DATE_FORMAT(date_of_birth, '%m-%d') 
    FROM employees 
    WHERE DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') 
    AND DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$len_time DAY), '%m-%d') 
    AND active=TRUE
    ORDER BY DATE_FORMAT(date_of_birth, '%m-%d');";
    $num_cols = 6;
    if($results = mysqli_query($mysqli, $sql))
    {
        if(mysqli_num_rows($results) > 0)
        {
            echo "Number of records: " . mysqli_num_rows($results);
            echo "<br>";
            echo "<table class=\"dt\">"; 
                echo "<tr>";
                    echo "<th class=\"dt\">First Name</th>";      // i==0
                    echo "<th class=\"dt\">Last Name</th>";       // i==1
                    echo "<th class=\"dt\">Email</th>";           // i==2
                    echo "<th class=\"dt\">Address</th>";         // i==3
                    echo "<th class=\"dt\">Phone Number</th>";    // i==4
                    echo "<t class=\"dt\">Birthday (mm-dd)</th>";// i==5
                echo "</tr>";
                while($row = mysqli_fetch_array($results))
                {
                    echo "<tr>";
                    for($i=0; $i<$num_cols; $i++)
                    {
                        if($censor)
                        {
                            if($i==1 || $i==2 || $i==3 || $i==4)
                            {
                                if ($i==4)  // if on phone numbers column
                                {
        
                                    $p = format_phone_number($row[$i], true);
                                    // class=\"black-background\"
                                    echo "<td class=\"black-background\" \"dt\">$p</td>";
                                }
                                else
                                {
                                    //echo "<td class=\"black-background\">$row[$i]</td>";
                                    echo "<td class=\"black-background\" \"dt\">XXXXXXXXXXXX</td>";
                                }
                            }
                            else
                            {
                                echo "<td class=\"dt\">$row[$i]</td>";
                            }
                        }
                        if(!$censor)
                        {
                            if ($i==4)  // if on phone numbers column
                            {
    
                                $p = format_phone_number($row[$i]);
                                echo "<td class=\"dt\">$p</td>";
                            }
                            else
                            {
                                echo "<td class=\"dt\">$row[$i]</td>";
                            }
                        }
                    }
                    echo "</tr>";
                }
            echo "</table>";
            mysqli_free_result($results);    
        }
        else
        {
            echo "<strong>No employees with birthdays in that timeframe.</strong>";
        }
    }else{
        echo "<strong>ERROR - end of get_birthdays()</strong>";
    }
    return;

}

/**
 *  Get FULL html for table populated from database - ENTIRE DATABASE TABLE 'employees'
 * 
 * @param $mysqli - mysql database connection
 */
function pull_database($mysqli, $censor=false) 
{
    // censor data, yes or no
    $censor = ($censor || $GLOBALS['CENSOR']);
    $columns = get_col_names($mysqli);
    $num_cols = count($columns);
    // order employees by active first, then last_name
    $sql = 
    "SELECT * FROM employees 
    ORDER BY active DESC, position ASC, last_name ASC";
    //$rows = $result->fetch_all(MYSQLI_ASSOC);
    if($result = mysqli_query($mysqli, $sql))
    {
        if(mysqli_num_rows($result) > 0)
        {
            echo "<b>Note:</b> RED background rows are INACTIVE employees";
            echo "<br><br>";
            echo "Number of records: " . mysqli_num_rows($result);
            echo "<br>";
            echo "<table class=\"dt\">"; 
                echo "<tr>";
                    for($i=0; $i<$num_cols-1; $i++)   //TODO: should be $num_cols
                    {
                        echo "<th class=\"dt\">$columns[$i]</th>";
                    }
                echo "</tr>";
                while($row = mysqli_fetch_array($result))
                {
                    if($row[$num_cols-1]==0)
                    {
                        echo "<tr class=\"red-row\">";
                    }
                    else
                    {
                        $job = strtolower($row[8]);
                        for($k=0; $k<strlen($job); $k++)
                        {
                            if($job[$k]==' ')
                            {
                                $job[$k]='-';
                            }
                        }
                        //$pos_str = str_replace(" ", "-", $temp, 1);
                        echo "<tr class=\"$job\">";
                        //echo "<tr><td>$pos_str</td></tr>";
                        //echo "<tr>";
                    }
                    for($i=0; $i<$num_cols-1; $i++)
                    {
                        if($censor)
                        {
                            // censoring mechanism for making a gif for my documentation:
                            if($columns[$i]=='last_name' || $columns[$i]=='date_of_birth' || $columns[$i]=='address' || $columns[$i]=='email' || $columns[$i]=='phone_number')
                            {
                                if ($columns[$i]=='phone_number')
                                {
                                    $p = format_phone_number($row[$i], true);
                                    echo "<td class=\"black-background\" \"dt\">$p</td>";
                                }
                                else
                                {
                                    //echo "<td class=\"black-background\">$row[$i]</td>";
                                    echo "<td class=\"black-background\" \"dt\">XXXXXXXXX</td>";
                                }
                            }
                            else
                            {
                                echo "<td class=\"dt\">$row[$i]</td>";
                            }
                        }
                        if(!$censor)
                        {
                            // insert dashes into phone number
                            if ($columns[$i]=='phone_number')
                            {
                                $p = format_phone_number($row[$i]);
                                echo "<td class=\"dt\">$p</td>";
                            }
                            else
                            {
                                echo "<td class=\"dt\">$row[$i]</td>";
                            }
                        }
                        
                    }
                    echo "</tr>";
                }
            echo "</table>";  
            mysqli_free_result($result);    
        }
        else
        {
            echo "<strong> No records matched your query</strong>";
        }
    }
    else
    {
        echo "ERROR - end of pull_database()";
    }
    return;
}

/**
 *  Get FULL HTML table for all ACTIVE OR INACTIVE employees in the db
 * 
 * @param $active $boolean 
 * @param $mysqli - connection to database
 */
function get_active_employees($mysqli, $active, $censor=false)
{
    $censor = ($censor || $GLOBALS['CENSOR']);
    if($active == true)
    {
        $sql = "SELECT * FROM employees where active=true ORDER BY position, last_name;";
    }
    else
    {
        $sql = "SELECT * FROM employees where active=false ORDER BY position, last_name;";
    }

    $columns = get_col_names($mysqli);
    $num_cols = count($columns);
    if($result = mysqli_query($mysqli, $sql))
    {
        if(mysqli_num_rows($result) > 0)
        {
            echo "Number of records: " . mysqli_num_rows($result);
            echo "<br>";
            echo "<table class=\"dt\">"; 
                echo "<tr>";
                //for($i=0; $i<$num_cols-1; $i++)     // don't show active row ($num_cols-1)
                //{
                //    echo "<th>$columns[$i]</th>";
                //}
                echo "<th class=\"dt\">First Name</th>";
                echo "<th class=\"dt\">Last Name</th>";
                echo "<th class=\"dt\">Start Date</th>";
                echo "<th class=\"dt\">Date Of Birth</th>";
                echo "<th class=\"dt\">Address</th>";
                echo "<th class=\"dt\">Email</th>";
                echo "<th class=\"dt\">Phone Number</th>";
                echo "<th class=\"dt\">Schedule</th>";
                echo "<th class=\"dt\">Position</th>";
                echo "</tr>";
                while($row = mysqli_fetch_array($result))
                {
                    if($active)
                    {
                        $role = strtolower($row[8]);
                        for($k=0; $k<strlen($role); $k++)
                        {
                            if($role[$k]==' ')
                            {
                                $role[$k]='-';
                            }
                        }
                        echo "<tr class=\"$role\">";
                    }
                    else    // inactive record row (red background)
                    {
                        echo "<tr class=\"red-row\">";
                    }
                    
                    for($i=0; $i<$num_cols-1; $i++)
                    {
                        if($censor)
                        {
                            if($columns[$i]=='last_name' || $columns[$i]=='date_of_birth' || $columns[$i]=='address' || $columns[$i]=='email' || $columns[$i]=='phone_number')
                            {
                                if($columns[$i]=="phone_number")
                                {
                                    $p = format_phone_number($row[$i], true);
                                    echo "<td class=\"black-background\" \"dt\">$p</td>";
                                }
                                else
                                {
                                    //echo "<td class=\"black-background\">$row[$i]</td>";
                                    echo "<td class=\"black-background\" \"dt\">XXXXXXXXXXXXXXXX</td>";
                                }
                            }
                            else
                            {
                                echo "<td class=\"dt\">$row[$i]</td>";
                            }
                        }
                        if(!$censor)
                        {
                            if($columns[$i]=="phone_number")
                            {
                                $p = format_phone_number($row[$i]);
                                echo "<td class=\"dt\">$p</td>";
                            }
                            else
                            {
                                echo "<td class=\"dt\">$row[$i]</td>";
                            }
                        }
                    }
                    echo "</tr>";
                }
            echo "</table>";  
            mysqli_free_result($result);    
        }
        else
        {
            echo "<strong>No records matched your query</strong>";
        }
    }
    else
    {
        echo "ERROR - end of get_active_employees()";
    }
    return;
}

/**
 *     **DEPRECATED** 
 */ 
function csv_to_db($csvPath, $conn)
{
    $file = fopen($csvPath, "r");
    while (($data = fgetcsv($file, 10000, ",")) !== FALSE)
    {
        $sql = "REPLACE INTO employees(first_name,last_name,start_date,date_of_birth,address,email,phone_number,schedule,position,active) VALUES('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]',1);";
        mysqli_query($conn, $sql);
    }
    fclose($file);

    return $conn;
}
