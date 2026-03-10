<?php

namespace app\sdLdap;

class adUserAttr extends \table {
	public $pageName="域账户属性";
	public $TN = "sdsamba.adattruser";
	public $colKey = "aauid";
	public $colOrder = "aau_order";
	public $colFid = "";
	public $colName = "aau_name";
	public $orderDesc = false;
	public $POST = [];
	
	
	
	public function gridAfter(&$data){
		$key = $this->POST['keyList'][0];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$search_filter = '(&(distinguishedName='.$key.'))';
		$result = ldap_search($ldapconn, $ldap_dn, $search_filter);
		 
		// 获取搜索结果
		if ($result) {
			$entries = ldap_get_entries($ldapconn, $result);
			//sdAlert($entries);
			$row = $entries[0];
			
			//echo "<pre>";
			//print_r($row);
			//echo "</pre>";
			
			foreach($data as $i=>$da){
				$key = $da['aau_key'];
				$val ='';
				if(isset($row[$key])){
					$val = $row[$key][0];
				}
				
				$val = adUtils::val_ad2show($val,$da['aau_adtype'],$da['aau_typeopt']);
				
				$data[$i]['aau_val'] = $val;
			}
			
			
		}
		
		
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'aau_name','name'=>'名称'],
				['col'=>'aau_key','name'=>'属性'],
				['col'=>'aau_val','name'=>'值',
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
				['col'=>'aau_mod','name'=>'修改',
					'type'=>'edit',
					'popTitle'=>'修改属性值',
					'goto'=>'modVal',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center',
					'modify'=>function($text,$row){
						if(!$row['aau_modble']){
							return ['type'=>'text'];
						}
						return $text;
					}
				],
				['col'=>'aau_mark','name'=>'说明',
					'type'=>'fetch',
					'align'=>'center',
					'goto'=>'showMark',
					'popTitle'=>'详细说明',
					'popWidth'=>'800px',
					'popHeight'=>'500px',
					'icon'=>'leixing',
					'modify'=>function($text,$row){
						if(!$text){
							return ['type'=>'text'];
						}
						return $text;
					},
				],
				['col'=>'aau_order','name'=>'排序',
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
		$keyList = $post['keyList'];
		$key = $post['key'];
		$row = $this->currentRow;
		$adkey = $row['aau_key'];
		
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$search_filter = '(&(distinguishedName='.$keyList[0].'))';
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
		
		$value = adUtils::val_ad2form($value,$row['aau_adtype'],$row['aau_typeopt']);
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"aau_name",
				"type"=>'show',
		];
		$back[]=[
				"name"=>"属性名",
				"col"=>"aau_key",
				"type"=>'show',
		];
		$back[]=[
				"name"=>"值",
				"col"=>"aau_val",
				"type"=>$row['aau_type'],
				"options"=>json_decode($row['aau_typeopt']?:'[]',true),
				"value" => $value,
		];
		return $back;
	}
	
	
	public function editSaveBefore_modVal(){
		$post=&$this->POST;
		
		$keyList = $post['keyList'];
		$key = $post['key'];
		$row = $this->currentRow;
		$adkey = $row['aau_key'];
		$val = $post['formVal']['aau_val'];
		
		$val = adUtils::val_form2ad($val,$row['aau_adtype'],$row['aau_typeopt']);
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$ldaprecord[$adkey] = $val;
		
		$r = ldap_modify($ldapconn, $keyList[0], $ldaprecord);

		ldap_close($ldapconn);
		
		return $this->out(0,'','修改成功');
	}
	
	
	
	public function fetch_showMark(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		
		return $this->out(0, $row['aau_mark']);
	}
	
	
	
	
	
	
	
	
	
	public function crudAddSet(){
		$post=$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"aau_name",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"属性名",
				"col"=>"aau_key",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"是否可修改",
				"col"=>"aau_modble",
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
				"col"=>"aau_type",
				"hintMore"=>"指示表单项所用插件",
				"type"=>'select',
				"value"=>'text',
				'options'=>adUtils::$attrFormType,
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"显示方式",
				"col"=>"aau_adtype",
				"hintMore"=>"用于 显示值--AD值--表单值 之间转换",
				"type"=>'select',
				"value"=>'n',
				'options'=>adUtils::$attrShowType,
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"类型扩展",
				"col"=>"aau_typeopt",
				"type"=>'text',
		];
		$back[]=[
				"name"=>"说明",
				"col"=>"aau_mark",
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