<?php

require 'dbh.inc.php';

$langs = array();
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
    if (count($lang_parse[1])) {
        $langs = array_combine($lang_parse[1], $lang_parse[4]);
        foreach ($langs as $lang => $val) {
            $langs[$lang] = ($val === '') ? 1 : $val;
        }
        arsort($langs, SORT_NUMERIC);
    }
}

$langs = '"langs": ' . json_encode(array_keys($langs));
$state = '{ "err": "", "ok": true, "msg": "table exists", ' . $langs . ', "data": { "loggedIn": false } }';

$sql = "DESCRIBE `kb_users`;";

if (!mysqli_query($conn, $sql)) {
    $sql = "CREATE TABLE IF NOT EXISTS `kb_users` (
        `user_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_uid` tinytext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `user_email` tinytext NOT NULL,
        `user_pwd` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `user_vkey` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `user_verified` tinyint(1) NOT NULL DEFAULT 0,
        `user_timestamp` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
        `user_name` tinytext NOT NULL,
        PRIMARY KEY (`user_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    if (!mysqli_query($conn, $sql)) {
        $state ='{ "err": "sql error: ' . mysqli_error($conn) . '", "ok": false, ' . $langs . ', "data": { "loggedIn": false } }';
    } else {
        $state = '{ "err": "", "ok": true, "msg": "table created", ' . $langs . ', "data": { "loggedIn": false } }';
    }
}

mysqli_close($conn);

echo $state;
exit();