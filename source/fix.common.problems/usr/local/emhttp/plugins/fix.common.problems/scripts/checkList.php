<?
########################################
#                                      #
# Fix Common Problems                  #
# Copyright 2020-2024, Lime Technology #
# Copyright 2015-2024, Andrew Zawadzki #
#                                      #
# Licenced under GPLv2                 #
#                                      #
########################################

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: "/usr/local/emhttp";
if (is_file("$docroot/webGui/include/MarkdownExtra.inc.php") ) {
    require_once "$docroot/webGui/include/MarkdownExtra.inc.php";
}
require_once("/usr/local/emhttp/plugins/dynamix/include/Markdown.php");

$checkList = "
* Implied Cache Only shares do not have files / folders stored outside the cache drive
* Cache Only shares do not have files / folders stored outside the cache drive
* Array Only shares do not have files / folders stored on the cache drive
* Dynamix checking for plugin updates
* Dynamix checking for docker updates
* Community Applications Installed - Only because of its plugin auto update feature
* Community Applications set to auto update itself
* Dynamix WebUI set to auto update (via Community Applications)
* This plugin set to auto update itself (via Community Applications)
* Ability for the server to communicate to the outside world (ping github.com)
* Ability to write a file to each drive in array and cache
* Ability to write a file to the flash drive
* Ability to write a file to the docker image
* Similar named shares only differing by case (eg: MyShare and myshare)
* Default appdata storage location is set to /mnt/cache/.... (skipped 6.2-rc3+)
* Default appdata storage location is a cache only share
* Look for disabled disks
* Look for missing disks
* Look for read errors
* Look for file system errors
* Check if plugins are up to date (and ignore if autoupdate settings are enabled in CA)
* Check for docker applications updates available
* Check individual docker application's /config mappings set to /mnt/user (should be /mnt/cache) (skipped 6.2-rc3+)
* Check for /var/log greater than 50% full
* Check for tmpfs greater than 75 % full
* Check for docker image file greater than 80% full
* Check for scheduled parity checks
* Check for shares with included and excluded disks both set
* Check for shares with both included and excluded disks having overlaps
* Check for global share settings both included and excluded disks set
* Check for global share settings with included and excluded disks having overlaps
* Check for only supported file system types (reiserFS, xfs, btrfs) on array / cache devices
* Check for flash drive formatted as fat32
* Check for built-in FTP server running
* Check for destination set for Alert level notifications
* Check for destination set for Warning level notifications 
* Check for email server and recipient addresses set if email notifications are selected
* Check for plugins installed being blacklisted
* Check for plugins installed not being known to Community Applications (implies incompatible)
* Check for ad blocker's interfering with unRaid
* Check for illegal characters in share names
* Check for docker applications not running in the network mode template author specifies
* Check for HPA on drives  (Error on parity, other warning for all other drives)
* Check for illegal suffixes on cacheFloor settings
* Check for cache free space less than cacheFloor
* Check for cache floor greater than cache total space
* Check for permissions of 0777 on shares
* Check for Hack Attacks on your server
* Check for Moderated / Blacklisted docker applications
* Check for plugins incompatible for your unRaid version
* Check for cache only shares, but no cache drive
* Check for user shares named the same as a disk share
* Check for extra parameters set via Repository section instead of Extra Parameters Section (docker Apps)
* Check for Out Of Memory errors
* Check for MCE errors
* Check for Files / Folders contained within /mnt (anything other than disk1,disk2, etc or cache or disks
* Check for exhaustion of inotify watches
* Check for SSD cache drive formatted as reiserFS (format does not support trim)
* Check for SSD cache drive, but Dynamix SSD Trim not installed
* Check for Marvel Based controller
* Check for Directory Bread's (ie: flash drive disconnected)
* Check for minimum 2G memory installed
* Incompatible docker applications installed
* Check for CPU overheating
* Check for Stats plugin installed, but Preclear Not installed
* Mover logging enabled
* PHP Warnings enabled via Tips and Tweaks plugin
* Disk included in share setting that doesn't exist on array
* Check for deprecated --cpuset-cpus in extraparameters AND via CPU pinning via GUI
* Check for collisions on CPU isolation and Docker CPU pinning (multiple collisions only)
* Check for invalid docker template xml's
* Check for write cache disabled on drives
* Check for syslog being mirrored to flash drive
* Check for UD installed but not UD+
* Check for unRaid being mitigated against sysdream
* Check for CA Notifications being enabled
* Legecy methods of device isolation (6.9+)
* Network bonding issues
* Extra packages within /boot/extra
* Authorized keys being set up via go file
* Using reserved names as share names
* Check for root password set
* Check for xmrig (possible hack)
* 6.9.2 Check for Spaces in shares set to use cache: Prefer
* Check for invalid TLD naming
* Check for legacy method of setting docker host IP address
* Slave mode on UD mounted disks / shares for docker containers
* Check for non-existent cache pools being referenced in share settings
* Check for files stored within a cache pool that isn't allowed within a share's settings
* Check for flash drive corruption
* Check for Date and Time on server being incorrect by significant amount
</strong>
";
echo Markdown($checkList);
?>
