本系统图标，是从https://www.iconfont.cn/上生成项目并下载使用的

在我的资源管理里，有dftIcon的项目


项目配置里:
1.设定了family是dftIcon，这个在sdIcon.js里会用到
2.设定了图标名前缀是dftIcon-，这个在sdIcon.js里也会用到
（项目名，family，前缀三者其实可以不同，但为了方便管理，还是强制相同）

要更新的话
1.在网站上，添加删除图标
2.把项目下载下来，把里面的iconfont.css,iconfont.json,iconfont.woff2 三个文件拷贝到dftIcon目录
只需这两个步骤即可


三个文件解释：
iconfont.woff2  是图标数据所在文件，本系统用的是woff2格式，其他格式没有深究
iconfont.json   是图标信息的json格式，页面要罗列图标，需要此文件
iconfont.css    是图标的css文件，在sdIcon.css里引用




若要添加自己的项目的话
一.网站上：
	.在网站上新建项目，如：newIcon
	.项目配这里，family设置为 newIcon
	.项目配这里，图标名前缀设置为 newIcon-

二.下载复制
	.下载项目
	.在本文件所在目录里新建目录 newIcon
	.把下载的项目里的iconfont.css,iconfont.json,iconfont.woff2，拷贝到 newIcon 下

三.修改：
	.在本目录里的sdIcon.css里添加下面行：
	@import url("newIcon/iconfont.css");

	.在本目录里的sdIcon.json文件里添加新元素：newIcon，最终如下：
	["dftIcon","newIcon"]

四.使用
	.在使用图标时，图标字符串写成: newIcon@xiugai

五.注意
	.项目名，family名，前缀-，三种必须相同，
	.且最好取名特殊些，避免和系统里其他css冲突

	
	
