<!-- TODO: Location refresh on edit/delete/crate -->
<!-- TODO: roles integratiion & roles manager -->
<?php

use Utils\S3Connector;
use DB\Users;
use DB\Playlists;
use Utils\FileManager;

global $s3Connector;
global $users;
global $playlists;


$fileManager = new FileManager(ROOT_DIR . "/" . 'uploads');

//Process Form Data                
if (isset($_GET["p"]) && isset($_GET["a"])) {
    $action = $_GET["a"];
    $page = $_GET["p"];
    switch ($action) {
        case "edit":
            $user = Users::get_by_id($logged_user->id);

            $data = new stdClass();
            $data->short_name =  $user->short_name; //todo: query whithout short_name;
            $data->full_name = $_POST["full_name"];
            $data->email = $_POST["email"];
            $data->description = $_POST["description"];
            $data->role_id = $logged_user->role_id;
            $data->id = $user->id;


            if (isset($_FILES["avatar"]) && $_FILES["avatar"]["size"] > 0) {
                $dir = $_FILES["avatar"]["tmp_name"];

                //Validate width/height
                $size = getimagesize($dir);
                $width = $size[0];
                $height = $size[1];

                if ($width == $height && $width >= 200) {
                    $fileManager->upload($dir, $user->avatar_url, "users/"); //TODO: Check if PNG with JPEG extension works
                } else {
                    echo ("<script>alert('Cover image should be square and bigger than 200px')</script>");
                }
            }

            if (!empty($_POST["password"])) {
                if ($_POST["password"] == $_POST["confirm_password"]) {
                    $data->password = $_POST["password"];
                    Users::update_password($data); //TODO: This should be one single query
                } else {
                    echo ("<script>alert('passwords dont match')</script>");
                }
            }
            unset($_POST);
            $_POST = array();
            Users::update_user($data);

            //Update session user
            $db_user = Users::get_by_id($logged_user->id);
            $_SESSION['logged_user'] = json_encode($db_user);
            $_SESSION["avatar_url"] = $fileManager->get("users", $db_user->avatar_url);
            echo ("<script>window.location.href = '/admin/?p=profile' </script>"); //TODO: Handle better refreshes

            break;
    }
}

?>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-drawer icon-gradient bg-happy-itmeo">
                </i>
            </div>
            <div>Manage Profile
                <div class="page-title-subheading">todo
                </div>
            </div>
        </div>

    </div>
</div>
<div class="tab-content" role="tabpanel">
    <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">

        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <form enctype="multipart/form-data" class="" method="POST" action="?p=profile&a=edit">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="position-relative form-group">
                                        <label for="examplePassword11" class="">Password</label>
                                        <input name="password" id="examplePassword11" placeholder="password placeholder"  minlength="5" type="password" class="form-control"></div>
                                    <p>Leave password empty if you dont want to change the password</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative form-group"><label for="examplePassword11" class="">Confirm Password</label><input name="confirm_password" id="examplePassword11" placeholder="password placeholder" type="password" minlength="5" class="form-control"></div>
                                </div>
                            </div>
                            
                            <div class="position-relative form-group"><label for="exampleAddress" class="">Full Name</label><input value="<?= $logged_user->full_name ?>" name="full_name" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></div>
                            <div class="position-relative form-group"><label for="exampleAddress" class="">User description</label><textarea name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"><?= $logged_user->description ?></textarea></div>

                            <div class=" form-row mb-3">
                                <label for="exampleAddress" class="">Avatar Image</label>
                                <input class="form-control" type="file" name="avatar">
                            </div>

                            <button class="mt-2 btn btn-primary">Update Profile</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>