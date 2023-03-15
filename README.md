 ## UnrealIRCd Administration WebPanel
 
[![Version](https://img.shields.io/badge/UnrealIRCd-6.0.6_or_later-darkgreen.svg)]()
[![Version](https://img.shields.io/badge/Version-Beta-blue.svg)]()
[![Maintained](https://img.shields.io/badge/Maintained-yes-darkgreen.svg)]()
[![Unreal](https://img.shields.io/badge/PHP-8.0_or_later-darkgreen.svg)](https://unrealircd.org)

The UnrealIRCd Administration WebPanel gives you an overview of your IRC network,
with detailed information about servers, users and channels.
You can easily add and remove server bans, spamfilters and do other administrative tasks,
all from the convenience of your web browser.

## Example Overview from Desktop
 <img src="https://i.ibb.co/7SdFZnk/Screenshot-from-2023-01-14-07-26-21.png">
 <img src="https://i.ibb.co/6vQ6wg7/spamfilter.png">

## Example Overview from Mobile
<div class="row">
<img src="https://i.ibb.co/KGLdB43/Screenshot-20230123-233804-Chrome.jpg" height="30%" width="30%">
<img src="https://i.ibb.co/tB980kd/Screenshot-20230124-000204-Chrome.jpg" height="30%" width="30%">
</div>

## Prerequisites ##
- UnrealIRCd 6.0.6 or later
- A webserver (e.g. apache or nginx)
- PHP 8 or later

Note: PHP 8 may require special installation instructions.
Here are some instructions for:
- Ubuntu 22.04: `apt-get install apache2 libapache2-mod-php`
- [Ubuntu 20.04](https://linuxhint.com/install-php-ubuntu/)
- [Debian](https://www.vultr.com/docs/how-to-install-php-8-on-debian-11/)
- [CentOS](https://www.tecmint.com/install-php-8-on-centos/)

For more installation methods for PHP 8, make a websearch for how to install PHP 8 on your operating system.
## Installation ##

Note: This instructional assumes this is going to be hosted in the webroot directory `/var/www/` (html/) directory.

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
Edit the configuration file to match your UnrealIRCd's RPC credentials and save

IMPORTANT: You will want to put the webpanel behind a login, using a
`.htaccess` file or similar. Don't open it up to the world!

## Updating for end-users ##
For end-users, when you want to update to the latest version:
```bash
git pull
composer install
```

## Authentication
Right now, by default everyone being able to access the URL can use the
webpanel. In future versions this will no longer be the case.

### SQL Authentication
First, create a database and a user in MySQL or MariaDB. Typical commands to
do so are:

```sql
CREATE DATABASE unrealircdwebpanel;
CREATE USER 'unrealircdwebpanel'@'localhost' IDENTIFIED BY 'enter-some-random-password-here';
GRANT ALL ON unrealircdwebpanel.* TO 'unrealircdwebpanel'@'localhost';
```

Now, open your `config.php` and in the define PLUGINS section load the `sql_auth`
module by uncommenting the // from the `"sql_auth"` line.
And fill in the information for your SQL setup, so set `SQL_IP`,
`SQL_DATABASE`, `SQL_USERNAME` and `SQL_PASSWORD` properly.

Finally, configure a first admin user by setting the information in
`SQL_DEFAULT_USER`. This will be added to the SQL database first time you
start the panel.

Then, surf to the webpanel page and you should see a login page. If you
don't see a login page then you have not loaded the sql_auth plugin
properly.

Once succesfully logged in you should remove the `SQL_DEFAULT_USER` item
again from your `config.php` because it is no longer needed.

You can now add and delete users through the `Panel Access` tab.

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
