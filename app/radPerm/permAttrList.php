<?php

namespace app\radPerm;

class permattrList extends \table{
	public $pageName='属性组';
	public $TN = "sdaaa.nas_typeattr";
	public $colKey = "ntaid";
	public $colOrder = "nta_order";
	public $colFid = "";
	public $colName = "nta_attr";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridBefore($db){
		$db->leftJoin('sdaaa.nas_type','nta_ntid','=','ntid')
			->orderBy('nt_order')
			->orderBy('ntid')
			->orderBy('nta_pro');
	}
	
	public function gridAfter(&$data){
		$post = $this->POST;
		$key = $post['keyList'][0];
		
		$attrall=\DB::table('sdaaa.permattr_list')
				->field('gal_ntid','gal_pro','gal_attr','gal_val')
				->where('gal_gaid', '=', $key)
				->get();
		$attrarray=[];
		foreach($attrall as $row){
			$arrkey = $row['gal_ntid']."%".$row['gal_pro']."%".$row['gal_attr'];
			$attrarray[$arrkey] = $row['gal_val'];
		}
		
		foreach($data as $di=>$dv){
			$arrkey = $dv['nta_ntid']."%".$dv['nta_pro']."%".$dv['nta_attr'];
			if(isset($attrarray[$arrkey])){
				$data[$di]['saveVal'] = $attrarray[$arrkey];
			}
		}
		
	}
	
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'nt_name','name'=>'类型',
					'modify'=>function($text){
						if(!$text){
							return '公共属性';
						}
						return $text;
					}
				],
				['col'=>'nta_pro','name'=>'协议',
					'valMap'=>[
						'0'=>'radius',
						'1'=>'tacacs',
					]
				],
				['col'=>'nta_attr','name'=>'名称'],
				['col'=>'saveVal','name'=>'值'],
				
				['col'=>'saveVal','name'=>'修改',
					'type'=>'edit',
					'popTitle'=>'修改',
					'goto'=>'modVal',
					'align'=>'center',
				],
			],
			'toolEnable' => false,
			'operEnable' => false ,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function editSet_modVal(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->currentRow;
		$oldVal = $post['val'];
		
		$vals = explode(';;',$row['nta_val']);
		$optt = [];
		foreach($vals as $v){
			$optt[$v]=$v;
		}
		
		$back=[];
		$back[]=[
				"name"=> $row['nta_attr'],
				"col"=> 'saveVal',
				"type"=> $row['nta_val']?'select':'text',
				//"ask"=>true, 
				'options'=> $optt,
				'value'=> $oldVal
			];
		
		return $back;
	}
	
	public function editSaveBefore_modVal(){
		$post=$this->POST;
		$gaid = $post['keyList'][0];
		$key = $post['key'];
		$row = $this->currentRow;
		$val = $post['formVal']['saveVal'];
		
		\DB::table('sdaaa.permattr_list')
			->where('gal_gaid', '=', $gaid)
			->where('gal_ntid', '=', $row['nta_ntid'])
			->where('gal_attr', '=', $row['nta_attr'])
			->delete();
		
		if($val){
			\DB::table('sdaaa.permattr_list')
			->insert([
				'gal_gaid' => $gaid,
				'gal_pro' =>$row['nta_pro'],
				'gal_ntid' => $row['nta_ntid'],
				'gal_attr' => $row['nta_attr'],
				'gal_val' => $val
			]);
		}
		
		return $this->out(0,'','修改成功');
	}
	
}