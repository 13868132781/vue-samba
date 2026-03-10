<?php

namespace app\sdLdap;

class adGPOPolicy extends \table {
	public $pageName="组策略policy";
	public $TN = "";
	public $colKey = "sdid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "sdid";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'class','name'=>'所属'],
				['col'=>'keyname','name'=>'键名'],
				['col'=>'valuename','name'=>'值名'],
				['col'=>'type','name'=>'类型'],
				['col'=>'data','name'=>'数据'],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable' => true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function zdySource($inopt=[]){
		
		$post=&$this->POST;
		$key = $post['keyList'][0];
		$keyname = explode(',',explode('=',$key)[1])[0];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo show '".$keyname."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		if($code){
			sdError([$cmd,$res]);
		}
		
		$data=[];
		$startPolicy = false;
		$policyStr = '';
		foreach($res as $line){
			$line = trim($line);
			if($startPolicy){
				$policyStr .= $line;
			}
			if(substr($line, 0, strlen('Policies')) === 'Policies'){
				$startPolicy=true;
				continue;
			}
		}
		
		$data = json_decode($policyStr,true);
		foreach($data as $k=>$v){
			$data[$k]['sdid'] = $v['class']."#".$v['keyname'].'#'.$v['valuename'];
			if(is_array($v['data'])){
				$data[$k]['data'] = json_encode($v['data']);
			}
			
			if(isset($inopt['byid'])){
				if($data[$k]['sdid']==$inopt['byid']){
					return [$data[$k]];
				}
			}
		}
		
		return $data;
	}
	
	public $regType = [
		'REG_SZ' => 'REG_SZ',
		'REG_BINARY' => 'REG_BINARY',
		'REG_DWORD' => 'REG_DWORD',
		'REG_QWORD' => 'REG_QWORD',
	];
	public function crudAddSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"所属",
				"col"=>"class",
				"ask"=>true,
				"type"=>'select',
				"options"=>[
					'USER' => 'USER',
					'MACHINE' => 'MACHINE',
				],
				"valueIndex"=>0,
		];
		$back[]=[
				"name"=>"键名",
				"col"=>"keyname",
				"ask"=>true,
				"type"=>'text',
				"hintMore" =>'如：Software\Policies\Mozilla\Firefox\Homepage',
		];
		$back[]=[
				"name"=>"值名",
				"col"=>"valuename",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"类型",
				"col"=>"type",
				"ask"=>true,
				"type"=>'select',
				"options"=>$this->regType,
				"valueIndex"=>0,
		];
		$back[]=[
				"name"=>"数据",
				"col"=>"data",
				"type"=>'text',
		];
		return $back;
	}
	
	public function crudAddBefore(){
		$post=&$this->POST;
		
		if($post['formVal']['type']=='REG_BINARY'){
			$post['formVal']['data'] = json_decode($post['formVal']['data'],true);
		}
		
		$gpodata = [[
			'keyname' => $post['formVal']['keyname'],
			'valuename' => $post['formVal']['valuename'],
			'class' => $post['formVal']['class'],
			'type' => $post['formVal']['type'],
			'data' => $post['formVal']['data'],
		]];
		
		$filename = "/tmp/sdgpoload";
		exec("sudo rm ".$filename);
		file_put_contents($filename, json_encode($gpodata));
		
		$fakey = $post['keyList'][0];
		$fakeyname = explode(',',explode('=',$fakey)[1])[0];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo load '".$fakeyname."' --content='".$filename."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		exec("sudo rm ".$filename);
		if($code){
			return $this->out(1,'','添加失败：'.join('.',$res) );	
		}
		
		return $this->out(0,'','添加成功');	
	}
	
	
	
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"所属",
				"col"=>"class",
				"ask"=>true,
				"type"=>'show',
				"options"=>[
					'USER' => 'USER',
					'MACHINE' => 'MACHINE',
				],
				"valueIndex"=>0,
		];
		$back[]=[
				"name"=>"键名",
				"col"=>"keyname",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"值名",
				"col"=>"valuename",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[//类型没法改
				"name"=>"类型",
				"col"=>"type",
				"ask"=>true,
				"type"=>'show',
				"options"=>$this->regType,
				"valueIndex"=>0,
		];
		$back[]=[
				"name"=>"数据",
				"col"=>"data",
				"type"=>'text',
		];
		return $back;
	}
	
	
	public function crudModBefore(){
		$post=&$this->POST;
		
		if($post['formVal']['type']=='REG_BINARY'){
			$post['formVal']['data'] = json_decode($post['formVal']['data'],true);
		}
		
		$gpodata = [[
			'keyname' => $post['formVal']['keyname'],
			'valuename' => $post['formVal']['valuename'],
			'class' => $post['formVal']['class'],
			'type' => $post['formVal']['type'],
			'data' => $post['formVal']['data'],
		]];
		
		$filename = "/tmp/sdgpoload";
		exec("sudo rm ".$filename);
		file_put_contents($filename, json_encode($gpodata));
		
		$fakey = $post['keyList'][0];
		$fakeyname = explode(',',explode('=',$fakey)[1])[0];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo load '".$fakeyname."' --content='".$filename."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		exec("sudo rm ".$filename);
		if($code){
			return $this->out(1,'','修改失败：'.join('.',$res) );	
		}
		
		return $this->out(0,'','修改成功');	
	}
	
	
	
	public function crudDelBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		$row = $this->currentRow;
		
		$gpodata = [[
			'keyname' => $row['keyname'],
			'valuename' => $row['valuename'],
			'class' => $row['class'],
		]];
		
		$filename = "/tmp/sdgpoload";
		exec("sudo rm ".$filename);
		file_put_contents($filename, json_encode($gpodata));
		
		$fakey = $post['keyList'][0];
		$fakeyname = explode(',',explode('=',$fakey)[1])[0];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo remove '".$fakeyname."' --content='".$filename."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		exec("sudo rm ".$filename);
		if($code){
			return $this->out(1,'','删除失败：'.join('.',$res) );	
		}
		
		return $this->out(0,'','删除成功');	
		
	}
	
}

?>