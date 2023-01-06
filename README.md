# unrealircd-webpanel
 UnrealIRCd Administration WebPanel
 
 <img src="https://i.ibb.co/7jtddG4/Screenshot-from-2022-12-31-04-53-35.png">

## Prerequisites ##
- PHP 7 or later
- A webserver
- UnrealIRCd 6.0.5

## Installation ##

Note: This instructional assumes this is going to be hosted in the webroot directory `/var/www/` (html/) directory.
You may want to hide this somehow. Well, you definitely should. I don't know how to do that though. Sorry.

Please make sure you have [correctly setup UnrealIRCd for use with JSON-RPC](https://www.unrealircd.org/docs/JSON-RPC) before you continue.

- First, if you have just installed apache2 or something else, remove the `html` directory and navigate to `/var/www/`
```
cd /var/www/
rm -rf html
```

- Clone this repository to a new directory called `html`
```
git clone https://github.com/ValwareIRC/unrealircd-webpanel html
```

- Install the required dependencies using composer:
```
composer install
```
(If you don't have composer, then [install it](https://getcomposer.org/download/) first)

- Move into the `html` directory and edit the configuration file
```
cd html
cp config.php.sample config.php
nano config.php
```
The file will look like this:<br>
<img src="https://i.ibb.co/zZ7LsXD/Screenshot-from-2023-01-04-17-03-20.png">

Edit the configuration file to match your UnrealIRCd's RPC credentials and save

