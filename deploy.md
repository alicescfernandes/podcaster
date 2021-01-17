# Steps to deploy this website
For apache2 & mysql. Guide for EC2 with Ubuntu

## Requirements
- Amazon S3 Bucket
- Amazon Root Key
- Gmail account with F2A auth. Also generate an app password
- Terminal with Vim or Nano, because you don't have access a visual IDE 

## Instalation
1. Create a EC2 Instance
2. Install apache2, php & mysql
3. On apache2 do the following:  
    1. Enable the mod_rewritee
    2. Add AllowOverride rule to the default config
    3. If you get a SimpleXML error, install SimpleXML   
        ```sudo apt install php7-xml```   
        ```sudo apt-get install php-xml```
    4. Install GD  
    ```sudo apt-get install php-gd```
    ```sudo apt install php-gd```
4. On your `php.ini` do the following:  
    1. change `max_upload_size` to `256M`
    2. change `max_upload_size` to `256M`
    3. set `short_open_tag` to `On`
5. Restart your apache2  
```sudo systemctl restart apache2```
6. On mySQL create a user, with write privileges