GallerySys (To be named)
========================
This is a Photo Gallery initially written for http://eyekiss.net
You can see it in action by visiting the [Gallery Section](http://eyekiss.net/gallery/) of the site.

Additionally, you can also see the workflow of the Gallery in this [Imgur Gallery](http://imgur.com/a/twAHD) though there
are some additional cosmetic features that have been added since.

The Purpose of the Gallery is to check for new folders within the /albums/ directory and
create thumbnails as well as add watermarks to the images with the selected directory.


Dependancies
------------
* GD Image Library -- This system has only been written to handle JPGs though if you would like to handle additional formats.
  be sure to edit the functions.class.php file for thumbnail and watermark generation.
* MySQL -- This Gallery uses MySQL to store the processed galleries as well as which galleries are private and their corrosponding passwords.
* A Basic knowledge of HTML -- This is required if you would like the change the gallery template.

Setup
-----
### MySQL Setup
Firstly be sure to setup your MySQL database, this only requires one table which you can create with the below statement:
```SQL
CREATE TABLE IF NOT EXISTS `gallery` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(70) NOT NULL,
`folder` varchar(70) NOT NULL,
`isPrivate` tinyint(1) NOT NULL default '0',
`password` varchar(50) default NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
```
### Configuration
Then you will need to edit the config file which is located at /inc/phpcls/config.php, the following things are REQUIRED otherwise the system will not work:
* Database Credidentials
* Paths -- Both the GalleryRoot and the GalleryWebRoot need to be set.
* AuthPass -- This is the Login password for your Administration Area (Located at index.php?mode=admin), the default is 1234.
The other varibles within the config file are not as critical but would be best changing. All of the different varibles are documented within the config file.

Also be sure to CHMOD /albums/ 777 though this won't matter if PHP is running under the same user account which owns the files.

### Templates
To change the layout of the gallery, the template file is located in /inc/phpcls/template/template.html

Usage
-----
Once you have succesfully setup the Gallery:

1.  Upload a test folder with some JPGs to /albums/
2.  Login to the Admin Area, this would be located at http://youdomain.com/gallery/index.php?mode=admin or what ever GalleryWebRoot you've setup for it.
3.  Add the details for your Album and Process the New Album. This will create a thumbs directory inside your uploaded folder and once they are created
    The uploaded images will be resized and the watermarks added. Be sure to grab a beverage of your choice, I like a good whisky :D
4.  Bask in the glory of your new Gallery :D

Issues and Contributing to this Project
----------------------------------------
If you have and Issues, be sure to log it in the [Issues Section](https://github.com/Datascribe/GallerySys/issues) of this project, whoever is involved with
the development will get to you when possible.

Also if you would like to Contribute to this project:
* Be sure to Fork this project.
* Make your changes.
* Then Submit a Pull Request.
* Whoever has Push access to this Project will review the request and merge it.
You'll also receive a big thankyou and will be added to the list of contributors below :D

Contributors
------------
[Adam Prescott](https://github.com/adamprescott) -- Wrote the Initial Project.