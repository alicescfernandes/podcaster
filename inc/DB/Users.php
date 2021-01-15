<?php
namespace DB;
include_once("DB.php");

use DB\DB;
use stdClass;

class Users extends DB{
    protected static $table = "users";
    
    static function get_user_by_shortname(string $id){
     $query = 'SELECT * FROM `users` WHERE `short_name` = "'.$id.'";';
     return static::make_query($query);
    }

        
    static function get_user_by_email(string $email){
        $query = 'SELECT * FROM `users` WHERE `email` = "'.$email.'";';
        return static::make_query($query);
       }
   
    static function create_user(stdClass $data){
        $visibility = "1";
        $date_created = date("Y-m-d H:i:s");
        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
        
        $data->created_by_user_id = intval($data->created_by_user_id);
        $query = "INSERT INTO `users`(`role_id`, `short_name`, `full_name`, `avatar_url`, `description`, `visibility`, `date_created`, `created_by_user_id`, `pwd`, `email`) 
                  VALUES ('$data->role_id', '$data->short_name', '$data->full_name', '{$data->file}', '$data->description', '$visibility', '$date_created', $data->created_by_user_id, '$password_hash', '$data->email');";
        static::make_query($query);

        $u = self::get_user_by_shortname($data->short_name);

        $v = $data->token;
        $query = "INSERT INTO `email_validation`(`user_id`,`validation_token`) VALUES ('{$u->id}', '{$v}');";
        return static::make_query($query);  
    }

    static function validate_email($token){
        $query = "UPDATE `email_validation` SET `validated` = '1' WHERE (`validation_token` = '{$token}');";
        return self::make_query($query);

    }
    
    static function get_users_with_roles(){
        $query = "SELECT users.*, roles.machine_name as role_name, roles.id as role_id FROM users LEFT JOIN roles ON users.role_id = roles.id;";
        return static::make_query_array($query);
    }
    


    static function get_users_by_role($machine_name){
        $query = "SELECT users.*, roles.machine_name as role_name, roles.id  as role_id FROM users INNER JOIN roles on users.role_id = roles.id
        WHERE roles.machine_name = '{$machine_name}'";
        return static::make_query_array($query);
    }
   
    static function check_email($id){
        $query = "SELECT validated FROM email_validation where id={$id};";
        return static::make_query($query);
    }
   

    static function update_user($data){
        $query = "UPDATE `users`
                  SET `role_id`='$data->role_id', `full_name` ='$data->full_name', `description` = '$data->description'
                  WHERE `id` = $data->id;";
        return static::make_query($query);
    }

    //TODO: Make query with password update. Remove this call;
    static function update_password($data){
        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);

        $query = "UPDATE `users`
                  SET `pwd`='$password_hash'
                  WHERE `id` = $data->id;";
        return static::make_query($query);
    }
}
