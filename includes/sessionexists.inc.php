<?php

session_start();

if (isset($_SESSION['user_id'])) {
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