<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Page</title>
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/global-styles.css">
    <style>
        .main { background-color: var(--secondary-bgr); }
        .main a { color: var(--secondary-accent); }
        .main a:hover, main a:focus { text-decoration: underline; font-style: italic; }
        .main p span, .main h2 span { color: var(--primary-accent); }
    </style>    
</head>
<body class="main" tabindex=0 >

    <?php
        $vkey = isset($_GET['vkey']) ? $_GET['vkey'] : '';
        if (!empty($vkey)) {
            require 'includes/dbh.inc.php';
            $sql ="SELECT user_name, user_vkey, user_verified FROM kb_users WHERE user_verified = 0 AND user_vkey = ? LIMIT 1;";
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                echo 'sqlerr';
                exit();
            }
            else {
                mysqli_stmt_bind_param($stmt, "s", $vkey);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {     //exactly one user with the proper vkey exists
                    $row = mysqli_fetch_assoc($result);
                    $user = $row['user_name'];

                    $sql ="UPDATE kb_users SET user_verified = 1 WHERE user_vkey = ? LIMIT 1;";
                    $stmt = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        echo 'sqlerr';
                        exit();
                    }
                    else {
                        mysqli_stmt_bind_param($stmt, "s", $vkey);
                        mysqli_stmt_execute($stmt);
                        echo "<h2 align='center'>Congratulations <span>$user,</span> your account has been verified successfully!<br></h2>";
                        echo '<p align="center"><a href="https://wolfgang-siebert.de/projects/simple-login/index.php"><br>Please click here, go to the login page.</a></p>';
                    }
                }
                else {
                    echo '<p align="center">Invalid account or account already verified!</p>';
                    echo '<p align="center"><a href="https://wolfgang-siebert.de/projects/simple-login/index.php"><br>Please click here, to return to the start page.</a></p>';
                }
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        } 
        else {
            echo '<h2 align="center">Nothing to do here!</h2>';     // reject request if $vkey is empty
            echo '<p align="center"><a href="https://wolfgang-siebert.de/index.html"><br>Please click here, to check out my portfolio.</a></p>';
            exit(); 
        }
    ?>

</body>
</html>