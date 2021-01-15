<?php session_start();
//TODO: Backend validation for audio files
//TODO: Validation to make sure that this is opnened within a window
header("Cache-Control: no-cache, must-revalidate");
require_once( "../../.htconfig.php");
require_once( "../../vendor/autoload.php");

if (!isset($_SESSION['logged_user'])) {
    header("Location: //" . HTTP_HOST ."/admin/login.php");
}else{
}

require_once ROOT_DIR . "/inc/S3Connector.php";
require_once(ROOT_DIR . "/inc/DB/Episodes.php");
global $folder;
global $s3Connector;
use Utils\S3Connector;    
use DB\Episodes;

$folder = $_GET["folder"];
if (isset($_FILES["audio_file"])) {
    
    $dir = $_FILES["audio_file"]["tmp_name"];
    $name = filter_var($_FILES["audio_file"]["name"], FILTER_SANITIZE_SPECIAL_CHARS);
    $s3Connector = new S3Connector();
    
    $s3Connector->multipartUpload("episodes/{$folder}", $name,$dir);
    $obj = new stdClass();
    $obj->file = $name;
    $obj->folder = $folder;
    Episodes::update_audio_only($obj);

    echo ("<script>
    document.write('File Uploaded with success. Closing in 3 seconds');
    window.opener.postMessage({folder:'$folder',status:'ok'});
    window.setTimeout(function(){
        //window.close()
    },3000)   
    </script>");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <form enctype="multipart/form-data" class="" method="POST" action="//<?= HTTP_HOST?>/admin/pages/audio_upload_handler.php?folder=<?= $folder ?>">
        <input type="hidden" name="folder" value="<?= $folder ?>">
        <fieldset>
            <input accept="audio/*" type="file" name="audio_file" required>
        </fieldset>
        <button type="submit">Upload</button>
    </form>
    <p>This will handle the file upload, and closes as soon is finished</p>
    <script>
        if(!window.opener){ 
            alert("This window should be opened from the site")
            window.onload = function(){
                document.write("");
            }
        }
        </script>
</body>
</html>