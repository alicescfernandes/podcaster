<!-- TODO: Location refresh on edit/delete/crate -->
<!-- TODO: roles integratiion & roles manager -->
<?php

use Utils\S3Connector;
use DB\Users;
use DB\Playlists;
use DB\Episodes;
use Utils\FileManager;

global $s3Connector;
global $playlists;
global $episodes;
global $users;
$fileManager = new FileManager(ROOT_DIR . "/" . 'uploads');


$dir = ROOT_DIR."/"."themes/".DEFAULT_THEME."/emails" . "/";

//Process Form Data                
if (isset($_GET["p"]) && isset($_GET["a"])) {
    $action = $_GET["a"];
    $page = $_GET["p"];
    switch ($action) {
        case "create":
            if (isset($_POST["short_title"])) {
                $user = json_decode($_SESSION["logged_user"]); //Use global user variable
                $dir = $_FILES["cover_image"]["tmp_name"];
                $folder = filter_var($_REQUEST["folder"],FILTER_SANITIZE_SPECIAL_CHARS) ;

                $obj = new stdClass();
                $obj->episodes = ""; //episode ids. TODO: Starts empty
                $obj->created_by_user_id = (int) $user->id;
                $obj->updated_by_user_id = (int) $user->id;
                $obj->folder = $folder;
                $obj->file  = "";
                $obj->mdata  = '';
                $obj->image = $_FILES["cover_image"]["name"];
           
                $obj->type = filter_var($_REQUEST["type"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->tags = filter_var($_REQUEST["tags"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->description = filter_var($_REQUEST["description"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->long_title = filter_var($_REQUEST["long_title"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->playlist_id = filter_var($_REQUEST["playlist_id"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->short_title = filter_var($_REQUEST["short_title"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->guest_user_id = filter_var($_REQUEST["guest"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->visibility = filter_var($_REQUEST["visibility"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->duration = filter_var($_REQUEST["duration"],FILTER_SANITIZE_NUMBER_INT);

                //Validate width/height
                $size = getimagesize($dir);
                $width = $size[0];
                $height = $size[1];

                //Add tags for this playlist on a different table

                if(isset($_FILES["markers"]) && $_FILES["markers"]["size"] > 0){
                    $obj->mdata = base64_encode(file_get_contents($_FILES["markers"]["tmp_name"]));
                }

                if ($width == $height && $width >= MIN_IMG_SIZE) {
                    Episodes::create($obj);
                    $fileManager->upload($dir, $obj->image, "episodes/{$folder}/");
                    $_POST = array();
                } else {
                    echo ("<script>alert('Cover image should be square and bigger than 1024px')</script>");
                }
            }
            break;
        case "delete":
            $id = (int) $_GET["id"];
            $playlist = Episodes::get_by_id($id);

            if ($playlist != null) {
                Episodes::delete($id);
                $fileManager->unlink("episodes/{$playlist->folder}", $playlist->image_1x1);
            }
            echo ("<script>window.location = '?p=episodes';</script>"); //TODO: Handle this redirects with more care

            break;
        case "update":
            echo '<script>
                    window.onload = function(){
                        $("#edit-episode").modal("show")
                    }
                </script>';
            break;
    }
}

//Get fresh data
$users = Users::get_all(false);
$playlists = Playlists::get_all(true);
$episodes = Episodes::get_all(true);
?>

<script>
    window.onmessage = function(e) {
        $("#id_" + e.data.folder).hide();
    }

    function open_window(folder) {
        var w = window.open("//<?= HTTP_HOST ?>/admin/pages/audio_upload_handler.php?folder=" + folder, "_blank", "width=500,height=500");
    }
</script>

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-drawer icon-gradient bg-happy-itmeo">
                </i>
            </div>
            <div>Manage Episodes
                <div class="page-title-subheading">todo
                </div>
            </div>
        </div>

    </div>
</div>
<ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav">
    <?php if ($role->can_list_episodes == "1") { ?>
        <li class="nav-item">
            <a role="tab" class="nav-link active" id="tab-1" data-toggle="tab" href="#tab-content-0">
                <span>Edit Episodes</span>
            </a>
        </li>
    <?php } ?>


    <?php if ($role->can_create_episodes == "1") { ?>
        <li class="nav-item">
            <a role="tab" class="nav-link" id="tab-0" data-toggle="tab" href="#tab-content-1">
                <span>Create Episode</span>
            </a>
        </li>
    <?php } ?>


</ul>
<div class="tab-content" role="tabpanel">
    <?php if ($role->can_create_episodes == "1") { ?>
        <div class="tab-pane tabs-animation fade " id="tab-content-1" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <form id="form" enctype="multipart/form-data" class="" method="POST" action="?p=episodes&a=create">
                                <?php
                                $folder = "e_" . time();
                                ?>
                                <input type="hidden" name="folder" value="<?= $folder ?>">
                                <div class="form-row mb-3">
                                    <label for="title" class="">Title <span class="required">*</span></label>
                                    <input required name="long_title" id="title" placeholder="Long Title" minlength="5" type="text" class="form-control">
                                </div>
                                <div class="form-row mb-3">
                                    <label for="short_title" class="">Short Title <span class="required">*</span></label>
                                    <input required name="short_title"  minlength="5" id="short_title" placeholder="Short Title" type="text" class="form-control">
                                </div>
                                <div class="form-row mb-3">
                                    <label for="exampleAddress" class="">Description <span class="required">*</span></label>
                                    <textarea required required name="description"  minlength="5" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></textarea>
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Cover Image <span class="required">*</span></label>
                                    <input class="form-control" type="file" accept="image/*" required name="cover_image">
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" cepisodeslass="">Audio File</label><br>
                                    <!--<input class="form-control" type="file" required name="file">-->
                                    <br/>
                                    <p style="width: 100%;display: block;">Audio file can only be uploaded after the episode is created</p>
                                    <button disabled id="file_upload" type="button" onclick="open_window('<?= $folder ?>')">Upload file</button>
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Tags <span class="required">*</span></label>
                                    <input required name="tags" type="text" class="form-control" placeholder="tag1,tag2...    ">
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="" >Duration (in minutes) <span class="required">*</span> </label>
                                    <input required name="duration" type="number" class="form-control" placeholder="30">
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Visibility <span class="required">*</span> </label>
                                    <select name="visibility" required class="mb-2 form-control">
                                        <option value="public">Public</option>
                                        <option value="invisible">Invisible</option>
                                    </select>
                                </div>
                                
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Playlist Type <span class="required">*</span></label>
                                    <select name="type" required class="mb-2 form-control">
                                        <option value="podcast">Podcast</option>
                                        <option value="mix">Mix</option>
                                    </select>
                                </div>
                                <div class="form-row mb-3">
                                    <label for="exampleAddress" class="">Markers</label>
                                    <input class="form-control" type="file"  accept="application/json" name="markers">
                                    <p>Upload a JSON file with markers</p>
                                    
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Playlist <span class="required">*</span></label>
                                    <select name="playlist_id" required class="mb-2 form-control">
                                        <?php
                                        global $playlists;
                                        foreach ($playlists as $p) {
                                        ?>
                                            <option value="<?= $p->id ?>"><?= $p->title ?></option>
                                        <?php
                                        } ?>
                                    </select>
                                </div>

                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Guest User</label>
                                    <select name="guest" class="mb-2 form-control">
                                        <option value="null">No Guest User</option>
                                        <?php
                                        global $users;
                                        foreach ($users as $u) {
                                        ?>
                                            <option value="<?= $u->id ?>"><?= $u->short_name ?></option>
                                        <?php
                                        } ?>
                                    </select>
                                </div>
                                <!--<div class="form-check">
                                <input required name="check" id="exampleCheck" type="checkbox" class="form-check-input">
                                <label for="exampleCheck" class="form-check-label">Check me out</label>
                            </div>-->
                                <button class="mt-2 btn btn-primary">Create</button>
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
                                        <th>Title</th>
                                        <th>Edited By</th>
                                        <th>Last Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    global $episodes;
                                    global $s3Connector;

                                    if ($episodes != null) {
                                        foreach ($episodes as $p) {
                                            if ($p == null) continue;
                                            $editor = Users::get_by_id($p->updated_by_user_id);
                                            $img = $fileManager->get("episodes/{$p->folder}", $p->image_1x1)
                                            //$s3Connector->getObjectURL("episodes/{$p->folder}", $p->image_1x1);
                                    ?>
                                            <tr>
                                                <td> <img class="cover-image" src="<?= $img ?>"> <?= $p->short_title ?></td>
                                                <td><?= $editor->full_name ?></td>
                                                <td> <?= $p->date_modified ?></td>
                                                <td>
                                                    <?php if ($role->can_edit_episodes == "1") { ?>
                                                        <a href="?p=episodes&a=delete&id=<?= $p->id ?>" type="button" class="btn mr-2 mb-2 btn-danger">
                                                            Delete
                                                        </a>
                                                        <a href="?p=episodes&a=update&id=<?= $p->id ?>" type="button" class="btn mr-2 mb-2 btn-primary">
                                                            Edit
                                                        </a>

                                                        <?php if ($p->file_high_quality == "") { ?>
                                                            <button id="id_<?= $p->folder ?>" onclick="open_window('<?= $p->folder ?>')" type="button" class="btn mr-2 mb-2 btn-warning">
                                                                Upload file
                                                            </button>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php
                                        }
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
    jQuery.validator.addMethod("isvisible", function(value, element) {
        return ["public","invisible"].indexOf(value) > -1; 
    }, "* Must be either invisible or visible");

    jQuery.validator.addMethod("hastags", function(value, element) {
        return value.split(",").length > 0;
    }, "* Must have at least 1 tag");

        $("#form").validate({
            rules: {
                title: {
                    required: true,
                },     
                short_title: {
                    required: true,
                },
                description: {
                    required: true,
                },
                cover_image: {
                    required: true,
                },
                tags:{
                    required:true,
                    hastags:true,
                },
                duration: {
                    required: true
                },
                visibility:{
                    required:true,
                    isvisible:true

                }   
            }
        });
    })
</script>