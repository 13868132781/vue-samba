<?php

/*
已废弃，只用于参考 【设定】的写法

*/

namespace app\sdLdap;

class adDomain extends \table {
	public $pageName="系统网络";
	public $TN = "sdsamba.adattrdomain";
	public $colKey = "aadid";
	public $colOrder = "aad_order";
	public $colFid = "";
	public $colName = "aad_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridAfter(&$data){
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$search_filter = '(&(distinguishedName='.$ldap_dn.'))';
		$result = ldap_search($ldapconn, $ldap_dn, $search_filter);
		 
		// 获取搜索结果
		if ($result) {
			$entries = ldap_get_entries($ldapconn, $result);
			//sdAlert($entries);
			$row = $entries[0];
			foreach($data as $i=>$da){
				$key = $da['aad_key'];
				$val ='';
				if(isset($row[$key])){
					$val = $row[$key][0];
				}
				
				$val = (new adServer)->valfy1($da['aad_type'],$val);
				
				$data[$i]['aad_val'] = $val;
			}
			
			
		}
		
		
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'aad_name','name'=>'名称'],
				['col'=>'aad_key','name'=>'属性'],
				['col'=>'aad_val','name'=>'值'],
				['col'=>'aad_mark','name'=>'说明',
					'type'=>'fetch',
					'align'=>'center',
					'goto'=>'showMark',
					'popTitle'=>'详细说明',
					'popWidth'=>'800px',
					'popHeight'=>'500px',
					'modify'=>function($text,$row){
						if(!$text){
							return ['type'=>'text'];
						}
						return $text;
					},
				],
				['col'=>'aad_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
			],
			
			'rowOper'=>function($row){
				if($row['aad_key']=='distinguishedname'){
					$row['_operModEnable_']=false;
					$row['_operDelEnable_']=false;
				}elseif(!$row['aad_modble']){
					$row['_operModEnable_']=false;
				}
				return $row;
			},
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			
			'operExpands'=>[ //form page execute batch html link list
				[
					'name'=>'设定',
					'type'=>'edit',
					'goto'=>'cfgsheding',
					//'router'=>'/sysAcct/sysAcct', 
				],
			],
			
			
			'toolSearchColumn'=>[
				'name'=>'like',	
			],
			
		];
		return $gridSet;
	}
	
	
	
	public function crudAddSet(){
		$post=$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"aad_name",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"属性名",
				"col"=>"aad_key",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"是否可修改",
				"col"=>"aad_modble",
				"type"=>'radio',
				"value"=>'1',
				'options'=>[
					'0' => '不可修改',
					'1' => '可修改'
				],
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"说明",
				"col"=>"aad_mark",
				"type"=>'text',
		];
		return $back;
	}
	
	
	public function editSet_cfgsheding(){
		$post=$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	
	
	
	
	
	
	public function crudModSet(){
		$post=$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"aad_name",
				"type"=>'show',
		];
		$back[]=[
				"name"=>"属性名",
				"col"=>"aad_key",
				"type"=>'show',
		];
		$back[]=[
				"name"=>"值",
				"col"=>"aad_val",
				"type"=>'text',
		];
		return $back;
	}
	
	
	public function crudModBefore(&$post){
		$key = $post['key'];
		$row = $this->getById($post['key']);
		$val = $post['formVal']['aad_val'];
		
		$val = (new adServer)->valfy2($da['aad_type'],$val);
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$ldaprecord[$row['aad_key']] = $val;
		
		$r = ldap_modify($ldapconn, $ldap_dn, $ldaprecord);

		ldap_close($ldapconn);
		
		return $this->out(0,'','修改成功');
	}
	
	
	public function fetch_showMark(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		
		return $this->out(0, $row['aad_mark']);
	}
	
	
	
}

?>