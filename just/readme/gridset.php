<?php

$gridSet=[
	'columns'=>[
		[
			'col'=>'us_name',
			'name'=>'姓名',
			'value'=>'qqq',//在特性cell里存字段数据，在type=execute时存按钮文字
			'colspan'=> [//合并3列头到一个，该功能考虑废弃
				'name'=>'总名',
				'num'=>3,
			],
			'valMap'=>[//数据对应表
				'aa'=>'正确',
				'bb'=>'错误',
				'_default_'=>'未知',
			],
			'dotMap'=>[//小点颜色对应表
				'正确'=>'#385E0F',
				'错误'=>'#FF9912',
				'_default_'=>'#FF9912',
			],
			'modify'=>function($text,$row){//修改单元格数据
				return $text;//可直接返回修改后的数据文本
				return [//也可返回新的column数组，会和全局的合并
					'value'=>$text,
					'type'=>'onoff',
					... //其余键和column相同
				]
			},
			'overShow'=>true,//鼠标移到td上，显示字段内容,设置的是td的title属性
							//也可设置为字段名，则显示对应字段内容
							
			'overShowText'=>'直接写title内容',
			
			'color'=>'#f00',//文本颜色，
			
			'type'=>'text',//text onoff radio state fetch execute link html order table...
			
			'showInDlg'=>true,//点击单元格，弹框显示本字段内容，
								//也可设置为字段名，则显示对应字段内容
			
			'showColVal'=>true,//fetch类型，点击后显示本字段内容，而不是去服务器抓取
								//也可设置为字段名，则显示对应字段内容
			
			'icon' => '', //按钮类型的图标 ''显示默认的
			'disable' => true, //按钮类，不让点击
			'askSure' => '确定执行？'//按钮提交类，提交前确认
			'showType' => 'all',//按钮类是否依旧显示文本 all icon text
			
			//style相关
			'tdStyle'=>'',//td用到的style
			'tdInStyle'=>'',//td内第一个div的style
			'tdInInStyle'=>'',//cell里传到引用的组件里用到的style
			'align'=>'center', //设置到td上
			'width'=>'',  //100px 、20% ，设置到td里的div上
			'ellipsis'=>true, //超出省略。设置到td里的div上
			'wordNoBreak'=>true, //强制不换行。设置到td里的div上
			'wordBreak'=>true, //强制换行。设置到td里的div上
			
			//ajax相关
			'router'=>'',
			'post'=>'',
			'goto'=>'',//在传到前台之前，会移到post里
			'refresh'=>true,//ajax提交处理后强制刷新
			
			//窗口相关
			'popTitle'=>'',
			'popWidth'=>'', //100px 、 70%
			'popHeight'=>'',
			
			'linkUrl'=>'',//type=link时，跳转的地址
			
			'onoffMap'=>[0,1],//type=onoff时，禁用/启用 对应值
			
			'inputType'=>'select',//type=input时，text select ,
			'inputOptions' =>[],//type=input inputType='select'时，options列表
			'inputCol'=>'msg',//type=input时，存储数据的execVal键名
			
			//'formType' =>'edit', //type=edit时，前端会用到这个，php用不到
			
			'zdyCom' => '',//type=fetch时，所用的自定义组件
			
			'nameShow'=>'执行',//type=execute时按钮上显示的文字
			
			'headBatch'=>[//这是给表头批量按钮用的
				'batchAll'=>true,//不检查chackbox，所以行都执行	
			],
			
			'auditOper'=>'',//操作名称，用于审计，默认使用name,在传到前台之前，会移到post里
			
			'dirName'=>'upload', //type=upload、download时，staic存储文件得目录
		],
		[
		
		]
	],
	
	//不用设置，自动传给js的
	'colKey'=>'',//主键字段名
	'colName'=>'',//名称字段名
	'colNafy'=>'',//次名称字段名
	
	'treeInfo' => [
		'col'=>'ou_name',
		'depth'=>-1,//默认打开层级，-1所有层级
	],
	
	'rowSelectEnable'=>false,
				
	'toolEnable'=>true,
	'toolAddEnable '=> true,//新赠
	'toolExportEnable '=> false,//导出
	'toolRefreshEnable'=> true,//刷新
	'toolFilterEnable'=> false,//过滤
	'toolDeleteEnable'=> false,//批量删除查询条件下所有记录， true false slow
			//slow ,循环逐条删除，而不是sql语句全删除
	'toolImportEnable'=> false,//批量导入，要在crudAddSet里给需要导入的项设置import=>true
	'toolSearchColumn'=> [ //不设置或设置位null，表示不启用搜索
		'colname1'=>'like',	//colname1是否包含搜索串
		'colname2'=>'likeStart', //colname2是否以搜索串开头
		'colname3'=>'likeEnd', //colname3是否以搜索串结尾
		'colname4'=>'=', //colname4是否与搜索串相同
	],
	'toolExpands'=>[
		[
			'name'=>'',
			'icon'=>'',
			'type'=>'',//multEdit page execute batch html link list
			'batchAll'=>true,//type=batch时 执行所有行，而不仅仅是选择行
			'jsBefore'=>"function(jsCtrl){if(1){return true}}";//执行前的js代码
			'btnOptions'=>[
				['id'=>'a','name'=>'本页'],
				['id'=>'b','name'=>'全部']
			],//按钮是否有下拉选择，id会写到post.option里。目前只在execute里实现
			'askSure' => '确定执行？'//按钮提交类，提交前确认
			'linkUrl'=>'',//type=link时，跳转的地址
			'listOptions'=>[
				['name'=>'qwer','type'=>''],
			],//type=list时的选择列表.每一项是独立的tool，这和btnOptions不同，btnOptions只是个参数
			
			'inputType'=>'select',//type=input时，text select ,
			'inputOptions' =>[
				'a'=>'aaaa',
				'b'=>'bbbb',
			],//type=input inputType='select'时，options列表
			'inputCol'=>'msg',//type=input时，存储数据的execVal键名
			
			'export'=>false,//导出时是否导出该字段，默认true
		],
	],
				
	'operEnable '=>true ,//是否显示操作按钮
	'operModEnable'=> true,//是否显示编辑按钮
	'operDelEnable'=>true,//是否显示删除按钮
	'operDelHintMsg'=>'', //删除时的附加提示信息
	'operExpands'=>[//参照column的写法
		[
		]
	],
				
	'fenyeEnable'=>true,//是否分页
	'fenyeNum'=>20,//每页条目数，默认20 
	
	
	'orderColumn'=>[//设置按哪个字段排序，提交后修改colOrder字段内容，暂未实现
		'column1'=>'名称1',
		'column2'=>'名称2',
	],
	
	'rowOper' => function($row){//修改行数据
		if($row['us_name']=='system'){
			$row['_operDelEnable_']=false;//system用户不允许删除
		}
		return $row;
	}
];





?>