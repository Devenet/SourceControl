# ![Logo of Source Control](https://github.com/nicolabricot/SourceControl/raw/master/manage/assets/default/favicon/favicon.png) SourceControl

Provides an API to update Git repositories and easily manage them.

![Screenshot of Source Control](https://github.com/nicolabricot/SourceControl/raw/master/sourcecontrol.png)

***

## Installation

### Requirements

* The Apache configuration files written on `.htaccess` are for Apache 2.4.
* The application will have right to write in the `data` folder.
* If you want to send email, be sure your server is well configured.

### Source & Setup

The simplest way is to clone the current repository.

```sh
git clone https://github.com/nicolabricot/SourceControl sc
```

Otherwise you can download the last version on the [releases page](https://github.com/nicolabricot/SourceControl/releases), and unzip it as a `sc` folder into your web server root folder.

Then, in the folder `data` copy the `config.default.php` file into `config.php`.

```sh
cp sc/data/config.default.php sc/data/config.php
```

## Application

* The API is available under `sc/api` and is publicly accessible.
* The management page is available under `sc/manage` and is __by default publicly accessible__.  
  We assume that you have this URL under a restrictive area, for example with an Apache authentification.

We recommanded to you to have two virtualhosts or two distinc configuration files:  

1. The first for the API  
  _The URL `server.tld/api` points to the `sc/api` server file_
2. The second for the management page  
  _The URL `server.tld/sourcecontrol` points to the `sc/manage` server file and have an authentification_

With this configuration only the two web folder can be reached throug your web server.
