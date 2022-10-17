<?php

//might be needed to restrict messages to a certain country
//$ip_addr = $_SERVER['REMOTE_ADDR'];
//$geoplugin = unserialize( file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip_addr) );
//$country = $ip_addr == '127.0.0.1' ? 'DE' : $geoplugin['geoplugin_countryCode'];

switch($_SERVER['REQUEST_METHOD']){
    case("OPTIONS"): //Allow preflighting to take place.
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: content-type");
        exit;
    case("POST"): //Send the email;
        header("Access-Control-Allow-Origin: *");

        $json = file_get_contents('php://input');
        $params = json_decode($json);

        if ($params) {  //json data
          $antispam = property_exists($params, 'bcc') ? $params->bcc : 'xyz';
          $email = $params->email;
          $name = $params->name;
          $message = $params->message;
        } else {        //form data as sent by <form action="mail.php" method="post">
          $antispam = isset($_POST['bcc']) ? $_POST['bcc'] : 'xyz';
          $email = isset($_POST['email']) ? $_POST['email'] : '';
          $name = isset($_POST['name']) ? $_POST['name'] : '';
          $message = isset($_POST['message']) ? $_POST['message'] : '';
        }

//Your E-mail address goes here
        $recipient = 'kontakt@wolfgang-siebert.de';
        $subject = "new message from: $name";
        $headers = "From: $name <$email>";

//E-mail is only sent if field bcc exists and is empty
        if ($antispam == '') {      // && $country == 'DE') {
          echo '<br><i><h3 align="center">Thank you. Your message has been successfully sent.</h3></i><br>';
          mail($recipient, $subject, "$name\r\n$email\r\n \r\n$message", $headers);
        } else {
          echo '<br><i><h3 align="center">Your message has been sent.</h3></i><br>';
        }
//Your website url goes here
        echo '<p align="center"><a href="https://wolfgang-siebert.de/index.html"><br>Please click here, to return to the start page.</a></p>';
        break;
    default: //Reject any non POST or OPTIONS requests.
        header("Allow: POST", true, 405);
        exit;
}
