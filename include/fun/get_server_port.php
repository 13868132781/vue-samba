<?php

function _HLCFUNC_PORT_SSH($p){
	if($p!=''){
		exec("sudo sed -i '/^Port/cPort ".$p."' /etc/ssh/sshd_config");
		exec("sudo /etc/init.d/ssh restart");
	}
	return exec("sudo cat /etc/ssh/sshd_config | grep Port | awk '{printf $2}'");
}
$_HLCPHP['portssh']="_HLCFUNC_PORT_SSH";



function _HLCFUNC_PORT_XRDP($p){
	if($p!=''){
		exec("sudo sed -i  '1,7s/port=[0-9].*/port=".$p."/' /etc/xrdp/xrdp.ini ");
		exec("sudo /etc/init.d/ssh restart");
	}
	return exec("head /etc/xrdp/xrdp.ini -n 7 | grep port | awk -F = '{printf $2}'");
}
$_HLCPHP['portxrdp']="_HLCFUNC_PORT_XRDP";
?>