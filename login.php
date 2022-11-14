<?php
if(!isset($_SESSION)) session_start();
$DEBUG = false;
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
$message = "";
//if(!empty($_POST['send'])) {
if(isset($_POST['send']) && isset($_POST['username']) && isset($_POST['password']))
{
    $isSuccess = 0;
    $conn = connectToDB();
    if ($conn== null) {
        die("Connection failed:");
      }
    $input_username = $_POST['username'];
    if($DEBUG) echo "<br><strong>input_username -   $input_username</strong><br>";
    $input_pw = $_POST['password'];

    if($DEBUG)
    {
        echo "<br><strong>USE THIS TO HASH A PASSWORD AND THEN STORE IT IN THE DB MANUALLY: </strong><br>";
        $input_pw_hash = password_hash($input_pw, PASSWORD_DEFAULT);
        echo "<br><strong>$input_pw_hash</strong<br>";
    }

    // prepare stmt to protect against sql injection
    $stmt = $conn->prepare('SELECT password_hash FROM users WHERE display_name= ?');
    $stmt->bind_param('s', $input_username);
    $stmt->execute();
    $results = $stmt->get_result();

    $db_hashed_pw = "";
    if($row = mysqli_fetch_array($results))
    {
        if($row[0])
        {
            $db_hashed_pw = $row[0];
        }
    }
    if($DEBUG) echo "<br><strong>$ db_hashed_pw: $db_hashed_pw</strong><br>";
    if(password_verify($input_pw, $db_hashed_pw))
    {
        $isSuccess = 1;
        $_SESSION['username'] = $input_username;
    }

    $conn->close();
    if ($isSuccess == 0) {
        if(!$DEBUG) header('Location: ./login.php?invalid_login');
        
    } else {
        //echo "<br><strong>User Authenticated!</strong><br>";
        //sleep(2);
        if(!$DEBUG) header("Location: ./index.php");
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Authorized Users Only</title>
        <link href="login_style.css" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="/asset/img/favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
    
        <br><br>
        <form id="frmUser" name="user-form" method="post" action="">
            <h1 class="table-header">Authorized Users Only</h1>
            
            <div class="message" id="successmsg"><?php if(isset($_GET['invalid_login'])){ echo "<p>Invalid Username or Password!</p>";} else echo "<br>"; ?></div>
            <table class="tblLogin">
                <tbody class="body">
                    <tr class="table-row">
                        <td id="usr" class="table-header"><label class="table-header">Username</label></td>
                        <!-- <label> </label>    -->
                        <td><input class="login-input" type="text" name="username"class="full-width" required></input></td>
                    </tr>
                    <tr class="table-row">
                        <td id="pw" class="table-header"><label class="table-header">Password</label></td>
                        <td><input class="login-input" type="password" name="password" class="full-width" required></input></td>
                    </tr>
                    <tr class="table-row">                                                                          <!-- value="Submit" -->
                        <td id="submit-btn" colspan="2"><input id="post-btn" class="btnSubmit" type="submit" name="send" value="Submit"></input></td>
                    </tr>
                </tbody>
            </table>        <!-- TODO: TEST THIS POST TO authenticate.php -->
            <br><br>
            <div>
                <h3 class="table-subheader">Demo Mode: <br>username: guest <br>password: demo </h3>
            </div>
        </form> 
        <!--
        <div class="demo">
            <table class="demo-msg-tbl">
                <tr class="table-row">
                    <td colspan="2">Demo Mode</td>
                </tr>
                <tr class="table-row">
                    <td >Username:</td>
                    <td >guest</td>
                </tr>
                <tr class="table-row">
                    <td >Password:</td>
                    <td >demo</td>
                </tr>
            </table>
        </div>   -->
    </body>
</html>

