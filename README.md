# KISSGallery
Keep It Stupid Simple Gallery

PHP + javascript gallery in 1 file.  

KISS : https://en.wikipedia.org/wiki/KISS_principle  

## Install
```bash
sudo apt install php-gd
cd /<WHERE_YOUR_IMAGES_ARE>/
wget https://raw.githubusercontent.com/Oros42/KISSGallery/master/index.php
```

# You want more ?

## For uploading files
Add this : https://github.com/Oros42/tiny_DnDUp

## Security ?
### Apache
https://httpd.apache.org/docs/current/howto/htaccess.html  
Set in you /etc/apache2/sites-available/<YOUR_CONF>.conf :  
```
AllowOverride All
```
And  
```bash
cd <YOUR_WWW__GALLERY_DIR>
htpasswdPATH="<SAFE_PLACE>/.htpasswd"
echo "Authtype Basic
Authname 'Who are you ?'
require valid-user
AuthUserFile $htpasswdPATH
" > .htaccess
htpasswd -c $htpasswdPATH <MY_USER_NAME>
```

#### Nginx
https://docs.nginx.com/nginx/admin-guide/security-controls/configuring-http-basic-authentication/
