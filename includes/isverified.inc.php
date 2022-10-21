<?php

session_start();

if (isset($_SESSION['user_id'])) {
    $jsonData = false;
    $json = file_get_contents('php://input');
    $params = json_decode($json);
    if ($params) {                                      //json data
        $jsonData = true;
    }

    $uid = $_SESSION['user_uid'];
    require 'dbh.inc.php';
    $sql ="SELECT * FROM kb_users WHERE user_uid=? LIMIT 1;";
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
        mysqli_stmt_bind_param($stmt, "s", $uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $name = $_SESSION['user_name'] = $row['user_name'];
            $email = $_SESSION['user_email'] = $row['user_email'];
            $verified = $_SESSION['user_verified'] = $row['user_verified'];
            $time = $_SESSION['user_timestamp'] = $row['user_timestamp'];
            $_SESSION['user_vkey'] = $row['user_vkey'];
            $verified = $verified == 1 ? true : false;
            if ($jsonData) {
                $jsonResult = [
                    "err" => "",
                    "ok" => true,
                    "data" => [ "loggedIn" => true, "userId" => $uid, "userName" => $name, "userEmail" => $email, "userVerified" => $verified, "userTimestamp" => $time, ],
                ];
                echo json_encode($jsonResult);
            } else {
                header("Location: ../index.php?verified=$verified");
            }
            exit();
        } else {                                              //should not happen at this point, just in case
            if ($jsonData) {
                echo '{ "err": "db error", "ok": false, "data": { "loggedIn": false } }';
            } else {
                header("Location: ../index.php?login=dberr");
            }
            exit();
        }
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}