<?php
/*
本框架提供了一个mysql链式操作类
该类是全局的
*/

// 一.直接执行sql语句=====================================
// 1.直接执行select语句，返回的是数组
$data = \DB::doExec("select * from ...");

// 2.直接执行insert语句，返回的是id号
$insertId = \DB::doExec("insert into ...");

// 2.直接执行update或delete语句，返回的是影响行数
$affectedNum = \DB::doExec("update ...");
$affectedNum = \DB::doExec("delete ...");

//===二.实例对象================================================
//======1.通过table静态函数实例*
$mydb = \DB::table('sdaaa.raduser');

//======2.通过table类的子类的DB函数实例
class myTable extends \table {
	public $TN = "sdaaa.raduser";
}
$mydb = \myTable::DB();//相当于\DB::table("sdaaa.raduser")


// 三.加密字段存储
// 1.自动加密解密
//pass pass1都要加密存储，
//在查询、插入、更新时，会自动加密解密这两个字段的数据
$data = $mydb->crypt('pass','pass1');

// 2.DB类提供了两个静态函数，用于使用者自己加解密
$cryptText = \DB::encrypt('aaa');
$plainText = \DB::decrypt($cryptText);

// 3.通过table类的子类的DB函数实例时，如定义了colCrypt，会自动调用
class myTable extends \table {
	public $TN = "sdaaa.raduser";
	public $colCrypt = ['pass','pass1'];
}
$mydb = \myTable::DB();
//相当于\DB::table("sdaaa.raduser")->crypt('pass','pass1');


// 三.查询数据
// 1.查询所有数据，返回表中所有数据
$data = $mydb->get();

// 2.查询第一条数据
$row = $mydb->first();

// 3.查询第一条数据的某个字段值
$row = $mydb->value('myname');

// 4.查询数据条目
$num = $mydb->count();

// 5.查询特定字段的数据，field可多次调用，会自动合并
//select name,user,pass as textpass,mark from table;
$data = $mydb->field('name','user',['pass','textpass'])->field('mark')->get();

// 6.排序order 查询
$data = $mydb->orderBy('time')->get();
$data = $mydb->orderBy('time','desc')->get();//反序

// 7.limit限定查询结果
$data = $mydb->limit(10)->get();//开头10个
$data = $mydb->limit(10,10)->get();//第10个到第20个


// 四.where过滤数据
// 1. 一个参数
$data = $mydb->where("name='hong'")->get();

// 1.两个参数
$data = $mydb->where('name','hong')->get();

// 2.三个参数
$data = $mydb->where('time','>','2024-12-23')->get();

// 3.原始字符串
$data = $mydb->where('time','>',\DB::raw('now()'))->get();

// 4.多个where and
$data = $mydb->where('name','hong')->where('code','1234')->get();

// 5.多个where or
$data = $mydb->where('name','hong')->orWhere('code','0')->get();

// 6.查询子串
//select * from table where name='hong' and (code='0' or timeout='0');
$data = $mydb
	->where('name','hong')
	->where(function($db){
		$db->where('code','0')->orWhere('timeout','0');
	})
	->get();

// 7.left join查询
//select * from (select * from table where usid='123')a left join sdtest.table1 b on a.rrr=b.uuu left join (select * from sdtest.table2 where name='aaa')c on a.ccc=c.zzz where 
$mydb->where('usid','123')
->field('rrr')
->leftJoin("sdtest.table1","rrr","uuu",['uuu'])
->leftJoin("sdtest.table2",function($mydb){
	$mydb->where("name","aaa")
	->field('name','ccc')
	->fieldOut('name') 
	->on('ccc','zzz');
})
->whereOut()
->get();
	
	

// 五.插入数据



// 六.更新数据



// 六.删除数据

?>