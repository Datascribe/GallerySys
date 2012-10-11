<?php

/**
 * This class contains all the functions needed for the image resizing, thumbnail creation, watermark adding and also removal of Albums
 * @author Adam Prescott <adam.prescott@datascribe.co.uk>
 */
class galFunc {
    
    /**
     * Generates Square cropped Thumbnails at 120px X 120px
     * @param string $dirname The directory which has all the jpg files where thumbnails need to be created
     * @return boolean Returns true when completed.
     * @throws Exception when the image can't be read.
     * @throws Exception when the new thumbnail can't be saved
     */
    static function makeThumbs($dirname="") {
        set_time_limit(0);
        $folder = $dirname;
        if(!file_exists("{$folder}/thumbs")) {
            mkdir("{$folder}/thumbs" , 0777);
        }
        
        $pattern="(\.jpg$)|(\.jpeg$)"; //valid image extensions
        $handle  = opendir($dirname);
        while(false !== ($filename = readdir($handle))) {
            if(eregi($pattern, $filename)){ //if this file is a valid image
                $files[] = $filename;
            }
        }
        if (count($files)<>0) {
            sort($files);
        }

        $curimage=0;

        while($curimage !== count($files)){
            $cropfile=$dirname.'/'.$files[$curimage];
            //echo '<br>'.$cropfile;
            $source_img = @imagecreatefromjpeg($cropfile); //Create a copy of our image for our thumbnails...
            if (!$source_img) {
                throw new Exception("Thumbnail Creation - Could not create image handle.");
            }
            $new_w = 120;
            $new_h = 120;

            $orig_w = imagesx($source_img);
            $orig_h = imagesy($source_img);

            $w_ratio = ($new_w / $orig_w);
            $h_ratio = ($new_h / $orig_h);

            if ($orig_w > $orig_h ) {//landscape from here new
                $crop_w = round($orig_w * $h_ratio);
                $crop_h = $new_h;
                $src_x = ceil( ( $orig_w - $orig_h ) / 2 );
                $src_y = 0;
            } elseif ($orig_w < $orig_h ) {//portrait
                $crop_h = round($orig_h * $w_ratio);
                $crop_w = $new_w;
                $src_x = 0;
                $src_y = ceil( ( $orig_h - $orig_w ) / 2 );
            } else {//square
                $crop_w = $new_w;
                $crop_h = $new_h;
                $src_x = 0;
                $src_y = 0;
            }
            $dest_img = imagecreatetruecolor($new_w,$new_h);
            imagecopyresampled($dest_img, $source_img, 0 , 0 , $src_x, $src_y, $crop_w, $crop_h, $orig_w, $orig_h); //till here
            if(imagejpeg($dest_img, $dirname."/thumbs/".$files[$curimage])) {
                imagedestroy($dest_img);
                imagedestroy($source_img);
            } else {
                throw new Exception("Thumbnail Creation - Could not make thumbnail image");
            }
            $curimage++;
        }
        return true;
    }
    
    /**
     * Generates watermarks against jpgs within a given directry based from a given TTF Font File
     * @param string $directory The directory which has all the jpg files where watermarks need to be added
     * @return boolean true on completion
     * @throws Exception if the image can't be overwritten/saved
     */
    static function addWatermark($directory){
        ini_set('memory_limit','200M');
        if ($handle = opendir($directory)) {
            $localJpgs = array();
            $pattern="(\.jpg$)|(\.jpeg$)";
            while (false !== ($file = readdir($handle))) {
                if (eregi($pattern, $file)) {
                    $localJpgs[] = $file;
                }
            }
            closedir($handle);
            
            $font_path = GalleryRoot.'inc/phpcls/GILSANUB.TTF';
            $font_size = WMFontSize;
            $water_mark_text_2 = WMText;
            $water_mark_text_1 = WMText;
            
            foreach ($localJpgs as $oldimage_name) {
                $new_image_name = $oldimage_name = $directory.'/'.$oldimage_name;
                list($owidth,$oheight) = getimagesize($oldimage_name);
                //$width = $height = 300;
                $width = WMWidth;
                $calcheight = floor($oheight * ($width / $owidth));
                if($oheight > $owidth && $calcheight > WMHeight) {
                    $height = WMHeight;
                    $width = floor($owidth * (WMHeight / $oheight));
                } else {
                    $height = $calcheight;
                }
                
                if($oheight > $owidth) {
                    $txtX = intval($width / 8);
                } else {
                    $txtX = intval($width / 3);
                }
                $txtY = intval((($height - (($height / 3) * 2)) / 2) + (($height / 3) * 2));
                $image = imagecreatetruecolor($width, $height);
                $image_src = imagecreatefromjpeg($oldimage_name);
                imagecopyresampled($image, $image_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
            // $black = imagecolorallocate($image, 0, 0, 0);
                $blue = imagecolorallocatealpha($image, 63, 124, 181, 60);
            // imagettftext($image, $font_size, 0, 30, 190, $black, $font_path, $water_mark_text_1);
                imagettftext($image, $font_size, 45, $txtX, $txtY, $blue, $font_path, $water_mark_text_2);
                if(imagejpeg($image, $new_image_name, 100)) {
                    imagedestroy($image);
                    //unlink($oldimage_name);
                } else {
                    echo $new_image_name;
                    throw new Exception("Watermark Creation Failed.");
                }
            }
            return true;
        }
    }
    
    /**
     * Recursivly removes a given Directory and all files/folders within it
     * @param string $dir The Directory to be removed
     */
    static function rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                galFunc::rrmdir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

}
?>
