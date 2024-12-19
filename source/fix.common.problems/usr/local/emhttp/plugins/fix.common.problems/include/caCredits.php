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

function getLineCount($directory) {
  global $lineCount, $charCount;

  $allFiles = array_diff(scandir($directory),array(".",".."));
  foreach ($allFiles as $file) {
    if (is_dir("$directory/$file")) {
      getLineCount("$directory/$file");
      continue;
    }
    $extension = pathinfo("$directory/$file",PATHINFO_EXTENSION);
    if ( $extension == "sh" || $extension == "php" || $extension == "page" ) {
      $lineCount = $lineCount + count(file("$directory/$file"));
      $charCount = $charCount + filesize("$directory/$file");
    }
  }
}

$caCredits = "
<style>
table {background-color:transparent;}
</style>
    <center><table align:'center'>
      <tr>
        <td><img src='https://github.com/Squidly271/plugin-repository/raw/master/Chode_300.gif' width='50px';height='48px'></td>
        <td><strong>Andrew Zawadzki</strong></td>
        <td>Main Development</td>
      </tr>
    </table></center>
    <br>
    <center><em>Copyright 2020-2024 Lime Technology</em></center>
    <center><em>Copyright 2015-2024 Andrew Zawadzki</em></center>

  ";
 
  $caCredits = str_replace("\n","",$caCredits);
?>