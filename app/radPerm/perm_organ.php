<?php
namespace app\radPerm;

class perm_organ extends \table{
	public $pageName='角色机构';
	public $TN = "sdaaa.nasorgan";
	public $colKey = "onid";
	public $colOrder = "on_order";
	public $colFid = "on_fid";
	public $colName = "on_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridBefore($db){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		
		$db->leftJoin("sdaaa.perm_organ",function($mydb)use($gpid){
			$mydb->where("gpo_gpid",$gpid)
			->on('onid','gpo_onid');
		});
	
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'on_name','name'=>'名称'],
				['col'=>'gpo_check','name'=>'设备',
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
				['col'=>'gpo_attr','name'=>'属性组',
					'valMap'=>permAttr::options(['0'=>'--'])
				],
				['col'=>'gpo_cmd','name'=>'命令组',
					'valMap'=>permCmd::options(['0'=>'--'])
				],
				['col'=>'gpo_shid','name'=>'时段组',
					'valMap'=>permShid::options(['0'=>'--'])
				],
				['col'=>'gpo_limit','name'=>'限制组',
					'valMap'=>permLimit::options(['0'=>'--'])
				],
				['col'=>'gpo_save','name'=>'修改',
					'type'=>'edit',
					'goto'=>'quanx',
					'align'=>'center',
				],
				['col'=>'gpo_save','name'=>'设备',
					'type'=>'dialog',
					'router'=>'/radPerm/perm_nas',
					'align'=>'center',
				],
				['col'=>'gpo_reset','name'=>'重置',
					'modify'=>function($text,$row){
						if($row['gpoid']){
							return [
							'type'=>'execute',
							'name'=>'重置',
							'goto'=>'clearOne',
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
			'treeInfo' => [
				'col'=>'on_name',
				'depth'=>-1,//默认打开层级，-1所有层级
			],
			
			'toolEnable' => true,
			'toolAddEnable'=>false,
			'toolFilterEnable'=>false,
			'toolExpands'=>[ //form page execute batch html link list
				[
					'name'=>'全部重置',
					'type'=>'execute',
					'goto'=>'clearAll',
					//'router'=>'/sysAcct/sysAcct', 
				],
			],
			'operEnable' => false ,
				
				
			'fenyeEnable'=> false,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	public function gridAfter(&$data){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		
		foreach($data as $i=>$row){
			$bacj=$this->realPermOrgan($gpid,$row[$this->colKey]);
			foreach($bacj as $k=>$v){
				$data[$i]['gpo_'.$k]=$v;
			}
		}
		
		//print_r($data);
	}
	
	
	public function realPermOrgan($gpid,$onid){
		$perm = \DB::table("sdaaa.perm")
		->where('gpid',$gpid)
		->first();
		$permOrgan = \DB::table("sdaaa.perm_organ")
		->where('gpo_gpid',$gpid)
		->where('gpo_onid',$onid)
		->first();
		
		$permAll = array_merge($perm,$permOrgan?:[]);
		
		$result=[];
		$qzs=['gpo','gp'];
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
	
	
	public function editSet_quanx(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['key'];
		
		$row=\DB::table('sdaaa.perm_organ')
			->where('gpo_gpid',$gpid)
			->where('gpo_onid',$onid)
			->first();
		
		$back=[];
		$back[]=[
			"name"=>"所属设备",
			"col"=>"check",
			"type"=>'radio',
			"options"=>[
				'-1'=>'继承全局设置','0'=>'拒绝','1'=>'允许'
			],
			'value'=>(isset($row['gpo_check'])?$row['gpo_check']:'-1'),
			"ask"=>true,
			'hintMore'=>'是否允许在该机构所属设备上登录',
		];
		$back[]=[
			"name"=>"属性组",
			"col"=>"attr",
			"type"=>'select',
			"options"=>permAttr::options(['-1'=>'继承全局设置','0'=>'不使用']),
			"value"=>(isset($row['gpo_attr'])?$row['gpo_attr']:'-1'),
			"ask"=>true,
		];
		$back[]=[
			"name"=>"命令组",
			"col"=>"cmd",
			"type"=>'select',
			"options"=>permCmd::options(['-1'=>'继承全局设置','0'=>'不使用']) ,
			"value"=>(isset($row['gpo_cmd'])?$row['gpo_cmd']:'-1'),
			"ask"=>true,
		];
		$back[]=[
			"name"=>"时段组",
			"col"=>"shid",
			"type"=>'select',
			"options"=>permShid::options(['-1'=>'继承全局设置','0'=>'不使用']) ,
			"value"=>(isset($row['gpo_shid'])?$row['gpo_shid']:'-1'),
			"ask"=>true,
		];
		$back[]=[
			"name"=>"限制组",
			"col"=>"limit",
			"type"=>'select',
			"options"=>permLimit::options(['-1'=>'继承全局设置','0'=>'不使用']),
			"value"=>(isset($row['gpo_limit'])?$row['gpo_limit']:'-1'),	
			"ask"=>true,			
		];
		return $back;
	}
	
	public function editSaveBefore_quanx(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['key'];
		
		$check = $post['formVal']['check'];
		$attr = $post['formVal']['attr'];
		$cmd = $post['formVal']['cmd'];
		$shid = $post['formVal']['shid'];
		$limit = $post['formVal']['limit'];
		
		
		\DB::table('sdaaa.perm_organ')
			->where('gpo_gpid',$gpid)
			->where('gpo_onid',$onid)
			->delete();
		
		\DB::table('sdaaa.perm_organ')
			->insert([
				'gpo_gpid'=>$gpid,
				'gpo_onid'=>$onid,
				'gpo_check'=>$check,
				'gpo_attr'=>$attr,
				'gpo_cmd'=>$cmd,
				'gpo_shid'=>$shid,
				'gpo_limit'=>$limit,
			]);
		
		
		return $this->out(0,'','保存成功');
	}
	
	public function execute_clearOne(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['key'];
		
		\DB::table('sdaaa.perm_organ')
			->where('gpo_gpid',$gpid)
			->where('gpo_onid',$onid)
			->delete();
		
		$naslist = \DB::table('sdaaa.nas')->where('na_organ',$onid)->get();
		foreach($naslist as $nl){
			\DB::table('sdaaa.perm_nas')
				->where('gpn_gpid',$gpid)
				->where('gpn_naip',$nl['na_ip'])
				->delete();
		}
		
		return $this->out(0,'','',true); 
	}
	public function execute_clearAll(){
		$post = $this->POST;
		$gpid = $post['keyList'][0];
		
		\DB::table('sdaaa.perm_organ')
			->where('gpo_gpid',$gpid)
			->delete();
		
		\DB::table('sdaaa.perm_nas')
			->where('gpn_gpid',$gpid)
			->delete();
		
		return $this->out(0,'','',true); 
	}
	
	
	public function onoff_check(){
		$post=$this->POST;
		$gpid = $post['keyList'][0];
		$onid = $post['key'];
		
		$val = $post['val'];
		
		$row=$this->DB()->doExec("select * from sdaaa.ag_perm_organ where gpo_gpid='".$gpid."' and gpo_onid='".$onid."'");
		
		if($row and count($row)>0){
			\DB::table('sdaaa.perm_organ')
			->where('gpo_gpid',$gpid)
			->where('gpo_onid',$onid)
			->update([
				'gpo_check'=>$val,
			]);
		}else{
			\DB::table('sdaaa.perm_organ')
			->insert([
				'gpo_gpid'=>$gpid,
				'gpo_onid'=>$onid,
				'gpo_check'=>$val,
			]);
		}
		
		return  $this->out(0,'','保存成功');
	}
	
}


?>