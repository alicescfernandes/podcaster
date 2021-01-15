<?php session_start();
require_once __DIR__ . '/parts/header-register.php'; //Database settings
require_once "../inc/S3Connector.php";
require_once  "../inc/FileManager.php";
require_once  "../inc/Mailer.php";
require_once "../inc/DB/Episodes.php";
require_once "../inc/DB/Users.php";
require_once "../inc/DB/Roles.php";
require_once "../inc/DB/Playlists.php";
require_once "../.htconfig.php";
require_once  '../vendor/autoload.php'; //Composer autoload
error_reporting(0);
use Gregwar\Captcha\CaptchaBuilder;
use DB\Playlists;
use DB\Users;
use DB\Episodes;
use Utils\S3Connector;
use Utils\FileManager;
use Utils\Mailer;


define("MIN_IMG_SIZE", 1000); //TODO: Put this in the database maybe

global $fileManager;
$fileManager = new FileManager(ROOT_DIR . "/" . 'uploads');

$dir = ROOT_DIR . "/" . "themes/" . DEFAULT_THEME . "/emails" . "/";
$mailer = new Mailer($dir);
$success = false;

$URI = $_SERVER['REQUEST_URI'];


if (isset($_GET["a"])) {
    $action = $_GET["a"];
    $page = $_GET["p"];
    switch ($action) {
        case "create":
            if (isset($_POST["short_name"])) {

                if ($_SESSION['phrase'] == $_POST["captcha"]) {
                    $dir = $_FILES["avatar"]["tmp_name"];

                    $data = new stdClass();
                    $data->role_id =  4;
                    $data->short_name = filter_input(INPUT_POST, "short_name", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->password =  filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->full_name = filter_input(INPUT_POST, "full_name", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
                    $data->description = filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->created_by_user_id = "";

                    //Get file extension
                    $ext = explode(".", $_FILES["avatar"]["name"]);
                    $ext = $ext[count($ext) - 1];
                    $time = time();
                    $data->file = "a_{$time}.{$ext}";

                    //Validate width/height
                    $size = getimagesize($dir);
                    $width = $size[0];
                    $height = $size[1];

                    if ($width == $height && $width >= 200) {
                        $fileManager->upload($dir, $data->file, "users/");
                        $_POST = array();
                    } else {
                        echo ("<script>alert('Cover image should be square and bigger than 200px')</script>");
                    }

                    $data->token = base64_encode(time());
                    Users::create_user($data);
                    //Get the validation token and send it by email
                    $cenas["password"] = $data->password;
                    $cenas["username"] = $data->short_name;
                    $cenas["url"] =  HTTP_HOST . "/admin/validate.php?token=" . $data->token;
                    $mailer->addRecepients($data->email, $data->full_name);
                    $mailer->loadTemplate("createAccount");
                    $mailer->setMessageParams($cenas);
                    $mailer->send("An Account has been created for you");

                    $success = true;
                    unset($_SESSION['phrase']);

                    //Clear the post fields

                    if (!empty($_POST)) {
                        //magic     
                        header("location:$URI");
                    }
                } else {
                    echo ("<script>alert('Captcha está errado')</script>");
                    unset($_SESSION['phrase']);
                }
            }


            break;
    }
}
if (!$success) {
    unset($_SESSION['phrase']);
    $builder = new CaptchaBuilder;
    $builder->build();
    $_SESSION['phrase'] = $builder->getPhrase();
}
?>

<?php if (isset($_GET["a"]) && $success) { ?>
    <div class="tab-content" role="tabpanel">
        <div class="tab-pane tabs-animation fade show active" id="tab-content-1" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <h2>An account has been created. please validate email</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php } else { ?>
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
                            <div>Register Account
                                <div class="page-title-subheading">todo
                                </div>
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
                                        <form enctype="multipart/form-data" id="registerForm" class="" method="POST" action="?a=create">
                                            <div class="form-row">
                                                <div class="col-md-6">
                                                    <div class="position-relative form-group">
                                                        <label for="username" class="">Short Name <span class="required">*</span> </label>
                                                        <input name="short_name" id="username" placeholder="with a placeholder" type="text" class="form-control" autocomplete="no" required>
                                                        <div class="error error-username"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="position-relative form-group"><label for="examplePassword11" class="">Password <span class="required">*</span> </label><input pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" name="password" id="examplePassword11" placeholder="password placeholder" type="password" required minlength="5" class="form-control">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="position-relative form-group"><label for="email" class="">Email <span class="required">*</span> </label><input name="email" required id="email" placeholder="email" type="email" minlength="5" class="form-control">
                                                <div class="error error-email"></div>
                                            </div>

                                            <div class="position-relative form-group"><label for="exampleAddress" class="">Full Name <span class="required">*</span> </label><input name="full_name" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></div>
                                            <div class="position-relative form-group"><label for="exampleAddress" class="">User description</label><textarea name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></textarea></div>

                                            <div class=" form-row mb-3">
                                                <label for="exampleAddress" class="">Avatar Image <span class="required">*</span> </label>
                                                <input class="form-control" type="file" accept="image/*" required name="avatar">
                                            </div>

                                            <div class=" form-row mb-3">
                                                <p style="width:100%" for="exampleAddress" class="">Captcha <span class="required">*</span> </p>
                                                <br />
                                                <img style="display:block" src="<?php echo $builder->inline(); ?>" />
                                                <input class="form-control" type="text" required name="captcha">
                                            </div>

                                            <button id="create_user" class="mt-2 btn btn-primary">Create user</button>

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
    </div>
<?php } ?>
<script>
    document.addEventListener('DOMContentLoaded', function(e) {
        $validator = $("#registerForm").validate({
            rules: {
                short_name: {
                    required: true,

                },
                password: {
                    required: true,
                    email: false
                },
                email: {
                    required: true,
                    email: true
                },
                full_name: {
                    required: true,
                },
                avatar: {
                    required: true,
                },
                captcha: {
                    required: true
                }
            },
            messages: {
                email: {
                    email: "Your email address must be in the format of name@domain.com"
                }
            }
        });


        $("#username").on("input", function() {
            /* Show errors on the form */

            $username = $(this)
            if ($username[0].checkValidity()) {
                $.get("//<?= HTTP_HOST ?>/api/username/" + $username.val()).then(function(res) {
                    if (res == true) {
                        $("#create_user").prop("disabled", true)
                        $(".error-username").text("Username already taken")
                        $("#username").addClass("error-username")
                        return;
                    } else {
                        $("#create_user").prop("disabled", false)
                        $(".error-username").text("")
                        $("#username").removeClass("error-username")

                    }
                })
            }
        })

        $("#email").on("input", function() {
            $email = $(this)
            if ($email[0].checkValidity()) {
                $.get("//<?= HTTP_HOST ?>/api/email/" + $email.val()).then(function(res) {
                    if (res == true) {
                        $("#create_user").prop("disabled", true)
                        $(".error-email").text("Email already taken")
                        $("#email").addClass("error-email")
                        return;
                    } else {
                        $("#create_user").prop("disabled", false)
                        $(".error-email").text("")
                        $("#email").removeClass("error-email")

                    }
                })
            }
        })
    })
</script>

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

    input.error-username {
        border: 1px solid red;
        color: red;
    }

    input.error-email {
        border: 1px solid red;
        color: red;
    }
</style>
</body>


</html>