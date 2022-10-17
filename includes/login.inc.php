<?php

switch($_SERVER['REQUEST_METHOD']) {
    case("OPTIONS"):                                        //Allow preflighting to take place.
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: content-type");
        exit;

    case("POST"):                                           //Perform sign up
        $jsonData = false;
        $json = file_get_contents('php://input');
        $params = json_decode($json);
    
        if ($params) {                                      //json data
            $jsonData = true;
            $antispam = property_exists($params, 'bcc') ? $params->bcc : 'xyz';
            $uid = $params->uid;
            $pwd = $params->pwd;
        } else if (isset($_POST['login-submit'])) {         //form data as sent by <form action="signup.php" method="post">
            $antispam = isset($_POST['bcc']) ? $_POST['bcc'] : 'xyz';
            $uid = $_POST['uid'];
            $pwd = $_POST['pwd'];
        } else {
            header("Location: ../index.php#login");         //reject form data sent from elsewhere
            exit(); 
        }

        if (!empty($antispam)) {                            //hidden bcc field not empty
            if ($jsonData) { 
                echo '{ "err": "empty" }';
            } else { 
                header("Location: ../index.php?login=empty");
            }
            exit();
        } 
        else if (empty($uid)) {              //empty field(s)!          
            if ($jsonData) { 
                echo '{ "err": "empty", "fields": ["uid"] }';
            } else { 
                header("Location: ../index.php?login=empty");
            }
            exit();
        }
        else if (empty($pwd)) {              //empty field(s)!          
            if ($jsonData) { 
                echo '{ "err": "empty", "fields": ["pwd"] }';
            } else { 
                header("Location: ../index.php?login=empty");
            }
            exit();
        }
        else if (!filter_var($uid, FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z0-9]*$/", $uid)) {   //invalid data!
            if ($jsonData) {
                echo '{ "err": "uid", "fields": ["uid"] }';
            } else {
                header("Location: ../index.php?login=invaliduid");
            }
            exit();
        }
        else {                                              //try login
            require 'dbh.inc.php';
            $sql ="SELECT * FROM kb_users WHERE user_uid=? OR user_email=?;";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                if ($jsonData) {
                    echo '{ "err": "sql error" }';
                } else {
                    echo 'SQL ERROR: Something went horribly wrong!';
                }
                exit();
            }
            else {
                mysqli_stmt_bind_param($stmt, "ss", $uid, $uid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $pwdCheck = password_verify($pwd, $row['user_pwd']);
                    if ($pwdCheck == false) {                           //wrong password!
                        if ($jsonData) {
                            echo '{ "err": "login incorrect", "fields": ["pwd"] }';
                        } else {
                            header("Location: ../index.php?login=nomatch");
                        }
                        exit();
                    }
                    else if ($pwdCheck == true) {                       //login successful!
                        session_start();
                        $_SESSION['user_id'] = $row['user_id'];
                        $uid = $_SESSION['user_uid'] = $row['user_uid'];
                        $name = $_SESSION['user_name'] = $row['user_name'];
                        $email = $_SESSION['user_email'] = $row['user_email'];
                        $_SESSION['user_vkey'] = $row['user_vkey'];
                        $verified = $_SESSION['user_verified'] = $row['user_verified'];
                        $time = $_SESSION['user_timestamp'] = $row['user_timestamp'];
                        $verified = $verified == 1 ? true : false;
                        if ($jsonData) {
                            $jsonResult = [
                                "err" => "",
                                "ok" => true,
                                "data" => [ "loggedIn" => true, "userId" => $uid, "userName" => $name, "userEmail" => $email, "userVerified" => $verified, "userTimestamp" => $time, ],
                            ];
                            echo json_encode($jsonResult);
                        } else {
                            header("Location: ../index.php?login=success");
                        }
                        exit();
                    }
                    else {                                              //should not happen at this point, just in case
                        if ($jsonData) {
                            echo '{ "err": "login incorrect" }';
                        } else {
                            header("Location: ../index.php?login=nomatch");
                        }
                        exit();
                    }
                }
                else {                                                  //no such user!
                    if ($jsonData) {
                        echo '{ "err": "login incorrect", "fields": ["uid"] }';
                    } else {
                        header("Location: ../index.php?login=nouser");
                    }
                    exit();
                }
            }
            mysqli_stmt_close($stmt);   // close connection
            mysqli_close($conn);
        }
        break;

    default:    //Reject any non POST or OPTIONS requests.
        header("Allow: POST", true, 405);
        exit;
} 