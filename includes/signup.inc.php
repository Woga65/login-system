<?php

switch($_SERVER['REQUEST_METHOD']) {
    case("OPTIONS"): //Allow preflighting to take place.                    //header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: content-type");
        exit;

    case("POST"): //Perform sign up                                         //header("Access-Control-Allow-Origin: *");

        $jsonData = false;
        $json = file_get_contents('php://input');
        $params = json_decode($json);
    
        if ($params) {                                      //json data
            $jsonData = true;
            $antispam = property_exists($params, 'bcc') ? $params->bcc : 'xyz';
            $uid = $params->uid;
            $name = $params->name;
            $email = $params->email;
            $pwd = $params->pwd;
            $pwdRepeat = $params->pwdrepeat;
        } else if (isset($_POST['signup-submit'])) {        //form data as sent by <form action="signup.php" method="post">
            $antispam = isset($_POST['bcc']) ? $_POST['bcc'] : 'xyz';
            $uid = $_POST['uid'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $pwd = $_POST['pwd'];
            $pwdRepeat = $_POST['pwdrepeat'];
        } else {
            header("Location: ../index.php#signup");        // reject form data sent from elsewhere
            exit(); 
        } 

        if (!empty($antispam)) {
            //echo 'empty';
            if ($jsonData) { 
                echo '{ "err": "empty data", "ok": false }';
            } else { 
                header("Location: ../index.php?signup=empty");
            }
            exit();
        }
        else if (empty($uid) || empty($name) || empty($email) || empty($pwd) || empty($pwdRepeat)) {
            //echo 'empty';
            if ($jsonData) {
                echo '{ "err": "required field(s) empty", "ok": false }';
            } else {
                header("Location: ../index.php?signup=empty");
            }
            exit();
        }
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z0-9]*$/", $uid)) {
            //echo 'data';
            if ($jsonData) {
                echo '{ "err": "invalid data", "ok": false, "fields": ["uid", "email"] }';
            } else { 
                header("Location: ../index.php?signup=invalid");
            }
            exit();
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //echo 'email';
            if ($jsonData) {
                echo '{ "err": "invalid email address", "ok": false, "fields": ["email"] }';
            } else { 
                header("Location: ../index.php?signup=invalidemail");
            }
            exit();
        }
        else if(!preg_match("/^[a-zA-Z0-9]*$/", $uid)) {
            //echo 'uid';
            if ($jsonData) {
                echo '{ "err": "invalid user id", "ok": false, "fields": ["uid"] }';
            } else {
                header("Location: ../index.php?signup=invaliduid");
            }
            exit();
        }
        else if ($pwd !== $pwdRepeat) {
            //echo 'nomatch';
            if ($jsonData) {
                echo '{ "err": "Passwords do not match", "ok": false, "fields": ["pwd", "pwdrepeat"] }';
            } else {
                header("Location: ../index.php?signup=nomatch");
            }
            exit();
        }
        else {
            require 'dbh.inc.php';

            $sql ="SELECT user_uid FROM kb_users WHERE user_uid=? OR user_email=?;";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                if ($jsonData) {
                    echo '{ "err": "sql error", "ok": false }';
                } else {
                    echo 'SQL ERROR: Something went horribly wrong!';
                }
                exit();
            }
            else {
                mysqli_stmt_bind_param($stmt, "ss", $uid, $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $result = mysqli_stmt_num_rows($stmt);
                if ($result > 0) {
                    //echo 'exists';
                    if ($jsonData) {
                        echo '{ "err": "User exists", "ok": false, "fields": ["uid", "email"] }';
                    } else {
                        header("Location: ../index.php?signup=exists");
                    }
                    exit();
                }
                else {
                    $sql = "INSERT INTO kb_users (user_uid, user_name, user_email, user_pwd, user_vkey, user_verified) VALUES (?, ?, ?, ?, ?, ?);";
                    $stmt = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        if ($jsonData) {
                            echo '{ "err:" "sql error", "ok": false }';
                        } else {
                            echo 'SQL ERROR: Something went terribly wrong!';
                        }
                        exit();
                    }
                    else {  //success
                        $hashedpwd = password_hash($pwd, PASSWORD_DEFAULT);
                        $vkey = password_hash(date('Y-m-d H:i:s') . $uid, PASSWORD_DEFAULT);
                        $verified = 0;
                        mysqli_stmt_bind_param($stmt, "sssssi", $uid, $name, $email, $hashedpwd, $vkey, $verified);
                        mysqli_stmt_execute($stmt);
                        $recipient = $email;
                        $subject = "your registration: Kanban Project";
                        $headers = "From: Kanban <noreply@ws-kanban.de>";
                        $message = "Hi $name,\r\n \r\nto complete the registration process, please follow the link below: \r\n \r\n";
                        $message = $message . "https://wolfgang-siebert.de/projects/simple-login/verify.php?vkey=$vkey";                           
                        mail($recipient, $subject, $message, $headers);
                        //echo 'success';
                        if ($jsonData) {
                            echo '{ "err": "", "ok": true }';
                        } else {
                            header("Location: ../index.php?signup=success");
                        }
                        exit();
                    }
                }
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
        break;

    default: //Reject any non POST or OPTIONS requests.
        header("Allow: POST", true, 405);
        exit;
}