<?php
namespace appsys\service;

class serviceDev extends \table{
	public $pageName="系统服务";
	public $TN = "{sysDB}.service";
	public $colKey = "svid";
	public $colOrder = "sv_order";
	public $colFid = "";
	public $colName = "sv_name";
	public $orderDesc = false;
	public $POST = [];
	public $classPrex = "appsys\service\srv_";
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'sv_name','name'=>'名称'],
				['col'=>'sv_mark','name'=>'说明'],
				['col'=>'sv_class','name'=>'php类名'],
				['col'=>'sv_debugable','name'=>'可调试'],
				['col'=>'sv_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center'
				],
			],
			
			'fenyeEnable'=> false,
			
		];
		return $gridSet;
	}

	
	public function crudAddSet(){
		
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"sv_name",
			"type"=>'text',
			"ask"=>true,
			"unique" =>true,			
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"sv_mark",
			"type"=>'text',			
		];
		$back[]=[
			"name"=>"命令",
			"col"=>"sv_script",
			"type"=>'text',
			'hintMore'=>"若提供命令，则启动命令为服务，如: php ./aaa/bbb.php。文件名开头：/ 表示系统根目录、./ 表示站点里的app目录、sys/ 表示站点里的appsys目录",
		];
		
		$back[]=[
			"name"=>"可否调试",
			"col"=>"sv_debugable",
			"type"=>'select',
			"options"=>[
				'0'=>'不可调试',
				'1'=>'可以调试',
			],
			"value"=>'0',
			"ask"=>true,
		];
		
		$back[]=[
			"name"=>"调试参数",
			"col"=>"sv_debug",
			"type"=>'text',
			"value"=>'debug',
			'hintMore'=>"若提供命令，运行时调试参数将跟在命令后面，如: php ./aaa/bbb.php debug",
		];
		
		$back[]=[
			"name"=>"控制类名",
			"col"=>"sv_class",
			"type"=>'text',
			"ask"=>true, 
			"value" =>'default',
			"hintMore"=>"服务控制类的类名，若提供命令，这里填default"
		];
		
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	
	public function crudAddBefore(){
		$post = &$this->POST;
		if($post['formVal']['sv_script']!=''){
			$post['formVal']['sv_class']='default';
		}
	}
	public function crudModBefore(){
		$post = &$this->POST;
		if($post['formVal']['sv_script']!=''){
			$post['formVal']['sv_class']='default';
		}
		$row = $this->currentRow;
		$class = $this->classPrex.$row['sv_class'];
		$class::do_init($row['sv_script'],$row['sv_debug']);
		$res = $class::do_stop();
		$this->setDBSety($post['key'],'0');
	}
	
	public function crudDelBefore(){
		$row = $this->currentRow;
		$class = $this->classPrex.$row['sv_class'];
		$class::do_init($row['sv_script'],$row['sv_debug']);
		$res = $class::do_stop();
		$this->setDBSety($post['key'],'0');
	}
	
	
	//设置表里的设定状态值，该值用于检测服务状态是否符合设定
	public function setDBSety($key,$sety){
		$this->DB()->where($this->colKey,$key)
			->update([
				'sv_sety'=>$sety,
		]);
	}

}


?>