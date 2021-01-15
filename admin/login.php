<?php session_start();
include("../inc/DB/Users.php");
include("../inc/FileManager.php");
include("../.htconfig.php");
include("../inc/Utils.php");

use Utils\FileManager;

global $fileManager;
$fileManager = new FileManager($_SERVER["DOCUMENT_ROOT"] . "/" . 'uploads');

use DB\Users;
use Utils\Utils;

$is_valid = true;
$success = true;
if (isset($_SESSION["logged_user"]) && $_SESSION["logged_user"] != "null") {
    header("Location: index.php");
}
if (isset($_POST["user"]) && isset($_POST["pwd"])) {

    $db_user = Users::get_user_by_shortname($_POST["user"]);
    
    if ($db_user != null && password_verify($_POST["pwd"], $db_user->pwd)) {
        $account_is_validated = Users::check_email($db_user->id);
        if (!$account_is_validated->validated && $db_user->role_id != "1") {
            $is_valid = false;
        } else {
            $_SESSION['logged_user'] = json_encode($db_user); //LOGIN;
            $_SESSION["avatar_url"] = $fileManager->get("users", $db_user->avatar_url);
            header("Location: index.php");
        }
    }else{
        $success = false;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Analytics Dashboard - This is an example dashboard created using build-in elements and components.</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="This is an example dashboard created using build-in elements and components.">
    <meta name="msapplication-tap-highlight" content="no">
    <!--
    =========================================================
    * ArchitectUI HTML Theme Dashboard - v1.0.0
    =========================================================
    * Product Page: https://dashboardpack.com
    * Copyright 2019 DashboardPack (https://dashboardpack.com)
    * Licensed under MIT (https://github.com/DashboardPack/architectui-html-theme-free/blob/master/LICENSE)
    =========================================================
    * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
    -->
    <link href="./main.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/es6-shim/0.35.3/es6-shim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/additional-methods.js"></script>


</head>

<body>
    <div class="app-main">
        <div class="app-main__outer" style="padding: 0px;">
            <div class="app-main__inner">
                <div class="app-page-title">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div class="page-title-icon">
                                <i class="pe-7s-drawer icon-gradient bg-happy-itmeo">
                                </i>
                            </div>
                            <div>Login

                            </div>
                        </div>

                    </div>
                </div>

                <div class="tab-content" role="tabpanel">
                    <div class="tab-pane tabs-animation fade show active" id="tab-content-1" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="main-card mb-3 card">
                                    <div class="card-body">
                                        <form id="login" method="post" action="login.php">
                                            <div class="form-row">
                                                <div class="col-md-12">
                                                    <div class="position-relative form-group">
                                                        <label for="exampleEmail11" class="">Username <span class="required">*</span> </label><input name="user" id="exampleEmail11" placeholder="with a placeholder" type="text" class="form-control" required></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="position-relative form-group"><label for="examplePassword11" class="">Password <span class="required">*</span> </label><input name="pwd" id="examplePassword11" placeholder="password placeholder" type="password" required minlength="5" class="form-control"></div>
                                                </div>
                                            </div>

                                            <?php
                                            if (!$is_valid) {
                                                echo ("<div class='error'>this email is not validated</div>");
                                            }

                                            if (!$success) {
                                                echo ("<div class='error'>username or password is wrong</div>");
                                            }
                                            ?>
                                            <button class="mt-2 btn btn-primary">Login</button>

                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
    </div>


    <script>


 document.addEventListener('DOMContentLoaded', function(e) {
        $("#login").validate({
            rules: {
                pwd: {
                    required: true,

                },
                username: {
                    required: true,
                },

            },
          
        });

    })
    </script>
</body>

</html>