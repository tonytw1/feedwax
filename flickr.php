<?php
include('config/globals.php');
include('header.php');
include('nav.php');
if ($_GET['q']) {
	$q = urlencode($_GET['q']); 
		} else { 
	$q = "n0ticed";
}
if ($_GET['lat']) {
	$lat = $_GET['lat']; 
		} else { 
	$lat = "51.534631";
}
if ($_GET['long']) {
	$long = $_GET['long']; 
		} else { 
	$long = "-0.121965";
}
if ($_GET['radius']) {
	$radius = $_GET['radius']; 
		} else { 
	$radius = "10";
}
if ($_GET['maxresults']) {
	$maxresults = $_GET['maxresults']; 
		} else { 
	$maxresults = "10";
}
if ($_GET['license'] == "all") {
	$license = "0,1,2,3,4,5,6,7"; 
	$licensenocomma = "01234567"; 
		} else { 
	$license = "4,5,6,7"; 
	$licensenocomma = "4567"; 
}

if ($_GET['unlocatedphotos']) {
	$unlocatedphotos = $_GET['unlocatedphotos']; 
		} else { 
	$unlocatedphotos = "exclude";
}
$assignedloc = $_GET['assignedloc'];
if ($_GET['unlocatedphotos'] == "exclude") {
	$assignedloc = null;
}
function explodeCommas($commalist) {
	return explode(',', $commalist);
}
list($assignedlat,$assignedlong) = explodeCommas($assignedloc);
?>

<div class="hero-unit">
<h1>Build a feed with Flickr</h1>
<ul class="nav nav-tabs">
	<li class="active"><a href="flickr.php">Search by keyword</a></li>
	<li><a href="flickrgeo.php">Search by location</a></li>
</ul>
<div class="well">
<table class="table">
<tr><td><form action="" method="GET" class="well">
    <strong>Search Terms:</strong> <input type="text" class="text" value="<?php if($_GET['q']) { echo $_GET['q']; } else { echo ""; } ?>" name="q" /><br>
<hr>
	<strong>Options:</strong><br>
	License:<br><input type="radio" class="radio" value="cc" name="license" <?php if($_GET['license'] != "all") {echo "checked";} ?>/> CC only <input type="radio" class="text" value="all" name="license" <?php if($_GET['license'] == "all") {echo "checked";} ?>/> All<br>
	Include photos without location data<br><input type="radio" class="radio" value="exclude" name="unlocatedphotos" <?php if($unlocatedphotos != "assign") {echo "checked";} ?>/> Exclude <input type="radio" class="text" value="assign" name="unlocatedphotos" <?php if($unlocatedphotos == "assign") {echo "checked";} ?>/> Assign<br>
	Assign location:<br><input type="text" class="input-xlarge" value="<?php if($assignedloc) { echo $assignedloc; } else { echo ""; } ?>" name="assignedloc"  placeholder="lat,long" /><br>
	<input type="hidden" value="<?php if($_GET['maxresults']) { echo $_GET['maxresults']; } else { echo "10"; } ?>" name="maxresults" />
<button type="submit" value="build feed" class="btn pull-right"><i class="icon-fire"></i> build feed</button>
</form></td>
<td>
  Find Place: <input type="text" class="input-medium" id="address"/><input type="button" value="Go" onclick="geocode()">
  <div id="map">
    <div id="map_canvas" style="width:100%; height:200px"></div>
    <div id="crosshair"></div>
  </div>

  <table>
    <tr><td>Lat/Lng:</td><td><div id="latlng"></div></td></tr>
    <tr><td>Address:</td><td><div id="formatedAddress"></div></td></tr>
  </table>
</td></tr>
</table>
</div>
</div>

<?php 
if ($_GET) {
$extras = "date_taken,date_upload,description,geo,last_update,license,owner_name,url_z";
$secret = $flickr_secret;
$api_key = $flickr_key;
$params = "accuracy11api_key" . $api_key . "extrasdate_takendescriptiongeoowner_nametagsformatjsonlicense" . $licensenocomma . "methodflickr.photos.searchnojsoncallback1per_page" . $maxresults . "sortdate-posted-desctags" . $q . "text" . $q;
$sigmaker = $secret.$params;
$api_sig = md5($sigmaker);
$flickr_url = "http://api.flickr.com/services/rest/?method=flickr.photos.search&accuracy=11&api_key=$api_key&extras=".urlencode($extras)."&format=json&license=".urlencode($license)."&nojsoncallback=1&per_page=$maxresults&sort=date-posted-desc&tags=$q&text=$q";
$string .= file_get_contents($flickr_url); // get json content
$array = json_decode($string, true); //json decoder
$n0ticefeed_url="http://" . $_SERVER['SERVER_NAME'] . "/feeders/flickrfeed.php?" . $_SERVER['QUERY_STRING'];

echo "<form action=\"http://feedton0tice.com/feeds/new\" method=\"GET\">\n";
echo "<input type=\"hidden\" name=\"url\" value=\"" . $n0ticefeed_url . "\">";
if ($_GET['license'] == "all") {
$warningmessage = "<strong>Photo re-use guidelines:</strong> The results below have varying license rights and may have use restrictions.  Be sure to get permission and notify the copyright owner when you publish someone's photos.";
} else {
$warningmessage = "<strong>Photo re-use guidelines:</strong> The results below all include a Creative Commons license and permit commercial use.  As always, you may wish to notify the copyright owner when you publish someone's photos.";
}
echo "<div class=\"alert span7\">";
echo $warningmessage;
echo "</div>";
echo "<button class=\"btn btn-large btn-primary span4 align-right\" type=\"submit\">feed this into n0tice</button>";
echo "</form>";

echo "<table class=\"table\" width=\"100%\">";

echo " <thead>";
echo "    <tr>";
echo "      <th>Results</th>";
echo "      <th>Image</th>";
echo "    </tr>";
echo "  </thead>";
echo "  <tbody class=\"well\">";

$i = 0; 
foreach ($array['photos']['photo'] as $v) {
	if (preg_match("/_MG_|IMG_|DSC_/", $v['title'])) {
		$titlerewrite = true;
	} else {
		$titlerewrite = false;
	}
	if ($v['license'] == "0") {$getlicense = "All Rights Reserved";}
	if ($v['license'] == "1") {$getlicense = "Attribution-NonCommercial-ShareAlike License";}
	if ($v['license'] == "2") {$getlicense = "Attribution-NonCommercial License";}
	if ($v['license'] == "3") {$getlicense = "Attribution-NonCommercial-NoDerivs License";}
	if ($v['license'] == "4") {$getlicense = "Attribution License";}
	if ($v['license'] == "5") {$getlicense = "Attribution-ShareAlike License";}
	if ($v['license'] == "6") {$getlicense = "Attribution-NoDerivs License";}
	if ($v['license'] == "7") {$getlicense = "No known copyright restrictions";}

	if ($v['geo_is_public'] != 1) {
		echo "<tr><td>";
		if($unlocatedphotos != "assign") {
			$locationmsg =  "(No location...photo will be excluded from feed)";
		} else {
			$locationmsg =  "(No location found. Assigning: ".$assignedlat.",".$assignedlong.")";
		}			
	} else {
		echo "<tr class=\"success\"><td>";
		$locationmsg = "Location: " . $v['latitude'] . "," . $v['longitude'] . "<br>";
	}
	if ($titlerewrite == false) {echo htmlspecialchars($v['title']);} else {echo "Photo by ".$v['ownername'];}
	echo "<br><br>";
	echo "<br><a href=\"http://flickr.com/photos/" . $v['owner'] . "/" . $v['id'] . "\">http://flickr.com/photos/" . $v['owner'] . "/" . $v['id'] . "</a><br>\n";
	echo $locationmsg."<br>";
	echo "License: ".$getlicense."<br>";
	$pubdate = strtotime($v['datetaken']);
	echo date("D, d M y H:i:s O", $pubdate);
	echo "</td><td><img src=\"" . $v['url_z'] . "\"></td></tr>\n";
$i++;
}
echo "  </tbody>";
echo "  </table>";
include ('warning.php');
}

echo "<a href=\"http://feedton0tice.com/feeds/new?url=" . $n0ticefeed_url . "\"><img src=\"/img/rss1.png\" width=\"10\" align=\"right\"></a>";
?>

<?php include('footer.php'); ?>