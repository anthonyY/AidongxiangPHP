1.文件写入权限。
项目根目录/Cache/	//	缓存
项目根目录/public/uploadfiles/	//	图片
项目根目录/public/push_log/	//	推送、短信日志
项目根目录/vendor/Core/System/AiiUtility/AiiPush/log/	//	推送、短信错误日志
项目根目录/vendor/Core/System/WxApi	// 微信access_token

2.设置访问路径：项目根目录/public/

3.图片服务器
没有，图片存于自己项目下

4.计划任务


5.其它设置
外网status_config.php内，ENVIRONMENT_TYPE调整成2；
外网注意 /public和 /public/uploadfiles 都有 .htaccess 文件，内容是不一样的

6.部分定死的参数在project_config.php编辑
外网需要改成define('HELP_IDS', '775,776,777,778,779,780');
