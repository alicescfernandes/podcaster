<!-- TODO: Location refresh on edit/delete/crate -->
<!-- TODO: roles integratiion & roles manager -->

<?php

use DB\Users;
use DB\Roles;
use Utils\FileManager;
use Utils\Mailer;
use Gregwar\Captcha\CaptchaBuilder;


global $users;
global $fileManager;
global $roles;
$dir = ROOT_DIR . "/" . "themes/" . DEFAULT_THEME . "/emails" . "/";

$fileManager = new FileManager(ROOT_DIR . "/" . 'uploads');
$mailer = new Mailer($dir);
$success = false;
if (isset($_GET["p"]) && isset($_GET["a"])) {
    $action = $_GET["a"];
    $page = $_GET["p"];
    switch ($action) {
        case "create":
            if ($_SESSION['phrase'] == $_POST["captcha"]) {
                if (isset($_POST["short_name"])) {
                    $dir = $_FILES["avatar"]["tmp_name"];

                    $user =  json_decode($_SESSION["logged_user"]);
                    $data = new stdClass();
                    $data->role_id =  filter_input(INPUT_POST, "role_id", FILTER_SANITIZE_NUMBER_INT);

                    $data->short_name = filter_input(INPUT_POST, "short_name", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->password =  filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->full_name = filter_input(INPUT_POST, "full_name", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
                    $data->description = filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);
                    $data->created_by_user_id = (int) $user->id;

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
                    unset($_SESSION['phrase']);
                }
            } else {
                echo ("<script>alert('Captcha está errado')</script>");
                unset($_SESSION['phrase']);
            }


            break;
        case "delete":
            $uid = $_GET["id"];
            $user = Users::get_by_id($uid);
            Users::delete($uid);
            $mailer->addRecepients($user->email, $user->full_name);
            $mailer->loadTemplate("deleteAccount");
            $mailer->send("Your Account has been deleted");

            break;
        case "update":
            echo '<script>
                    window.onload = function(){
                        $("#exampleModal").modal("show")
                    }
                </script>';
            break;
    }
}

if(!$success){
    unset($_SESSION['phrase']);
    unset($_SESSION['captcha_img']);
    $builder = new CaptchaBuilder();
    
    $builder->build();
    $_SESSION['phrase'] = $builder->getPhrase();
    $_SESSION['captcha_img'] = $builder->inline();
}

$users = Users::get_users_with_roles();
$roles = Roles::get_all(false);
?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-drawer icon-gradient bg-happy-itmeo">
                </i>
            </div>
            <div>Manage users
                <div class="page-title-subheading">todo
                </div>
            </div>
        </div>

    </div>
</div>
<ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav">
    <?php if ($role->can_edit_users == "1") { ?>
        <li class="nav-item">
            <a role="tab" class="nav-link active" id="tab-1" data-toggle="tab" href="#tab-content-0">
                <span>Edit User</span>
            </a>
        </li>
    <?php } ?>
    <?php if ($role->can_create_users == "1") { ?>
        <li class="nav-item">
            <a role="tab" class="nav-link" id="tab-0" data-toggle="tab" href="#tab-content-1">
                <span>Create User</span>
            </a>
        </li>
    <?php } ?>
</ul>



<div class="tab-content" role="tabpanel">
    <?php if ($role->can_create_users == "1") { ?>

        <div class="tab-pane tabs-animation fade " id="tab-content-1" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <form enctype="multipart/form-data" id="registerForm" class="" method="POST" action="?p=users&a=create">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <div class="position-relative form-group">
                                            <label for="username" class="">Short Name <span class="required">*</span> </label><input name="short_name" id="username" placeholder="with a placeholder" type="text" class="form-control" required>
                                            <div class="error error-username"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="position-relative form-group"><label for="examplePassword11" class="">Password <span class="required">*</span> </label><input title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" name="password" id="examplePassword11" placeholder="password placeholder" type="password" required minlength="5" class="form-control"></div>
                                    </div>
                                </div>

                                <div class="position-relative form-group"><label for="email" class="">Email <span class="required">*</span> </label><input name="email" required id="email" placeholder="email" type="email" minlength="5" class="form-control">
                                <div class="error error-email"></div>
                            </div>

                                <div class="position-relative form-group"><label for="exampleAddress" class="">Full Name <span class="required">*</span> </label><input name="full_name" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></div>
                                <div class="position-relative form-group"><label for="exampleAddress" class="">User description <span class="required">*</span> </label><textarea name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></textarea></div>

                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Avatar Image <span class="required">*</span> </label>
                                    <input class="form-control" type="file" accept="image/*" required name="avatar">
                                </div>

                                <div class="position-relative form-group"><label for="exampleAddress" class="">Role <span class="required">*</span> </label>
                                    <select name="role_id" class="mb-2 form-control">
                                        <?php foreach ($roles as $r) { ?>
                                            <?php if (in_array($r->machine_name, $permission_roles)) { ?>
                                                <option value="<?= $r->id ?>"><?= $r->name ?></option>
                                            <?php } ?>
                                        <?php } ?>

                                    </select>

                                </div>



                                <div class=" form-row mb-3">
                                    <p style="width:100%" for="exampleAddress" class="">Captcha <span class="required">*</span> </p>
                                    <br />
                                    <img style="display:block" src="<?php echo $_SESSION["captcha_img"]; ?>" />
                                    <input class="form-control" type="text" required name="captcha">
                                </div>

                                <button id="create_user" class="mt-2 btn btn-primary">Create user</button>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>

    <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <h5 class="card-title">Table responsive</h5>
                        <div class="table-responsive">
                            <table class="mb-0 table">
                                <thead>
                                    <tr>

                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($users as $u) {
                                        if ($u->id != $user->id) {
                                    ?>
                                            <tr>
                                                <td><?= $u->full_name ?></td>
                                                <td>

                                                    <?php
                                                    if ($role->can_edit_users == "1" && in_array($u->role_name, $permission_roles)) {

                                                    ?>

                                                        <a href="?p=users&a=delete&id=<?= $u->id ?>" type="button" class="btn mr-2 mb-2 btn-danger">
                                                            Delete User
                                                        </a>
                                                        <a href="?p=users&a=update&id=<?= $u->id ?>" type="button" class="btn mr-2 mb-2 btn-primary">
                                                            Edit User
                                                        </a>
                                                    <?php } ?>
                                                </td>

                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function(e) {
        $("#registerForm").validate({
            rules: {
                short_name: {
                    required: true,

                },
                password: {
                    required: true,
                    email: false,
                    //pattern: "(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}",
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
<style>

input.error-username {
        border: 1px solid red;
        color: red;
    }

    input.error-email {
        border: 1px solid red;
        color: red;
    }
</style>