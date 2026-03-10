<?php

namespace app\sdLdap;

class adDns extends \table {
	public $pageName="域DNS";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "fid";
	public $colName = "loginname";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'loginname','name'=>'主机名','wordBreak'=>true],
				//['col'=>'dn','name'=>'dn','wordBreak'=>true],
				//['col'=>'description','name'=>'描述'],
				['col'=>'dnsrstr','name'=>'DNS记录','wordBreak'=>true,'type'=>'html'],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable' => false,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function zdyData($inopt=[]){
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		$ldap_ym = $ldapobj[2];
		$ldap_dnf = 'DC='.$ldap_ym.',CN=MicrosoftDNS,DC=DomainDnsZones,'.$ldap_dn;
		
		$data=[];
		// 搜索LDAP
		$myFileter='';
		if(isset($inopt['byid'])){
			$myFileter='(distinguishedName='.$inopt['byid'].')';
		}
		$objectCategory="CN=Dns-Node,CN=Schema,CN=Configuration,".$ldap_dn;
		$search_filter = '(&(objectCategory='.$objectCategory.')'.$myFileter.')';
		//$search_filter = '';
		$attributes = [];// array('cn', 'name', 'dn','description');
		$result = ldap_search($ldapconn, $ldap_dnf, $search_filter, $attributes);
		
		
		// 获取搜索结果
		if ($result) {
			$entries = ldap_get_entries($ldapconn, $result);
			//sdAlert($entries);
			for ($i = 0; $i < $entries['count']; $i++) {
				if(strstr($entries[$i]['name'][0],'.')){
					continue;
				}
				if(in_array($entries[$i]['name'][0],['@','_msdcs','DomainDnsZones','ForestDnsZones'])){
					continue;
				}
				
				$description='';
				if(isset($entries[$i]['description'])){
					$description=$entries[$i]['description'][0];
				}
				$dnsRecords = [];
				$dnsRStr = "";
				if(isset($entries[$i]['dnsrecord'])){
					$attrdnsrecord = $entries[$i]['dnsrecord'];
					for($ri=0;$ri<$attrdnsrecord['count'];$ri++){
						$dnsRecordo = $this->decodeDNSRecord($attrdnsrecord[$ri]);
						$dnsRecords[] = $dnsRecordo;
						if($dnsRStr) $dnsRStr.= '<br/>';
						$dnsRStr .= 'type='.$dnsRecordo['type'].',data='.$dnsRecordo['data'];
					}
				}
				
				$data[]=[
					"loginname" => $entries[$i]['name'][0],
					"id" => $entries[$i]['dn'],
					"dn" => $entries[$i]['dn'],
					"dnsrstr" => $dnsRStr,
					"dnsrecords" => $dnsRecords,
					"description" => $description,
				];
			}
		}
		ldap_close($ldapconn); 
		
		if(isset($inopt['count']) and $inopt['count'] ){
			return $entries['count'];
		}
		
		//sdAlert($data);
		return $data;
	}
	
	
	public function crudAddSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"主机名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"IP地址",
				"col"=>"address",
				"ask"=>true,
				"type"=>'text',
		];
		/*
		$back[]=[
				"name"=>"隶属",
				"col"=>"fadn",
				"ask"=>true,
				"type"=>'select',
				"options"=>adOrunit::options([],[],'','dnfy'),
		];
		*/
		return $back;
	}
	
	
	
	public function crudAddBefore(){
		$post=&$this->POST;
		
		$loginname = $post['formVal']['loginname'];
		$address = $post['formVal']['address'];
		
		$opt=[
			'name' => $loginname, 
			'type' => 'A',
			'data' => $address,
		];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool dns add ".$asArgs['ip']." ".$asArgs['domain']." '".$opt['name']."' ".$opt['type']." '".$opt['data']."' ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		
		array_unshift($res,$cmd);
		$ress = join('。',$res);
		
		if($code){
			return $this->out(1,'','添加失败：'.$ress);	
		}
		
		return $this->out(0,'','添加成功');	
	}
	
	
	
	
	public function crudmodSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"记录",
				"col"=>"dnsrstr",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"描述",
				"col"=>"description",
				"ask"=>true,
				"type"=>'text',
		];
		return $back;
	}
	
	public function crudModBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$modarr=[
			'description' => $post['formVal']['description'],
		];
		
		$r = ldap_modify($ldapconn, $key,$modarr);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
			
		if(!$r){
			return $this->out(1,'','修改失败：'.$err);	
		}
		
		return $this->out(0,'','修改成功');		
	}
	
	public function crudDelBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$r = ldap_delete($ldapconn, $key);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
		
		
		if(!$r){
			return $this->out(1,'','删除失败：'.$err);	
		}
		
		return $this->out(0,'','删除成功');		
	}
	
	
	
	public function decodeDNSRecord($binaryData) {
		//格式参考samba源码里的dnsp.idl里的dnsp_DnssrvRpcRecord结构体
		
		$hexstr = bin2hex($binaryData);
		
		$wDataLength = unpack('v',substr($binaryData, 0,2))[1];
		$wType = unpack('v', substr($binaryData, 2,2))[1];
		$version = unpack('C', $binaryData[4])[1];
		$rank = unpack('C', $binaryData[5])[1];
		$flags = unpack('v', substr($binaryData, 6,2))[1];
		$dwSerial = unpack('V',substr($binaryData, 8,4))[1];
		$ttl = unpack('N', substr($binaryData, 12,4))[1];
		$dwReserved = unpack('V',substr($binaryData, 16,4))[1];
		$dwTimeStamp = unpack('V',substr($binaryData, 20,4))[1];
		
		$wData = "";
		if($wType==1){//A记录
			$ipData = unpack('N', substr($binaryData, 24,4))[1];
			$wData = long2ip($ipData);
			
		}else if($wType==2){//NS记录
			$moreData = substr($binaryData, 24,$wDataLength);
			$wData = $this->disDnspName($moreData);
			
		}else if($wType==33){//SRV记录
			$moreData = substr($binaryData, 24,$wDataLength);
			$wPriority = unpack('n', substr($moreData, 0,2))[1];
			$wWeight = unpack('n', substr($moreData, 2,2))[1];
			$wPort = unpack('n', substr($moreData, 4,2))[1];
			$mLen = unpack('C', substr($moreData, 6,1))[1];
			$mname = $this->disDnspName(substr($moreData, 6,$mLen+2));
			$wData = "nameTarget=".$mname;
			
		}else if($wType==6){//SOA记录
			$moreData = substr($binaryData, 24,$wDataLength);
			$soaSerial = unpack('N', substr($moreData, 0,4))[1];
			$soaRefresh = unpack('N', substr($moreData, 4,4))[1];
			$soaRetry = unpack('N', substr($moreData, 8,4))[1];
			$soaExpire = unpack('N', substr($moreData, 12,4))[1];
			$soaMinimum = unpack('N', substr($moreData, 16,4))[1];
			$mLen = unpack('C', substr($moreData, 20,1))[1];
			$mname = $this->disDnspName(substr($moreData, 20,$mLen+2));
			$rlen = unpack('C', substr($moreData, 20+$mLen+2,1))[1];
			$rname = $this->disDnspName(substr($moreData, 20+$mLen+2,$rlen+2));
			$wData = 'ns='.$mname.';mail='.$rname;
		}
		
		$ret = [
			'hex' => $hexstr,
			'type' => $wType,
			'rank' => $rank,
			'flags' => $flags,
			'serial' =>$dwSerial,
			'ttl' => $ttl,
			'data' => $wData,
		];
		
		return $ret;
	}
	
	public function disDnspName($moreData){
		$totalLen = unpack('C', $moreData[0])[1];//后续数据长度，不包含最后的00
		$moreData = substr ($moreData ,0 ,$totalLen+1);//根据上面的长度，截取数据
		$totalCount = unpack('C', $moreData[1])[1];//后续字符串个数
		$myip = '';
		$myOffset=2;
		for($t=0;$t<$totalCount;$t++){//逐个处理每个字符串
			$strLen = unpack('C', $moreData[$myOffset])[1];//这个字符串长度
			$myOffset++;
			$strdj = substr($moreData, $myOffset,$strLen);//按照长度取真实字符串
			$myOffset+=$strLen;
			if($myip)$myip.='.';//用.连接字符串
			$myip .= $strdj;
		}
		return $myip;
	}
	
	
	
	public function dns_get($opt=[]){
		$row = (new adServer)->ldapConnArgs();
		
		$serverIp = explode(':',explode("//",$row['as_ip'])[1])[0];
		
		$cmd ="samba-tool dns query ".$serverIp." ".$row['domain']." @ ALL -U ".$row['user']." --password='".$row['pass']."'";
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		
		array_unshift($res,$cmd);
		$ress = join('。',$res);
		
		$data=[];
		for($i=0;$i<count($res);$i++){
			$line = $res[$i];
			if(!strstr($line,"Name=")){
				continue;
			}
			$lines=explode(',',$line);
			$name = explode('=',$lines[0])[1];
			$recount = explode('=',$lines[1])[1];
			$children = explode('=',$lines[2])[1];
			
			$records=[];
			$recordstr='';
			for($j=$i+1;$j< intval($recount)+$i+1;$j++ ){
				$records[] = $res[$j];
				
				if($recordstr){$recordstr.='<br/>';}
				$recordstr.= trim($res[$j]);
			}
			
			$row1=[
				'name'=>$name,
				'name'=>$name,
				'records'=>$records,
				'recordstr'=>$recordstr
			];
			
			$data[]=$row1;
		}
		return [$code ,$ress, $data ];	
	}
	
	
	
}

?>