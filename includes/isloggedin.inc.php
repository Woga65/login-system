<?php

session_start();

if (isset($_SESSION['user_id'])) {

    require 'dbh.inc.php';
    $user_id = $_SESSION['user_id'];
    $sql ="SELECT * FROM kb_users WHERE user_id=? LIMIT 1;";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        if ($jsonData) {
            echo '{ "err": "sql error", "ok": false, "data": { "loggedIn": false } }';
        } else {
            echo 'SQL ERROR: Something went horribly wrong!';
        }
        exit();
    } 
    else {
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['user_uid'] = $row['user_uid'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['user_email'] = $row['user_email'];
            $_SESSION['user_verified'] = $row['user_verified'];
            $_SESSION['user_timestamp'] = $row['user_timestamp'];
            $_SESSION['user_vkey'] = $row['user_vkey'];
        } else {                                              //should not happen at this point, just in case
            echo '{ "err": "db error", "ok": false, "data": { "loggedIn": false } }';
            exit();
        }
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    $verified = isset($_SESSION['user_verified']) ? $_SESSION['user_verified'] : 0;
    $login_state = [
        "err" => "",
        "ok" => true,
        "data" => [
            "loggedIn" => true,
            "userId" => isset($_SESSION['user_uid']) ? $_SESSION['user_uid'] : "",
            "userName" => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "",
            "userEmail" => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : "",
            "userVerified" => $verified == 1 ? true : false,
            "userTimestamp" => isset($_SESSION['user_timestamp']) ? $_SESSION['user_timestamp'] : "",
        ],
    ];
    echo json_encode($login_state);        
} else {
    echo '{"err":"","ok":true,"data":{"loggedIn":false}}';
}

