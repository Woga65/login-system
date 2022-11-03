# login-system

A simple login system with user verification via email based on PHP, MySql and JavaScript

## Installation

- Have a look at the code to get/modify the data fields to your needs
- You might also want to take measures that the user chooses a strong password
- Create a database with phpmyadmin or the tools provided by your hosting company
- If you want a guest account, sign up with `Username: Guest / Password: 123456` otherwise add `.guest-button { display: none; }` to the file `css/login.css`
- Edit the file includes/dbh.inc.php (database credentials)
- Edit lines 134-137 in the file includes/signup.inc.php (verification email)
- Edit lines 48, 53, 61 in the file verify.php (path to your index.html)
- Upload the project files and folders to your PHP enabled web server
 

