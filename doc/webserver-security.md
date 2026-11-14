# Web server settings for the UnrealIRCd webpanel

The panel keeps its secrets in two PHP files:

* `config/config.php`: database credentials, the password pepper and the encryption key
* `data/database.php`: the file database (when using the `file_db` backend)

These are only safe because the web server *executes* them as PHP, which prints nothing.
A copy with another extension, like an editor backup `config.php~` or
`config.php.bak`, would be sent to anyone as plain text. The same goes for the
`.git/` directory of a git checkout.

## Apache

The `.htaccess` files that come with the panel block `config/`, `data/`,
backup and swap files, and `.git/`. They only work when Apache allows them:

```apache
<Directory /var/www/html/unrealircd-webpanel>
    AllowOverride All
</Directory>
```

Debian and Ubuntu ship with `AllowOverride None` for `/var/www`, so check this.

## nginx

nginx ignores `.htaccess` files. Add this to the `server { }` block of the panel
(adjust the path prefix if the panel is not at `/unrealircd-webpanel/`):

```nginx
# Secrets and internal files of the UnrealIRCd webpanel
location ~ ^/unrealircd-webpanel/(config|data|vendor|tests|\.git|\.prism)(/|$) { deny all; return 404; }
location ~ (~|\.(bak|old|orig|save|swp|swo|tmp|dist|inc|sql|log)|#)$ { deny all; return 404; }
location ~ /(composer\.(json|lock)|Dockerfile|phpunit\.xml|\.gitignore|\.gitattributes)$ { deny all; return 404; }
```

The Docker image of the panel already contains these rules.

## Docker

All settings and accounts live in `config/` and `data/`. Keep them on volumes,
otherwise a rebuilt container starts without a configuration and the
installer is open again for anyone who reaches the panel first:

```sh
docker run -v panel-config:/var/www/html/config -v panel-data:/var/www/html/data ...
```

## The panel checks this too

On the Overview page, users who can manage the panel get a warning when
`config/` or `data/` contain files that are not supposed to be there (such
as backup copies), or when an old `config.php` is still in the panel's root
directory.
