<?php

namespace app\sdLdap;

class adGPO extends \table {
	public $pageName="组策略";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "displayname";
	public $colNafy = "loginname";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'displayname','name'=>'显示名'],
				['col'=>'loginname','name'=>'名称'],
				//['col'=>'dn','name'=>'dn'],
				['col'=>'description','name'=>'描述'],
				['col'=>'gp_organ','name'=>'应用',
					'type'=>'dialog',
					'popTitle'=>'应用对象列表',
					'router'=>'/sdLdap/adGPOMember',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
				['col'=>'gp_organ','name'=>'详情',
					'type'=>'dialog',
					'popTitle'=>'详情列表',
					'router'=>'/sdLdap/adGPOShow',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
			],
			
			'operExpands'=>[ //form page execute batch html link list
				[
					'name'=>'策略管理',
					'type'=>'dialog',
					'popTitle'=>'策略列表',
					'router'=>'/sdLdap/adGPOPolicy',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center',
				],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable' => true,
			'operDelEnable'=> true,
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
		$objectCategory="CN=Group-Policy-Container,CN=Schema,CN=Configuration,".$ldap_dn;
		$search_filter = '(&(objectCategory='.$objectCategory.')'.$myFileter.')';
		$attributes = array('cn', 'name', 'dn','description','displayname','gpcfilesyspath');
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
					"gpcfilesyspath" => adUtils::getVal($entries[$i],'gpcfilesyspath'),
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
		/*
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'text',
		];
		*/
		$back[]=[
				"name"=>"显示名",
				"col"=>"displayname",
				"ask"=>true,
				"type"=>'text',
		];
		/*
		$back[]=[
				"name"=>"描述",
				"col"=>"description",
				"type"=>'text',
		];
		*/
		return $back;
	}
	
	public function crudAddBefore(){
		$post=&$this->POST;
		
		$displayname = $post['formVal']['displayname'];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo create '".$displayname."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		if($code){
			return $this->out(0,'','添加失败：'.join('.',$res));	
		}
		
		return $this->out(0,'','添加成功');	
		
		/*
		$firstRow = $this->zdyData()[0];
		$gPCFileSysPath = explode("\\{",$firstRow['gpcfilesyspath'])[0]."\\{".strtoupper(adUtils::createGuid())."}";
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$loginname = $post['formVal']['loginname'];
		$displayname = $post['formVal']['displayname'];
		$description = $post['formVal']['description'];
		
		$mydn = 'CN='.$loginname.','.$post['formVal']['fadn'];	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "groupPolicyContainer";
		if($displayname)
			$ldaprecord['displayname'] = $displayname;
		if($description)
			$ldaprecord['description'] = $description;
		
		$ldaprecord['gpcfilesyspath'] = $gPCFileSysPath;
		$ldaprecord['flags'] = 0;
		$ldaprecord['gPCFunctionalityVersion'] = 2;
		$ldaprecord['gPCUserExtensionNames'] ="[{C6DC5466-785A-11D2-84D0-00C04FB169F7}{BACF5C8A-A3C7-11D1-A760-00C04FB9603F}]";
		
		$r = ldap_add($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		ldap_close($ldapconn);
		if(!$r){
			return $this->out(0,'','添加失败：'.$err );	
		}
		
		return $this->out(0,'','添加成功');	
		*/
	}
	
	
	
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=[];
		
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"显示名",
				"col"=>"displayname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"描述",
				"col"=>"description",
				"type"=>'text',
		];
		return $back;
	}
	
	
	public function crudModBefore(){
		$post=&$this->POST;
		$row = $this->currentRow;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$mydn = $this->POST['key'];
		$loginname = $post['formVal']['loginname'];
		$displayname = $post['formVal']['displayname'];
		$description = $post['formVal']['description'];
		
		
		if($displayname)
			$ldaprecord['displayname'] = $displayname;
		if($description)
			$ldaprecord['description'] = $description;
		
		$r = ldap_modify($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		if(!$r){
			ldap_close($ldapconn);
			return $this->out(0,'','修改失败：'.$err );	
		}
		
		
		$newdn = 'CN='.$loginname.','.$row['fadn'];	
		if($newdn != $mydn){
			$newdnx = 'CN='.$loginname;
			$fadnx = $post['formVal']['fadn'];
			$r = ldap_rename($ldapconn, $mydn, $newdnx, $fadnx , true);
			$err = ldap_error($ldapconn);
			if(!$r){
				ldap_close($ldapconn);
				return $this->out(1,'','修改失败：'.$err );	
			}
		}
		
		ldap_close($ldapconn);
		
		return $this->out(0,'','修改成功');	
	}
	
	
	
	public function crudDelBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		
		$name = $this->currentRow['loginname'];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo del '".$name."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		if($code){
			return $this->out(0,'','删除失败：'.join('.',$res));	
		}
		
		return $this->out(0,'','删除成功');	
		
		/*
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
		*/
	}
	
}

?>