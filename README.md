# KISSGallery
Keep It Stupid Simple Gallery
  
PHP + javascript gallery in 1 file.  
  
Demo : https://oros42.github.io/KISSGallery/index.html
  
KISS : https://en.wikipedia.org/wiki/KISS_principle  

## Install
```bash
sudo apt install php-gd
cd /<WHERE_YOUR_IMAGES_ARE>/
wget https://raw.githubusercontent.com/Oros42/KISSGallery/master/index.php
```

## Recommendation, resize images you share
Install imagemagick
```bash
sudo apt install imagemagick
```
Resize any «.jpg» in 2000x2000px :
```bash
# /!\ This line modify files !
for i in *.{jpg,JPG,jpeg}; do convert "$i" -resize 2000x2000 -strip -interlace Plane -auto-orient "${i}"; done
```
Adapte this to your needs.  

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
