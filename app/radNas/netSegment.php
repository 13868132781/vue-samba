<?php
namespace app\radNas;
use app\perm\perm;
use app\service\srv_radius;

class netSegment extends \table{
	public $pageName='设备网段';
	public $TN = "sdaaa.netsegment";
	public $colKey = "nsid";
	public $colOrder = "ns_order";
	public $colFid = "";
	public $colUnit = "";
	public $colName = "ns_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'nsid','name'=>'编号','jsCtrl'=>['width'=>'30px']],
				['col'=>'ns_name','name'=>'名称'],
				['col'=>'ns_seg','name'=>'网段'],
				['col'=>'ns_secret','name'=>'密钥'],
				['col'=>'ns_mark','name'=>'说明'],
				['col'=>'ns_type','name'=>'类型',
					'valMap'=>nasType::options() 
				],
				['col'=>'ns_organ','name'=>'机构',
					'valMap'=>radNasOrgan::options() 
				],
				['col'=>'ns_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
				
			],
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'toolFilterEnable'=>false,
			/*
			'toolExpands'=>[ 
				[
					'type'=>'dialog',
					'name'=>'公共属性',
					'router'=>'/radNas/nasTypeAttr',
					'post'=>[
						'keyList'=>[0]
					],
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
			],
			*/
			
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> false,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
		
	public function crudAddSet(){
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"ns_name",
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"网段",
			"col"=>"ns_seg",
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"密钥",
			"col"=>"ns_secret",
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"ns_mark",
			"type"=>'text',
			'value'=>'',
		];
		
		$back[]=[
			"name"=>"类型",
			"col"=>"ns_type",
			"type"=>'select',
			'options'=>nasType::options(),
		];
		
		$ourow=(new radNasOrgan)->getFirst();
		$back[]=[
			"name"=>"机构",
			"col"=>"ns_organ",
			"type"=>'treePick',
			"router"=>'/radNas/radNasOrgan',
			"value"=>$ourow?$ourow['onid']:'',//默认值
			"xsname"=>$ourow?$ourow['on_name']:'',//默认显示
			"ask"=>true, 
			"import"=>true,
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	
	
	
	
	public function crudAddAfter(){
		$post = &$this->POST;
		
		$seg = $post['formVal']['ns_seg'] ;
		
		$a1=explode("/",$seg);
		$a=explode(".",$a1[0]);
		if ($a1[1]=='8'){
			$ip_min=$a[0].'.0.0.0';
			$ip_max=$a[0].'.255.255.255';
		}elseif($a1[1]=='16'){	
			$ip_min=$a[0].'.'.$a[1].'.0.0';
			$ip_max=$a[0].'.'.$a[1].'.255.255';	
		}elseif($a1[1]=='24'){
			$ip_min=$a[0].'.'.$a[1].'.'.$a[2].'.0';
			$ip_max=$a[0].'.'.$a[1].'.'.$a[2].'.255';		
		}else{
			$ip_min=$a1[0];	
			$ip_max=$a1[0];		
		}
		$ip_start=ip2long($ip_min);
		$ip_end=ip2long($ip_max);
		
		$this->DB()->where($this->colKey,$post['key'])
		->update([
			'ns_start' => $ip_start,
			'ns_end' => $ip_end
		]);
		
		
		$data=$this->DB()->get();
		$string='';
		$fileName="/etc/freeradius/3.0/clients.conf";
		foreach($data as $da){
			$string.="client ".$da['ns_name']." {"."\n";
			$string.="	ipaddr =  ".$da['ns_seg']."\n";
			$string.="	secret =  ".$da['ns_secret']."\n";
			$string.="}"."\n";
		}
		file_put_contents("/temp/clients.conf",$string);
		exec("sudo rm ".$fileName);
		exec("sudo mv /temp/clients.conf ".$fileName);
		exec("sudo chmod 755 ".$fileName);
		
		$class = new srv_radius();
		$class::do_stop();
		$class::do_start();
	
	}
	public function crudModAfter(){
		return $this->crudAddAfter();
	}
	
	
}

?>