<?php
namespace appsys\sysCron;

class sysCronDev extends \table{
	public $pageName='系统调度';
	public $TN = "{sysDB}.cron";
	public $colKey = "crid";
	public $colOrder = "cr_order";
	public $colFid = "";
	public $colName = "cr_name";
	public $colNafy = "";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'crid','name'=>'ID'],
				['col'=>'cr_name','name'=>'名称'],
				['col'=>'cr_time','name'=>'周期'],
				['col'=>'cr_timeout','name'=>'超时'],
				['col'=>'en_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center'
				],
			]
		];
		return $gridSet;
	}
	
	public function crudAddSet(){
		
		$back=[];
		$back[]=[
			"name"=>"姓名",
			"col"=>"cr_name",
			"type"=>"text",
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"周期",
			"col"=>"cr_time",
			"type"=>"text",
			"hintMore" => "每分钟：* * * * *，每10分钟：*/10 * * * *，每3小时：0 */3 * * *，每天4点：0 4 * * *",
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"超时",
			"col"=>"cr_timeout",
			"hintMore"=>"10s / 10m / 10h / 10d 默认12h",
			"type"=>"text",
		];
		$back[]=[
			"name"=>"命令",
			"col"=>"cr_script",
			"type"=>"text",
			"hintMore" => "文件开头：如: php ./aaa/bbb.php。文件名开头：/ 表示系统根目录、./ 表示站点里的app目录、sys/ 表示站点里的appsys目录",
			"ask"=>true,
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	
	public function crudAddAfter(){
		(new sysCron)->writeToFile();
	}
	
	public function crudModAfter(){
		(new sysCron)->writeToFile();
	}
	
	public function crudDelAfter(){
		(new sysCron)->writeToFile();
	}
	
	
}
?>