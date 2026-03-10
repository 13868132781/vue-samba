<?php

namespace app\sdLdap;

class adDomainAttr extends \table {
	public $pageName="域属性";
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
				
				$val = adUtils::val_ad2show($val,$da['aad_adtype'],$da['aad_typeopt']);
				
				$data[$i]['aad_val'] = $val;
			}
			
			
		}
		
		
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'aad_name','name'=>'名称'],
				['col'=>'aad_key','name'=>'属性'],
				['col'=>'aad_val','name'=>'值',
					'modify'=>function($text,$row){
						if(strstr($text,"<br/>")){
							return [
								"value"=>$text,
								"type"=>'html',
							];
						}
						return $text;
					}
				],
				['col'=>'aad_mod','name'=>'修改',
					'type'=>'edit',
					'popTitle'=>'修改属性值',
					'goto'=>'modVal',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center',
					'modify'=>function($text,$row){
						if(!$row['aad_modble']){
							return ['type'=>'text'];
						}
						return $text;
					}
				],
				['col'=>'aad_mark','name'=>'说明',
					'type'=>'fetch',
					'align'=>'center',
					'goto'=>'showMark',
					'popTitle'=>'详细说明',
					'popWidth'=>'800px',
					'popHeight'=>'500px',
					'icon' => 'leixing',
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
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			
			'toolSearchColumn'=>[
				'name'=>'like',	
			],
			
		];
		return $gridSet;
	}
	
	
	
	
	public function editSet_modVal(){
		$post=&$this->POST;
		$key = $post['key'];
		$row = $this->currentRow;
		$adkey = $row['aad_key'];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$search_filter = '(&(distinguishedName='.$ldap_dn.'))';
		$result = ldap_search($ldapconn, $ldap_dn, $search_filter);
		 
		// 获取搜索结果
		if ($result) {
			$entries = ldap_get_entries($ldapconn, $result);
			$adrow = $entries[0];
		}
		$value='';
		if($adrow and isset($adrow[$adkey])){
			$value = $adrow[$adkey][0];
		}
		
		$value = adUtils::val_ad2form($value,$row['aad_adtype'],$row['aad_typeopt']);
		
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
				"type"=>$row['aad_type'],
				"options"=>json_decode($row['aad_typeopt']?:'[]',true),
				"value" => $value ,
		];
		return $back;
	}
	
	
	public function editSaveBefore_modVal(){
		$post= $this->POST;
		$key = $post['key'];
		$row = $this->getById($post['key']);
		$val = $post['formVal']['aad_val'];
		
		$val = adUtils::val_form2ad($val,$row['aad_adtype'],$row['aad_typeopt']);
		
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
				"name"=>"表单类型",
				"col"=>"aad_type",
				"hintMore"=>"指示表单项所用插件",
				"type"=>'select',
				"value"=>'text',
				'options'=>adUtils::$attrFormType,
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"显示方式",
				"col"=>"aad_adtype",
				"hintMore"=>"用于 显示值--AD值--表单值 之间转换",
				"type"=>'select',
				"value"=>'n',
				'options'=>adUtils::$attrShowType,
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"类型扩展",
				"col"=>"aad_typeopt",
				"type"=>'text',
		];
		$back[]=[
				"name"=>"说明",
				"col"=>"aad_mark",
				"type"=>'text',
		];
		return $back;
	}
	
	
	public function crudModSet(){
		$post=$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	
	
	
	
}

?>