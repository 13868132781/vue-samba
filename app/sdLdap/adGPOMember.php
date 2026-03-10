<?php

namespace app\sdLdap;

class adGPOMember extends \table {
	public $pageName="组策略成员";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "dn";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'dn','name'=>'dn'],
			],
			
			
			'toolEnable' => false,
			'toolAddEnable' => false,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operEnable'=> false,
			'operModEnable' => false,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function zdySource($inopt=[]){
		
		$post=&$this->POST;
		$fakey = $post['keyList'][0];
		$fakeyname = explode(',',explode('=',$fakey)[1])[0];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo listcontainers '".$fakeyname."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		if($code){
			sdError([$cmd,$res]);
		}
		
		$data=[];
		foreach($res as $line){
			$line = trim($line);
			if(substr($line, 0, strlen('DN:')) === 'DN:'){
				$clval = trim(explode(':',$line,2)[1]);
				$data[]=[
					'id' => $clval,
					'dn' => $clval,
				];
			}
		}
		
		
		return $data;
	}
	
	
	public function crudAddSet(){
		$post=$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"隶属",
				"col"=>"oudn",
				"ask"=>true,
				"type"=>'select',
				"options"=>adGroup::options([],['needtop'=>true],'','dnfy'),
		];
		return $back;
	}
	
	public function crudAddBefore(){
		$post = &$this->POST;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$loginname = $post['formVal']['loginname'];
		
		$newdn = 'OU='.$loginname.','.$post['formVal']['oudn'];	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "organizationalUnit";
		
		//$ldaprecord["displayName"] = $displayname;// 显示名
		
		//$ldaprecord["UserAccountControl"] = "512";   //权限
		
		//$r = ldap_add($ldapconn, $newdn, $ldaprecord);

		ldap_close($ldapconn);
		
		return $this->out(0,'','添加成功');	
	}
	
	
	public function crudDelBefore(){
		$post = &$this->POST;
		
		$key = $post['key'];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		//$r = @ldap_delete($ldapconn, $key);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
		
		
		if(!$r){
			return $this->out(1,'',$err);		
		}
		
		
		
		return $this->out(0,'','删除成功');	
		
	}
	
}

?>