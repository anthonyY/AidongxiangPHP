1.到 人人开放平台、 新浪微博开放平台、 QQ开放平台申请接入网站,获得相关的appkey和secretkey
2.修改system/core.php中三大平台对应的key和secret的值
3.如果你在本地测试环境运行,请将相应的域名添加到HOSTS,映射到你本地,否则会出现连接时报redirect uri is illegal

SDK演示地址：http://blog.iplaybus.com/demo/openoauth2/

如果有任何疑问，请到http://blog.iplaybus.com/weibo-qq-renren-sdk-php-oauth2.html留言