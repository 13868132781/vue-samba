<?php
$timestart = microtime(true);
exec('php radexec.php "section=authen||nas=192.168.0.202||user=admin||pass=qqq000,,,||nac=192.168.0.204||state="');
//exec('ls');
$t2 =  microtime(true);
echo "==\n\n==php test time: ".round($t2-$timestart,3)."s\n\n";

?>