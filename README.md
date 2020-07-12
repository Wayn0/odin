Odin php framework
====

### NO LONGER MAINTAINED

Odin is a *very basic* PHP framework. 
It allows _*me*_ to rapidly prototype web applications. 
If it helps you to... enjoy :)


### Requirements

* Web server with rewrite
* composer
* php > 7.0 
* php PDO

See the wiki for configuration options.

### Quick Setup

#### DB

```
CREATE DATABASE meh
CREATE USER 'foo'@'localhost' IDENTIFIED BY 'barr';
GRANT ALL PRIVILEGES ON foo.* TO 'meh'@'localhost';
```

#### Debian / Ubuntu

```
git clone https://github.com/Wayn0/odin.git
cd odin
mkdir vendor
mkdir logs
echo '{}' > composer.lock
chown www-data vendor logs composer.lock
cp app/config/config.inc.php.example app/config/config.inc.php
$EDITOR app/config/config.inc.php
```

#### Redhat / Centos

```
git clone https://github.com/Wayn0/odin.git
cd odin
mkdir vendor
mkdir logs
echo '{}' > composer.lock
chown apache vendor logs composer.lock
cp app/config/config.inc.php.example app/config/config.inc.php
$EDITOR app/config/config.inc.php
```

### Thanks

* @davidtmiller for [startbootstrap-sb-admin-2](https://github.com/BlackrockDigital/startbootstrap-sb-admin-2)
