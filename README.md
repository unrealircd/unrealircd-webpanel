 ## UnrealIRCd Administration WebPanel
 
[![Version](https://img.shields.io/badge/UnrealIRCd-6.0.6_or_later-darkgreen.svg)]()
[![Version](https://img.shields.io/badge/Version-Beta-blue.svg)]()
[![Maintained](https://img.shields.io/badge/Maintained-yes-darkgreen.svg)]()
[![Unreal](https://img.shields.io/badge/PHP-8.0_or_later-darkgreen.svg)](https://unrealircd.org)

 Are you tired of managing your IRC network through a command line interface? Do you wish there was a more user-friendly way to keep track of your servers, channels, and users? Look no further than the UnrealIRCd Web Panel!

This web-based tool provides a comprehensive overview of your IRC network, including all channels and users, as well as detailed information about individual servers and users. With the UnrealIRCd Web Panel, you can easily add and remove server bans and spamfilter entries, rehash your entire network, and filter lists of information based on input criteria, all from the convenience of your web browser.

One of the key features of the UnrealIRCd Web Panel is its user-friendly interface. The panel is designed to be easy to navigate, with all the information you need displayed in an organized and easy-to-understand format. This makes it much easier to manage your network, especially if you have multiple servers or a large number of users.

Additionally, the UnrealIRCd Web Panel is a great tool for keeping track of your network's activity. You can view detailed information about the channels and users on your network as well as manage your servers. This allows you to quickly identify and address any issues that may arise.

Overall, the UnrealIRCd Web Panel is a must-have tool for any administrator managing an IRC network. Its user-friendly interface and powerful management capabilities make it easy to keep track of your network and ensure that everything is running smoothly. So why not give it a try and see how it can improve your IRC network management experience?

## Example Overview
 <img src="https://i.ibb.co/7SdFZnk/Screenshot-from-2023-01-14-07-26-21.png">

## Prerequisites ##
- PHP 8 or later
- A webserver
- UnrealIRCd 6.0.6-git recommended. Minimal functionality available with UnrealIRCd 6.0.5.

Note: PHP 8 may require special installation instructions.
Here are some instructions for:
- Ubuntu 22.04: `apt-get install apache2 libapache2-mod-php`
- [Ubuntu 20.04](https://linuxhint.com/install-php-ubuntu/)
- [Debian](https://www.vultr.com/docs/how-to-install-php-8-on-debian-11/)
- [CentOS](https://www.tecmint.com/install-php-8-on-centos/)

For more installation methods for PHP 8, make a websearch for how to install PHP 8 on your operating system.
## Installation ##

Note: This instructional assumes this is going to be hosted in the webroot directory `/var/www/` (html/) directory.
You may want to hide this somehow. Well, you definitely should. I don't know how to do that though. Sorry.

Please make sure you have [correctly setup UnrealIRCd for use with JSON-RPC](https://www.unrealircd.org/docs/JSON-RPC) before you continue.

- Go to your webserver root, for example `/var/www/html/`, and clone
  this repository:
```
cd /var/www/html
git clone https://github.com/unrealircd/unrealircd-webpanel
```

- Go into the directory and run composer to install the dependencies
  (If you don't have composer, then [install it](https://getcomposer.org/download/) first):
```
cd unrealircd-webpanel
composer install
```

- Edit the configuration file
```
cp config.php.sample config.php
nano config.php
```
The file will look like this:<br>
<img src="https://i.ibb.co/zZ7LsXD/Screenshot-from-2023-01-04-17-03-20.png">

Edit the configuration file to match your UnrealIRCd's RPC credentials and save

NOTE: You most likely want to put the webpanel behind a login, using a
`.htaccess` file or similar.

## Updating for end-users ##
For end-users, when you want to update to the latest version:
```bash
git pull
composer install
```

## Developers ##
Developers of the webpanel will naturally use the same procedure as
above. However, sometimes you will want to update to a newer version
of the unrealircd-rpc-php library. You then need to run:
```bash
# For devs only!
composer update
git commit composer.lock
```
Commiting the composer.lock file updates the dependency for all
other users, that way a `composer update` by end-users will update
to exactly the version that `composer install` just installed.
