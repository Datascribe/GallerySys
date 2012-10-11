/**
 * General Javascript for the Admin Area
 * Author: Adam Prescott <adam.prescott@datascribe.co.uk>
 */
$(document).ready(function() {
    $('.procAlbum').click(function() {
        $('#gallst').fadeOut("normal", function() {
            $(this).html("<h1>The Thumbnails are being created, Watermarks are being added and images are being resized for web.</h1><h3>This may take several minutes depending on how many images are in the Album.<br>You will be redirected to the Album upon completion.<br>If you loose connection or you close this page, the Album will still be generated so please</h3><h1>DO NOT resubmit this Album</h1><h1>DO NOT Refresh this page.</h1>");
            $(this).fadeIn();
        });
    });
    
});