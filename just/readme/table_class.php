<?php

/*
$WEBSITE = 网站根目录


首先，在config.php里建立一个左侧菜单项目
菜单项里的 'router' => '/sdLdap/adOrunit'

这里的sdLdap对应目录 $WEBSITE/app/php/sdLdap
adOrunit对应目录下的类文件 $WEBSITE/app/sdLdap/adOrunit.php
*/

/*
这个app,就是对应到 $WEBSITE/app
后面跟这sdLdap，就得存在对应目录：$WEBSITE/app/sdLdap
*/
namespace app\sdLdap;

/*
每个页面类都是继承自table：
按照namespace的设定，该类文件就得在 $WEBSITE/php/sdLdap/里面
类名adOrunit得跟类文件名相同
*/
class adOrunit extends \table {


//====================成员变量=================================
	public $pageName='';//页名，审计用
	public $TN = ""; //表名如 sdaaa.user
	public $colKey = ""; //主键字段名
	public $colKeyList = [];//如果是多层弹窗页面，得列出多层父级的字段名
	public $colOrder = ""; //排序字段名
	public $colFid = ""; //父级id字段名，用户生成树形结构
	public $colUnit = "";//用于右侧菜单查询的机构字段名
	public $colName = "";//名称字段名
	public $colNafy = "";//唯一标识字段名，如登录名 IP
	public $orderDesc = false; //是否反向排序
	public $treeBeginId=''; //要生成树形结构的话，开始节点Id
	public $noAudit=false;//是否做审计
	public $zdyBackend=false; //后端不是数据库，而是自定义数据
	public $currentRow=null; //如果是对行数据操作的话，这里保存当前行数据



//=====================成员函数========================================
//==============[grid，查询数据]==============================
//必须
//返回页面表格的定义，返回一个array，
//具体格式参考gridSet.php
public function gridSet(){}

//可选
//查询表格数据之前所执行的函数，
//可在里面修改查询条件等内容
//$db的采用链式查询方式，具体参考DB.php
public function gridBefore(&$db){}

//可选
//查询表格数据结束后执行的函数，
//可在里面修改将要返回的数据
public function gridAfter(&$data){}

//可选
//在对查询数据做必要处理(具体处理要求在gridSet里定义)后执行的函数，
//可在里面修改将要返回的数据
public function gridAfterDeal(&$data){}


//==============[crudAdd，新增条目]====================================
//必须
//返回新增条目的表单定义，返回一个array，
//具体说明参考addSet.php
public function crudAddSet(){}

//可选
//在往数据里插入数据之前执行
//可在里面修改表单数据$post['formVal']
//可返回空或out格式
public function crudAddBefore(){
	return;//会继续插入数据
	return $this->out(0,'成功');//不再插入数据库，直接返回浏览器
}

//可选
//在往数据里插入数据之后执行
//可在里面做一些插入数据之后的工作
//可返回空或out格式
public function crudAddAfter(){
	$post = &$this->POST;
	$post['key']; //插入数据库后返回的insert_id
	return;//基类会继续执行下去，返回标准的成功或失败
	return $this->out(0,'新增成功，但...');//定义我自己的返回信息，直接返回浏览器
}


//==============[crudMod，修改条目]===================================
//必须
//返回修改条目的表单定义，返回一个array，
//具体说明参考addSet.php
public function crudModSet(){
	$post = &$this->POST;
	$key = $post['key']; //要修改的数据id
	$row = $this->currentRow;//要修改的数据条目
}

//可选
//在往数据里修改数据之前执行
//可在里面修改表单数据$post['formVal']
//可返回空或out格式
public function crudModBefore(){
	$post = &$this->POST;
	$key = $post['key']; //要修改的数据id
	$row = $this->currentRow;//要修改的数据条目
	$form = $post['formVal']; //表单数据
	return;//会继续修改数据库里的数据
	return $this->out(0,'成功');//不再修改数据库，直接返回浏览器
}

//可选
//在往数据里修改数据之后执行
//可在里面做一些修改数据之后的工作
//可返回空或out格式
public function crudModAfter(){
	$post = &$this->POST;
	$key = $post['key']; //要修改的数据id
	$row = $this->currentRow;//要修改的数据条目
	$form = $post['formVal']; //表单数据
	return;//基类会继续执行下去，返回标准的成功或失败
	return $this->out(0,'修改成功，但...');//定义我自己的返回信息，直接返回浏览器
}


//==============[crudDel，删除条目]======================================
//可选
//在往数据里删除数据之前执行
//可在里面一些删除数据前的工作
//可返回空或out格式
public function crudDelBefore(){
	$post = &$this->POST;
	$key = $post['key']; //要删除的数据id
	$row = $this->currentRow;//要删除的数据条目
	return;//会继续删除数据库里的数据
	return $this->out(0,'成功');//不再删除数据库，直接返回浏览器
}

//可选
//在往数据里删除数据之后执行
//可在里面做一些删除数据之后的工作
//可返回空或out格式
public function crudDelAfter(){
	$post = &$this->POST;
	$key = $post['key']; //要删除的数据id
	$row = $this->currentRow;//要删除的数据条目
	return;//基类会继续执行下去，返回标准的成功或失败
	return $this->out(0,'删除成功，但...');//定义我自己的返回信息，直接返回浏览器
}


//==============[edit，编辑]==================================
//和修改功能相同，但需在gridSet里手动添加按钮，并定义一个goto以作标识
//比如，除了正常的修改外，你想给每一行再定义一个编辑按钮，以修改其他单独的或例外的字段

//必须
//在往数据里修改数据之前执行
//可在里面修改表单数据$post['formVal']
//可返回空或out格式
public function editSet_[goto](){
	$post = &$this->POST;
	$key = $post['key']; //要修改的数据id
	$row = $this->currentRow;//要修改的数据条目
	$form = $post['formVal']; //表单数据
	return;//会继续修改数据库里的数据
	return $this->out(0,'成功');//不再修改数据库，直接返回浏览器
}


//可选
//在往数据里修改数据之前执行
//可在里面修改表单数据$post['formVal']
//可返回空或out格式
public function editSaveBefore_[goto](){
	$post = &$this->POST;
	$key = $post['key']; //要修改的数据id
	$row = $this->currentRow;//要修改的数据条目
	$form = $post['formVal']; //表单数据
	return;//会继续修改数据库里的数据
	return $this->out(0,'成功');//不再修改数据库，直接返回浏览器
}

//可选
//在往数据里修改数据之后执行
//可在里面做一些修改数据之后的工作
//可返回空或out格式
public function editSaveAfter_[goto](){
	$post = &$this->POST;
	$key = $post['key']; //要修改的数据id
	$row = $this->currentRow;//要修改的数据条目
	$form = $post['formVal']; //表单数据
	return;//基类会继续执行下去，返回标准的成功或失败
	return $this->out(0,'修改成功，但...');//定义我自己的返回信息，直接返回浏览器
}



//===============[multEdit，批量编辑]===================================
//批量修改按钮，上面edit的批量形式，
//出现在表头上方的工具栏
//修改所有选中行

//必须函数，必须定义
public function multEditSet_[goto](){}

//并联函数，可选，必须返回out格式，以结束请求
public function multEditSaveBefore_[goto](){}


public function multEditSaveAfter_[goto](){}



//=================[onoff，开关按钮]=================================
//开关按钮默认只识别  0 1 值

//可选
//在往数据里修改数据之前执行
//可在里面做一些修改数据的工作
//可返回空或out格式
public function onoffBefore_[goto](){
	$post = &$this->POST;
	$key = $post['key'];//行数据id
	$col = $post['col'];//开关字段名
	$val = $post['val'];//开关值
	return;//会继续修改数据库里的数据
	return $this->out(0,'成功');//不再修改数据库，直接返回浏览器
}

//可选
//在往数据里修改数据之后执行
//可在里面做一些修改数据的工作
//可返回空或out格式
public function onoffAfter_[goto](){
	$post = &$this->POST;
	$key = $post['key'];//行数据id
	$col = $post['col'];//开关字段名
	$val = $post['val'];//开关值
	return;//会继续执行基类代码，返回标准信息
	return $this->out(0,'成功');//自定义返回信息，直接返回浏览器
}


//====================[radio，单选按钮]================================
//表格里有多行，只允许某一个行选中，其他行不选中

//可选
//在往数据里修改数据之前执行
//可在里面做一些修改数据的工作
//可返回空或out格式
public function radioBefore_[goto](){
	$post = &$this->POST;
	$key = $post['key'];//行数据id
	$col = $post['col'];//单选字段名
	//单选没有val，选中哪一行，就将id位$key的行的字段col置为1，其他行的col字段置为0
	return;//会继续修改数据库里的数据
	return $this->out(0,'成功');//不再修改数据库，直接返回浏览器
}

//可选
//在往数据里修改数据之后执行
//可在里面做一些修改数据的工作
//可返回空或out格式
public function radioAfter_[goto](){
	$post = &$this->POST;
	$key = $post['key'];//行数据id
	$col = $post['col'];//单选字段名
	//单选没有val，选中哪一行，就将id位$key的行的字段col置为1，其他行的col字段置为0
	return;//会继续执行基类代码，返回标准信息
	return $this->out(0,'成功');//自定义返回信息，直接返回浏览器
}



//==============[filter，过滤]=====================================
//过滤和搜索不同，搜索只需定义一下搜索哪几个字段
//过滤的话，得定义一个表单，确定过滤哪些字段，过滤规则是什么

//过滤开关打开的话，必须提供该函数
//返回array，具体规则，参考filterSet.php
public function filterSet(){}



//==============[按钮类型：execute，执行]=====================================
//在gridSet里定义了execute类型的按钮的话，就必须定义该函数
//执行按钮在点击后，会通过ajax向后台发送一个执行动作
//具体执行动作，由用户自己决定
//返回后，结果显示在按钮文本和颜色上
//执行操作不直接关联数据库，所以没有before after函数
public function execute_[goto](){
	return $this->out(0,'成功啦');//执行成功
	return $this->out(1,'失败啦');//执行失败
}


//==============[按钮类型：fetch，抓取]====================================
//在gridSet里定义了fetch类型的按钮的话，就必须定义该函数
//抓取按钮点击后，会弹出窗口，以呈现从服务器抓取来的内容
//如果要求内容按一定规则展示，则要自定定义一个vue插件来显示。
//抓取操作不直接关联数据库，所以没有before after函数
public function fetch_[goto](){
	return $this->out(0,'所要显示的内容');
}


//==================[state，状态检测]===================================
//在gridSet里定义了fetch类型状态检测的按钮的话，就必须定义该函数
//点击后，会向后台发送检测请求
//检测结果返回给浏览器的话，浏览器会呈现勾或叉
//主要用于IP地址联通性检测，账户可登录性检测
public function state_[goto](){
	return $this->out(0);//检测成功
	return $this->out(1);//检测失败
}




//==================[自定义数据源]===================================
//如果是自定义的数据的，不是从数据库里查出来的数据，需实现zdyData函数
//只有成员变量$zdyBackend=true;时，下面函数才有效
public function zdyData($inopt){
	//$inopt['byid']='123';//按id值来获取记录
	//$inopt['count']=true;//获取统计值
	//$inopt['where']=[];//查询条件
}


//===================================================

}


?>