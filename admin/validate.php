<?php
require_once __DIR__ . '/parts/header-register.php'; //Database settings
require_once "../inc/S3Connector.php";
require_once  "../inc/FileManager.php";
require_once  "../inc/Mailer.php";
require_once "../inc/DB/Episodes.php";
require_once "../inc/DB/Users.php";
require_once "../inc/DB/Roles.php";
require_once "../inc/DB/Playlists.php";
require_once "../.htconfig.php";

use DB\Playlists;
use DB\Users;
use DB\Episodes;
use Utils\S3Connector;
use Utils\FileManager;
use Utils\Mailer;

define("MIN_IMG_SIZE", 1000); //TODO: Put this in the database maybe

global $fileManager;
$fileManager = new FileManager($_SERVER["DOCUMENT_ROOT"] . "/" . 'uploads');

$dir = $_SERVER["DOCUMENT_ROOT"] . "/" . "themes/" . DEFAULT_THEME . "/emails" . "/";
$mailer = new Mailer($dir);
$success = false;

if (isset($_GET["token"])) {
    $token = $_GET["token"];
    //Validate token
    try {
        //code...
        Users::validate_email($token);
        $success = true;
    } catch (\Exception $th) {
        //throw $th;
    }
}
?>

    <div class="tab-content" role="tabpanel">
        <div class="tab-pane tabs-animation fade show active" id="tab-content-1" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <?php 
                            
                                if($success){
                                    echo("<h2>Your email has been validated</h2>");
                                }else{
                                    echo(" <h2>Your email has not been validated</h2>");
                                }
                            ?>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<!--<script type="text/javascript" src="./assets/scripts/main.js"></script>-->
<style>
    .list-group {
        width: 49%;
        display: inline-block;
        vertical-align: top;
        min-height: 200px;
        max-height: 208px;
        overflow-y: scroll;
        overflow-x: hidden;
        margin-bottom: 20px;
        padding: 5px;
        box-sizing: border-box;
    }

    .cenas {
        min-height: 100px;
    }
</style>
</body>


</html>