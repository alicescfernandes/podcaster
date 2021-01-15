<?php
namespace DB;
include_once("DB.php");

use DB\DB;
use stdClass;

class Playlists extends DB{
    protected static $table = "playlists";

    static function create(stdClass $obj){
        $date_created = date("Y-m-d H:i:s");
          
        $query = "INSERT INTO `playlists` ( `title`, `description`, `date_created`, `date_modified`, `visibilty`, `image_16x9`, `image_1x1`, `type`, `created_by_user_id`, `updated_by_user_id`, `edited_by_user_id`, `folder`) 
        VALUES ('{$obj->title}', '{$obj->description}', '{$date_created}', '{$date_created}', '{$obj->visibility}', '{$obj->image}', '{$obj->image}', '{$obj->type}', {$obj->created_by_user_id}, {$obj->updated_by_user_id}, '{$obj->edited_by_user_id}', '{$obj->folder}'); ";
        
        parent::make_query($query);
        
        $result = self::get_most_recent(1);
        $tags = explode(",",$obj->tags);
        self::set_tags($tags, $result[0]->id);

        $result = self::get_most_recent(1);
        self::set_episodes($obj->episodes,$result[0]->id);
        return "";
    }

    static function get_all_tags($limit = 4){
    $query = "SELECT playlist_tags.tag FROM playlist_tags UNION  SELECT episode_tags.tag FROM episode_tags limit {$limit}";
        return self::make_query_array($query);
    }

    static function get_most_recent(int $limit = 6){
        $query = "SELECT * FROM playlists order by date_modified desc limit {$limit}";
        return parent::make_query_array($query);
    }

    static function get_items_from(int $playlist_id){
        $query = "SELECT DISTINCT episode_id,`index` FROM `playlist_items` WHERE `playlist_id`={$playlist_id} order by `index`;";
        return parent::make_query_array($query);
    }

    private static function delete_from_playlist(int $playlist_id){
        $query = "DELETE FROM `playlist_items` WHERE `playlist_id`={$playlist_id};";
        return parent::make_query_array($query);
    }


    private static function set_episodes(array $episodes, int $playlist_id){
        if(count($episodes) == 0) return;
        self::delete_from_playlist($playlist_id);
        $query = "INSERT INTO `playlist_items` ( `episode_id`,`index`, playlist_id) VALUES";
        
        for ($i=0; $i < count($episodes); $i++) { 
            $episodes[$i] = " ({$episodes[$i]},{$i}, {$playlist_id})"; 
        }
        $query = $query . implode(",",$episodes) . ";";
        return parent::make_query($query);
    }

    static function delete(int $id){
        self::delete_from_playlist($id);
        self::delete_tags_from($id);
        return parent::delete($id);
    }

  
    static function update(stdClass $obj){
        $date_modified = date("Y-m-d H:i:s");        

        // `image_16x9` = '{$obj->image}',
        // `image_1x1` = '{$obj->image}',
        
        $query = "UPDATE `playlists` SET
                    `title` = '{$obj->title}',
                    `description` = '{$obj->description}',
                    `date_modified` = '{$date_modified}',
                    `visibilty` = '{$obj->visibility}',
                    `type` = '{$obj->type}',
                    `updated_by_user_id` = {$obj->updated_by_user_id},
                    `edited_by_user_id` = {$obj->edited_by_user_id}
                    WHERE `id` = {$obj->id};";


        if(!empty($obj->episodes)){
            self::set_episodes($obj->episodes, $obj->id);
        }else{
            self::delete_from_playlist($obj->id);
        }

        $tags = explode(",",$obj->tags);
        self::set_tags($tags, $obj->id);


        return parent::make_query($query);
    }

    static function update_with_image(stdClass $obj){
        $date_modified = date("Y-m-d H:i:s");        

        $query = "UPDATE `playlists` SET `episodes` = '{$obj->episodes}',
                    `title` = '{$obj->title}',
                    `description` = '{$obj->description}',
                    `date_modified` = '{$date_modified}',
                    `visibilty` = '{$obj->visibility}',
                    `type` = '{$obj->type}',
                    `updated_by_user_id` = {$obj->updated_by_user_id},
                    `tags` = '{$obj->tags}',
                    `image_16x9` = '{$obj->image}',
                    `image_1x1` = '{$obj->image}',
                    `edited_by_user_id` = {$obj->edited_by_user_id}
                    WHERE `id` = {$obj->id};";
        return parent::make_query($query);
    }

    static function get_tags(){
        $query = "SELECT DISTINCT tag FROM `playlist_tags`;";
        return parent::make_query_array($query);
    }
   
    static function get_tags_from(int $object_id){
        $query = "SELECT DISTINCT tag FROM `playlist_tags` WHERE `object_id`={$object_id};";
        return parent::make_query_array($query);
    }


    static function delete_tags_from(int $object_id){
        $query = "DELETE FROM `playlist_tags` WHERE `object_id`={$object_id};";
        return parent::make_query_array($query);
    }

    private static function set_tags(array $tags, int $object_id){
        $query = "DELETE FROM `playlist_tags` WHERE `object_id` = {$object_id};";
        parent::make_query($query);
        
        $query = "INSERT INTO `playlist_tags` ( `object_id`,`tag`) VALUES";

        
        for ($i=0; $i < count($tags); $i++) { 
            $tags[$i] = " ({$object_id},'{$tags[$i]}')"; 
        }
        $query = $query . implode(",",$tags) . ";";
        return parent::make_query($query);
    }
}
