<?php
/* TODO:
- Check for .htconfig.php
- Field for DB name
- Field for DB server & port
- Field for DB user & password
- Test connection
- create .htconfig.php
- unlink setup.php (optional)
*/

$config_filename = ".htconfig.php";

if (isset($_POST["submit"])) { //only allow this via post method
    $db_name = $_REQUEST['db_name'];
    $user_name = $_REQUEST['db_user_name'];
    $user_pwd = $_REQUEST['db_user_pwd'];
    $srv_name = $_REQUEST['srv_name'];
    $srv_port = $_REQUEST['srv_port'];

    $base = dirname($_SERVER['SCRIPT_NAME']);
    $root_dir = $_SERVER["DOCUMENT_ROOT"] . $base;
    $http_host = $_SERVER['HTTP_HOST'] . $base;
 
    $data = "<?php
    require_once('.htconstants.php');
    define('DB_NAME','$db_name');
    define('DB_USER','$user_name');
    define('DB_PASS','$user_pwd');
    define('DB_SERVER','$srv_name');
    define('DB_PORT','$srv_port');
    define('DEFAULT_THEME','podcaster');

    define('AWS_REGION','us-east-2');
    define('AWS_KEY','AKIAJB57IHG6QWTFC2UA');
    define('AWS_SECRET','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');
    define('AWS_S3_BUCKET','podcasterisel');
    
    define('EMAIL_SERVER','smtp.gmail.com');
    define('EMAIL_USER','aliceisel45741@gmail.com');
    define('EMAIL_PASSWORD','hbnmfdyoeeahjovw');
    define('EMAIL_PORT',587);

    define('BASE', '$base');
    define('ROOT_DIR','$root_dir');
    define('HTTP_HOST', '$http_host');


    ?>";

    //Create the so-needed aditional tables for the CMS to use 
    $conn = new mysqli($srv_name, $user_name, $user_pwd);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($conn->query("CREATE SCHEMA `$db_name`;") === TRUE) {
    } else {
        echo "Error creating table: " . $conn->error;
        die();
    }

    if ($conn->query("USE `$db_name`;") === TRUE) {
    } else {
        echo "Error creating table: " . $conn->error;
        die();
    }

    $success = true;
    //Run commands line-by-line
    $handle = fopen(getcwd() . "/setup_files/db.sql", "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            if ($conn->query($line) === TRUE) {
            } else {
                $success = false;
                echo "Error creating table: " . $conn->error;
                break;
            }
        }

        fclose($handle);
    } else {
        // error opening the file.
    }

    //Create admin user
    $date_created = date("Y-m-d H:i:s");
    if ($_REQUEST['user_pwd'] == $_REQUEST['user_pwd_check']) {
        $admin_password = password_hash($_REQUEST['user_pwd'], PASSWORD_DEFAULT);
        $query = 'INSERT INTO `users`(`id`, `role_id`, `short_name`, `full_name`, `avatar_url`, `description`, `visibility`, `date_created`  , `created_by_user_id`, `pwd`, `email`) 
                VALUES(1, 1, "' . $_REQUEST['user_name'] . '", "Administrator", "admin.jpg", "", "", "' . $date_created . '", 1,"' . $admin_password . '","' . $_REQUEST['user_email'] . '");';
        if ($conn->query($query)) {
        }
    } else {
        echo ("Passwords don't match");
        die();
    }

    if ($success) file_put_contents(getcwd() . "/" . $config_filename, $data);

    $conn->close();


    //
}

$config_exists = file_exists($config_filename);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <?php if (!$config_exists) { ?>
        <main>
            <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
                <fieldset>
                    <legend>
                        Database Settings
                    </legend>
                    <p>This script handles the creation of the table</p>
                    <p>Database name: <input name="db_name" type="text" required minlength="1"> </p>
                    <p>User name: <input name="db_user_name" type="text" required minlength="1"> </p>
                    <p>User password: <input name="db_user_pwd" type="password"> </p>
                    <p>Server: <input name="srv_name" value="localhost" type="text" required minlength="1"> </p>
                    <p>Server port: <input name="srv_port" value="3307" type="number" required minlength="1"> </p>
                </fieldset>
                <!--<fieldset>
                    <legend>
                        Email settings
                    </legend>
                    <p>Host: <input name="email_host" value="smtp.gmail.com" type="text" required minlength="1"> </p>
                    <p>Port: <input name="email_port" value="587" type="number" required minlength="1"> </p>
                    <p>Email Sender: <input name="email_username" value="" type="email" required> </p>
                    <p>Email Password : <input name="email_pwd" type="password" required minlength="5"> </p>

                </fieldset>

                <fieldset>
                    <legend>Amazon S3</legend>
                    <p>We use Amazon S3 to store the audio files. You need to have a bucket already configured, as well the credetials for the account</p>
                    <p>Region: <input name="s3_region" value="admin" type="text" required minlength="1"> </p>
                    <p>Bucket Name: <input name="s3_bucket" value="" type="text" required minlength="1"> </p>
                    <p>Key: <input name="s3_key" value="" type="text" required> </p>
                    <p>Secret: <input name="s3_secret" type="password" required minlength="5"> </p>
                </fieldset>
        -->
                <fieldset>
                    <legend>
                        Administrator User settings
                    </legend>
                    <p>Username: <input name="user_name" value="admin" type="text" required minlength="1"> </p>
                    <p>email: <input name="user_email" value="" type="email" required minlength="1"> </p>
                    <p>Password: <input name="user_pwd" value="" type="password" required minlength="5"> </p>
                    <p>Repeat password : <input name="user_pwd_check" type="password" required minlength="5"> </p>

                </fieldset>

                <button name="submit" type="submit">Submit</button>
                <button type="reset">reset</button>
            </form>
        </main>
    <?php  } else { ?>
        <h2>Site has already been setup. You can proceed now to the index</h2>

        <script>
            window.setTimeout(function() {
                window.location.href = "//<?= $http_host ?>";
            }, 1 * 1000)
        </script>
    <?php  } ?>
</body>

</html>