<?php

session_start();

if (isset($_SESSION['user_id'])) {
    echo '{"ok":true,"data":{"loggedIn":true}}';
} else {
    echo '{"ok":true,"data":{"loggedIn":false}}';
}
