使用说明:
	1. 在二奶机(也可以在本机) 安装php环境
	2. 把web内的文件解压到php的www文件夹内
	3. 修改dns.php内的静态变量
		$GLOBALS['AccessKeyId'] = '阿里云 AccessKeyId';  //填Access Key Id
		$GLOBALS['AccessKeySecret'] = '阿里云 AccessKeySecret'; //填Access Key Secret
		$GLOBALS['DomainName'] = '域名 xxxx.com'; //填域名

	4. 修改RouterServer.conf内的配置内容

		UpdateUrl这边指向php网站的地址 本机就填localhost或者127.0.0.1
		例子http://localhost/dns.php?func=updateip&sub=

		BindSubHostName 二级域名的名称 以 domain.com为例
		需要将www.domain.com指向本机就填写www

		UpdateDiff 更新时间
		可以设置长一点也没关系


		DynamicIp.UpdateUrl 			= http://192.168.50.13/dns.php?func=updateip&sub=
		DynamicIp.BindSubHostName		= www
		DynamicIp.UpdateDiff			= 5000


	5. 为什么把php和请求exe分开？
		因为考虑到有些朋友(包括我自己)需要多台机器做分布式管理. 比如论坛放在一台机器上, 主页放在一台机器上，游戏服务器放在一台机器上， 登录服务器放在一台机器上这种情况




有问题可以联系我  
	QQ 		602809934 
	邮箱 	lijingwei88306@hotmail.com