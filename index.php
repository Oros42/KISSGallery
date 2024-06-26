<?php
/**
 * KISSGallery (Keep It Stupid Simple Gallery)
 *
 * @author  Oros42 <oros.kissgallery@ecirtam.net>
 * @contributors OranginaRouge (orangina-rouge.org)
 * @link    https://github.com/Oros42/KISSGallery
 * @license CC0 Public Domain
 * @version 1.8
 * @date    2024-04-13
 *
 * Install :
 * $ sudo apt install php-gd
 * $ cd /<WHERE_YOUR_IMAGES_ARE>/
 * $ wget https://raw.githubusercontent.com/Oros42/KISSGallery/master/index.php
 * 
 * KISS : https://en.wikipedia.org/wiki/KISS_principle
 */
$title = "KISSGallery"; // You can change
define("HEIGHT", 300); // You can change
define("MAKE_THUMBNAIL", true); // You can change but it's better to leave it to true
define("FILE_SORT", "ASC"); // You can change. ascending:"ASC" or descending:"DESC"
// You can change the favicon if you want
define("FAVICON_PATH", "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAL0lEQVQ4y2NkYGD4z0ABYGFgYGD4/x+7GYyMjAyE5JkYKAQDbwDjaCCOBuLwCEQAApMWH3p4gJkAAAAASUVORK5CYII=");
//define("FAVICON_PATH", "favicon.png");
define("LIMIT_IMAGE_PER_PAGE", 50); // You can change. Interger > 0

// If the script is run in cli mode (for creating cache)
// then we don't show HTML
define("SHOW_HTML", php_sapi_name() != "cli");

if (MAKE_THUMBNAIL) {
	define("TMP_DIR", "./cache/"); // You can change
	if (!is_dir(TMP_DIR)) {
		if (!@mkdir(TMP_DIR, 0755)) {
			die("Can't mkdir ".TMP_DIR);
		}
	}
	if (!function_exists("imagecreatefromjpeg")) {
		die("sudo apt install php-gd\nOr set MAKE_THUMBNAIL to false");
	}
} else {
	define("TMP_DIR", "");
}
$allowedExt = ['jpeg', 'png', 'gif', 'bmp', 'wbmp', 'webp', 'xbm']; // Don't change
$excludedFiles = ['.gitignore', 'favicon.ico', 'favicon.png', 'index.php', 'LICENSE', 'README.md', 'titles.csv'];

$titles = []; // titles of images
if (is_file("titles.csv")) {
	if (($handle = fopen("titles.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			//$titles[<image name>] = "<title>";
			$titles[$data[0]] = $data[1];
		}
		fclose($handle);
	}
}

function makeThumbnail($file, $ext) {
	echo "make thumbnail ".$file;
	if (SHOW_HTML) {
		ob_flush(); flush(); 
	}
	list($width, $height) = getimagesize($file);
	if ($height < 1) {
		$height = 1;
	}
	if ($width < 1) {
		$width = 1;
	}
	$newwidth = $width * (HEIGHT/$height);
	$imgcreate = "imagecreatefrom".$ext;
	$src = $imgcreate($file);
	$dst = imagecreatetruecolor($newwidth, HEIGHT);
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, HEIGHT, $width, $height);
	// https://www.php.net/manual/en/function.exif-read-data.php#110894
	$exif = exif_read_data($file);
	if(!empty($exif['Orientation'])) {
		switch($exif['Orientation']) {
			case 8:
				$dst = imagerotate($dst,90,0);
				break;
			case 3:
				$dst = imagerotate($dst,180,0);
				break;
			case 6:
				$dst = imagerotate($dst,-90,0);
				break;
		}
	}
	imageinterlace($dst, true);
	$imgsave = "image".$ext;
	if (!$imgsave($dst, TMP_DIR.$file)) {
		echo " can't save :-/";
		$returnStatus = 0;
	} else {
		echo " ok";
		$returnStatus = 1;
	}
	chmod(TMP_DIR.$file, 0755);
	echo "<br>\n";
	return $returnStatus;
}
if (SHOW_HTML) {
?><!DOCTYPE html>
<!-- https://github.com/Oros42/KISSGallery -->
<html>
<head>
	<title><?php echo $title; ?></title>
	<meta charset="utf-8">
	<link rel="shortcut icon" href="<?php echo FAVICON_PATH; ?>" />
	<style type="text/css">
body{background-color:black;color:#AAAAAA;}
a{margin:6px;color:red;text-decoration:none;display:inline-block;}
.loader{position:absolute;border:5px solid #636363;border-radius:50%;border-top:5px solid #333;border-bottom:5px solid #333;width:40px;height:40px;-webkit-animation:spin 4s linear infinite;animation:spin 4s linear infinite;z-index:-1;left:50%;top:50%;margin-left:-25px;padding:0;margin-top:-25px;}
@-webkit-keyframes spin{0%{-webkit-transform:rotate(0deg);}100% {-webkit-transform:rotate(360deg);}}
@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
#diapo{position:fixed;width:100%;height:100%;z-index:110;top:0;left:0;right:0;bottom:0;background:black;}
#bigImg{max-height:100%;max-width:100%;top:0;bottom:0;margin-top:auto;margin-bottom:auto;position:fixed;margin-left:auto;margin-right:auto;left:0;right:0;}
.prev_next{position:fixed;top:0;bottom:0;width:25%;color:#0000;}
#close_diapo{right:0;height:100px;bottom:initial;}
#close_diapo div{top:0;bottom:initial;}
#prev_diapo{left:0;}
#next_diapo{top:100px;right:0;}
.prev_next div{font-size:xx-large;background:#0006;padding:2px 5px 5px 10px;border-radius:5px;position:absolute;color:#D1CFCFCC;bottom:0;}
.prev_next:hover div{color:#FFF;}
#next_diapo div,#close_diapo div{right:0;}
#paging{text-align:center;}
#paging a{background-color:#333;margin:0;padding:10px;}
<?php 
if (!MAKE_THUMBNAIL) {
	printf("#imgListe a img{ height: %dpx;}\n", HEIGHT);
}
?>
	</style>
</head>
<body>
<?php
}
$imgListe = [];
$liste = scandir(".");
if (strtoupper(FILE_SORT) == "DESC") {
	$liste = array_reverse($liste);
}
$needReload = false;
if (!empty($_GET['page'])) {
	$page = (int)$_GET['page'];
} else {
	$page = 0;
}
$startOffset = $page * LIMIT_IMAGE_PER_PAGE;
$end = count($liste);
$cptImg = 0;
$showNextBtn = false;
$totalImg = 0;
for ($i=2; $i < $end; $i++) { 
	$file = $liste[$i];
	if (!in_array($file, $excludedFiles)) {
		if (is_file($file) && filesize($file) > 11) {
			$exif = exif_imagetype($file);
			if ($exif > 0) {
				$ext = substr(image_type_to_extension($exif),1); // don't trust file name !
				if (in_array($ext, $allowedExt)) {
					$totalImg++;
					if (MAKE_THUMBNAIL && !is_file(TMP_DIR.$file)) {
						if (makeThumbnail($file, $ext)) {
							if ($cptImg < LIMIT_IMAGE_PER_PAGE) {
								$imgListe[] = $file;
								$cptImg++;
							}
						}
						$needReload = true;
					} else {
						if ($startOffset > 0) {
							$startOffset--;
							continue;
						}
						if ($cptImg < LIMIT_IMAGE_PER_PAGE) {
							$imgListe[] = $file;
							$cptImg++;
						}
					}
				}
			}
		}
	}
}
$nbPages = (int)($totalImg / LIMIT_IMAGE_PER_PAGE);
if (SHOW_HTML) {
	if ($needReload) {
	   	echo "<a href=''>Reload</a><br>\n";
	}
	echo "<div id='imgListe'>\n";
	$i=0;
	foreach ($imgListe as $img) {
		if (isset($titles[$img])) {
			$imgTitle = trim(htmlentities($titles[$img]));
		} else {
			$imgTitle = "";
		}
		$img = rawurlencode($img);
		echo sprintf("<a href='%s' title=\"%s\" id='a%d'><img src='%s%s' loading='lazy'></a>\n", $img, $imgTitle, $i, TMP_DIR, $img);
		$i++;
	}
?>
</div><?php
	if ($nbPages>0) {
		echo "<br><div id='paging'>";
		if ($page > 0) {
			echo "<a href='?page=".($page-1)."'>◀</a> \n";
		}
		$initPagePrev = max(0, $page-3);
		if($initPagePrev > 0){
			echo "<a href='?page=0'>0</a> \n";
			if ($initPagePrev > 1) {
				echo "<a href='?page=".($page-1)."'>&hellip;</a> \n";
			}

		}
		for ($pagePrev=$initPagePrev; $pagePrev < $page; $pagePrev++) { 
			echo "<a href='?page=$pagePrev'>$pagePrev</a> \n";
		}
		echo "<a href='?page=$page'>[$page]</a> \n";
		$endPaging = min($page+4, $nbPages);
		for ($pageNext=$page+1; $pageNext < $endPaging; $pageNext++) { 
			echo "<a href='?page=$pageNext'>$pageNext</a> \n";
		}
		if ($page +3 < $nbPages) {
			echo "<a href='?page=".($page+1)."'>&hellip;</a>\n";
		}
		if($page < $nbPages){
			echo "<a href='?page=".($nbPages)."'>$nbPages</a>\n";
		}
		if ($page<$nbPages) {
			echo "<a href='?page=".($page+1)."'>▶</a>\n";
		}
		echo "</div>";
	}
?>
<div id="diapo" hidden="">
	<div id="loader" class="loader" hidden=""></div>
	<img id="bigImg" src="" alt="" title="">
	<a href="#" onclick="prevDiapo();return false;" id="prev_diapo" class="prev_next" title="Previous"><div>❮</div></a>
	<a href="#" onclick="closeDiapo();" id="close_diapo" class="prev_next" title="Close"><div>✕</div></a>
	<a href="#" onclick="nextDiapo();return false;" id="next_diapo" class="prev_next" title="Next"><div>❯</div></a>
</div>
<script type="text/javascript">
	var imgIndex = -1;
	var aList = imgListe.children;
	function showDiapo() {
		bigImg.src = "";
		loader.hidden = false;
		document.location.hash = aList[imgIndex].hash;
		bigImg.src = aList[imgIndex].hash.split("&")[1];
		bigImg.title = aList[imgIndex].title;
		diapo.hidden = false;
		close_diapo.href=`#a${imgIndex}`;
		return false;
	}
	function closeDiapo() {
		diapo.hidden = true;
		loader.hidden = true;
		document.location = close_diapo.href;
	}
	function prevDiapo() {
		imgIndex--;
		if (imgIndex < 0) {
			imgIndex = -1;
			closeDiapo();
		} else {
			showDiapo();
		}
		return false;
	}
	function nextDiapo() {
		imgIndex++;
		if (imgIndex >= aList.length) {
			imgIndex = aList.length;
			closeDiapo();
		} else {
			showDiapo();
		}
		return false;
	}
	for (var i = 0; i < aList.length; i++) {
		aList[i].href = "#"+i+'&'+aList[i].pathname;
		aList[i].addEventListener('click', (function(i){
			return function(){
				imgIndex = i;
				return showDiapo();
			}
		}(i)));
	}
	if (document.location.hash.length > 1) {
		var i = parseInt(document.location.hash.split("&")[0].substring(1));
		if (i >= 0 && i < aList.length) {
			imgIndex = i;
			showDiapo();
		}
	}
	window.addEventListener("keydown", function (event) {
		if (event.defaultPrevented) {
			return;
		}
		switch (event.key) {
			case "Up":
			case "ArrowUp":
			case "Left":
			case "ArrowLeft":
				prevDiapo();
			break;
			case "Down":
			case "ArrowDown":
			case "Right":
			case "ArrowRight":
				nextDiapo()
			break;
			case "Esc":
			case "Escape":
				closeDiapo();
			break;
			default:
			return;
		}
		event.preventDefault();
	}, true);
</script>
</body>
</html><?php
} ?>
