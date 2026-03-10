<?php

namespace app\sdLdap;

class adGroupUser extends \table {
	public $pageName="域组成员用户";
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
				//['col'=>'dn','name'=>'dn'],
				//['col'=>'description','name'=>'描述'],
			],
			
			
			'toolEnable' => true,
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
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$keyList = $this->POST['keyList'];
		
		$data=[];
		// 搜索LDAP
		$objectCategory="CN=Person,CN=Schema,CN=Configuration,".$ldap_dn;
		$search_filter = '(&(objectCategory='.$objectCategory.')(memberof='.$keyList[0].'))';
		$attributes = array('cn', 'name', 'dn','description','displayname');
		$result = ldap_search($ldapconn, $ldap_dn, $search_filter, $attributes);
		
		// 获取搜索结果
		if (!$result) {
			return $data;
		}
		
		$entries = ldap_get_entries($ldapconn, $result);
		//sdAlert($entries);
		for ($i = 0; $i < $entries['count']; $i++) {
			$data[]=[
				"loginname" => $entries[$i]['name'][0],
				"displayname" => adUtils::getVal($entries[$i],'displayname'),
				"id" => $entries[$i]['dn'],
				"dn" => $entries[$i]['dn'],
				"description" => adUtils::getVal($entries[$i],'description'),
			];
		}
		ldap_close($ldapconn); 
		//sdAlert($data);
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