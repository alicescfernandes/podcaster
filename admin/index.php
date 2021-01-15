<?php session_start();
//TODO: Roles & roles chooser & roles creator
header("Cache-Control: no-cache, must-revalidate");
error_reporting(0);
if (!isset($_SESSION['logged_user'])) {
    header("Location: login.php");
}

global $logged_user;
$logged_user = json_decode($_SESSION['logged_user']);

require_once __DIR__ . '/parts/header.php'; //Database settings
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
use DB\Roles;
use Utils\S3Connector;
use Utils\FileManager;

$allowed_pages = ["users", "playlists", "episodes", "roles", "settings", "profile", "register"]; //making this very easy, so i just don't give up on the class
$requested_page = "users";

define("MIN_IMG_SIZE", 1000); //TODO: Put this in the database maybe

if (isset($_GET['p'])) {
    $parameter_page = $_GET['p'];
    if (in_array($parameter_page, $allowed_pages)) {
        $requested_page = $parameter_page;
    }
}
global $fileManager;
$fileManager = new FileManager(ROOT_DIR . "/" . 'uploads');

global $role;
$role = Roles::get_by_id($logged_user->role_id);

global $permission_roles;
$permission_roles = explode(",", $role->permission_over);



//Get fresh data
$users = Users::get_all(false);
$playlists = Playlists::get_all(true);
$episodes = Episodes::get_all(true);
?>

<div class="app-main">
    <?php require_once __DIR__ . '/parts/sidebar.php';  ?>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <?php require_once __DIR__ . "/pages/$requested_page.php"; ?>
        </div>
        <?php require_once __DIR__ . '/parts/header.php'; ?>
    </div>
    <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
</div>
</div>


<?php
//Form processing code
if (isset($_GET["p"]) && isset($_GET["a"])) {
                 
    $action = $_GET["a"];
    $page = $_GET["p"];

    switch ($page) {
        case "users":
            switch ($action) {
                case "edit":
                    if (isset($_GET["id"])) {
                        $user = Users::get_by_id($_GET["id"]);
                        if (isset($_POST["full_name"])) {
                            $data = new stdClass();
                            $data->short_name =  filter_var($_POST["short_name"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $data->full_name = filter_var($_POST["full_name"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $data->email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
                            $data->description = filter_var($_POST["description"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $data->id = filter_var($_GET["id"], FILTER_SANITIZE_NUMBER_INT);
                            $data->role_id =  filter_var($_POST["role_id"], FILTER_SANITIZE_NUMBER_INT);

                            if (isset($_FILES["avatar"]) && $_FILES["avatar"]["size"] > 0) {
                                $dir = $_FILES["avatar"]["tmp_name"];

                                //Validate width/height
                                $size = getimagesize($dir);
                                $width = $size[0];
                                $height = $size[1];

                                if ($width == $height && $width >= 200) {
                                    $fileManager->upload($dir, $user->avatar_url, "users/"); //TODO: Check if PNG with JPEG extension works
                                    $_POST = array();
                                } else {
                                    echo ("<script>alert('Cover image should be square and bigger than 200px')</script>");
                                }
                            }
                            Users::update_user($data);
                            unset($_POST["full_name"]);
                            $_POST = array();
                            echo ("<script>window.location = window.location.href</script>"); //TODO: Handle this redirects with more care


                        }
                    }
                    break;
            }
            break;

        case "playlists":
            switch ($action) {
                case "edit":
                   
                    if (isset($_GET["id"])) {
                        if (isset($_POST["playlist_title"])) {
                            $obj = new stdClass();
                            $user = json_decode($_SESSION["logged_user"]);

                            $obj->episodes =  empty($_REQUEST["episodes"]) ? [] : explode(",", filter_var($_REQUEST["episodes"], FILTER_SANITIZE_SPECIAL_CHARS)); //episode ids. TODO: Starts empty
                            $obj->title = filter_var($_REQUEST["playlist_title"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->description = filter_var($_REQUEST["description"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->created_by_user_id = (int) $user->id;
                            $obj->updated_by_user_id = (int) $user->id;
                            $obj->tags = filter_var($_REQUEST["tags"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->edited_by_user_id = filter_var($_POST["editor"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->type = "playlist";
                            $obj->id = filter_var($_GET["id"], FILTER_SANITIZE_NUMBER_INT);
                            $obj->visibility = filter_var($_REQUEST["visibility"], FILTER_SANITIZE_SPECIAL_CHARS);
                            if (isset($_FILES["cover_image"]) && $_FILES["cover_image"]["size"] > 0) {
                                $folder = filter_var($_POST["playlist_folder"], FILTER_SANITIZE_SPECIAL_CHARS); //name
                                $dir = $_FILES["cover_image"]["tmp_name"];
                                $obj->image = $_FILES["cover_image"]["name"];

                                //Validate width/height
                                $size = getimagesize($dir);
                                $width  = $size[0];
                                $height = $size[1];

                                if ($width == $height && $width >= MIN_IMG_SIZE) {
                                    $s3Connector = new S3Connector();
                                    Playlists::update_with_image($obj);
                                    $fileManager->upload($dir, $obj->image, "playlists/{$folder}/");
                                    echo ("<script>window.location = '?p=playlists';</script>"); //TODO: Handle this redirects with more care
                                } else {
                                    echo ("<script>alert('Cover image should be square and bigger than 1000px')</script>");
                                }
                            } else {
                                Playlists::update($obj);
                                echo ("<script>window.location = '?p=playlists';</script>"); //TODO: Handle this redirects with more care
                            }
                        }
                    }
                    break;
            }
            break;


        case "episodes":
            switch ($action) {
                case "edit":
                    if (isset($_GET["id"])) {
                        if (isset($_POST["short_title"])) {
                            $obj = new stdClass();
                            $user = json_decode($_SESSION["logged_user"]);
                            $ep = Episodes::get_by_id($_GET["id"]);

                            $obj = new stdClass();
                            $obj->description = filter_var($_REQUEST["description"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->image = $_FILES["cover_image"]["name"];
                            $obj->updated_by_user_id = (int) $user->id;
                            $obj->tags =  filter_var($_REQUEST["tags"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->folder = $folder;
                            $obj->long_title =  filter_var($_REQUEST["long_title"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->playlist_id =  filter_var($_REQUEST["playlist_id"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->short_title =  filter_var($_REQUEST["short_title"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->duration = filter_var($_REQUEST["duration"], FILTER_SANITIZE_NUMBER_INT);
                            $obj->visibility =  filter_var($_REQUEST["visibility"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->guest_user_id =  filter_var($_REQUEST["guest"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->type =  filter_var($_REQUEST["type"], FILTER_SANITIZE_SPECIAL_CHARS);
                            $obj->id = filter_var($_REQUEST["id"], FILTER_SANITIZE_NUMBER_INT);

                            if (isset($_FILES["markers"]) && $_FILES["markers"]["size"] > 0) {
                                $obj->mdata = base64_encode(file_get_contents($_FILES["markers"]["tmp_name"]));
                            }


                            if (isset($_FILES["cover_image"]) && $_FILES["cover_image"]["size"] > 0) {
                                $folder = $_POST["folder"]; //name
                                $file_path = $_FILES["cover_image"]["tmp_name"];
                                $obj->image = $_FILES["cover_image"]["name"];

                                //Validate width/height
                                $size = getimagesize($file_path);
                                $width  = $size[0];
                                $height = $size[1];

                                if ($width == $height && $width >= MIN_IMG_SIZE) {
                                    //Upload Cover to S3
                                    $fileManager->upload($file_path, $obj->image, "episodes/{$folder}/");
                                } else {
                                    echo ("<script>alert('Cover image should be square and bigger than 1000px')</script>");
                                }
                            }

                            Episodes::update($obj);
                            echo ("<script>window.location = '?p=episodes';</script>"); //TODO: Handle this redirects with more care
                        }
                    }
                    break;
            }
            break;
    }
}
?>

<?php if (isset($_GET["id"]) && $_GET["a"] == "update" && $_GET["p"] == "users") { ?>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="editU" enctype="multipart/form-data" class="" method="POST" action="?p=users&a=edit&id=<?= $_GET["id"] ?>">
                        <?php
                        if (isset($_GET["id"])) {
                            $user2 = Users::get_by_id($_GET["id"]);
                        }
                        ?>
                        <div class="form-row">
                           

                        </div>
                        <input type="hidden" name="id" value="<?= $_GET["id"] ?>">

                        <div class="position-relative form-group"><label for="exampleAddress" class="">Full Name</label><input name="full_name" id="exampleAddress" value="<?= $user2->full_name ?>" placeholder="1234 Main St" type="text" class="form-control"></div>
                        <div class="position-relative form-group"><label for="exampleAddress" class="">User description</label><textarea name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"><?= $user2->description ?></textarea></div>

                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Avatar Image</label>
                            <input class="form-control" type="file" name="avatar">
                        </div>

                        <div class="position-relative form-group"><label for="exampleAddress" class="">Role</label>
                            <select name="role_id" class="mb-2 form-control">
                                <?php foreach ($roles as $r) { ?>
                                    <?php if (in_array($r->machine_name, $permission_roles)) { ?>
                                        <option <?= $user2->role_id == $r->id ? "selected" : "" ?> value="<?= $r->id ?>"><?= $r->name ?></option>
                                    <?php } ?>
                                <?php } ?>

                            </select>
                        </div>



                        <div class="position-relative form-check"><input name="check" id="exampleCheck" type="checkbox" class="form-check-input"><label for="exampleCheck" class="form-check-label">Check me out</label></div>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>


                </div>

            </div>
        </div>
    </div>
<?php } ?>

<?php if (isset($_GET["id"]) && $_GET["a"] == "update" && $_GET["p"] == "episodes") { ?>
    <div class="modal fade" id="edit-episode" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Episode</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="editEp" enctype="multipart/form-data" class="" method="POST" action="?p=episodes&a=edit&id=<?= $_GET["id"] ?>">
                        <?php
                        if (isset($_GET["id"]) && $_GET["a"] == "update" && $_GET["p"] == "episodes") {
                            global $episode;
                            $episode = Episodes::get_by_id($_GET["id"]);
                            $t = Episodes::get_tags_from((int) $_GET["id"]);
                            $tags = [];
                            foreach ($t as $tag) {
                                $tags[] = $tag->tag;
                            }
                            $tags = implode(",", $tags);
                        }
                        ?>

                        <?php
                        $folder = $episode->folder;
                        ?>
                        <input type="hidden" name="folder" value="<?= $folder ?>">
                        <div class="form-row mb-3">
                            <label for="exampleEmail11" class="">Title</label>
                            <input required name="long_title" id="exampleEmail11" placeholder="Long Title" type="text" value="<?= $episode->long_title ?>" class="form-control">
                        </div>
                        <div class="form-row mb-3">
                            <label for="exampleEmail11" class="">Short Title</label>
                            <input required name="short_title" id="exampleEmail11" placeholder="Short Title" value="<?= $episode->short_title ?>" type="text" class="form-control">
                        </div>
                        <div class="form-row mb-3">
                            <label for="exampleAddress" class="">Description</label>
                            <textarea required required name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"><?= $episode->description ?></textarea>
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Cover Image</label>
                            <input class="form-control" type="file" name="cover_image">
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Audio File</label>
                            <button id="file_upload" type="button" onclick="open_window('<?= $folder ?>')">Upload file</button>
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Tags</label>
                            <input required name="tags" value="<?= $tags ?>" type="text" class="form-control" placeholder="tag1,tag2...    ">
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Playlist</label>
                            <select name="type" required class="mb-2 form-control">

                                <option <?= $episode->type == "mix" ? "selected" : "" ?> value="mix">Mix</option>
                                <option <?= $episode->type == "podcast" ? "selected" : "" ?> value="podcast">Podcast</option>

                            </select>
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Duration (in minutes) <span class="required">*</span> </label>
                            <input required name="duration" value="<?= $episode->duration ?>" type="number" class="form-control" placeholder="30">
                        </div>


                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Playlist</label>
                            <select name="playlist_id" required class="mb-2 form-control">
                                <?php
                                global $playlists;
                                foreach ($playlists as $p) {
                                    $isSelected = $p->id == $episode->playlist_id;

                                ?>
                                    <option <?= $isSelected ? "selected" : "" ?> value="<?= $p->id ?>"><?= $p->title ?></option>
                                <?php
                                } ?>
                            </select>
                        </div>

                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Markers</label>
                            <input class="form-control" type="file" accept="application/json" name="markers">
                            <p>Upload a JSON file with markers</p>
                        </div>

                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Visibility <span class="required">*</span> </label>
                            <select name="visibility" required class="mb-2 form-control">
                                <option <?= $episode->visibilty == "public" ? "selected" : "" ?> value="public">Public</option>
                                <option <?= $episode->visibilty == "invisible" ? "selected" : "" ?> value="invisible">Invisible</option>
                            </select>
                        </div>


                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Guest User</label>
                            <select name="guest" class="mb-2 form-control">
                                <option value="null">No Guest User</option>
                                <?php
                                global $users;
                                foreach ($users as $u) {
                                    $isSelected = $u->id == $episode->guest_user_id;

                                ?>
                                    <option <?= $isSelected ? "selected" : "" ?> value="<?= $u->id ?>"><?= $u->short_name ?></option>
                                <?php
                                } ?>
                            </select>
                        </div>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>


                </div>

            </div>
        </div>
    </div>
<?php } ?>

<?php
if (isset($_GET["id"]) && $_GET["a"] == "update" && $_GET["p"] == "playlists") {

    global $playlist;


    $playlist = Playlists::get_by_id($_GET["id"]);
    $episodesSql = Playlists::get_items_from($playlist->id);
    $playlist_ids = [];
    if ($episodesSql[0] != null) {
        foreach ($episodesSql as $ep) {
            $playlist_ids[] = $ep->episode_id;
        }
    }
    //$playlist_ids = explode(",", $playlist->episodes);

    $available_eps = $episodes;
    $playlist_eps = [];

    if (count($playlist_ids) > 0) {
        $available_eps = Episodes::get_all_except($playlist_ids);

        if ($available_eps[0] == null) $available_eps = [];

        $playlist_eps = Episodes::get_only($playlist_ids);
    }
    $eps = [];
    foreach ($playlist_eps as $p) {
        $eps[] = $p->id;
    }
    $eps = implode(",", $eps); //$eps

    $t = Playlists::get_tags_from((int) $_GET["id"]);
    $tags = [];
    foreach ($t as $tag) {
        $tags[] = $tag->tag;
    }
    $tags = implode(",", $tags);

?>
    <div class="modal fade" id="edit-playlist" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Playlist</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="editPl" enctype="multipart/form-data" class="" method="POST" action="?p=playlists&a=edit&id=<?= $_GET["id"] ?>">
                        <input type="hidden" name="playlist_folder" value="<?= $playlist->folder ?>">
                        <div class="form-row mb-3">
                            <label for="exampleEmail11" class="">Title</label>
                            <input required name="playlist_title" value="<?= $playlist->title ?>" id="exampleEmail11" placeholder="Playlist title" type="text" class="form-control">
                        </div>

                        <div class="form-row mb-3">
                            <label for="exampleAddress" class="">Description</label>
                            <textarea required name="description" id="exampleAddress" placeholder="1234 Main St" type="text" class="form-control"><?= $playlist->description ?></textarea>
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Cover Image</label>
                            <input class="form-control" type="file" name="cover_image">
                            <br>
                            <p>This will replace the current cover image. Leaving empty won't delete the current image</p>
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Tags</label>
                            <input required name="tags" value="<?= $tags ?>" type="text" class="form-control" placeholder="tag1,tag2...    ">
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Playlist User Editor</label>
                            <select name="editor" required class="mb-2 form-control">
                                <?php
                                global $users;
                                foreach ($users as $u) {
                                    $isSelected = $u->id == $playlist->edited_by_user_id;
                                ?>
                                    <option <?= $isSelected ? "selected" : "" ?> value="<?= $u->id ?>"><?= $u->full_name ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class=" form-row mb-3">
                            <label for="exampleAddress" class="">Visibility <span class="required">*</span> </label>
                            <select name="visibility" required class="mb-2 form-control">
                                <option <?= $playlist->visibilty == "public" ? "selected" : "" ?> value="public">Public</option>
                                <option <?= $playlist->visibilty == "invisible" ? "selected" : "" ?> value="invisible">Invisible</option>
                            </select>
                        </div>

                        <div class="playlist-items">
                            <input type="hidden" name="episodes" id="playlist_episodes2" value="<?= $eps ?>">
                            <div class="list-group">
                                <p>All episodes</p>
                                <div id="listWithHandle3" class="cenas">
                                    <?php
                                    foreach ($available_eps as $p) {
                                    ?>
                                        <div data-episode-id=" <?= $p->id ?>" class="list-group-item">
                                            <?= $p->short_title ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="list-group">
                                <p>Playlist episodes</p>
                                <div id="listWithHandle4" class="cenas">
                                    <?php
                                    foreach ($playlist_eps as $p) {
                                    ?>
                                        <div data-episode-id=" <?= $p->id ?>" class="list-group-item">
                                            <?= $p->short_title ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>


                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>


                </div>

            </div>
        </div>
    </div>
<?php } ?>


<!-- Latest Sortable -->
<script src="https://raw.githack.com/SortableJS/Sortable/master/Sortable.js"></script>


<script>
    window.addEventListener("load", function() {
        if (window.listWithHandle) {
            Sortable.create(listWithHandle, {
                animation: 150,
                group: "list"
            });
        }

        if (window.listWithHandle2) {
            Sortable.create(listWithHandle2, {
                animation: 150,
                group: "list",
                onSort: function() {
                    var el = document.getElementById("playlist_episodes");
                    el.value = Array.prototype.slice.call(listWithHandle2.querySelectorAll("div")).map(function(el) {
                        return el.getAttribute("data-episode-id");
                    }).join(",").replace(/\s/g, '');

                }
            });
        }

        if (window.listWithHandle3) {
            Sortable.create(listWithHandle3, {
                animation: 150,
                group: "list2"
            });
        }

        if (window.listWithHandle4) {
            Sortable.create(listWithHandle4, {
                animation: 150,
                group: "list2",
                onSort: function() {
                    var el = document.getElementById("playlist_episodes2");
                    el.value = Array.prototype.slice.call(listWithHandle4.querySelectorAll("div")).map(function(el) {
                        return el.getAttribute("data-episode-id");
                    }).join(",").replace(/\s/g, '');

                }
            });
        }


        jQuery.validator.addMethod("isvisible", function(value, element) {
            return ["public", "invisible"].indexOf(value) > -1;
        }, "* Must be either invisible or visible");

        jQuery.validator.addMethod("hastags", function(value, element) {
            return value.split(",").length > 0;
        }, "* Must have at least 1 tag");

        $("#editEp").validate({
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
                tags: {
                    required: true,
                    hastags: true,
                },
                duration: {
                    required: true
                },
                visibility: {
                    required: true,
                    isvisible: true

                }
            }
        });

        $("#editPl").validate({
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

                tags: {
                    required: true,
                    hastags: true,
                },
                duration: {
                    required: true
                },
                visibility: {
                    required: true,
                    isvisible: true

                }
            }
        });

        $("#editU").validate({
            rules: {
                short_name: {
                    required: true,

                },
                password: {
                    required: true,
                    email: false,
                    pattern: "(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}",
                },
                email: {
                    required: true,
                    email: true
                },
                full_name: {
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
    })

    $(".mobile-toggle-header-nav").click(function() {
        $(".app-header__content").toggleClass("header-mobile-open")
    })

    $(".mobile-toggle-nav").click(function() {
        $(".app-container").toggleClass("sidebar-mobile-open")
        $(this).toggleClass("is-active")

    })
</script>
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