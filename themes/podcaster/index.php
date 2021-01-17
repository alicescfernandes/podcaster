<?php   namespace Theme;
session_start();

global $logged_user;
$logged_user = false;

global $user_visiblity;
$user_visiblity = ["public"];


if (isset($_SESSION['logged_user'])) {
    $logged_user = json_decode($_SESSION['logged_user']);
    $user_visiblity[] = "invisible";
    $user_visiblity[] = "private";
}

require_once __DIR__ . "/.htversioning.php";
require_once ROOT_DIR . '/inc/DB/Episodes.php';
require_once ROOT_DIR . '/inc/DB/Playlists.php';
require_once ROOT_DIR . '/inc/DB/Roles.php';
require_once ROOT_DIR . '/inc/DB/Users.php';
require_once ROOT_DIR . '/inc/FileManager.php';
require_once ROOT_DIR . '/inc/S3Connector.php';

use DB\Episodes;
use DB\Playlists;
use DB\Roles;
use DB\Users;
use eftec\bladeone\BladeOne;
use Utils\FileManager;
use Utils\S3Connector;

global $fileManager;
$fm = new FileManager(ROOT_DIR . "/" . 'uploads');

global $s3;
$s3 = new S3Connector();

$views = __DIR__ . '/pages/blade-templates/';
$cache = __DIR__ . '/cache';

global $blade;
$blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);

$klein = new \Klein\Klein();


$klein->respond('GET', BASE . '/', function () {
    global $blade;
    global $user_visiblity;
    global $logged_user;
    $episodes = Episodes::get_most_recent();

    if ($episodes[0] != null) {
        for ($i = 0; $i < count($episodes); $i++) {
            $episodes[$i]->playlist = Episodes::get_playlist($episodes[$i]->id);
        }
    }



    $tags = Playlists::get_all_tags();
    echo $blade->run("pages.index", ["logged_user" => $logged_user, "episodes" => $episodes, "tags" => $tags, "user_visibility" => $user_visiblity]);
});


$klein->respond('GET', BASE . '/tags/[:tag]', function ($request) {
    global $blade;
    global $user_visiblity;
    $episodes_arr = Episodes::get_by_tag($request->tag); //TODO
    $episodes = [];
    if($episodes_arr[0] != null){
        for ($i = 0; $i < count($episodes_arr); $i++) {
            $episodes_arr[$i] = $episodes_arr[$i]->object_id;
        }
        $episodes = Episodes::get_only($episodes_arr);
    }


    echo $blade->run("pages.tags", ["tag" => $request->tag, "episodes" => $episodes, "user_visibility" => $user_visiblity]);
});

$klein->respond('GET', BASE . '/playlists/[:id]', function ($request) {
    global $blade;
    global $user_visiblity;

    $playlist = Playlists::get_by_id($request->id);
    $episodesOnPlaylists = Playlists::get_items_from($request->id);
    $user = Users::get_by_id($playlist->created_by_user_id);
    for ($i = 0; $i < count($episodesOnPlaylists); $i++) {
        $episodesOnPlaylists[$i] = $episodesOnPlaylists[$i]->episode_id;
    }

    $episodes = Episodes::get_only($episodesOnPlaylists);
    $tags = Playlists::get_tags_from($request->id);

    echo $blade->run("pages.playlists", [
        "episodes" => $episodes,
        "playlist" => $playlist,
        "user" => $user,
        "tags" => $tags,
        "user_visibility" => $user_visiblity
    ]);
});

$klein->respond('GET', BASE . "/api", function ($request, $response) {
    $response->abort(404);
});
//API Routes
$klein->respond('GET', BASE . '/api/username/[:username]', function ($request, $response) {
    $user = Users::get_user_by_shortname(filter_var($request->username, FILTER_SANITIZE_SPECIAL_CHARS));
    if ($user != null) {
        $response->json(true);
    } else {
        $response->json(false);
    }
});

$klein->respond('GET', BASE . '/api/email/[:email]', function ($request, $response) {
    if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
        $response->json(true);
    }

    $user = Users::get_user_by_email(filter_var($request->email, FILTER_SANITIZE_SPECIAL_CHARS));
    if ($user != null) {
        $response->json(true);
    } else {
        $response->json(false);
    }
});

$klein->onHttpError(function ($code, $router) {
    global $blade;
    echo $blade->run("pages.404");
});



$klein->dispatch();
