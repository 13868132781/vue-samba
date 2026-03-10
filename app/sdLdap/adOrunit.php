<?php

namespace app\sdLdap;

class adOrunit extends \table {
	public $pageName="OU";
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
				['col'=>'loginname','name'=>'名称']
			],
			
			'treeInfo' => [
				'col'=>'loginname',
				'depth'=>-1,//默认打开层级，-1所有层级
			],
			
			'rowOper'=>function($row){
				if($row['loginname']=='Users'){
					$row['_operEnable_']=false;
					$row['_operModEnable_']=false;
					$row['_operDelEnable_']=false;
				}
				return $row;
			},
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable' => true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			'toolSearchColumn'=>[
				'loginname'=>'like',
			]
		];
		return $gridSet;
	}
	
	public function zdyData(){
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$data=[];
		$userDn = "CN=Users,".$ldap_dn;
		$data[]=[
			"loginname" => 'Users',
			"id" => $userDn,
			"fid" => '0',
			"dn" => $userDn,
			"dnfy" => $this->dn2ufn($userDn,$ldap_dn),
			"fadn" => $ldap_dn,
			"order" => '1',
		];
		
		// 搜索LDAP
		$search_filter = '(&(objectClass=organizationalUnit))'; 
		//$search_filter = '(cn=*)';
		$attributes = array('cn', 'name', 'dn','whencreated');
		$result = ldap_search($ldapconn, $ldap_dn, $search_filter,$attributes);
		
		// 获取搜索结果
		if ($result) {
			$entries = ldap_get_entries($ldapconn, $result);
			//sdAlert($entries);
			for ($i = 0; $i < $entries['count']; $i++) {
				
				if($entries[$i]['name'][0]=='Domain Controllers'){
					continue;
				}
				
				$fid = explode(",",$entries[$i]['dn'],2)[1];
				if($fid==$ldap_dn){
					$fid='0';
				}
				
				$data[]=[
					"loginname" => $entries[$i]['name'][0],
					"id" => $entries[$i]['dn'],
					"fid" => $fid,
					"dn" => $entries[$i]['dn'],
					"dnfy" => $this->dn2ufn($entries[$i]['dn'],$ldap_dn),
					"fadn" => explode(",",$entries[$i]['dn'],2)[1],
					"order" => substr($entries[$i]['whencreated'][0],0,-3),
				];
			}
		}
		ldap_close($ldapconn); 
		
		//sdAlert($data);
		return $data;
	}
	
	
	public function crudAddSet(){
		$post=&$this->POST;
		$asArgs = (new adServer)->ldapConnArgs();
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"隶属",
				"col"=>"fadn",
				"ask"=>true,
				"type"=>'select',
				"options"=>$this->options([$asArgs['dn']=>'顶级'],null,'','dnfy'),
		];
		return $back;
	}
	
	public function crudAddBefore(){
		$post = &$this->POST;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$loginname = $post['formVal']['loginname'];
		
		$mydn = 'OU='.$loginname.','.$post['formVal']['fadn'];	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "organizationalUnit";
		
		//$ldaprecord["displayName"] = $displayname;// 显示名
		
		//$ldaprecord["UserAccountControl"] = "512";   //权限
		
		
		$r = ldap_add($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		if(!$r){
			ldap_close($ldapconn);
			return $this->out(1,'',$err);	
		}
		
		ldap_close($ldapconn);
		
		return $this->out(0,'','添加成功');	
	}
	
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	public function crudModBefore(){
		$post=&$this->POST;
		$key = $post['key'];
		$row = $this->currentRow;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$loginname = $post['formVal']['loginname'];
		
		$mydn = $this->POST['key'];	 
		
		//$r = ldap_modify($ldapconn, $mydn, $ldaprecord);

		$newdn = 'OU='.$loginname.','.$post['formVal']['fadn'];
		if($newdn != $mydn){
			$newdnx = 'OU='.$loginname;
			$fadnx = $post['formVal']['fadn'];
			$r = ldap_rename($ldapconn, $mydn, $newdnx, $fadnx , true);
			$err = ldap_error($ldapconn);
			if(!$r){
				ldap_close($ldapconn);
				return $this->out(1,'',$err);	
			}
		}
		
		ldap_close($ldapconn);
		
		return $this->out(0,'','修改成功');	
	}
	
	
	
	public function crudDelBefore(){
		$post=&$this->POST;
		
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
		
		
		
		return $this->out(0,'','删除成功'.$key);	
		
	}
	
	
	public function dn2ufn($dn,$f){
		$dn = substr($dn,0,strlen($f)*-1)."DC=顶级";
		//sdAlert([$dno,$f,$dn]);
		$back = [];
		$dns = explode("=",$dn);
		foreach($dns as $i=>$dno){
			if($i==0){
				continue;
			}
			
			$back[]=explode(",",$dno)[0];
		}
		$back = array_reverse($back);
		return join(">>",$back);
	}
	
	
	public static function optionsForUser(){
		
		
	}
	
	public static function optionsForSelf(){
		
		
	}
	
}

?>