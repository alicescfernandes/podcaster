<?php
namespace DB;
include_once("DB.php");

use DB\DB;
use stdClass;

class Episodes extends DB{
    protected static $table = "episodes";

    static function create(stdClass $obj){
        $visibility = "public";
        $date_created = date("Y-m-d H:i:s");
          
        $query = "INSERT INTO `episodes` 
        (`long_title`,
        `description`,
        `file_low_quality`,
        `image_16x9`,
        `image_1x1`,
        `date_created`,
        `date_modified`,
        `playlist_id`,
        `created_by_user_id`,
        `updated_by_user_id`,
        `short_title`,
        `file_medium_quality`,
        `file_high_quality`,
        `duration`,
        `guest_user_id`,
        `type`,
        `tags`,
        `lang`,
        `folder`,
        `marker_data`,
        `visibilty`)
        VALUES(
        '{$obj->long_title}',
        '{$obj->description}',
        '{$obj->file}',
        '{$obj->image}',
        '{$obj->image}',
        '{$date_created}',
        '{$date_created}',
         {$obj->playlist_id},
         {$obj->created_by_user_id},
         {$obj->updated_by_user_id},
        '{$obj->short_title}',
        '{$obj->file}',
        '{$obj->file}',
         {$obj->duration},
         {$obj->guest_user_id},
        '{$obj->type}',
        '',
        '',
        '{$obj->folder}',
        '{$obj->mdata}',
        '{$obj->visibility}');";

        parent::make_query($query);
        $result = self::get_most_recent(1);
        $tags = explode(",",$obj->tags);
        self::set_playlist($result[0]->id,$obj->playlist_id);
        return  self::set_tags($tags, $result[0]->id);
    }

    static function get_by_folder(int $folder){
        $query = "select  * FROM `episodes` WHERE `folder`={$folder};
        ";
        return parent::make_query($query);
    }

    private static function set_tags(array $tags, int $episode_id){
        $query = "DELETE FROM `episode_tags` WHERE `object_id` = {$episode_id};";
        parent::make_query($query);
        
        $query = "INSERT INTO `episode_tags` ( `object_id`,`tag`) VALUES";

        
        for ($i=0; $i < count($tags); $i++) { 
            $tags[$i] = " ({$episode_id},'{$tags[$i]}')"; 
        }
        $query = $query . implode(",",$tags) . ";";
        return parent::make_query($query);
    }

    static function update(stdClass $obj){
        $visibility = "public";
        $date_modified = date("Y-m-d H:i:s");      
        $image_portion = "";
        $file_portion = "";
        $marker_portion = "";
        //Build query
        if(!empty($obj->image)){
            $image_portion = "
            ,`image_16x9` = '{$obj->image}'
            ,`image_1x1` = '{$obj->image}'";
        }

        //Build query
        if(!empty($obj->file)){
            $file_portion = "
            `,file_low_quality` = '{$obj->file}',
            `file_medium_quality` = '{$obj->file}',
            `file_high_quality` = '{$obj->file}'";
        }

         //Build query
         if(!empty($obj->mdata)){
            $marker_portion = ",`marker_data` = '{$obj->mdata}'";
        }

        $query = "UPDATE `episodes`
        SET
        `long_title` = '{$obj->long_title}',
        `description` = '{$obj->description}',
        `date_modified` = '{$date_modified}',
        `playlist_id` = '{$obj->playlist_id}',
        `updated_by_user_id` = {$obj->updated_by_user_id},
        `short_title` = '{$obj->short_title}',
        `visibilty` = '{$obj->visibility}',
        `duration` = {$obj->duration},
        `guest_user_id` = {$obj->guest_user_id},
        `type` = '{$obj->type}',
        `tags` = '',
        `lang` = ''
        {$image_portion}
        {$file_portion}
        {$file_portion}
        {$marker_portion}
        WHERE `id` = {$obj->id};";   

        $tags = explode(",",$obj->tags);
        self::set_tags($tags, $obj->id);

        return parent::make_query($query);
    }

    static function update_audio_only(stdClass $obj){
        $visibility = "public";
        $date_modified = date("Y-m-d H:i:s");      
            
        $query = "UPDATE `episodes`
        SET
        `file_low_quality` = '{$obj->file}',
        `file_medium_quality` = '{$obj->file}',
        `file_high_quality` = '{$obj->file}'
        WHERE `folder` = '{$obj->folder}';";
        return parent::make_query($query);
    }
    static function get_most_recent(int $limit = 6){
        $query = "SELECT * FROM episodes order by date_modified desc limit {$limit}";
        return parent::make_query_array($query);
    }

    static function get_tags(){
        $query = "SELECT DISTINCT tag FROM `episode_tags`;";
        return parent::make_query_array($query);
    }
    
    static function get_all_except(array $arrayOfEpisodes){
        $whereClause = "WHERE ";
        //id != 36 and id != 38  
        for ($i=0; $i < count($arrayOfEpisodes); $i++) { 
           $arrayOfEpisodes[$i] = "id != ". $arrayOfEpisodes[$i];
        }
        $whereClause = $whereClause . implode(" and ", $arrayOfEpisodes);

        $query = "SELECT * FROM episodes {$whereClause};";
        return parent::make_query_array($query);
    }
    
    static function get_playlist(int $episode_id){
        $query = "SELECT * FROM playlists WHERE id = (SELECT DISTINCT playlist_id FROM `playlist_items` WHERE `episode_id`={$episode_id} LIMIT 1 )";
        return parent::make_query($query);
    }

    static function get_only(array $arrayOfEpisodes){
        $whereClause = "WHERE ";
        
        for ($i=0; $i < count($arrayOfEpisodes); $i++) { 
           $arrayOfEpisodes[$i] = "id = ". $arrayOfEpisodes[$i];
        }   
        
        $whereClause = $whereClause . implode(" or ", $arrayOfEpisodes);
        $query = "SELECT * FROM episodes {$whereClause};";
        return parent::make_query_array($query);
    }

    static function get_tags_from(int $episode_id){
        $query = "SELECT DISTINCT tag FROM `episode_tags` WHERE `object_id`={$episode_id};";
        return parent::make_query_array($query);
    }


    static function delete_tags_from(int $episode_id){
        $query = "DELETE FROM `episode_tags` WHERE `object_id`={$episode_id};";
        return parent::make_query_array($query);
    }

    static function set_playlist(int $episode_id, int $playlist_id){
        self::delete_from_playlist($episode_id);
        //Get total count and increment
        //select count(*) as count from playlist_items where episode_id = 2
        $query = "select count(*) as count from playlist_items where episode_id = {$episode_id}"; 
        $count = parent::make_query($query);
        $count = ((int) $count->count) + 1;

        $query = "INSERT INTO `playlist_items`(`episode_id`,`playlist_id`,`index`)
        VALUES({$episode_id},{$playlist_id},{$count});";
        return parent::make_query_array($query);
    }
    //
    static function get_by_tag(string $tag){
        $query = "SELECT object_id FROM episode_tags where tag='{$tag}'";
        return parent::make_query_array($query);
    }

    static function delete_from_playlist(int $episode_id){
        $query = "DELETE FROM `playlist_items` WHERE `episode_id`={$episode_id};";
        return parent::make_query_array($query);
    }

    static function delete(int $id){
        self::delete_tags_from($id);
        self::delete_from_playlist($id);
        return parent::delete($id);
    }
}

?>