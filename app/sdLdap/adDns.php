<?php

namespace app\sdLdap;

class adDns extends \table {
	public $pageName="域DNS";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "fid";
	public $colName = "name";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'name','name'=>'主机名','wordBreak'=>true,'rowspan'=>true],
				//['col'=>'dn','name'=>'dn','wordBreak'=>true],
				['col'=>'type','name'=>'类型'],
				['col'=>'data','name'=>'数据'],
				['col'=>'dnsrstr','name'=>'参数','wordBreak'=>true,'type'=>'html'],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable' => false,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			
			'toolSearchColumn'=>[
				'name'=>'like',	
				'data'=>'like',
			],
		];
		return $gridSet;
	}
	
	public function zdyData(){
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool dns query ".$asArgs['ip']." ".$asArgs['domain']." @ ALL ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		
		$data=[];
		$crtObj=['name'=>'','count'=>0];
		foreach($res as $line){
			$line = trim($line);
			$lines = explode('=',$line);
			if($lines[0]=='Name'){
				$crtObj=[];
				$crtObj['name'] = trim(explode(',',$lines[1])[0]);
				$crtObj['count'] = intval(trim(explode(',',$lines[2])[0]));
				continue;
			}
			if($crtObj['name'] and $crtObj['count']>0){
				$lineA = explode(':',$line,2);
				$lineB = explode('(',$lineA[1],2);
				$datao = [
					'id'=>md5($crtObj['name'].'-'.$line),
					'name'=>$crtObj['name'],
					'type'=>trim($lineA[0]),
					'data'=>trim($lineB[0]),
					'dnsrstr'=>trim($lineB[1],')'),
				];
				$data[] = $datao;
			}
		}
		
		//sdAlert($data);
		return $data;
	}
	
	
	public function crudAddSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"主机名",
				"col"=>"name",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"IP地址",
				"col"=>"address",
				"ask"=>true,
				"type"=>'text',
		];
		/*
		$back[]=[
				"name"=>"隶属",
				"col"=>"fadn",
				"ask"=>true,
				"type"=>'select',
				"options"=>adOrunit::options([],[],'','dnfy'),
		];
		*/
		return $back;
	}
	
	
	
	public function crudAddBefore(){
		$post=&$this->POST;
		
		$loginname = $post['formVal']['name'];
		$address = $post['formVal']['address'];
		
		$opt=[
			'name' => $loginname, 
			'type' => 'A',
			'data' => $address,
		];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool dns add ".$asArgs['ip']." ".$asArgs['domain']." '".$opt['name']."' ".$opt['type']." '".$opt['data']."' ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		
		array_unshift($res,$cmd);
		$ress = join('。',$res);
		
		if($code){
			return $this->out(1,'','添加失败：'.$ress);	
		}
		
		return $this->out(0,'','添加成功');	
	}
	
	
	
	
	public function crudmodSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"记录",
				"col"=>"dnsrstr",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"描述",
				"col"=>"description",
				"ask"=>true,
				"type"=>'text',
		];
		return $back;
	}
	
	public function crudModBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		$row = $this->currentRow;
		

		if($code){
			return $this->out(1,'','修改失败：'.$err);	
		}
		
		return $this->out(0,'','修改成功');		
	}
	
	public function crudDelBefore(){
		$post=&$this->POST;
		$key = $post['key'];
		$row = $this->currentRow;
		
		$opt=[
			'name' => $row['name'], 
			'type' => $row['type'],
			'data' => $row['data'],
		];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool dns delete ".$asArgs['ip']." ".$asArgs['domain']." '".$opt['name']."' ".$opt['type']." '".$opt['data']."' ".$asArgs['stAuth'];
		
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		
		if($code){
			return $this->out(1,'','删除失败：'.$err);	
		}
		
		return $this->out(0,'','删除成功');		
	}
	
}

?>