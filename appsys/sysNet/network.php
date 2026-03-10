<?php

namespace appsys\sysNet;

class network extends \table {
	public $pageName="系统网络";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "name";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'name','name'=>'网卡'],
				['col'=>'ip','name'=>'地址'],
				['col'=>'mask','name'=>'掩码'],
				['col'=>'gate','name'=>'网关'],
			],
			
			'colKey'=>$this->colKey,
			'toolEnable' => false,
			'toolAddEnable' => false,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operDelEnable'=> false,
			'fenyeEnable'=> false,
			
			'toolModInfo' =>[
				'askSure'=>"点击确定后，新IP生效，旧IP将不可用，确定要保存么",
			]
			
		];
		return $gridSet;
	}
	
	public function zdyData(){
		$data=$this->getNetwork();
		return $data;
	}
	
	
	public function crudModSet(){
		$post=$this->POST;
		$key = $post['key'];
		//$row=$this->getNetwork($key);
		
		$back=[];
		$back[]=[
			"name"=>"网卡",
			"col"=>"name",
			"type"=>'show',
			"width"=>'50px',
		];
		$back[]=[
			"name"=>"地址",
			"col"=>"ip",
			"type"=>'text',
			"ask"=>true,
			'valid'=>[
				'type'=>'ip',
			]
		];
		$back[]=[
			"name"=>"掩码",
			"col"=>"mask",
			"type"=>'text',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"网关",
			"col"=>"gate",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'ip',
			]
		];
		return $back;
	}
	
	public function crudModBefore(){
		$post = &$this->POST;
		$key = $post['key'];
		$form = $post['formVal'];
		$cmd = "sudo nmcli connection modify '".$key."' ipv4.addresses ".$form['ip']."/".$form['mask']." 2>&1";
		exec($cmd,$res,$code);
		if($code){
			return $this->out(1,'', join('.',$res));	
		}
		$cmd = "sudo nmcli connection modify '".$key."' ipv4.gateway ".$form['gate']." 2>&1";
		exec($cmd,$res,$code);
		if($code){
			return $this->out(1,'', join('.',$res));	
		}
		$cmd = "sudo nmcli connection modify '".$key."' ipv4.method manual  2>&1";
		exec($cmd,$res,$code);
		if($code){
			return $this->out(1,'', join('.',$res));	
		}
		$cmd = "sudo nmcli connection up '".$key."'  2>&1";
		exec($cmd,$res,$code);
		if($code){
			return $this->out(1,'', join('.',$res));	
		}
		
		return $this->out(0,'','修改成功');	
	}
	
	
	public function execute_restart(){
		$post = &$this->POST;
		$key = $post['key'];
		
		exec("sudo nmcli connection up '".$key."'  2>&1",$res,$code);
		if($code){
			return $this->out(0,'', join('.',$res));	
		}
		
		return ['code'=>0,'msg'=>'重启成功'];
		
	}
	
	
	
	public function getNetwork(){
		$iparray=array();
		$nameList=[
			'GENERAL.DEVICE' =>'name',
			'IP4.ADDRESS[1]' => 'ip',
			'IP4.GATEWAY' => 'gate',
		];
		
		exec("nmcli d show ",$result, $code);
		
		$myResa=[];
		$myreso=[];
		foreach($result as $line){
			if(!$line){
				$myResa[] = $myreso;
				$myreso=[];
				continue;
			}
			$lines = explode(":",$line,2);
			$key = trim($lines[0]);
			$val = trim($lines[1]);
			$myreso[$key] = $val;
		}
		$myResa[] = $myreso;
		$myreso=[];
		
		$netLists=[];
		foreach($myResa as $myreso){
			$netListo = [];
			$netListo['id'] = $myreso['GENERAL.DEVICE'];
			$netListo['name'] = $myreso['GENERAL.DEVICE'];
			$netListo['ip'] = $myreso['IP4.ADDRESS[1]'];
			$netListo['gate'] = $myreso['IP4.GATEWAY'];
			$ips = explode('/',$netListo['ip']);
			$netListo['ip'] = $ips[0];
			$netListo['mask'] = $ips[1];
			if($netListo['name']=='lo'){
				continue;
			}
			$netLists[] = $netListo;
		}
		return $netLists;
	}	
	
}

?>