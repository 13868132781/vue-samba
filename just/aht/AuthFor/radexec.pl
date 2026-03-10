
#
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA
#
#  Copyright 2002  The FreeRADIUS server project
#  Copyright 2002  Boian Jordanov <bjordanov@orbitel.bg>
#

#
# Example code for use with rlm_perl
#
# You can use every module that comes with your perl distribution!
#
# If you are using DBI and do some queries to DB, please be sure to
# use the CLONE function to initialize the DBI connection to DB.
#

#use strict;
use warnings;
#use DBI;
use File::Basename;
use Time::HiRes qw(gettimeofday tv_interval);

# use ...
use Data::Dumper;

# Bring the global hashes into the package scope
our (%RAD_REQUEST, %RAD_REPLY, %RAD_CONFIG , %RAD_CHECK, %RAD_STATE);

#这样的全局哈希表，是否会造成不同包并发时冲突呢
our %RAD_PERL ;

# This is hash wich hold original request from radius
#my %RAD_REQUEST;
# In this hash you add values that will be returned to NAS.
#my %RAD_REPLY;
#This is for check items
#my %RAD_CHECK;
# This is the session-sate
#my %RAD_STATE;
# This is configuration items from "config" perl module configuration section
#my %RAD_PERLCONF;

# Multi-value attributes are mapped to perl arrayrefs.
#
#  update request {
#    Filter-Id := 'foo'
#    Filter-Id += 'bar'
#  }
#
# This results to the following entry in %RAD_REQUEST:
#
#  $RAD_REQUEST{'Filter-Id'} = [ 'foo', 'bar' ];
#
# Likewise, you can assign an arrayref to return multi-value attributes

#
# This the remapping of return values
#
use constant {
	RLM_MODULE_REJECT   => 0, # immediately reject the request
	RLM_MODULE_OK       => 2, # the module is OK, continue
	RLM_MODULE_HANDLED  => 3, # the module handled the request, so stop
	RLM_MODULE_INVALID  => 4, # the module considers the request invalid
	RLM_MODULE_USERLOCK => 5, # reject the request (user is locked out)
	RLM_MODULE_NOTFOUND => 6, # user not found
	RLM_MODULE_NOOP     => 7, # module succeeded without doing anything
	RLM_MODULE_UPDATED  => 8, # OK (pairs modified)
	RLM_MODULE_NUMCODES => 9  # How many return codes there are
};

# Same as src/include/log.h
use constant {
	L_AUTH         => 2,  # Authentication message
	L_INFO         => 3,  # Informational message
	L_ERR          => 4,  # Error message
	L_WARN         => 5,  # Warning
	L_PROXY        => 6,  # Proxy messages
	L_ACCT         => 7,  # Accounting messages
	L_DBG          => 16, # Only displayed when debugging is enabled
	L_DBG_WARN     => 17, # Warning only displayed when debugging is enabled
	L_DBG_ERR      => 18, # Error only displayed when debugging is enabled
	L_DBG_WARN_REQ => 19, # Less severe warning only displayed when debugging is enabled
	L_DBG_ERR_REQ  => 20, # Less severe error only displayed when debugging is enabled
};

#加载模块时，实例化时，应该在这里加载nas和secret列表
sub instantiate {
	
}

# Function to handle authorize
sub authorize {
	my $code = &execphp("author");
	return $code;
}

# Function to handle authenticate
sub authenticate {
	my $code = &execphp("authen");
	return $code;
}

# Function to handle preacct
sub preacct {

	return RLM_MODULE_OK;
}

# Function to handle accounting
sub accounting {

	return RLM_MODULE_OK;
}

# Function to handle checksimul
sub checksimul {

	return RLM_MODULE_OK;
}

# Function to handle pre_proxy
sub pre_proxy {

	return RLM_MODULE_OK;
}

# Function to handle post_proxy
sub post_proxy {

	return RLM_MODULE_OK; 
}

# Function to handle post_auth
sub post_auth {
	&execphp("postauth");
	return RLM_MODULE_OK;
}

# Function to handle xlat
sub xlat {
	# Loads some external perl and evaluate it
	my ($filename,$a,$b,$c,$d) = @_;
	&radiusd::radlog(L_DBG, "From xlat $filename ");
	&radiusd::radlog(L_DBG,"From xlat $a $b $c $d ");
	local *FH;
	open FH, $filename or die "open '$filename' $!";
	local($/) = undef;
	my $sub = <FH>;
	close FH;
	my $eval = qq{ sub handler{ $sub;} };
	eval $eval;
	eval {main->handler;};
}

#卸载模块时 Function to handle detach
sub detach {
	
} 

our $execcount = 0;
sub execphp {
	my $timestart = [gettimeofday];
	my($section) = @_;
	
	$execcount++;
	&radiusd::radlog(L_DBG,"sd-log: execcount: ".$execcount);
	&radiusd::radlog(L_DBG,"sd-log: execsection: ".$section);
	
	my $args = "from=rad||section=".$section;
	$args .= "||"."user=".$RAD_REQUEST{'User-Name'};
	$args .= "||"."pass=".$RAD_REQUEST{'User-Password'};
	$args .= "||"."nas=".$RAD_REQUEST{'NAS-IP-Address'};
	$args .= "||"."nac=".(defined($RAD_REQUEST{'Calling-Station-Id'})?$RAD_REQUEST{'Calling-Station-Id'}:'');
	$args .= "||"."state=".(defined($RAD_REQUEST{'State'})?$RAD_REQUEST{'State'}:'');
	$args .= "||"."posttype=".(defined($RAD_CONFIG{'Post-Auth-Type'})?$RAD_CONFIG{'Post-Auth-Type'}:''); 
	
	foreach my $key (keys(%RAD_PERL)){
		if($key eq ""){next;}#全局定义时%RAD_PERL = "",造成此问题
		my $record = $RAD_PERL{$key}; 
		$args .= "||".$key."=".$record;
		&radiusd::radlog(L_DBG,"sd-log: input: ".$key."=".$record);
	}
	
	&radiusd::radlog(L_DBG,"sd-log: php input: ".$args);
	my $result=readpipe("php ".dirname(__FILE__)."/radexec.php \"".$args."\"");
	&radiusd::radlog(L_DBG,"sd-log: php return: ".$result);
	
	my @resulta = split(/<tofather>/,$result);
	if(@resulta > 1){
		$result = $resulta[1];
	}
	
	my $code = "1";
	my @results = split(/\|\|/,$result); 
	foreach my $resulto (@results) {
		&radiusd::radlog(L_DBG, "sd-log: output: ".$resulto);
		my @attrs = split(/\=/,$resulto,2);
		my $attr0 = &trim($attrs[0]);
		my $attr1 = &trim($attrs[1],"'\"\\s");
		if($attr0 eq "code"){
			$code = $attr1;
			next;
		}
		if($attr0 eq "msg"){
			next;
		}
		my @names = split(/\:/,$attr0,2);
		my $value = $attr1;
		if ($names[0] eq "request"){
			$RAD_REQUEST{$names[1]} = $value;
		}elsif($names[0] eq "check"){
			$RAD_CHECK{$names[1]} = $value;
		}elsif($names[0] eq "reply"){
			$RAD_REPLY{$names[1]} = $value;
		}elsif($names[0] eq "config"){
			$RAD_CONFIG{$names[1]} = $value;
		}elsif($names[0] eq "perl"){
			$RAD_PERL{$names[1]} = $value;
		}
	}
	
	my $timeinter = tv_interval($timestart,[gettimeofday]);
	&radiusd::radlog(L_DBG, "sd-log:perl take time: ".sprintf("%.3f",$timeinter)."s");
	
	if($code eq "0"){ 
		return 2;
	}else{
		return 0;
	}
}

sub log_request_attributes {
	# This shouldn't be done in production environments!
	# This is only meant for debugging!
	for (keys %RAD_REQUEST) {
		&radiusd::radlog(L_DBG, "RAD_REQUEST: $_ = $RAD_REQUEST{$_}");
	}
}


sub trim 
{ 
	my $string = shift;
	my $str = shift;
	my $all = shift;#把$str作为一个整体来匹配
	if(defined($str)==0 ){
		$string =~ s/^\s+//; 
		$string =~ s/\s+$//; 
	}elsif(defined($all)==0){
		$string =~ s/^[$str]+//; 
		$string =~ s/[$str]+$//;	
	}else{
		$string =~ s/^($str)+//; 
		$string =~ s/($str)+$//;
	}
	return $string; 
}

#my $string="hongchenghongg";
#my $stro = "hong";
#print(trim($string,$stro));
