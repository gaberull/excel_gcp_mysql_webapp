<?php
// This should be scheduled to be run daily with "crontab -e" cmd

require 'vendor/autoload.php';
use \Mailjet\Resources;

// EDIT THESE VARIABLES AS NECESSARY -----------------
$num_days = 10;
$SENDER_EMAIL = "sohyung.cho@boolsa.io";
$RECIPIENT_EMAIL = "sohyung.cho@boolsa.io";
// EDIT THESE VARIABLES AS NECESSARY -----------------

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
            'HTMLPart' => "<p>This is a friendly reminder that you have employee birthdays coming up soon!</p><br><h3>Records From Employee Database: </h3><br> "
        ]
    ]
];
// Accessing the part of the array where the HTML should be edited:   $body['Messages'][0]['HTMLPart'] .=

/**
 * Use mysqli extension to connect to MySQL database
 * 
 * @return null - if json_decode() fails
 *         null - if mysqli object isn't created (connection to MySQL DB failed)
 *         mysqli (conn) object - if MySQL database connection was successful
 */
function connectToDB()
{
    $json_credentials = file_get_contents(__DIR__.'/keys/db_credentials.json');
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
 * 
 * @param SENDER_EMAIL - Email addr of sender for MailJet API call
 * @param RECIPIENT_EMAIL - Email addr of recipient 
 * @param body - email body (associative array)
 * 
 * @return response from post request to MailJet API if successful
 *         nothing - if not successful
 */
function send_email($SENDER_EMAIL, $RECIPIENT_EMAIL, $body)
{
    $json_credentials = file_get_contents(__DIR__.'/keys/mailjet_credentials.json');
    $json_data = json_decode($json_credentials, true);
    // Use your saved credentials, specify that you are using Send API v3.1
    //$mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'),true,['version' => 'v3.1']);
    $mj = new \Mailjet\Client($json_data['MJ_APIKEY_PUBLIC'], $json_data['MJ_APIKEY_PRIVATE'], true, ['version' => 'v3.1']);
    $response = $mj->post(Resources::$Email, ['body' => $body]);
    // Read the response
    //$response->success() && var_dump($response->getData());
    if($response->success())
    {
        return var_dump($response->getData());
    }
    return;
}
$fileName = 'bday_logs/log_' . date('m-d-Y_H-i-s') . '.html';

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

/* update databse to be only active employees   */
// SQL stmt to put new active employees into bday_emails table
$update_sql = 
"INSERT INTO bday_emails (first_name, last_name, date_of_birth) 
SELECT first_name, last_name, date_of_birth 
FROM employees 
WHERE (first_name, last_name) 
NOT IN (SELECT first_name, last_name
        FROM bday_emails);";

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
$remove_sql = 
"DELETE FROM bday_emails 
WHERE (first_name, last_name) 
NOT IN ( SELECT first_name, last_name FROM employees WHERE active=TRUE);";
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
$set_not_notified_sql = 
"UPDATE bday_emails 
SET notified = FALSE 
WHERE (first_name, last_name) 
NOT IN (SELECT first_name, last_name 
        FROM employees 
        WHERE DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') 
        AND DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') 
        ORDER BY DATE_FORMAT(date_of_birth, '%m-%d') );";
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
//$to_notify_sql = "SELECT first_name, last_name, DATE_FORMAT(date_of_birth, '%m-%d') as DOB_no_year from bday_emails where notified=FALSE and (first_name, last_name) in (SELECT first_name, last_name FROM employees where DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') and DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') ORDER BY DATE_FORMAT(date_of_birth, '%m-%d') );";
// **ALSO, FILTER OUT employees hired in last 6 months OR NO STATED STATING DATE**
//$to_notify_sql = "SELECT first_name, last_name, phone_number, email, DATE_FORMAT(date_of_birth, '%m-%d') as DOB_no_year from bday_emails where notified=FALSE and (first_name, last_name) in (SELECT first_name, last_name FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') AND (start_date <= curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month OR start_date IS NULL) ORDER BY DATE_FORMAT(date_of_birth, '%m-%d'));";
// the above (commented out) query works

// below query performs inner join on both employee database tables 
$to_notify_sql = 
"SELECT b.first_name, b.last_name, b.phone_number, b.address, b.email, DATE_FORMAT(b.date_of_birth, '%m-%d') AS DOB from bday_emails AS a 
INNER JOIN employees AS b
ON a.first_name=b.first_name AND a.last_name=b.last_name
WHERE a.notified=FALSE 
AND DATE_FORMAT(b.date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') 
AND DATE_FORMAT(b.date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') 
AND (b.start_date <= curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month OR b.start_date IS NULL) 
ORDER BY DATE_FORMAT(b.date_of_birth, '%m-%d');";

if($result = mysqli_query($mysqli, $to_notify_sql))
{
    if(mysqli_num_rows($result) > 0)
    {
        echo "Number of records $num_days days out from <b>" . date('Y-m-d');
        echo ":</b> " . mysqli_num_rows($result);
        $body['Messages'][0]['HTMLPart'] .= 'There is/are ';
        $body['Messages'][0]['HTMLPart'] .= mysqli_num_rows($result);
        $body['Messages'][0]['HTMLPart'] .= ' birthday(s) '.$num_days. ' days out from <b>'. date('Y-m-d');
        echo "<br>";
        $body['Messages'][0]['HTMLPart'] .= "</b><br><br>";
        echo "<table>"; 
        $body['Messages'][0]['HTMLPart'] .= "<table>";
            echo "<tr>";
            $body['Messages'][0]['HTMLPart'] .= "<tr>";
                echo "<th>first_name</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th><b>First Name</b></th>";
                echo "<th>last_name</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th><b>Last Name</b></th>";
                echo "<th>Phone Number</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th><b>Phone Number</b></th>";
                echo "<th>Address</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th><b>Address</b></th>";
                echo "<th>Email</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th><b>Email</b></th>";
                echo "<th>Birthday (mm-dd)</th>";
                $body['Messages'][0]['HTMLPart'] .= "<th><b>Birthday (mm-dd)</b></th>";
            echo "</tr>";
            $body['Messages'][0]['HTMLPart'] .= "</tr>";
            while($row = mysqli_fetch_array($result))
            {
                echo "<tr>";
                $body['Messages'][0]['HTMLPart'] .= "<tr>";
                for($i=0; $i<6; $i++)
                {
                    // if $i==2 (phone number) add dashes
                    if($i==2)
                    {
                        $phone_str = "";
                        $number = $row[$i];
                        $n = strlen($number);
                        for($j = $n-1; $j>=0; $j--) // handle 7, 9, 10, or 11 digits
                        {
                            $phone_str = $number[$j] . $phone_str;
                            if($j==$n-4 || $j==$n-7 || ($n>10 && $j==$n-10))
                            {
                                $phone_str = '-'. $phone_str;
                            }
                        }
                        echo "<td>$phone_str</td>";
                        $body['Messages'][0]['HTMLPart'] .= "<td>$phone_str</td>";
                    }
                    else
                    {
                        echo "<td>$row[$i]</td>";
                        $body['Messages'][0]['HTMLPart'] .= "<td>$row[$i]</td>";
                    }
                }
                echo "</tr>";
                $body['Messages'][0]['HTMLPart'] .= "</tr>";
            }
        echo "</table>";  
        $body['Messages'][0]['HTMLPart'] .= "</table>";  
        $body['Messages'][0]['HTMLPart'] .= "<br><br>";
        $body['Messages'][0]['HTMLPart'] .= "<br><p>This automated script was written by Mr. Gabriel Scott  :-) <br>Have a good day!</p><br>";
        echo "<p>";
        echo "<b>Email Body:</b><br><br>";
        echo send_email($SENDER_EMAIL, $RECIPIENT_EMAIL, $body);
        echo "</p>";

        // update database to show those employees as notified - no duplicate notifications
        // Note: this doesn't need to account for new employees. Doesn't matter if they are marked as notified
        $set_as_notified_sql = 
        "UPDATE bday_emails 
        SET notified = TRUE 
        WHERE (first_name, last_name) 
        IN (SELECT first_name, last_name 
            FROM employees where DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') 
            AND DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') 
            ORDER BY DATE_FORMAT(date_of_birth, '%m-%d') );";
                                                                                
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