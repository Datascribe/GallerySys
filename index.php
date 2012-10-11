<?php
/**
 * The License within LICENSE.txt applies to all the files within this project.
 * @author Adam Prescott <adam.prescott@datascribe.co.uk>
 */
require_once 'inc/phpcls/config.php';
require_once GalleryRoot.'inc/phpcls/db.class.php';
require_once GalleryRoot.'inc/phpcls/template/template.class.php';
require_once GalleryRoot.'inc/phpcls/functions.class.php';

try {
    switch ($_GET['mode']) {
        case 'admin':
            $DB = new DB();
            $temp = new siteTemplate();
            $temp->titlePrefix("Eyekiss Gallery");
            $temp->titleSep(" - ");
            $temp->addHead('<link rel="stylesheet" href="'.GalleryWebRoot.'style.css" type="text/css" media="screen">');
            $temp->addJS(GalleryWebRoot.'inc/js/admin.js');
            switch ($_GET['action']) {
                case 'process':
                    @session_start();
                    if($_SESSION['auth']) {
                        $folder = $_POST['folder'];
                        $name = $_POST['name'];
                        $password = $_POST['password'];
                        $isPrivate = $_POST['isPrivate'] == "true" ? 1 : 0;

                        if($folder != "" && $name != "") {
                            if(is_dir(GalleryRoot.'albums/'.$folder)) {
                                if(galFunc::makeThumbs(GalleryRoot.'albums/'.$folder)) {
                                    if(galFunc::addWatermark(GalleryRoot.'albums/'.$folder)) {
                                        $addQ = $DB->doQuery("INSERT INTO `gallery` (`id`,`name`,`folder`,`isPrivate`,`password`) VALUES (NULL,'%s','%s','%s','%s');",
                                                $name, $folder, $isPrivate, $password);
                                        if($addQ) {
                                            header('Location: ?id='.  mysql_insert_id());
                                        }
                                    }
                                }
                            } else {
                                $d .= "<h3>The Folder \"{$folder}\" doesn't exist, please make sure the directory exists.</h3>";
                                $d .= '<a href="?mode=admin">Click to go Back</a>';
                                $temp->content($d);
                                $temp->finish();
                            }
                        } else {
                            $d .= "<h3>Please enter a \"Name to Display\" for the folder \"{$folder}\".</h3>";
                            $d .= '<a href="?mode=admin">Click to go Back</a>';
                            $temp->content($d);
                            $temp->finish();
                        }
                    } else {
                        header("Location: ?");
                    }
                    break;
                case 'update':
                    @session_start();
                    if($_SESSION['auth']) {
                        $id = $_POST['id'];
                        $name = $_POST['name'];
                        $password = $_POST['password'];
                        $isPrivate = $_POST['isPrivate'] == "true" ? 1 : 0;

                        if($_POST['submit']=="Delete Album") {
                            if(is_numeric($id)) {
                                if($_POST['del']=="Yes") {
                                    $folderQ = $DB->doQuery("SELECT * FROM `gallery` WHERE `id` = '%s';", $id);
                                    if($DB->numRows($folderQ) == 1) {
                                        $f = $DB->getColumns($folderQ);
                                        $dir = GalleryRoot.'albums/'.$f['folder'];
                                        $delQ = $DB->doQuery("DELETE FROM `gallery` WHERE `id` = '%s' LIMIT 1", $f['id']);
                                        if($delQ) {
                                            galFunc::rrmdir($dir);
                                            header('Location: ?mode=admin');
                                        }
                                    } else {
                                        $SubTitle = "An Error Has occured, An Album With ID \"{$id}\" does not Exist";
                                        $temp->titlePage($SubTitle);
                                        $temp->custom("SUBTITLE", $SubTitle);
                                        $d .= '<a href="?mode=admin">Go Back</a>';
                                        $temp->content($d);
                                        $temp->finish();
                                    }
                                } elseif($_POST['del']=="No") {
                                    header('Location: ?mode=admin');
                                } else {
                                    $SubTitle = "Are you Sure You Want to Delete \"{$name}\"?";
                                    $temp->titlePage($SubTitle);
                                    $temp->custom("SUBTITLE", $SubTitle);
                                    $d .= '<form method="post" action="?mode=admin&action=update">';
                                    $d .= '<input type="hidden" name="id" value="'.$id.'">';
                                    $d .= '<input type="hidden" name="submit" value="Delete Album">';
                                    $d .= '<input type="submit" name="del" value="Yes"> <input type="submit" name="del" value="No"></form>';
                                    $temp->content($d);
                                    $temp->finish();
                                }
                            } else {
                                header('Location: ?mode=admin');
                            }
                        } elseif($_POST['submit']=="Update Album") {
                            if(is_numeric($id)) {
                                if($name != "") {
                                    $updateQ = $DB->doQuery("UPDATE `gallery` SET `name` = '%s', `password` = '%s', `isPrivate` = '%s' WHERE `id` = '%s' LIMIT 1;",
                                            $name, $password, $isPrivate, $id);
                                    if($updateQ) {
                                        header('Location: ?mode=admin');
                                    }
                                } else {
                                    $d .= "<h3>Please enter a \"Name to Display\" for the Album.</h3>";
                                    $d .= '<a href="?mode=admin">Click to go Back</a>';
                                    $temp->content($d);
                                    $temp->finish();
                                }
                            } else {
                                header('Location: ?mode=admin');
                            }
                        } else {
                            header('Location: ?mode=admin');
                        }
                    } else {
                        header('Location: ?');
                    }
                    break;
                default:
                    @session_start();
                    if($_SESSION['auth'] || $_POST['authpass'] == AuthPass) {
                        $_SESSION['auth'] = true;
                        $temp->titlePage("Admin Area");
                        $lst = $DB->doQuery("SELECT * FROM `gallery`;");
                        $DBDirs = array();
                        while($r = $DB->getColumns($lst)) {
                            $DBDirs[] = $r['folder'];
                        }
                        if ($handle = opendir(GalleryRoot.'albums/'.$dir['folder'])) {
                                $localDirs = array();
                                while (false !== ($file = readdir($handle))) {
                                    if (is_dir(GalleryRoot.'albums/'.$file) && $file != "." && $file != "..") {
                                        $localDirs[] = $file;
                                    }
                                }
                                closedir($handle);
                                $lCount = count($localDirs);
                                $DBCount = count($DBDirs);
                                $currow = 0;
                                if($lCount > $DBCount) {
                                    $d .= $lCount - $DBCount.' new folder(s) detected.';
                                    $temp->content($d);
                                    unset($d);
                                    foreach($localDirs as $dir) {
                                        if(!in_array($dir, $DBDirs)) {
                                            if($currow % 3 == 0) $d .= '<tr>';
                                            $currow++;
                                            $d .= '<th>';
                                            $d .= '<form method="POST" action="?mode=admin&action=process">';
                                            $d .= '<table cellspacing="0" cellpadding="5" class="dataset2">';
                                            $d .= '<tr><th colspan="2"><img src="'.GalleryWebRoot.'inc/img/foldernew.png'.'" alt="New" title="New"><br>New Gallery</th></tr>';
                                            $d .= '<tr><th align="right">Folder</th><td>'.$dir.'<input type="hidden" name="folder" value="'.$dir.'"></td></tr>';
                                            $d .= '<tr><th align="right">Name to Display</th><td><input type="text" name="name"></td></tr>';
                                            $d .= '<tr><th align="right">Password</th><td><input type="text" name="password"></td></tr>';
                                            $d .= '<tr><th align="right">Private</th><td><input type="checkbox" name="isPrivate" value="true"></td></tr>';
                                            $d .= '<tr><th colspan="2"><input type="submit" class="procAlbum" name="Submit" value="Process New Album"></th></tr>';
                                            $d .= '</table></form>';
                                            $d .= "</th>\n";
                                            if($currow % 3 == 0) $d .= "</tr>\n\n";
                                        }
                                    }
                                }
                                $lst = $DB->doQuery("SELECT * FROM `gallery`;");
                                while($r = $DB->getColumns($lst)) {
                                    if($currow % 3 == 0) $d .= '<tr>';
                                    $currow++;
                                    $isPrivate = $r['isPrivate'] == 1 ? " checked" : "";
                                    $d .= '<th>';
                                    $d .= '<form method="POST" action="?mode=admin&action=update">';
                                    $d .= '<table cellspacing="0" cellpadding="5" class="dataset2">';
                                    $d .= '<tr><th colspan="2"><a class="gallink" href="?id='.$r['id'].'" target="_blank"><img src="'.GalleryWebRoot.'inc/img/folder.png'.'" alt="Click to go to Gallery" title="Click to go to Gallery"><br>Click to go to Gallery</a></th></tr>';
                                    $d .= '<tr><th align="right">Folder</th><td>'.$r['folder'].'<input type="hidden" name="id" value="'.$r['id'].'"></td></tr>';
                                    $d .= '<tr><th align="right">Name to Display</th><td><input type="text" name="name" value="'.$r['name'].'"></td></tr>';
                                    $d .= '<tr><th align="right">Password</th><td><input type="text" name="password" value="'.$r['password'].'"></td></tr>';
                                    $d .= '<tr><th align="right">Private</th><td><input type="checkbox" name="isPrivate"'.$isPrivate.' value="true"></td></tr>';
                                    $d .= '<tr><th colspan="2"><input type="submit" name="submit" value="Update Album"><input type="submit" name="submit" value="Delete Album"></th></tr>';
                                    $d .= '</table></form>';
                                    $d .= "</th>\n";
                                    if($currow % 3 == 0) $d .= "</tr>\n\n";
                                }
                                $temp->custom("GALTBL", $d);
                                $temp->finish();
                            } else {
                                throw new Exception('Error Reading Directory');
                            }
                        } else {
                            $temp->titlePage("Please enter your password");
                            $d = '<form method="post" action="?mode=admin">';
                            $d .= '<b>Password: </b><input type="password" name="authpass"><br><input type="submit" name="submit" value="Login"></form>';
                            $temp->content($d);
                            $temp->finish();
                        }
                        break;
            }
            break;
        case 'logout':
            @session_start();
            session_destroy();
            header('Location: ?');
            break;
        case 'login':
            @session_start();
            $DB = new DB();
            $temp = new siteTemplate();
            $temp->titlePrefix("Eyekiss Gallery");
            $temp->titleSep(" - ");
            $temp->addHead('<link rel="stylesheet" href="'.GalleryWebRoot.'style.css" type="text/css" media="screen">');
            if(isset($_POST['password'])) {
                $lookupQ = $DB->doQuery("SELECT * FROM `gallery` WHERE `password` = '%s';", $_POST['password']);
                if($DB->numRows($lookupQ) == 1) {
                    $res = $DB->getColumns($lookupQ);
                    $_SESSION['uauth'] = $res['password'];
                    header('Location: ?id='.$res['id']);
                } else {
                    $temp->titlePage("That password does not exist.");
                    $temp->custom("SUBTITLE", "That password does not exist.<br>Please Enter Your Password.");
                    $d .= '<form method="post" action="?mode=login">';
                    $d .= '<input type="password" name="password"><br><input type="submit" name="submit" value="Login"></form>';
                    $temp->content($d);
                    $temp->finish();
                }
            } else {
                $SubTitle = "Please Enter Your Password.";
                $temp->titlePage($SubTitle);
                $temp->custom("SUBTITLE", $SubTitle);
                $d .= '<form method="post" action="?mode=login">';
                $d .= '<input type="password" name="password"><br><input type="submit" name="submit" value="Login"></form>';
                $temp->content($d);
                $temp->finish();
            }
            break;
        default:
            $DB = new DB();
            $temp = new siteTemplate();
            $temp->titlePrefix("Eyekiss Gallery");
            $temp->titleSep(" - ");
            $temp->addHead('<link rel="stylesheet" href="'.GalleryWebRoot.'style.css" type="text/css" media="screen">');

            if($_GET['id'] != "" && is_numeric($_GET['id'])) {
                $dirq = $DB->doQuery("SELECT * FROM `gallery` WHERE `id` = '%s';", $_GET['id']);
                $dir = $DB->getColumns($dirq);

                if($DB->numRows($dirq) < 1) {
                    header('Location: ?');
                    die('THOUGH SHALL NOT PASS!!');
                }
                @session_start();
                if($dir['isPrivate'] == 1 && $_SESSION['uauth'] != $dir['password']) {
                    if($_POST['password'] == $dir['password']) {
                        $_SESSION['uauth'] = $dir['password'];
                        header('Location: ?id='.$_GET['id']);
                    } else {
                        $SubTitle = "Please Enter the Password for \"{$dir['name']}\"";
                        $temp->titlePage($SubTitle);
                        $temp->custom("SUBTITLE", $SubTitle);
                        $d .= '<form method="post" action="?id='.$_GET['id'].'">';
                        $d .= '<input type="password" name="password"><br><input type="submit" name="submit" value="Login"></form>';
                        $temp->content($d);
                        $temp->finish();
                    }
                } else {
                    $SubTitle = $dir['name'];
                    $temp->titlePage($SubTitle);
                    $temp->addHead('<script type="text/javascript" src="'.GalleryWebRoot.'inc/js/paypalfrm.js"></script>');
                    $temp->addHead('<link rel="stylesheet" href="'.GalleryWebRoot.'inc/js/lightbox05.css" type="text/css" media="screen">');
                    $temp->addJS(GalleryWebRoot.'inc/js/lightbox05.js');
                    $head = "<script type=\"text/javascript\">\n$(document).ready(\nfunction(){\n$('#galtbl a').lightBox({imageLoading:'".GalleryWebRoot."inc/img/lightbox-ico-loading.gif',\nimageBtnPrev:'".GalleryWebRoot."inc/img/lightbox-btn-prev.gif',\nimageBtnNext:'".GalleryWebRoot."inc/img/lightbox-btn-next.gif',\nimageBtnClose:'".GalleryWebRoot."inc/img/lightbox-btn-close.gif',\nimageBlank:'".GalleryWebRoot."inc/img/lightbox-blank.gif'});\n});</script>";
                    $temp->addHead($head);
                    $temp->custom("SUBTITLE", $SubTitle);
                    if($dir['isPrivate'] == 1) {
                        $temp->content('<h3><a href="?mode=logout">Logout</a></h3>');
                    }

                    if ($handle = opendir(GalleryRoot.'albums/'.$dir['folder'])) {
                        $currow = 0;
                        $pattern="(\.jpg$)|(\.jpeg$)";
                        while (false !== ($file = readdir($handle))) {
                            if (eregi($pattern, $file)) {
                                if($currow % ItemsPerRow == 0) $d .= '<tr>';
                                $currow++;
                                $d .= '<th>';
                                $d .= '<a href="'.GalleryWebRoot.'albums/'.$dir['folder'].'/'.$file.'" rel="lb" title="'.$file.'"><img border="0" style="width:120px;height:120px;" src="'.GalleryWebRoot.'albums/'.$dir['folder'].'/thumbs/'.$file.'" alt="'.$r['name'].'" title="'.$file.'"><br>'.$file;
                                $d .= '</a>';
                                $d .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" onsubmit="this.target = \'paypal\'; ReadForm (this);">';
                                $d .= '<input type="hidden" name="basedes" value="'.$dir['name'].', '.$file.'">';
                                $d .= '<!--%PAYPALFRM%-->';
                                $d .= '</th>';
                                if($currow % ItemsPerRow == 0) $d .= "</tr>\n";
                            }
                        }
                        closedir($handle);
                        $temp->custom("GALTBL", $d);
                        $temp->incFile("PAYPALFRM", GalleryRoot.'inc/phpcls/template/forms/paypal.form.html');
                        $temp->custom("PPEMAIL", PayPalEmail);
                        $temp->finish();
                    } else {
                        throw new Exception('Error Reading Directory');
                    }
                }
            } else {
                $SubTitle = "Please Select a Gallery";
                $temp->titlePage($SubTitle);
                $temp->custom("SUBTITLE", $SubTitle);

                $galleries = $DB->doQuery("SELECT * FROM `gallery`;");
                $currow = 0;
                $PrivateOn = false;
                while($r = $DB->getColumns($galleries)) {
                    if($currow % ItemsPerRow == 0) $d .= '<tr>';
                    if(!$PrivateOn && HidePrivate) {
                        $currow++;
                        $d .= '<th>';
                        $d .= '<a href="?mode=login" border="0"><img style="width:120px;height:120px;" src="'.GalleryWebRoot.'inc/img/folder.png'.'" alt="Private Folder" title="Private Folder"><br>Private Folder</a>';
                        $d .= '</th>';
                        $PrivateOn = true;
                    }
                    if(HidePrivate) $Pri = $r['isPrivate'];
                    if($Pri != 1) {
                        $currow++;
                        $files = scandir(GalleryRoot.'albums/'.$r['folder'].'/thumbs/');
                        $pattern="(\.jpg$)|(\.jpeg$)";
                        foreach ($files as $curimg) {
                            if (eregi($pattern, $curimg)) {
                                $thumbimg = $curimg;
                                break;
                            }
                        }
                        $d .= '<th>';
                        $d .= '<a href="?id='.$r['id'].'" border="0"><img src="'.GalleryWebRoot.'albums/'.$r['folder'].'/thumbs/'.$thumbimg.'" alt="'.$r['name'].'" title="'.$r['name'].'"><br>'.$r['name'].'</a>';
                        $d .= '</th>';
                    }
                    if($currow % ItemsPerRow == 0) $d .= '</tr>';
                }
                $temp->custom("GALTBL", $d);
                $temp->finish();
            }
            break;
    }
} catch (Exception $e) {
    echo $e->getMessage().' - Thrown at Line: '.$e->getLine();
}

?>