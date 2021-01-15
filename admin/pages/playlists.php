<!-- TODO: Location refresh on edit/delete/crate -->
<!-- TODO: roles integratiion & roles manager -->
<?php

use Utils\S3Connector;
use DB\Users;
use DB\Playlists;
use Utils\FileManager;
use DB\Episodes;

global $s3Connector;
global $users;
global $playlists;
global $episodes;
$fileManager = new FileManager(ROOT_DIR . "/" . 'uploads');

//Process Form Data                
if (isset($_GET["p"]) && isset($_GET["a"])) {
    $action = $_GET["a"];
    $page = $_GET["p"];
    switch ($action) {
        case "create":
            if (isset($_POST["playlist_title"])) {
                $user = json_decode($_SESSION["logged_user"]);
                $folder = filter_var($_POST["playlist_folder"],FILTER_SANITIZE_SPECIAL_CHARS);
                $dir = $_FILES["cover_image"]["tmp_name"];

                $obj = new stdClass();
                $obj->episodes = empty($_REQUEST["episodes"]) ? [] : explode(",",$_REQUEST["episodes"]); //episode ids. TODO: Starts empty
                $obj->image = $_FILES["cover_image"]["name"];
                $obj->created_by_user_id = (int) $user->id;
                $obj->updated_by_user_id = (int) $user->id;
                
                $obj->tags = filter_var($_REQUEST["tags"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->title = filter_var($_REQUEST["playlist_title"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->description = filter_var($_REQUEST["description"],FILTER_SANITIZE_SPECIAL_CHARS);
                $obj->visibility = filter_var($_REQUEST["visibility"],FILTER_SANITIZE_SPECIAL_CHARS);
                
                $obj->edited_by_user_id = (int) $_POST["editor"];
                $obj->type = "playlist";
                $obj->folder = $folder;

                //Validate width/height
                $size = getimagesize($dir);
                $width = $size[0];
                $height = $size[1];

                if ($width == $height && $width >= MIN_IMG_SIZE) {
                    //Upload Cover to S3
                    Playlists::create($obj);
                    $fileManager->upload($dir, $obj->image, "playlists/{$folder}/");
                    //$s3Connector->upload("playlists/{$folder}", $obj->image, file_get_contents($dir));
                    $_POST = array();
                } else {
                    echo ("<script>alert('Cover image should be square and bigger than 1024px')</script>");
                }
            }
            break;
        case "delete":
            $id = (int) $_GET["id"];
            $playlist = Playlists::get_by_id($id);

            if ($playlist != null) {
                Playlists::delete($id);
                $fileManager->unlink("playlists/{$playlist->folder}/", $playlist->image_1x1);
            }
            echo ("<script>window.location = '?p=playlists';</script>"); //TODO: Handle this redirects with more care

            break;
        case "update":
            echo '<script>
                    window.onload = function(){
                        $("#edit-playlist").modal("show")
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

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-drawer icon-gradient bg-happy-itmeo">
                </i>
            </div>
            <div>Manage playlists
                <div class="page-title-subheading">todo
                </div>
            </div>
        </div>

    </div>
</div>
<ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav">
    <?php if ($role->can_list_playlist == "1") { ?>
        <li class="nav-item">
            <a role="tab" class="nav-link active" id="tab-1" data-toggle="tab" href="#tab-content-0">
                <span>Edit Playlists</span>
            </a>
        </li>
    <?php } ?>

    <?php if ($role->can_create_playlist == "1") { ?>
        <li class="nav-item">
            <a role="tab" class="nav-link " id="tab-0" data-toggle="tab" href="#tab-content-1">
                <span>Create Playlist</span>
            </a>
        </li>
    <?php } ?>

</ul>
<div class="tab-content" role="tabpanel">
    <?php if ($role->can_create_playlist == "1") { ?>
        <div class="tab-pane tabs-animation fade " id="tab-content-1" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <form id="form" enctype="multipart/form-data" class="" method="POST" action="?p=playlists&a=create">
                                <input type="hidden" name="playlist_folder" value="p_<?= time() ?>">
                                <div class="form-row mb-3">
                                    <label for="exampleEmail11" class="">Title</label>
                                    <input required name="playlist_title" id="exampleEmail11" placeholder="Playlist title" type="text" class="form-control">
                                </div>
                                <div class="form-row mb-3">
                                    <label for="exampleAddress" class="">Description</label>
                                    <textarea required required name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"></textarea>
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Cover Image</label>
                                    <input class="form-control" type="file" required name="cover_image">
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Tags</label>
                                    <input required name="tags" type="text" class="form-control" placeholder="tag1,tag2...    ">
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Playlist User Editor</label>
                                    <select name="editor" required class="mb-2 form-control">
                                        <?php
                                        global $users;
                                        foreach ($users as $u) {
                                        ?>
                                            <option value="<?= $u->id ?>"><?= $u->full_name ?></option>
                                        <?php
                                        } ?>
                                    </select>
                                </div>
                                <div class=" form-row mb-3">
                                    <label for="exampleAddress" class="">Visibility <span class="required">*</span> </label>
                                    <select name="visibility" required class="mb-2 form-control">
                                        <option value="public">Public</option>
                                        <option value="invisible">Invisible</option>
                                    </select>
                                </div>
                                
                                <div class="playlist-items">
                                    <input type="hidden" name="episodes" id="playlist_episodes" value="">
                                    <div class="list-group">
                                        <p>All episodes</p>
                                        <div id="listWithHandle" class="cenas">
                                            <?php
                                            foreach ($episodes as $p) {
                                            ?>
                                                <div data-episode-id=" <?= $p->id ?>" class="list-group-item">
                                                    <?= $p->short_title ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="list-group">
                                        <p>Playlist episodes</p>
                                        <div id="listWithHandle2" class="cenas">
                                        </div>
                                    </div>
                                </div>
                                <button class="mt-2 btn btn-primary">Create playlist</button>
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
                                    if ($playlists != null) {
                                        foreach ($playlists as $p) {
                                            if ($p == null) continue;
                                            $editor = Users::get_by_id($p->edited_by_user_id);
                                            $img = $fileManager->get("playlists/{$p->folder}", $p->image_1x1);
                                    ?>
                                            <tr>
                                                <td> <img class="cover-image" src="<?= $img ?>"> <?= $p->title ?></td>
                                                <td><?= $editor->full_name ?></td>
                                                <td> <?= $p->date_modified ?></td>
                                                <td>
                                                    <?php if ($role->can_edit_playlist == "1") { ?>
                                                        <a href="?p=playlists&a=delete&id=<?= $p->id ?>" type="button" class="btn mr-2 mb-2 btn-danger">
                                                            Delete Playlist
                                                        </a>
                                                        <a href="?p=playlists&a=update&id=<?= $p->id ?>" type="button" class="btn mr-2 mb-2 btn-primary">
                                                            Edit Playlist
                                                        </a>
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

<style>
    .list-group {
        width: 49%;
        display: inline-block;
        vertical-align: top;
        min-height: 200px;
        max-height: 208px;
        overflow-y: scroll;
        margin-bottom: 20px;
    }

    .cenas {
        min-height: 100px;
    }
</style>

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