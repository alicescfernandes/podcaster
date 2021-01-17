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
        ```sudo apt install php-xml```   
        ```sudo apt-get install php-xml```
    4. Install GD  
    ```sudo apt-get install php-gd```
    ```sudo apt install php-gd```
4. On your `php.ini` do the following:  
    1. change `max_upload_size` to `256M`
    2. change `max_upload_size` to `256M`
    3. set `short_open_tag` to `On`
5. In your PHP do:
    1. Enable mod_ssl  
    `sudo a2enmod ssl`
    2. Enable mod_rewrite   
    `sudo a2enmod rewrite`
5. On MySQL create a user, with write privileges
6. Setup HTTPS
    1. Get a SSL certificate
    2. Install the certs on apache  
        https://help.zerossl.com/hc/en-us/articles/360058295854-Installing-SSL-Certificate-on-Apache
    3. Copy the certs, and configure apache to use those certs

7. Restart your apache2  
```sudo systemctl restart apache2```