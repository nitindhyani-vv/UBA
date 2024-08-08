<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
$username = "dbo753285775";
$password = "PinsRise.1098";

$hostname = "db753285775.db.1and1.com";
$dbname   = "db753285775";

// if mysqldump is on the system path you do not need to specify the full path
// simply use "mysqldump --add-drop-table ..." in this case
$dumpfname = $dbname . "_" . date("d-m-Y_H-i-s").".sql";
$command = "mysqldump --add-drop-table --host=$hostname --user=$username ";
if ($password)
$command.= "--password=". $password ." ";
$command.= $dbname;
$command.= " > " . $dumpfname;
system($command);

rename($dumpfname, 'dashboard/backups/db/'.$dumpfname);

?>
