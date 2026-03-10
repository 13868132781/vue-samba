<?php

[
	"name"=>"",
	"col"=>"",
	
	"ask"=>true,//不可为空
	
	"type"=>"",//text show hidden password select radio
		//hidden的意义，针对value是动态生成的情况，如otp的seed，只在add里有意义
		//              不显示到前台，但后台数据库保存时得有此项
		//show 只是前台不允许修改，其他行为和正常项相同
	"options"=>[],//type=select radio时的选项
	
	"optionsList"=>[],//type=select时的多组选项
	"optionsKey"=>[],//决定上用哪组选项，由别的框关联设置
	
	"xsname"=>'',//默认显示 type=treePick时使用
	
	"import" => true,//该项是否出现在导入表里
	
	"value"=>"",//原始值，在modSet里，会被$row里的值覆盖掉
	"valueIndex"=>'',//如果是下列列表，默认显示第几项，只在addSet里有效
	"importValue"=>"",//如果该项不需要导入，就用此值
		//importValue存在的意义，在于补全没有出现在导入表的字段
	"sqlValue"=>"",//插入数据库时，如果空，就填此值
	
	"ignore"=>true, //，运行前台修改，但不保存到数据库
	"unique"=>true,//值在数据库中保持唯一性
	
	"crypt" => "md5",//数据加密后再存到数据库中
	"valid"=>[],//验证设定
	
	"hintMore"=>'自定义提示信息',
	
	"relate"=>[//关联变动，value改动时，关联变动
		[	
			'col'=>'',//对该字段的设定项做修改
			'optionsKey'=>'',
		],
	],
	
	"onChange"=>"function(info,infos){
		
		
	}",//自定义改动事件函数
	
	"jshidden"=>false,//该项是给relate关联设置的，
				//后台一般不会设置，由其他项onChange里设置，
				//且如果是true，不会在formVal里面返回数据
				//这是前台js唯一可能造成不返回数据的情况
				
				
	"maxHeight"=>'200px', //200px  selectms类型，设定最大高度，默认不设置，自行撑开
]




?>