<?PHP
########################################
#                                      #
# Fix Common Problems                  #
# Copyright 2020-2024, Lime Technology #
# Copyright 2015-2024, Andrew Zawadzki #
#                                      #
# Licenced under GPLv2                 #
#                                      #
########################################

require_once("/usr/local/emhttp/plugins/dynamix/include/Wrappers.php");
require_once("/usr/local/emhttp/plugins/fix.common.problems/include/paths.php");
require_once("/usr/local/emhttp/plugins/dynamix.plugin.manager/include/PluginHelpers.php");


if ( ! function_exists("file_put_contents_atomic") ) {
  function file_put_contents_atomic($filename, $data, $flags = 0, $context = null) {
    if (file_put_contents($filename."~", $data, $flags, $context) === strlen($data)) {
    return rename($filename."~",$filename,$context) ? strlen($data) : false;  
    }
    exec("logger ".escapeshellarg("Failed to write $filename"));
    @unlink($filename."~", $context);
    return false;
  }
}
####################################################################################################
#                                                                                                  #
# 2 Functions because unRaid includes comments in .cfg files starting with # in violation of PHP 7 #
#                                                                                                  #
####################################################################################################

if ( ! function_exists("my_parse_ini_file") ) {
  function my_parse_ini_file($file,$mode=false,$scanner_mode=INI_SCANNER_NORMAL) {
    if ( ! $file ) {
      return false;
    }
    return parse_ini_string(preg_replace('/^#.*\\n/m', "", @file_get_contents($file)),$mode,$scanner_mode);
  }
}
if ( ! function_exists("my_parse_ini_string") ) {
  function my_parse_ini_string($string, $mode=false,$scanner_mode=INI_SCANNER_NORMAL) {
    return parse_ini_string(preg_replace('/^#.*\\n/m', "", $string),$mode,$scanner_mode);
  }
}

#############################
#                           #
# Adds an error to the list #
#                           #
#############################

function addError($description,$action,$url="") {
  global $errors, $ignoreList, $ignored, $fixSettings;

  $originalDescription = $description;
  $description = str_replace("'","&#39;",$description);
  $newError['error'] = "<font color='#f17272'>$description</font>";
  $newError['suggestion'] = $action;
  if ( $url )
    $newError['suggestion'] .= "&nbsp;&nbsp;<a href='$url' target='_blank'>More Information</a>";
  
  if ( isset($ignoreList[strip_tags($description)]) ) {
    $ignored[] = $newError;
    if ( $fixSettings['logIgnored'] == "yes" ) {
      logger("Fix Common Problems: Error: ".strip_tags($description),true);
    }
  } else {
    $errors[] = $newError;
    logger("Fix Common Problems: Error: ".strip_tags($description),false);
  }
}

#############################
#                           #
# Add a warning to the list #
#                           #
#############################

function addWarning($description,$action,$url="") {
  global $warnings, $ignoreList, $ignored, $fixSettings;
  
  $originalDescription = $description;
  $description = str_replace("'","&#39;",$description);
  $newWarning['error'] = "$description";
  $newWarning['suggestion'] = $action;
  if ( $url )
    $newWarning['suggestion'] .= "&nbsp;&nbsp;<a href='$url' target='_blank'>More Information</a>";
  
  if ( isset($ignoreList[strip_tags($originalDescription)]) ) {
    $ignored[] = $newWarning;
    if ( $fixSettings['logIgnored'] == "yes" ) {
      logger("Fix Common Problems: Warning: ".strip_tags($description),true);
    }
  } else {
    $warnings[] = $newWarning;
    logger("Fix Common Problems: Warning: ".strip_tags($description),false);
  }
}

#####################################
#                                   #
# Adds an other comment to the list #
#                                   #
#####################################

function addOther($description,$action,$url="") {
  global $otherWarnings, $ignoreList, $ignored, $fixSettings;

  $originalDescription = $description;
  $description = str_replace("'","&#39;",$description);
  $newWarning['error'] = "$description";
  $newWarning['suggestion'] = $action;
  if ( $url )
    $newWarning['suggestion'] .= "&nbsp;&nbsp;<a href='$url' target='_blank'>More Information</a>";
  
  if ( isset($ignoreList[strip_tags($originalDescription)]) ) {
    $ignored[] = $newWarning;
    if ( $fixSettings['logIgnored'] == "yes" ) {
      logger("Fix Common Problems: Other Warning: ".strip_tags($description),true);
    }
  } else {
    logger("Fix Common Problems: Other Warning: ".strip_tags($description));
    $otherWarnings[] = $newWarning;
  }
}

############################################
#                                          #
# Adds a button with a link attached to it #
#                                          #
############################################

function addLinkButton($buttonName,$link) {
  $link = str_replace("'","&quot;",$link);
  return "<input type='button' value='$buttonName' onclick='window.location.href=&quot;/FixProblems/$link&quot;'>";
}

########################################
#                                      #
# Adds a button with a javascript link #
#                                      #
########################################

function addButton($buttonName,$action) {
  $action = str_replace("'","&quot;",$action);
  $id = mt_rand();
  return "<input type='button' id='$id' value='$buttonName' onclick='$action'>";
}

###########################################################################
#                                                                         #
# Helper function to determine if a plugin has an update available or not #
#                                                                         #
###########################################################################

function checkPluginUpdate($filename) {
  global $unRaidVersion;
  $filename = basename($filename);
  $installedVersion = plugin("version","/var/log/plugins/$filename");
  if ( is_file("/tmp/plugins/$filename") ) {
    $upgradeVersion = plugin("version","/tmp/plugins/$filename");
  } else {
    $upgradeVersion = "0";
  }
  if ( $upgradeVersion != "0" ) {	

    $OSversion = plugin("min","/tmp/plugins/$filename") ?: $unRaidVersion;
    if ( version_compare($unRaidVersion,$OSversion,"<") ) {
      return false;
    }
  }
  if ( $installedVersion < $upgradeVersion ) {
    $unRaid = plugin("unRAID","/tmp/plugins/$filename");
    if ( $unRaid === false || version_compare($unRaidVersion,$unRaid,">=") ) {
      return true;
    } else {
      return false;
    }
  }
  return false;
}

function pluginVersion($fullPath) {
  $version = exec("/usr/local/emhttp/plugins/dynamix.plugin.manager/scripts/plugin version $fullPath");
  return $version;
}

###################################################################################
#                                                                                 #
# returns a random file name                                                      #
#                                                                                 #
###################################################################################

function randomFile($basePath) {
  global $communityPaths;
  while (true) {
    $filename = $basePath."/".mt_rand().".tmp";
    if ( ! is_file($filename) ) {
      break;
    }
  }
  return $filename;
}

##################################################################
#                                                                #
# 2 Functions to avoid typing the same lines over and over again #
#                                                                #
##################################################################

function readJsonFile($filename) {
  return @json_decode(@file_get_contents($filename),true) ?: [];
}

function writeJsonFile($filename,$jsonArray) {
  file_put_contents_atomic($filename,json_encode($jsonArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

##############################################################
#                                                            #
# Searches an array of docker mappings (host:container path) #
# for a container mapping of /config and returns the host    #
# path                                                       #
#                                                            #
##############################################################

function findAppdata($volumes) {
  $path = false;
  $dockerOptions = @my_parse_ini_file("/boot/config/docker.cfg");
  $defaultShareName = basename($dockerOptions['DOCKER_APP_CONFIG_PATH']);
  $shareName = str_replace("/mnt/user/","",$defaultShareName);
  $shareName = str_replace("/mnt/cache/","",$defaultShareName);
  if ( ! is_file("/boot/config/shares/$shareName.cfg") ) { 
    $shareName = "****";
  }
  file_put_contents_atomic("/tmp/test",$defaultShareName);
  if ( is_array($volumes) ) {
    foreach ($volumes as $volume) {
      $temp = explode(":",$volume);
      $testPath = strtolower($temp[1]);
    
      if ( (startsWith($testPath,"/config")) || (startsWith($temp[0],"/mnt/user/$shareName")) || (startsWith($temp[0],"/mnt/cache/$shareName")) ) {
        $path = $temp[0];
        break;
      }
    }
  }
  return $path;
}

############################################
#                                          #
# Function to write a string to the syslog #
#                                          #
############################################

function logger($string,$ignored = false) {
  if ( $ignored ) {
    $string .= " ** Ignored";
  }
  $string = htmlspecialchars_decode($string, ENT_QUOTES); 
  shell_exec('logger "'.$string.'"');
}

###########################################
#                                         #
# Function to send a dynamix notification #
#                                         #
###########################################

function notify($event,$subject,$description,$message,$type="normal") {
  $command = '/usr/local/emhttp/plugins/dynamix/scripts/notify -e "'.$event.'" -s "'.$subject.'" -d "'.$description.'" -m "'.$message.'" -i "'.$type.'" -l "/Settings/FixProblems"';
  shell_exec($command);
}

#################################################################
#                                                               #
# Helper function to determine if $haystack begins with $needle #
#                                                               #
#################################################################

function startsWith($haystack, $needle) {
  return $needle === "" || strripos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

###############################################
#                                             #
# Helper function to download a URL to a file #
#                                             #
###############################################

function download_url($url, $path = "", $bg = false, $timeout = 45) {
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_FRESH_CONNECT,true);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
  curl_setopt($ch,CURLOPT_ENCODING,"");
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch,CURLOPT_FAILONERROR,true);
  
  if ( ! getenv("http_proxy") && is_file("/boot/config/plugins/community.applications/proxy.cfg") ) {
    $proxyCFG = parse_ini_file("/boot/config/plugins/community.applications/proxy.cfg");
    curl_setopt($ch, CURLOPT_PROXYPORT,intval($proxyCFG['port']));
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL,intval($proxyCFG['tunnel']));
    curl_setopt($ch, CURLOPT_PROXY,$proxyCFG['proxy']);
  }
  $out = curl_exec($ch);
  if ( ! $out ) {
    curl_setopt($ch,CURLOPT_ENCODING,"deflate");
    $out = curl_exec($ch);
  }
  curl_close($ch);
  if ( $path )
    file_put_contents($path,$out);

  return $out ?: false;
  }
##########################
#                        #
# returns unRaid version #
#                        #
##########################

function unRaidVersion() {
  $unRaidVersion = my_parse_ini_file("/etc/unraid-version");
  return $unRaidVersion['version'];
}

#################################################################
#                                                               #
# checks the Min/Max version of an app against unRaid's version #
# Returns: TRUE if it's valid to run, FALSE if not              #
#                                                               #
#################################################################

function versionCheck($template) {
  global $unRaidVersion;

  if ( isset($template['MinVer']) && ( version_compare($template['MinVer'],$unRaidVersion) > 0 ) ) { return false; }
  if ( isset($template['MaxVer']) && ( version_compare($template['MaxVer'],$unRaidVersion) < 0 ) ) { return false; }
  return true;
}

###############################################
#                                             #
# Function to read a template XML to an array #
#                                             #
###############################################

function readXmlFile($xmlfile) {
  if ( ! is_file($xmlfile) ) { return false; }
  $doc = new DOMDocument();
  $doc->load($xmlfile);
  if ( ! $doc ) { return false; }
    $o['WebUI']       = $doc->getElementsByTagName( "WebUI" )->item(0)->nodeValue;

  $o['Path']        = $xmlfile;
  $o['Repository']  = stripslashes($doc->getElementsByTagName( "Repository" )->item(0)->nodeValue ?? "");
  $o['Author']      = preg_replace("#/.*#", "", $o['Repository']);
  $o['Name']        = stripslashes($doc->getElementsByTagName( "Name" )->item(0)->nodeValue ?? "");
  $o['DockerHubName'] = strtolower($o['Name']);
  $o['Beta']        = strtolower(stripslashes($doc->getElementsByTagName( "Beta" )->item(0)->nodeValue ?? ""));
  $o['Changes']     = $doc->getElementsByTagName( "Changes" )->item(0)->nodeValue ?? null;
  $o['Date']        = $doc->getElementsByTagName( "Date" ) ->item(0)->nodeValue ?? null;
  $o['Project']     = $doc->getElementsByTagName( "Project" ) ->item(0)->nodeValue ?? null;
  $o['SortAuthor']  = $o['Author'] ?? "";
  $o['SortName']    = $o['Name'] ?? "";
  $o['MinVer']      = $doc->getElementsByTagName( "MinVer" ) ->item(0)->nodeValue ?? null;
  $o['MaxVer']      = $doc->getElementsByTagName( "MaxVer" ) ->item(0)->nodeValue ?? null;
  $o['Overview']    = $doc->getElementsByTagName("Overview")->item(0)->nodeValue ?? null;
  if ( strlen($o['Overview']) > 0 ) {
    $o['Description'] = stripslashes($doc->getElementsByTagName( "Overview" )->item(0)->nodeValue ?? "");
    $o['Description'] = preg_replace('#\[([^\]]*)\]#', '<$1>', $o['Description']);
  } else {
    $o['Description'] = $doc->getElementsByTagName( "Description" )->item(0)->nodeValue ?? "";
  }
  $o['Plugin']      = $doc->getElementsByTagName( "Plugin" ) ->item(0)->nodeValue ?? null;
  $o['PluginURL']   = $doc->getElementsByTagName( "PluginURL" ) ->item(0)->nodeValue ?? null;
  $o['PluginAuthor']= $doc->getElementsByTagName( "PluginAuthor" ) ->item(0)->nodeValue ?? null;

# support both spellings
  $o['Licence']     = $doc->getElementsByTagName( "License" ) ->item(0)->nodeValue  ?? null;
  $o['Licence']     = $doc->getElementsByTagName( "Licence" ) ->item(0)->nodeValue ?? null;
  $o['Category']    = $doc->getElementsByTagName ("Category" )->item(0)->nodeValue ?? null;

  if ( isset($o['Plugin']) ) {
    $o['Author']     = $o['PluginAuthor'];
    $o['Repository'] = $o['PluginURL'];
    $o['Category']   .= " Plugins: ";
    $o['SortAuthor'] = $o['Author'];
    $o['SortName']   = $o['Name'];
  }
  $o['Description'] = preg_replace('#\[([^\]]*)\]#', '<$1>', $o['Description']??"");
  $o['Overview']    = $doc->getElementsByTagName("Overview")->item(0)->nodeValue ?? null;

  $o['Announcement'] = $Repo['forum'] ??"";
  $o['Support']     = ($doc->getElementsByTagName( "Support" )->length ) ? $doc->getElementsByTagName( "Support" )->item(0)->nodeValue : $Repo['forum'];
  $o['Support']     = $o['Support'];
  $o['IconWeb']     = stripslashes($doc->getElementsByTagName( "Icon" )->item(0)->nodeValue);
  $o['TailscaleEnabled'] = $doc->getElementsByTagName("TailscaleEnabled")->item(0)->nodeValue ?? false;
  $o['Network'] = $doc->getElementsByTagName("Network")->item(0)->nodeValue ?? false;

 
  removeXMLtags($o);
  return $o;
}
function removeXMLtags(&$template) {
  foreach ($template as $key => &$element) {
    if ( is_array($element) ) {
        removeXMLtags($element);
    } else {
      $tempElement = htmlspecialchars_decode($element??"");
      $tempElement = str_replace("<br>","\n",$tempElement);
      if ( trim($tempElement) !== trim(strip_tags($tempElement)) ) {
        $tempElement = str_replace(["<",">"],["",""],$tempElement);
        $element = $tempElement;
      }
    }
  }
}
#########################################################
#                                                       #
# Returns an array of all of the appdata shares present #
#                                                       #
#########################################################

function getAppData() {
  $dockerRunning = is_dir("/var/lib/docker/tmp");
  $excludedShares = array();
  $allVolumes = array_diff(scandir("/mnt"),[".",".."]);

  if ( $dockerRunning ) {
    $DockerClient = new DockerClient();
    $info = $DockerClient->getDockerContainers();

    foreach ($info as $docker) {
      $appData = findAppData($docker['Volumes']);
      if ( ! $appData ) {
        continue;
      }
      foreach ($allVolumes as $volume) {
        $appData = str_replace("/mnt/$volume/","",$appData);
      }
      $pathinfo = explode("/",$appData);
      $excludedShares[$pathinfo[0]] = $pathinfo[0]."&nbsp;&nbsp&nbsp;(<i>Found as appdata location</i>)";
      
    }
  }  
  $dockerOptions = @my_parse_ini_file("/boot/config/docker.cfg");
  $sharename = $dockerOptions['DOCKER_APP_CONFIG_PATH'];
  file_put_contents("/tmp/blah",print_r($sharename,true));
  if ( $sharename ) {
    foreach ($allVolumes as $volume) {	
      $sharename = str_replace("/mnt/$volume/","",$sharename);
    }
    $pathinfo = explode("/",$sharename);
    $excludedShares[$pathinfo[0]] = $pathinfo[0]. "&nbsp;&nbsp&nbsp;(<i>Default Appdata Storage Location</i>)";
    
  }
  $dockerFolder = ( ($dockerOptions['DOCKER_IMAGE_TYPE'] ?? false) == "folder" ) ? $dockerOptions['DOCKER_IMAGE_FILE'] : false;
  if ($dockerFolder) {
    foreach ($allVolumes as $volume) {	
      $dockerFolder = str_replace("/mnt/$volume/","",$dockerFolder);
    }
    $pathinfo = explode("/",$dockerFolder);
    $excludedShares[$pathinfo[0]] = $pathinfo[0]."&nbsp;&nbsp&nbsp;(<i>Docker Folder share</i>)";
  
  }
  
  if ( is_file("/boot/config/plugins/ca.backup/BackupOptions.json") ) {
    $backupOptions = readJsonFile("/boot/config/plugins/ca.backup/BackupOptions.json");
    $backupDestination = $backupOptions['destinationShare'];
    $backupShare = explode("/",$backupDestination);
    $excludedShares[$backupShare[0]] = $backupShare[0]."&nbsp;&nbsp&nbsp;(Community Applications Backup Appdata Destination)";
  }

  return $excludedShares;  
}

#############################################################
#                                                           #
# Helper function to return an array of directory contents. #
# Returns an empty array if the directory does not exist    #
#                                                           #
#############################################################

function dirContents($path) {
  $dirContents = @scandir($path);
  if ( ! $dirContents ) { $dirContents = array(); }
  return array_diff($dirContents,array(".",".."));
}

################################################
# Returns the actual URL after any redirection #
################################################
function getRedirectedURL($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch,CURLOPT_TIMEOUT,15);
  $a = curl_exec($ch);
  return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
}

###############################################
# Search array for a particular key and value #
# returns the index number of the array       #
# return value === false if not found         #
###############################################
function searchArray($array,$key,$value) {
  $result = false;
  if (count($array) ) {
    for ($i = 0; $i <= max(array_keys($array)); $i++) {
      if ( !isset($array[$i][$key]) )
        continue;
      if ( $array[$i][$key] == $value ) {
        $result = $i;
        break;
      }
    }
  }
  return $result;
}
function curl_socket($socket, $url, $postdata = NULL) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $socket);
    if ($postdata !== NULL) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
function publish($endpoint, $message){
  global $fromDiagnostics;
  
  if ( $fromDiagnostics ) {
    $msg = json_decode($message,true);
    if ($msg) 
      $message = "Fix Common Problems Scan: ".$msg['test'];
    write($message);
  }	else
    curl_socket("/var/run/nginx.socket", "http://localhost/pub/$endpoint?buffer_length=1", $message);
}

###############################################
# add ipaddr function from Unraid 6.10        #
###############################################
if (!function_exists('ipaddr')) { 
  function ipaddr($ethX='eth0') {
    global $$ethX;
    switch ($$ethX['PROTOCOL:0']) {
    case 'ipv4':
    return $$ethX['IPADDR:0'];
    case 'ipv6':
    return $$ethX['IPADDR6:0'];
    case 'ipv4+ipv6':
    return [$$ethX['IPADDR:0'],$$ethX['IPADDR6:0']];
    default:
    return $$ethX['IPADDR:0'];
    }
  }
}
##########################################
# Certificate functions below from Larry #
##########################################

#######################
# Polyfill from PHP 8 #
#######################
if (!function_exists('str_ends_with')) {
  function str_ends_with($str, $end) {
    return (@substr_compare($str, $end, -strlen($end))==0);
  }
}

##########################################################
# Returns array of all valid CNs for a given certificate #
##########################################################
function getCertCns($certfile, $hostname = null) {
  if (!file_exists($certfile)) return false;
  // Unraid supports subjectAltNames as of 6.10.3
  // logic from rc.nginx, read commonName and all subjectAltName's
  $cns = null;
  exec(
    "openssl x509 -noout -subject -nameopt multiline -in " . escapeshellarg($certfile) . "| sed -n 's/ *commonName *= //p';" .
    "openssl x509 -noout -ext subjectAltName -in " . escapeshellarg($certfile) . " | grep -Eo 'DNS:[a-zA-Z 0-9.*-]*' | sed 's/DNS://g'",
    $cns
  );
  $cns = array_unique($cns);
  foreach ($cns as &$cn) {
    if ($hostname && !str_ends_with($cn,'.myunraid.net')) $cn = str_replace('*', $hostname, $cn); # support custom wildcard certs
  }
  return $cns;
}

##################################################
# Checks whether cert is valid for expected_host #
##################################################
function checkCertCn($certfile, $hostname, $expected_host) {
  if (!file_exists($certfile)) return false;
  $cns = getCertCns($certfile, $hostname);
  foreach ($cns as $cn) {
    if (strtolower($cn) === strtolower($expected_host)) return true;
  }
  return false;
}

################################################
# Checks whether cert is for unraid.net domain #
################################################
function isUnraidLegacyCert($certfile) {
  if (!file_exists($certfile)) return false;
  $cns = getCertCns($certfile);
  foreach ($cns as $cn) {
    if (str_ends_with($cn,'.unraid.net')) return true;
  }
  return false;
}

##################################################
# Checks whether cert is for myunraid.net domain #
##################################################
function isUnraidWildcardCert($certfile) {
  if (!file_exists($certfile)) return false;
  $cns = getCertCns($certfile);
  foreach ($cns as $cn) {
    if (str_ends_with($cn,'.myunraid.net')) return true;
  }
  return false;
}

#################################################
# Checks whether cert was self-signed by Unraid #
#################################################
function isUnraidSelfSignedCert($certfile) {
  if (!file_exists($certfile)) return false;
  $data = null;
  exec("/usr/bin/openssl x509 -noout -subject -nameopt multiline -in ".escapeshellarg($certfile), $data);
  $data = implode("\n", $data);
  if (strpos($data, "Self-signed") !== false) return true;
  return false;
}

function write(...$messages){
  $com = curl_init();
  curl_setopt_array($com,[
    CURLOPT_URL => 'http://localhost/pub/diagnostics?buffer_length=1',
    CURLOPT_UNIX_SOCKET_PATH => '/var/run/nginx.socket',
    CURLOPT_POST => 1,
    CURLOPT_RETURNTRANSFER => true
  ]);
  foreach ($messages as $message) {
    curl_setopt($com, CURLOPT_POSTFIELDS, $message);
    curl_exec($com);
  }
  curl_close($com);
  }
?>