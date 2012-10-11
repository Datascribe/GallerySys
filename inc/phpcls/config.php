<?php
/* Database Credidentials Start */
define("SQLHost", "localhost"); // The Host of the MySQL Server
define("SQLUsername",""); // MySQL Username
define("SQLPassword",""); // MySQL Password
define("SQLDatabase",""); // MySQL Database which contains the gallery table

/* Paths */
define("GalleryRoot", "/home/username/public_html/gallery/"); // The Full Path to the gallery directory on the Server
define("GalleryWebRoot", "/gallery/"); // The Web path to the gallery, this would be in relation to the URL e.g. http://example.com/gallery/

/* Gallery Config */
define("ItemsPerRow", 4); // Number of Pictures to display per Row.
define("MaxRows", 5); // Maximum number of Row for the purpose of Pagnation (Not Implimented)
define("AuthPass", "1234"); // The password to login to the Admin Area
define("PayPalEmail", "info@datascribe.co.uk"); // The PayPal Business E-Mail Address in order to use PayPal's Shopping Cart
define("HidePrivate", true); // Whether Private Galleries should be Hidden and the login area displayed.

/* Watermark Settings */
define("WMFontSize", 80); // The Font Size in Pixels for the Watermark
define("WMText", "eyekiss"); // The Text on the Watermark
define("WMWidth", 900); // The Maximum Width the image is to be resized to in Pixels
define("WMHeight", 600); // The Maximum Height the image is to be resized to in Pixels (Mainly Applies to Portrait Pictures)

?>
