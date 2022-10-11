<?php
// TODO: schedule this to be run daily
// TODO: Make logs something other than html? Or is html fine?

require 'vendor/autoload.php';
use \Mailjet\Resources;

// EDIT THESE 3 VARIABLES AS NECESSARY -----------------
$num_days = 11;
$SENDER_EMAIL = "sohyung.cho@boolsa.io";
$RECIPIENT_EMAIL = "sohyung.cho@boolsa.io";
// EDIT THESE 3 VARIABLES AS NECESSARY -----------------

/* email request body */              
$body = [
    'Messages' => [
        [
            'From' => [
                'Email' => "$SENDER_EMAIL",
                'Name' => "Boolsa"
            ],
            'To' => [
                [
                    'Email' => "$RECIPIENT_EMAIL",
                    'Name' => "You"
                ]
            ],
            'Subject' => "UPCOMING EMPLOYEE BIRTHDAYS",
            'TextPart' => "Greetings!",
            'HTMLPart' => "<h3>Birthday Records: </h3><br> "
        ]
    ]
];
// Accessing the part of the array where the HTML should be edited
// $body['Messages'][0]['HTMLPart'] .= 

/**
 * Use mysqli extension to connect to MySQL database
 * 
 * @return null - if json_decode() fails
 *         null - if mysqli object isn't created (connection to MySQL DB failed)
 *         mysqli object - if MySQL database connection was successful
 */
function connectToDB()
{
    $json_credentials = file_get_contents('keys/db_credentials.json');
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
 *  Function that sends the actual email
 */
function send_email($SENDER_EMAIL, $RECIPIENT_EMAIL, $body)
{
    // Use your saved credentials, specify that you are using Send API v3.1
    $mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'),true,['version' => 'v3.1']);

    $response = $mj->post(Resources::$Email, ['body' => $body]);
    // Read the response
    $response->success() && var_dump($response->getData());
    return;
}
$fileName = 'bday_logs/bday_email_log_' . date('m-d') . '.html';

// Turn on output buffering
ob_start();
echo "<!doctype html>";
echo "<html>";
echo "<body>";

$mysqli = connectToDB();
if($mysqli !== null)
{
    $mysqli->set_charset('utf8mb4');
}
else
{
    echo "<p>MYSQL connection failed</p>";
    echo "</body>";
    echo "</html>";
    // Return the contents of the output buffer
    $outString = ob_get_contents();
    // Clean the output buffer and turn off output buffering
    ob_end_clean(); 
    // Write final string to file
    file_put_contents($fileName, $outString);
    exit();
}

// update databse to be only active employees
// SQL stmt to put new active employees into bday_emails table
$update_sql = "INSERT INTO bday_emails (first_name, last_name, date_of_birth) select first_name, last_name, date_of_birth from employees where (first_name, last_name) NOT IN (select first_name, last_name from bday_emails);";
$result = mysqli_query($mysqli, $update_sql);
if(!$result)
{
    echo "<p>database update failed on query = $update_sql</p>";
    echo "</body>";
    echo "</html>";
    $outString = ob_get_contents();
    // Clean the output buffer and turn off output buffering
    ob_end_clean(); 
    // Write final string to file
    file_put_contents($fileName, $outString);
    exit();
}
// SQL stmt to delete employees from bday_emails table that are no longer active
$remove_sql = "DELETE FROM bday_emails WHERE (first_name, last_name) NOT IN ( SELECT first_name, last_name from employees where active=TRUE);";
$result = mysqli_query($mysqli, $remove_sql);
if(!$result)
{
    echo "<p>database update failed on $remove_sql</p>";
    echo "</body>";
    echo "</html>";
    //  Return the contents of the output buffer
    $outString = ob_get_contents();
    // Clean the output buffer and turn off output buffering
    ob_end_clean(); 
    // Write final string to file
    file_put_contents($fileName, $outString);
    exit();
}
// SQL stmt to set employees to NOT-notified if bday not in the specified time frame
$set_not_notified_sql = "UPDATE bday_emails SET notified = FALSE WHERE (first_name, last_name) NOT IN (SELECT first_name, last_name FROM employees where DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') and DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') ORDER BY DATE_FORMAT(date_of_birth, '%m-%d') );";
$result = mysqli_query($mysqli, $set_not_notified_sql);
if(!$result)
{
    echo "<p>setting NOT notified for people outside date range failed on query=$set_not_notified_sql</p>";
    echo "</body>";
    echo "</html>";
    //  Return the contents of the output buffer
    $outString = ob_get_contents();
    // Clean the output buffer and turn off output buffering
    ob_end_clean(); 
    // Write final string to file
    file_put_contents($fileName, $outString);
    exit();
}
// sql stmt setting who to send bday email about for upcoming birthday
$to_notify_sql = "SELECT first_name, last_name, DATE_FORMAT(date_of_birth, '%m-%d') as DOB_no_year from bday_emails where notified=FALSE and (first_name, last_name) in (SELECT first_name, last_name FROM employees where DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') and DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') ORDER BY DATE_FORMAT(date_of_birth, '%m-%d') );";
if($result = mysqli_query($mysqli, $to_notify_sql))
{
    if(mysqli_num_rows($result) > 0)
    {
        echo "Number of records $num_days days out from " . date('Y-m-d');
        echo ": " . mysqli_num_rows($result);
        $body['Messages'][0]['HTMLPart'] .= "There is/are " . mysqli_num_rows($result) ." birthday(s) $num_days days out from " . date('Y-m-d');
        echo "<br>";
        $body['Messages'][0]['HTMLPart'] .= "<br><br>";
        echo "<table>"; 
        $body['Messages'][0]['HTMLPart'] .= "<table>";
            echo "<tr>";
            $body['Messages'][0]['HTMLPart'] .= "<tr>";
                echo "<th>first_name</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th>First Name</th>";
                echo "<th>last_name</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th>Last Name</th>";
                echo "<th>Date_of_birth (Year Omitted)</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th>Birthday (mm-dd) </th>";
            echo "</tr>";
            $body['Messages'][0]['HTMLPart'] .= "</tr>";
            while($row = mysqli_fetch_array($result))
            {
                echo "<tr>";
                $body['Messages'][0]['HTMLPart'] .= "<tr>";
                for($i=0; $i<3; $i++)
                {
                    echo "<td>$row[$i]</td>";
                    $body['Messages'][0]['HTMLPart'] .= "<td>$row[$i]</td>";
                }
                echo "</tr>";
                $body['Messages'][0]['HTMLPart'] .= "</tr>";
            }
        echo "</table>";  
        $body['Messages'][0]['HTMLPart'] .= "</table>";  
        $body['Messages'][0]['HTMLPart'] .= "<br>";
        send_email($SENDER_EMAIL, $RECIPIENT_EMAIL, $body);

        $set_as_notified_sql = "UPDATE bday_emails SET notified = TRUE WHERE (first_name, last_name) IN (SELECT first_name, last_name FROM employees where DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') and DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') ORDER BY DATE_FORMAT(date_of_birth, '%m-%d') );";
        mysqli_free_result($result); 
        $result = mysqli_query($mysqli, $set_as_notified_sql);
        if(!$result)
        {
            echo "<p>SETTING AS notified for people inside date range failed on query=$set_as_notified_sql</p>";
        }
    }
    else
    {
        echo "<strong>No records matched your query</strong>";
    }
    echo "</body>";
    echo "</html>";
    //  Return the contents of the output buffer
    $outString = ob_get_contents();
    // Clean the output buffer and turn off output buffering
    ob_end_clean(); 
    // Write final string to file
    file_put_contents($fileName, $outString);
}
else
{
    echo "<p>Failed on query: $to_notify_sql </p>";
    echo "</body>";
    echo "</html>";
    //  Return the contents of the output buffer
    $outString = ob_get_contents();
    // Clean (erase) the output buffer and turn off output buffering
    ob_end_clean(); 
    // Write final string to file
    file_put_contents($fileName, $outString);
}
$mysqli->close();
// original sql stmt (time interval) - just for safe keeping
//$orig_sql = "SELECT first_name, last_name, email, DATE_FORMAT(date_of_birth, '%m-%d') FROM employees where DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') and DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') ORDER BY DATE_FORMAT(date_of_birth, '%m-%d');";
?>