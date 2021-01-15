
# CHANGELOG - PROGRESS TRACKER 

## 20/06/2020 - Tasks proposed & tasks done
- [x] more on routes: playlist routes

## 19/06/2020 - Tasks proposed & tasks done
- [x] visibility on playlists
- [x] tags on playlists
- [x] duration 

(been doing frontend on the meanwhile)
- [x] setup routes for frontend

## 12/06/2020 - Tasks proposed & tasks done
- [x] Add email config to the setup
- [x] Update the setup sql script to make sure that we have the correct db being created
- [ ] prepare episodes for markers data (make field for this on db & connection class, update field as file (accept only json))


## 12/06/2020 - Tasks proposed & tasks done
- [x] Send mail on account create/remove
- [X] add episodes do playlists & reordering
- [x] email connection class
- [x] emails: on account create & delete


## 11/06/2020 - Tasks proposed & tasks done
- [ ] Send mail on account create/remove
- [ ] add episodes do playlists & reordering
- [x] update roles on users & create user with correct role scope
- [x] profile page (edit current user settings) long name, avatar, description, "delete me & logout"
- [x] add email for users: create & update


## 10/06/2020 - Tasks proposed & tasks done
- [X] avatar (to remove requests from S3)
    https://www.w3schools.com/php/php_file_upload.asp
- [x] Add roles, and setup permissions
- [ ] Send mail on account create/remove
- [ ] add episodes do playlists & reordering
- [x] refactor all id's to make methods in the abstract db class (it should all be just id) 


## 09/06/2020 - Tasks proposed & tasks done
- [X] cover uploading (to remove requests from S3)
    https://www.w3schools.com/php/php_file_upload.asp
    
- [X] write db connecton for episodes
    - [x] create
    - [x] edit
    - [x] delete

## 08/06/2020 - Tasks proposed & tasks done
- [x] Write db connection class for playlists
    - [X] create playlist
    - [x] edit playlist
    - [x] delete playlist

- [x] Write playlists page
    - [X] create playlist
    - [x] edit playlist
    - [x] delete playlist 
    
- [ ] write db connecton for episodes
    - [ ] create
    - [ ] edit
    - [ ] delete

## 31/05/2020 - Tasks proposed & tasks done
- [X] Add email to users
- [ ] Check of existing users upon creation
- [X] add gravatar to users
- [X] test an S3 integration
- [ ] Write db connection class for playlists
    - [ ] create playlist
    - [ ] edit playlist
    - [ ] delete playlist
    
- [ ] write db connecton for episodes
    - [ ] create
    - [ ] edit
    - [ ] delete

- [X] ~~Simple file upload for images & audio~~ (This tasks were scraped in favour of S3 test integration)
    - [x] ~~playlists~~
    - [x] ~~episodes~~


## 30/05/2020 - Tasks proposed & tasks done
- [ ] Write db connection class for playlusts
    - [ ] create playlist
    - [ ] edit playlist
    - [ ] delete playlist
    
- [ ] write db connecton for episodes
    - [ ] create
    - [ ] edit
    - [ ] delete

- [ ] Simple file upload for images & audio
    - [ ] playlists
    - [ ] episodes
- [ ] test an S3 integration for this

## 29/05/2020 - Tasks proposed & tasks done

##### Site config & setup
- [ ] Bullet messages for the php actions
- [ ] Integrate with Users Admin
    - [x] create user
    - [x] delete user
    - [ ] edit user 
- [x] Admin Login/Logout & Session managment
- [x] Admin account creation

##### Tasks related with users:
- [X] Get an user  
- [ ] Update an user  
---

## 28/05/2020 - Tasks proposed & tasks done

##### Site config & setup
- [ ] Integrate with Users Admin
- [ ] Admin Login/Logout
##### Tasks related with users:
- [ ] Update an user  
---

## 27/05/2020 - Tasks proposed & tasks done

##### Site config & setup
- [ ] Setup apache  & ht rules (see from smi tutorial)
- [x] Install composer packages & setup stuff
    - [x] Authentication
- [ ] Setup base dashboard theme
    - [X] Users page 
- [ ] Integrate with Users Admin

##### Tasks related with users:
- [X] Create an user  
- [ ] Update an user  
- [X] Delete an user  
---

## 26/05/2020 - Tasks proposed & tasks done

##### Site config & setup
- [ ] Setup apache  & ht rules (see from smi tutorial)
- [ ] Install composer packages & setup stuff
    - [ ] Authentication
    - [x] Routing
    - [x] Blade
    - [x] S3
    - [x] Imageproxy
- [ ] Setup base dashboard theme

##### Tasks related with users:
- [ ] Create an user  
- [ ] Update an user  
- [ ] Delete an user  
---

## 25/05/2020 - Tasks proposed & tasks done

##### Site config & setup
- [ ] Setup apache  & ht rules (see from smi tutorial)
- [X] Create changelog
- [x] Setup SQL for site setting table
- [x] Setup SQL for roles table
- [X] Setup SQL for users table
- [X] Draw the base config lines & sample htaccess (dependant of setup apache task)
    - [x] Setup db name, 
    - [X] sql server 
    - [X] & user/password
    - [x] test the db setup with a simple query to check if the database exists
    - [x] create aditional tables, run db.sql script
- [ ] Setup base dashboard theme



