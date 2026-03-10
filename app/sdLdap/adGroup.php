<?php

namespace app\sdLdap;

class adGroup extends \table {
	public $pageName="域组";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "";
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
				['col'=>'description','name'=>'描述',
					'width'=>'150px',
					'ellipsis'=>true,
					'showInDlg'=>true,
				],
				['col'=>'gp_organ','name'=>'成员',
					'type'=>'dialog',
					'popTitle'=>'成员列表',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center',
					'tabList'=>[
						[
							'name' => '用户',
							'router'=>'/sdLdap/adGroupUser',
						],
						[
							'name' => '计算机',
							'router'=>'/sdLdap/adGroupComputer',
						],
						[
							'name' => '包含组',
							'router'=>'/sdLdap/adGroupGroup',
						],
						[
							'name' => '所属组',
							'router'=>'/sdLdap/adGroupGroupOf',
						]
					]
				],
			],
			
			
			'rowOper'=>function($row){
				if($row['loginname']=='Administrators'){
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
			'operModEnable' => false,
			'operDelEnable'=> true,
			'fenyeEnable'=> true,
		];
		return $gridSet;
	}
	
	public function zdyData(){
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$data=[];
		// 搜索LDAP
		$myFileter='';
		$objectCategory="CN=Group,CN=Schema,CN=Configuration,".$ldap_dn;
		$search_filter = '(&(objectCategory='.$objectCategory.')'.$myFileter.')';
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
		$post=&$this->POST;
		
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
				"options"=>adOrunit::options([],[],'','dnfy'),
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
		
		$loginname = $post['formVal']['loginname'];
		$displayname = $post['formVal']['displayname'];
		$description = $post['formVal']['description'];
		
		$mydn = 'CN='.$loginname.','.$post['formVal']['fadn'];	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "group";
		if($displayname)
			$ldaprecord['displayname'] = $displayname;
		if($description)
			$ldaprecord['description'] = $description;
		
		$r = ldap_add($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
		if(!$r){
			return $this->out(0,'','添加失败：'.$err );	
		}
		
		
		return $this->out(0,'','添加成功');	
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