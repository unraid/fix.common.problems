#!/usr/bin/php
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
  $script = "fix.common.problems.sh";
  
  @unlink("/etc/cron.daily/$script");
  @unlink("/etc/cron.hourly/$script");
  @unlink("/etc/cron.weekly/$script");
  @unlink("/etc/cron.monthly/$script");
  
  $settings = json_decode(@file_get_contents("/boot/config/plugins/fix.common.problems/settings.json"),true);
  
  if ( ( ! ($settings['frequency']??false) ) || ( $settings['frequency'] == "disabled" ) ) {
    exit;
  }
  $path = "/etc/cron.".$settings['frequency']."/$script";
  exec("cp /usr/local/emhttp/plugins/fix.common.problems/scripts/fix.common.problems.sh $path");
?>

