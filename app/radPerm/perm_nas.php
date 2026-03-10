<?php
namespace app\radPerm;
use app\radNas\nasType;

class perm_nas extends \table{
	public $pageName='网络设备';
	public $TN = "sdaaa.nas";
	public $colKey = "na_ip";
	public $colOrder = "";
	public $colFid = "";
	public $colUnit = "na_organ";
	public $colName = "na_name";
	public $colNafy	= "na_ip";
	public $orderDesc = false;
	public $POST = [];
	public $editReg=[
		'quanx'=>'权限编辑',
	];
	
	public function gridBefore($db){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['keyList'][1];
		
		$db->leftJoin("sdaaa.perm_nas",function($mydb)use($gpid,$onid){
			$mydb->where("gpn_gpid",$gpid)
			->on('na_ip','gpn_naip');
		})->where('na_organ',$onid);
	
	}
	
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'na_name','name'=>'名称'],
				['col'=>'na_ip','name'=>'地址'],
				['col'=>'gpn_check','name'=>'设备',
					'valMap'=>[
						'0'=>'拒绝',
						'1'=>'允许',
					],
					'modify'=>function($val,&$row){
						if($val=='允许'){
							return [
								'value'=>$val,
								'color'=>'#00f'
							];
						}
						return $val;
					}
				],
				['col'=>'gpn_attr','name'=>'属性组',
					'valMap'=>permAttr::options(['0'=>'--'])
				],
				['col'=>'gpn_cmd','name'=>'命令组',
					'valMap'=>permCmd::options(['0'=>'--'])
				],
				['col'=>'gpn_shid','name'=>'时段组',
					'valMap'=>permShid::options(['0'=>'--'])
				],
				['col'=>'gpn_limit','name'=>'限制组',
					'valMap'=>permLimit::options(['0'=>'--'])
				],
				['col'=>'gpn_save','name'=>'修改',
					'type'=>'edit',
					'goto'=>'quanx',
					'popWidth'=>'60%',
					'popHeight'=>'60%',
					'align'=>'center',
				],
				['col'=>'gpn_reset','name'=>'重置',
					'modify'=>function($text,$row){
						if($row['gpnid']){
							return [
							'type'=>'execute',
							'name'=>'重置',
							'post'=>['goto'=>'clearOne'],
							'align'=>'center',
							'width'=>'60px',
						];
						}else{
							return '';
						}
					},
					'width'=>'30px',
					'align'=>'center',
				]
				
			],
			'toolEnable' => true,
			'toolAddEnable'=>false,
			'toolFilterEnable'=>false,
			'toolSearchColumn'=>[
				'na_name'=>'like',
				'na_ip'=>'like',	
			],
			'toolExpands'=>[ //form page execute batch html link list
				[
					'name'=>'全部重置',
					'type'=>'execute',
					'goto'=>'clearAll',
					//'router'=>'/sysAcct/sysAcct', 
				],
			],
			
			'operEnable' => false ,
		];
		
		return $gridSet;
	}

	public function gridAfter(&$data){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['keyList'][1];
		
		foreach($data as $i=>$row){
			$bacj=$this->realPermNas($gpid,$onid,$row['na_ip']);
			foreach($bacj as $k=>$v){
				$data[$i]['gpn_'.$k]=$v;
			}
		}
		
		//print_r($data);
		
	}
	
	public function realPermNas($gpid,$onid,$ip){
		$perm = \DB::table("sdaaa.perm")
		->where('gpid',$gpid)
		->first();
		$permOrgan = \DB::table("sdaaa.perm_organ")
		->where('gpo_gpid',$gpid)
		->where('gpo_onid',$onid)
		->first();
		$permNas = \DB::table("sdaaa.perm_nas")
		->where('gpn_gpid',$gpid)
		->where('gpn_naip',$ip)
		->first();
		
		$permAll = array_merge($permNas?:[],$permOrgan?:[],$perm);
		
		$result=[];
		$qzs=['gpn','gpo','gp'];
		$hzs=['check','attr','cmd','shid','limit'];
		
		foreach($hzs as $hz){
			$result[$hz] = 0;
			foreach($qzs as $qz){
				$col = $qz."_".$hz;
				if(isset($permAll[$col]) and strlen($permAll[$col])>0 and $permAll[$col]!='-1'){
					$res = $permAll[$col];
					$result[$hz] = $permAll[$col];
					break;
				}	
			}
		}
		return $result;
	}
	
	
	
	/*
	public function realPermAuth($gpid,$ip){
		$perm = \DB::table("sdaaa.ag_perm")
		->where('gpid',$gpid)
		->first();
		if(!$perm){//没选或者选中已删除，就取默认
			$perm = \DB::table("sdaaa.ag_perm")
			->where('gp_enable','1')
			->first();
		}
		$permNas = \DB::table("sdaaa.ag_perm_nas")
		->where('gpn_gpid',$gpid)
		->where('gpn_naip',$ip)
		->first();
		$permOrgan = \DB::table("sdaaa.ag_perm_organ")
		->where('gpo_gpid',$gpid)
		->first();
		
		
		$permAll = array_merge($perm?:[],$permOrgan?:[],$permNas?:[]);
		
		$result=[];
		$qzs=['gpn','gpo','gp'];
		$hzs=['check','attr','cmd','shid','limit'];
		
		foreach($hzs as $hz){
			$result[$hz] = 0;
			foreach($qzs as $qz){
				$col = $qz."_".$hz;
				if(isset($permAll[$col]) and strlen($permAll[$col])>0 and $permAll[$col]!='-1'){
					$res = $permAll[$col];
					$result[$hz] = $permAll[$col];
					break;
				}	
			}
		}
		return $result;
	}
	*/
	
	
	public function editSet_quanx(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['keyList'][1];
		$naip = $post['key'];
		
		$row=\DB::table('sdaaa.perm_nas')
			->where('gpn_gpid',$gpid)
			->where('gpn_naip',$naip)
			->first();
		
		$back=[];
		$back[]=[
			"name"=>"该设备",
			"col"=>"check",
			"type"=>'radio',
			"options"=>[
				'-1'=>'继承所属机构设置','0'=>'拒绝','1'=>'允许'
			],
			'value'=>(isset($row['gpn_check'])?$row['gpn_check']:'-1'),
			"ask"=>true,
			'hintMore'=>'是否允许在该设备上登录',
		];
		$back[]=[
			"name"=>"属性组",
			"col"=>"attr",
			"type"=>'select',
			"options"=>permAttr::options(['-1'=>'继承所属机构设置','0'=>'不使用']),
			"value"=>(isset($row['gpn_attr'])?$row['gpn_attr']:'-1'),
			"ask"=>true,
		];
		$back[]=[
			"name"=>"命令组",
			"col"=>"cmd",
			"type"=>'select',
			"options"=>permCmd::options(['-1'=>'继承所属机构设置','0'=>'不使用']) ,
			"value"=>(isset($row['gpn_cmd'])?$row['gpn_cmd']:'-1'),
			"ask"=>true,
		];
		$back[]=[
			"name"=>"时段组",
			"col"=>"shid",
			"type"=>'select',
			"options"=>permShid::options(['-1'=>'继承所属机构设置','0'=>'不使用']) ,
			"value"=>(isset($row['gpn_shid'])?$row['gpn_shid']:'-1'),
			"ask"=>true,
		];
		$back[]=[
			"name"=>"限制组",
			"col"=>"limit",
			"type"=>'select',
			"options"=>permLimit::options(['-1'=>'继承所属机构设置','0'=>'不使用']),
			"value"=>(isset($row['gpn_limit'])?$row['gpn_limit']:'-1'),	
			"ask"=>true,			
		];
		return $back;
	}
	
	public function editSave_quanx(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['keyList'][1];
		$naip = $post['key'];
		
		$check = $post['formVal']['check'];
		$attr = $post['formVal']['attr'];
		$cmd = $post['formVal']['cmd'];
		$shid = $post['formVal']['shid'];
		$limit = $post['formVal']['limit'];
		
		\DB::table('sdaaa.perm_nas')
			->where('gpn_gpid',$gpid)
			->where('gpn_naip',$naip)
			->delete();
		
		\DB::table('sdaaa.perm_nas')
		->insert([
			'gpn_gpid'=>$gpid,
			'gpn_naip'=>$naip,
			'gpn_check'=>$check,
			'gpn_attr'=>$attr,
			'gpn_cmd'=>$cmd,
			'gpn_shid'=>$shid,
			'gpn_limit'=>$limit,
		]);
		
		
		return $this->out(0,'','保存成功');
	}
	
	public function execute_clearOne(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['keyList'][1];
		$nasip = $post['key'];
		
		\DB::table('sdaaa.perm_nas')
			->where('gpn_gpid',$gpid)
			->where('gpn_naip',$nasip)
			->delete();
		
		return $this->out(0,'','',true); 
	}
	public function execute_clearAll(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['keyList'][1];
		
		$naslist = $this->DB()->where('na_organ',$onid)->get();
		foreach($naslist as $nl){
			\DB::table('sdaaa.perm_nas')
				->where('gpn_gpid',$gpid)
				->where('gpn_naip',$nl['na_ip'])
				->delete();
		}
		return $this->out(0,'','',true); 
	}
	
	
}
			

?>