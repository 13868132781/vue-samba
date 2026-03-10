<?php
namespace app\radNas;
use app\radPerm\perm;
use app\radScript\script;

class radNas extends \table{
	public $pageName='网络设备';
	public $TN = "sdaaa.nas";
	public $colKey = "naid";
	public $colOrder = "";
	public $colFid = "";
	public $colUnit = "na_organ";
	public $colName = "na_name";
	public $colNafy	= "na_ip";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'na_name','name'=>'名称'],
				['col'=>'na_ip','name'=>'地址'],
				['col'=>'na_type','name'=>'类型',
					'valMap'=>nasType::options() 
				],
				['col'=>'na_bwid','name'=>'备份',
					'valMap'=>script::options() 
				],
				['col'=>'na_active','name'=>'启停',
					'type'=>'onoff',
					'goto'=>'active',
					'align'=>'center',
				],
				['col'=>'na_status','name'=>'状态',
					'valMap'=>[
						'0'=>'0',
						'_default_'=>'1',
					],
					'type'=>'state',
					'goto'=>'test',
					'align'=>'center',
					'width'=>'30px',
					'headBatch'=>[//这是给表头批量按钮用的，
						'batchAll'=>true,//不检查chackbox，所以行都执行	
					]
				],
				
			],
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => true,
			'toolRefreshEnable'=> true,
			'toolFilterEnable'=>true,
			'toolSearchColumn'=>[
				'na_name'=>'like',
				'na_ip'=>'like',	
			],
			'toolImportEnable'=>true,
			'toolExpands'=>[ //form page execute batch html link list
			],
			
				
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> true,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	
	public function filterSet(){
		
		$back=[
			[
				"name"=>"设备名",
				"col"=>"na_name",
				"type"=>'text',
			],
			[
				"name"=>"IP地址",
				"col"=>"na_ip",
				"type"=>'text',
				'valid'=>[
					'type'=>'ip',	
				]
			],
			[
				"name"=>"状态",
				"col"=>"na_status",
				"type"=>'radio',
				'options'=>[
					'0'=>'断开',
					'raw:>0'=>'连通',
				]
			],
			[
				"name"=>"启停",
				"col"=>"na_active",
				"type"=>'radio',
				"options"=>[
					'0'=>'停用',
					'1'=>'启用'
				],
			],
			[
				"name"=>"类型",
				"col"=>"na_type",
				"type"=>'select',
				'options'=>nasType::options(),
			]
		];
		return $back;
	}
	
	
	
	
	
	public function crudAddSet(){//获取编辑字段信息
		$back=[];
		$back[]=[
			"name"=>"设备名",
			"col"=>"na_name",
			"type"=>'text',
			"ask"=>true, 
			'unique'=>true,
			"import"=>true,
		];
		$back[]=[
			"name"=>"设备IP",
			"col"=>"na_ip",
			"type"=>'text',
			'valid'=>[
				'type'=>'ip'
			],
			"ask"=>true, 
			'unique'=>true,
			"import"=>true,
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"na_mark",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"密钥",
			"col"=>"na_secret",
			"type"=>'text',
			"ask"=>true,
			"import"=>true,
		];
		
		$ourow=(new radNasOrgan)->getFirst();
		$back[]=[
			"name"=>"机构",
			"col"=>"na_organ",
			"type"=>'treePick',
			"router"=>'/radNas/radNasOrgan',
			"value"=>$ourow?$ourow['onid']:'',//默认值
			"xsname"=>$ourow?$ourow['on_name']:'',//默认显示
			"ask"=>true, 
			"import"=>true,
		];
		$back[]=[
			"name"=>"类型",
			"col"=>"na_type",
			"type"=>'select',
			'options'=>nasType::options(),
			"ask"=>true, 
			"import"=>true,
		];
		$back[]=[
			"name"=>"备份方式",
			"col"=>"na_bwid",
			"type"=>'select',
			'options'=>script::optionsBackup(),
			"ask"=>true,
			"import"=>true,			
		];
		$back[]=[
			"name"=>"连接方式",
			"col"=>"na_ssh",
			"type"=>'radio',
			'options'=>[
					'0'=>'telnet',
					'1'=>'ssh1',
					'2'=>'ssh2',
				],
			"ask"=>true, 
			"import"=>true,
		];
		$back[]=[
			"name"=>"TE密码",
			"col"=>"na_tac_enable",
			"type"=>'text',
			"hintMore"=>"tacacs enable密码"
		];
		$back[]=[
				"name"=>"额外变量",
				"col"=>"na_exvar",
				"type"=>'table',
				'headers'=>['变量名','变量值'],
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	public function state_test(){
		$post=$this->POST;
		$key = $post['key'];
		
		$res=exec('php '.__DIR__.'/ht/ping.php '.$key,$back,$code);
		if($code!=0){
			return $this->out(1,'','报错：'.join("<br/>",$back));
		}
		$ress = explode('<returnstr>',$res);
		$returns='{"code":"100","logid":"-1"}';
		if(count($ress)<2){
			return $this->out(1,'','返回数据有误：'.$res);
		}
		$vals = json_decode($ress[1],true);
		if(!$vals or !isset($vals['ip']) or !isset($vals['code'])){
			return $this->out(1,'','返回数据格式错误：'.$ress[1]);
		}
		
		$code = ($vals['code']==0?0:1);
		
		
		return $this->out($code);
	}
	
}


?>