<?php
namespace Utils;
use \Exception;

class FileManager{
    private $uploads_path;
    function __construct($uploads_path){
       $this->uploads_path = $uploads_path;
    }
    
    function upload($file,$dist_name, $folder){
        $target_path = $this->uploads_path . "/".$folder;
        
        try{
          //Attempt to make dir
          mkdir($target_path,0777,true);
        }catch(Exception $e){

        }
        $target_file = $target_path . $dist_name;
        $allowUpload = true;
        $check = getimagesize($file); //$_FILES["fileToUpload"]["tmp_name"]
        if($check !== false) {
          $allowUpload = true;
        } else {
          $allowUpload = false;
        }

        // Check if file already exists
        /*if (file_exists($target_file)) {
            $allowUpload = true;
        }*/

        if($allowUpload){
            if (move_uploaded_file($file, $target_file)) {
                return $target_file;
              } else {
                 return false;
              }
        }else{
            return $allowUpload;
        }
    }
    function get($folder, $file){     
      $host =HTTP_HOST;
      $url  = "//{$host}/uploads/{$folder}/{$file}"; 
      return $url; 
   
    }

    function unlink($folder,$item){
        $target_path = $this->uploads_path . "/".$folder;
        $target_file = $target_path . $item;
        
        unlink($target_file);
    }
}
