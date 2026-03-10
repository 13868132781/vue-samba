<?php

namespace app\sdLdap;

class adComputer extends \table {
	public $pageName="域计算机";
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
				['col'=>'loginname','name'=>'名称'],
				['col'=>'displayname','name'=>'显示名'],
				['col'=>'dn','name'=>'dn'],
				['col'=>'description','name'=>'描述'],
			],
			
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable' => true,
			'operDelEnable'=> false,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function zdySource($inopt=[]){
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$data=[];
		// 搜索LDAP
		$myFileter='';
		if(isset($inopt['byid'])){
			$myFileter='(distinguishedName='.$inopt['byid'].')';
		}
		$objectCategory="CN=Computer,CN=Schema,CN=Configuration,".$ldap_dn;
		$search_filter = '(&(objectCategory='.$objectCategory.')'.$myFileter.')';
		$attributes = array('cn', 'name', 'dn','description','displayname');
		$result = ldap_search($ldapconn, $ldap_dn, $search_filter, $attributes);
		
		
		// 获取搜索结果
		if ($result) {
			$entries = ldap_get_entries($ldapconn, $result);
			//sdAlert($entries);
			for ($i = 0; $i < $entries['count']; $i++) {
				
				$data[]=[
					"loginname" => $entries[$i]['name'][0],
					"displayname" => adUtils::getVal($entries[$i],'displayname'),
					"id" => $entries[$i]['dn'],
					"dn" => $entries[$i]['dn'],
					"description" => adUtils::getVal($entries[$i],'description'),
					"fadn" => explode(",",$entries[$i]['dn'],2)[1],
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
		$asArgs = (new adServer)->ldapConnArgs();
		
		$back=[];
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"显示名",
				"col"=>"displayname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"隶属",
				"col"=>"fadn",
				"ask"=>true,
				"type"=>'select',
				"options"=>adOrunit::options([
					"CN=Computers,".$asArgs['dn'] => 'Computers',
					"OU=Domain Controllers,".$asArgs['dn'] => 'Domain Controllers',
				],null,'','dnfy'),
				"valueIndex"=>0,
		];
		$back[]=[
				"name"=>"描述",
				"col"=>"description",
				"type"=>'text',
		];
		return $back;
	}
	
	public function crudAddBefore(){
		$post=&$this->POST;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		$ldap_url = $ldapobj[2];
		
		$loginname = $post['formVal']['loginname'];
		$displayname = $post['formVal']['displayname'];
		$description = $post['formVal']['description'];
		
		$mydn = 'CN='.$loginname.','.$post['formVal']['fadn'];	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "computer";
		if($displayname)
			$ldaprecord['displayname'] = $displayname;
		if($description)
			$ldaprecord['description'] = $description;
		$ldaprecord['dnshostname'] = $loginname.".".$ldap_url;
		
		$r = ldap_add($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
		if(!$r){
			return $this->out(0,'','添加失败：'.$err );	
		}
		
		return $this->out(0,'','添加成功');	
	}
	
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	public function crudModBefore(){
		$post=&$this->POST;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		$ldap_url = $ldapobj[2];
		
		$mydn = $this->POST['key'];
		$loginname = $post['formVal']['loginname'];
		$displayname = $post['formVal']['displayname'];
		$description = $post['formVal']['description'];
		
		
		if($displayname)
			$ldaprecord['displayname'] = $displayname;
		if($description)
			$ldaprecord['description'] = $description;
		$ldaprecord['dnshostname'] = $loginname.".".$ldap_url;
		
		$r = ldap_modify($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		if(!$r){
			ldap_close($ldapconn);
			return $this->out(0,'','修改失败：'.$err );	
		}
		
		
		$newdn = 'CN='.$loginname.','.$post['formVal']['fadn'];	
		if($newdn != $mydn){
			$newdnx = 'CN='.$loginname;
			$fadnx = $post['formVal']['fadn'];
			$r = ldap_rename($ldapconn, $mydn, $newdnx, $fadnx , true);
			$err = ldap_error($ldapconn);
			if(!$r){
				ldap_close($ldapconn);
				return $this->out(0,'','修改失败：'.$err );	
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
		
		$r = @ldap_delete($ldapconn, $key);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
		
		
		if(!$r){
			return $this->out(1,'',$err);		
		}
		
		return $this->out(0,'','删除成功');	
		
	}
	
}

?>