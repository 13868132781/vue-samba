<?php
$sysCfgMenu=[];
$sysCfgMenu['super']=[
	[
		'name'=>"首页",
		'icon'=>"shouye",
		'com'=> 'main',
		'noTitle' => true,
	],
	[
		'name'=>"域登录审计",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adAuditLogin',
	],
	[
		'name'=>"OTP验证审计",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adAuditLoginOTP',
	],
	
	[
		'name'=>"其他登录审计",
		'icon'=>"jigouxinxi",
		'kids'=>[
			[
				'name'=>"AAA登录管理",
				'icon'=>"jigouxinxi",
				'router' => '/radAudit/radLogin',
			],
			[
				'name'=>"应用登录管理",
				'icon'=>"jigouxinxi",
				'router' => '/radAudit/radOper',
			],
		]
	],
	[
		'name'=>"域用户管理",
		'icon'=>"list",
		'router' => '/sdLdap/adUser',
		'midy' => '/sdLdap/adOrunit',
		'midyWidth'=>'200',
	],
	[
		'name'=>"域OU管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adOrunit',
	],
	[
		'name'=>"域组管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adGroup',
	],
	[
		'name'=>"域计算机管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adComputer',
	],
	[
		'name'=>"域组策略管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adGPO',
	],
	[
		'name'=>"域DNS管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adDns',
	],
	[
		'name'=>"域属性管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adDomainAttr',
	],
	[
		'name'=>"域服务器管理",
		'icon'=>"jigouxinxi",
		'router' => '/sdLdap/adServer',
	],
	

	/*
	[
		'name'=>"AAA用户管理",
		'icon'=>"jigouxinxi",
		'kids'=>[
			[
				'name'=>"用户管理",
				'icon'=>"jigouxinxi",
				'router' => '/radUser/radUser',
				'midy' => '/radUser/radUserOrgan',
			],
			[
				'name'=>"用户机构管理",
				'icon'=>"jigouxinxi",
				'router' => '/radUser/radUserOrgan',
			],
			
			[
				'name'=>"用户角色管理",
				'icon'=>"jiaose",
				'tabList' =>[
					[
						'name'=>'角色',
						'router'=>'/radPerm/perm',
					],
					[
						'name'=>'属性组',
						'router'=>'/radPerm/permAttr',
					],
					[
						'name'=>'命令组',
						'router'=>'/radPerm/permCmd',	
					],
					[
						'name'=>'时段组',
						'router'=>'/radPerm/permShid',	
					],
					[
						'name'=>'限制组',
						'router'=>'/radPerm/permLimit',	
					]
				]
			],
			[
				'name'=>"认证方式管理",
				'icon'=>"renzheng2",
				'router'=>'/radAuth/authWay', 
					
				'tabList'=>[
					[
						'name'=>'认证方式',
						'router'=>'/radAuth/authWay',
					],
					[
						'name'=>'AD认证源',
						'router'=>"/radAuth/serverAD"
					],
					[
						'name'=>'RADIUS认证源',
						'router'=>"/radAuth/serverRadius"
					],
				]	
			],
		]
	],
	[
		'name'=>"AAA设备管理",
		'icon'=>"jigouxinxi",
		'kids'=>[
			[
				'name'=>"设备管理",
				'icon'=>"jigouxinxi",
				'router' => '/radNas/radNas',
				'midy'=>"/radNas/radNasOrgan",
			],
			[
				'name'=>"设备机构管理",
				'icon'=>"jigouxinxi",
				'router' => '/radNas/radNasOrgan',
			],
			[
				'name'=>"设备类型管理",
				'icon'=>"navicon-jgda",
				'router'=> "/radNas/nasType",
			],
			[
				'name'=>"设备网段管理",
				'icon'=>"navicon-jgda",
				'router'=> "/radNas/netSegment",
			]
		]
	],
	*/
	//[
	//	'name'=>"单独页面",
	//	'icon'=>"jigouxinxi",
		//'iframeSrc' => 'https://www.runoob.com/php/func-misc-unpack.html',
	//	'iframeSrc' => '/sdLdap/html.php',
	//],
	[
		'name'=>"系统管理",
		'icon'=>"xitongguanli1",
		'kids'=>[
			[
				'name'=>"系统用户管理",
				'icon'=>"xitongguanli_yonghuguanli",
				'tabList'=>[
					[
						'name'=>'管理员',
						'router'=>"sys/auth/sysAcct"
					],
					[
						'name'=>'登录审计',
						'router'=>"sys/auth/sysLogin"
					],
					[
						'name'=>'操作审计',
						'router'=>"sys/auth/sysOper"
					],
				]
				
			],
			[
				'name'=>"系统环境变量",
				'icon'=>"shougongqianshou",
				'router'=>"sys/sysEnvir/sysEnvir"
			],
			[
					'name'=>"系统服务管理",
					'icon'=>"xitongguanli",
					'router'=>"sys/service/service"
			],
			[
				'name'=>"系统调度管理",
				'icon'=>"biangeng",
				'router'=>"sys/sysCron/sysCron"
			],
			[
				'name'=>"系统告警管理",
				'icon'=>"biangeng",
				'router'=>"sys/sysAlarm/sysAlarm"
			],
			[
				'name'=>"系统网络管理",
				'icon'=>"tongbu",
				'tabList'=>[
					[
						'name'=>'网卡设置',
						'router'=>"sys/sysNet/network",
						'info'=>'修改后，本网页将不可用，需用新IP打开',
					],
					[
						'name'=>'路由设置',
						'router'=>"sys/sysNet/router"
					],
				]
				
			],
			[
				'name'=>"下载资源管理",
				'icon'=>"biangeng",
				'router'=>"sys/sysDownload/sysDownload"
			],
		]
	],
];

$sysCfgMenu['superDev']=[
	'name'=>"开发者菜单",
	'icon'=>"xitongguanli1",
	'kids'=>[
		[
			'name'=>"服务器终端",
			'icon'=>"fujian",
			'com'=>"sdxterm",
			'noTitle' => true,
		],
		[
			'name'=>"环境变量增减",
			'icon'=>"fujian",
			'router'=>"sys/sysEnvir/sysEnvirDev"
		],
		[
			'name'=>"系统服务增减",
			'icon'=>"fujian",
			'router'=>"sys/service/serviceDev"
		],
		[
			'name'=>"系统调度增减",
			'icon'=>"fujian",
			'router'=>"sys/sysCron/sysCronDev",
		],
		[
			'name'=>"框架entry日志",
			'icon'=>"fujian",
			'router'=>"sys/entry/logEntry"
		],
		[
				'name'=>"下载资源增减",
				'icon'=>"biangeng",
				'router'=>"sys/sysDownload/sysDownloadDev"
			],
		[
			'name'=>"图标列表管理",
			'icon'=>"fujian",
			'com'=>"iconShow",
		],
		[
			'name'=>"域控初始化",
			'icon'=>"jigouxinxi",
			'com' => 'adInit',
		],
		[
			'name'=>"AAA初始化",
			'icon'=>"jigouxinxi",
			'com' => 'radInit',
		],
	]
];


$sysCfgMenu['normal']=$sysCfgMenu['super'];

$sysCfgMenu['audit']=[
	[
		'name'=>"首页", 
		'icon'=>"shouye",
		'com'=> 'main',
	],
	[
		'name'=>"系统管理",
		'icon'=>"xitongguanli1",
		'kids'=>[
			[
				'name'=>"系统用户管理",
				'icon'=>"xitongguanli_yonghuguanli",
				'tabList'=>[
					[
						'name'=>'管理员',
						'router'=>"/auth/sysAcct"
					],
					[
						'name'=>'登录审计',
						'router'=>"/auth/sysLogin"
					],
					[
						'name'=>'操作审计',
						'router'=>"/auth/sysOper"
					],
				]
				
			],
		]
	]
	
];





/*
              index.html
                 |
               index.js
                 |
               app.js
   ______________|________
  |       |      |        |
auth    menu    body    head
    _____________|_______
   |      |      |       |
  mydy  title   page    footer
     ____________|_________
    |            |         |
  tabbar      sdGrid     com
  __|____
 |       |
com     sdgrid



*/



?>