<?php
/*
 * STATUS字头
 * 协议返回用 start
 */
/*
 *  公共
 */
/** 1测试环境,2生产环境
 * @var 1
 */
define('ENVIRONMENT_TYPE', 2);
// define('REQUEST_DESCRIPTION_TYPE',1);

/** 操作成功 @var 0 */
define('STATUS_SUCCESS' , '0' );
define('DESCRIPTION_0' , '操作成功');
define('REAL_DESCRIPTION_0' , '操作成功');
/** 未知错误 @var 1000 */
define('STATUS_UNKNOWN' , '1000' );
define('DESCRIPTION_1000' , '未知错误');
define('REAL_DESCRIPTION_1000' , '哎呀，您的网络打瞌睡啦！');
/** 协议版本过低，服务器已经不支持 @var 1001 */
define('STATUS_VERSION_LOW' , '1001' );
define('DESCRIPTION_1001', '请更新客天下，享更多会员服务');
define('REAL_DESCRIPTION_1001', '请更新客天下，享更多会员服务！');
/** session id为空或不存在 @var 1002 */
define('STATUS_SESSION_EMPTY' , '1002' );
define('DESCRIPTION_1002' , 'session id为空或不存在');
define('REAL_DESCRIPTION_1002' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 验证码错误 @var 1003 */
define('STATUS_CAPTCHA_ERROR' , '1003' );
define('DESCRIPTION_1003' , '验证码错误');
define('REAL_DESCRIPTION_1003' , '验证码错误');
/** 请求参数不完整 @var 1004 */
define('STATUS_PARAMETERS_INCOMPLETE' , '1004' );
define('DESCRIPTION_1004' , '请求参数不完整');
define('REAL_DESCRIPTION_1004' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 请求超时 @var 1010 */
define('STATUS_TIMEOUT' , '1010' );
define('DESCRIPTION_1010' , '请求超时');
define('REAL_DESCRIPTION_1010' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 数据已删除 / 数据不存在 @var 1011 */
define('STATUS_NODATA' , '1011' );
define('DESCRIPTION_1011' , '数据已删除 / 数据不存在');
define('REAL_DESCRIPTION_1011' , '您访问的内容无法地球连接！');
/** session id 会话过期 @var 1012 */
define('STATUS_SESSION_TIMEOUT' , '1012' );
define('DESCRIPTION_1012' , 'session id 会话过期');
define('REAL_DESCRIPTION_1012' , '抱歉！请您重新登录账户！');
/** 未更新任何数据 @var 1013 */
define('STATUS_NOT_UPDATE' , '1013' );
define('DESCRIPTION_1013' , '未更新任何数据');
define('REAL_DESCRIPTION_1013' , '未更新任何数据');
/** 已经提交，不需要重复提交 @var 1014 */
define('STATUS_CAN_NOT_RESEND' , '1014' );
define('DESCRIPTION_1014' , '已经提交，不需要重复提交');
define('REAL_DESCRIPTION_1014' , '已经提交，不需要重复提交');
/** 短信发送失败 @var 1015 */
define('STATUS_SEND_SMSCODE_FAIL' , '1015' );
define('DESCRIPTION_1015' , '短信发送失败');
define('REAL_DESCRIPTION_1015' , '短信发送失败');
/** 参数不符合条件 @var 1016 */
define('STATUS_PARAMETERS_CONDITIONAL_ERROR' , '1016' );
define('DESCRIPTION_1016' , '参数不符合条件');
define('REAL_DESCRIPTION_1016' , '参数不符合条件');
/** 安全验证不通过 @var 1017 */
define('STATUS_MD5' , '1017' );
define('DESCRIPTION_1017' , '安全验证不通过');
define('REAL_DESCRIPTION_1017' , '安全验证不通过');
/** 缓存数据可用 @var 1020 */
define('STATUS_CACHE_AVAILABLE' , '1020' );
define('DESCRIPTION_1020' , '缓存数据可用');
define('REAL_DESCRIPTION_1020' , '缓存数据可用');
/** 操作太快，请稍后再试 @var 1021 */
define('STATUS_TOO_FAST' , '1021' );
define('DESCRIPTION_1021' , '操作太快，请稍后再试');
define('REAL_DESCRIPTION_1021' , '操作太快，请稍后再试');
/** 短信已发送 @var 1023 */
define('STATUS_FALSE_REPETITION' , '1023' );
define('DESCRIPTION_1023' , '短信已发送，请勿重复操作！');
define('REAL_DESCRIPTION_1023' , '短信已发送，请勿重复操作！');
/** 短信发送失败 验证码填写错误 @var 1023 */
define('STATUS_FAILED_TO_SEND' , '1026' );
define('DESCRIPTION_1026' , '短信发送失败，验证码填写错误！');
define('REAL_DESCRIPTION_1026' , '短信发送失败，验证码填写错误！');



/** 版本过低，尚不支持该功能 @var 1001 */
define('STATUS_VERSION_LOW_PAY' , '1027' );
define('DESCRIPTION_1027' , '版本过低，尚不支持该功能');
define('REAL_DESCRIPTION_1027' , '版本过低，尚不支持该功能');

// 用户
/** （用户）未登录 @var 1100 */
define('STATUS_USER_NOT_LOGIN' , '1100' );
define('DESCRIPTION_1100' , '（用户）未登录');
define('REAL_DESCRIPTION_1100' , '抱歉！请您登录后再进行操作！');
/** 用户名或密码错误 @var 1101 */
define('STATUS_PASSWORD_ERROR' , '1101' );
define('DESCRIPTION_1101' , '用户名或密码错误');
define('REAL_DESCRIPTION_1101' , '用户名或密码错误！');
/** （用户）标识非法 @var 1102 */
define('STATUS_USER_IDENTIFY_ILLEGAL' , '1102' );
define('DESCRIPTION_1102' , '（用户）标识非法');
define('REAL_DESCRIPTION_1102' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** （用户）不存在 @var 1103 */
define('STATUS_USER_NOT_EXIST' , '1103' );
define('DESCRIPTION_1103' , '（用户）不存在');
define('REAL_DESCRIPTION_1103' , '抱歉，该用户还没有注册！');

/** （用户）已存在 @var 1104 */
define('STATUS_USER_EXIST' , '1104' );
define('DESCRIPTION_1104' , '（用户）已存在');
define('REAL_DESCRIPTION_1104' , '抱歉，该用户已被人占用！');
/** （用户）已注销 @var 1105 */
define('STATUS_USER_CANCEL' , '1105' );
define('DESCRIPTION_1105' , '（用户）已注销');
define('REAL_DESCRIPTION_1105' , '抱歉，您的用户被禁用了！');
/** （用户）被锁定 @var 1106 */
define('STATUS_USER_LOCKED' , '1106' );
define('DESCRIPTION_1106' , '（用户）被锁定');
define('REAL_DESCRIPTION_1106' , '抱歉，您的用户被锁定了！');
/** （用户）在别处登录 @var 1107 */
define('STATUS_USER_OTHER_LOGIN' , '1107' );
define('DESCRIPTION_1107' , '（用户）在别处登录');
define('REAL_DESCRIPTION_1107' , '请注意，您的用户在别处登录了！');
/** （用户）个人认证还没通过 @var 1108 */
define('STATUS_USER_AUTH_FAIL' , '1108' );
define('DESCRIPTION_1108' , '（用户）个人认证还没通过');
define('REAL_DESCRIPTION_1108' , '（用户）个人认证还没通过');
/** （用户）用户类型错误 @var 1109 */
define('STATUS_USER_TYPE_ERROR' , '1109' );
define('DESCRIPTION_1109' , '用户类型错误');
define('REAL_DESCRIPTION_1109' , '用户类型错误');
/** （用户）推荐码不存在 @var 1110 */
define('STATUS_NO_REFERRER' , '1110' );
define('DESCRIPTION_1110' , '（用户）推荐码不存在');
define('REAL_DESCRIPTION_1110' , '（用户）推荐码不存在');
/** 原密码错误 @var 1111 */
define('STATUS_PASSWORD_ERROR_FOR_UPDATE' , '1111' );
define('DESCRIPTION_1111' , '原密码错误');
define('REAL_DESCRIPTION_1111' , '原密码错误');
/** 原密码错误 @var 1111 */
define('STATUS_PASSWORD_DISAGREE' , '1501' );
define('DESCRIPTION_1501' , '前后密码不一致');
define('REAL_DESCRIPTION_1501' , '前后密码不一致');
/** 权限不足 @var 1112 */
define('STATUS_INSUFFICIENT_PERMISSIONS' , '1112' );
define('DESCRIPTION_1112' , '权限不足');
define('REAL_DESCRIPTION_1112' , '权限不足');
/** （用户）手机号码已存在 @var 1113 */
define('STATUS_MOBILE_EXIST' , '1113' );
define('DESCRIPTION_1113' , '（用户）手机号码已存在');
define('REAL_DESCRIPTION_1113' , '抱歉，手机号码已存在！');


/** 用户名或密码为空 @var 1115 */
define('STATUS_PASSWORD_EMPTY' , '1115' );
define('DESCRIPTION_1115' , '用户名或密码为空');
define('REAL_DESCRIPTION_1115' , '用户名或密码为空');
/** （用户）用户名已存在 @var 1116 */
define('STATUS_USERNAME_EXIST' , '1116' );
define('DESCRIPTION_1116' , '（用户）用户名已存在');
define('REAL_DESCRIPTION_1116' , '抱歉，该用户名已存在！');

/** （用户）用户名小于三位 @var 1117 */
define('STATUS_USERNAME_GREATER_THAN_THREE' , '1117' );
define('DESCRIPTION_1117' , '用户名需大于三个字');
define('REAL_DESCRIPTION_1117' , '用户名需大于三个字！');
/*
 * 文件
 */
/** 文件大小超过限制 @var 1200 */
define('STATUS_FILESIZE_EXCEEDS_LIMIT' , '1200' );
define('DESCRIPTION_1200' , '文件大小超过限制');
define('REAL_DESCRIPTION_1200' , '您上传的内容不能超过2M！');
/** 文件类型非法 @var 1201 */
define('STATUS_FILETYPE_ILLEGAL' , '1201' );
define('DESCRIPTION_1201' , '文件类型非法');
define('REAL_DESCRIPTION_1201' , '哎呀，不要上传别的内容。');

/** 外快不足 @var 1303 */
define('STATUS_CASH_NOT_ENOUGH' , '1304' );
define('DESCRIPTION_1304' , '外快不足');
define('REAL_DESCRIPTION_1304' , '外快不足');

/** 现金不足 @var 1305 */
define('STATUS_CASH_NOT_ENOUGH_TWO' , '1305' );
define('DESCRIPTION_1305' , '现金不足');
define('REAL_DESCRIPTION_1305' , '现金不足');

/**
 * 库存不足
 */
define('STATUS_GOODS_NUMBER_NOT_ENOUGH','1401');
define('DESCRIPTION_1401' , '库存不足');
define('REAL_DESCRIPTION_1401' , '库存不足');

/**
 * 非法操作
 */
define('STATUS_ILLEGAL_OPERATION','1406');
define('DESCRIPTION_1406' , '非法操作');
define('REAL_DESCRIPTION_1406' , '非法操作');

/*
 * 其它
 */
/** 没有返回状态码 @var 9000 */
define('STATUS_NOSTATUS' , '9000' );
define('DESCRIPTION_9000' , '没有返回状态码');
define('REAL_DESCRIPTION_9000' , '有重大更新，请更新客户端。');
/** 协议格式不正确 @var 9001 */
define('STATUS_INCORRECT_FORMAT' , '9001' );
define('DESCRIPTION_9001' , '请求参数格式不正确');
define('REAL_DESCRIPTION_9001' , '有重大更新，请更新客户端。');
/** 协议不存在 @var 9002 */
define('STATUS_NO_PROTOCOL' , '9002' );
define('DESCRIPTION_9002' , '协议不存在');
define('REAL_DESCRIPTION_9002' , '有重大更新，请更新客户端。');

/**
 *订单生成成功,等待客户付款
 * @var 9003
 */
define('STATUS_WAIT_PAY','9003');
define('DESCRIPTION_9003' , '订单生成成功,等待客户付款!');
define('REAL_DESCRIPTION_9003' , '订单生成成功,等待客户付款!');

/**
 * 收货地址不能为空
 * @var 9004
 */
define('STATUS_NO_ADDRESS','9004');
define('DESCRIPTION_9004' , '收货地址不能为空!');
define('REAL_DESCRIPTION_9004' , '收货地址不能为空!');


/**
 * 支付方式不能为空
 * @var 9005
 */
define('STATUS_PAYTPE_NO','9005');
define('DESCRIPTION_9005' , '请选择支付方式在提交!');
define('REAL_DESCRIPTION_9005' , '支付方式不能为空!');


/**
 * 请绑定银行卡后在提现
 * @var 9006
 */
define('STATUS_NON_BINDING_BANK','9007');
define('DESCRIPTION_9007' , '请绑定银行帐号信息后再提现');
define('REAL_DESCRIPTION_9007' , '请绑定银行帐号信息后再提现');
/*
 * 协议返回用 end
 */
/*
 * 通用 start
 */
/** 打开 @var 1 */
define('OPEN_TRUE',1);
/** 关闭 @var 2 */
define('OPEN_FALSE',2);
/** 非删除 @var 0 */
define('DELETE_FALSE' , '0' );
/** 删除 @var 1 */
define('DELETE_TRUE' , '1' );
/** 正常 @var 1 */
define('STATUS_NORMAL' , '1' );
/** 停用 @var 2 */
define('STATUS_STOP' , '2' );
/**
 * 未激活
 * @var 3
 */
define('STATUS_NOT_ACTIVE','3');
/*
 * 通用 end
 */
/*
 * login 表 LOGIN_STATUS字头
 * 登录状态 start
 */
/** 临时 @var 1 */
define('LOGIN_STATUS_TEMP' , '1' );
/** 登录 @var 2 */
define('LOGIN_STATUS_LOGIN' , '2' );
/** 登出 @var 3 */
define('LOGIN_STATUS_LOGOUT' , '3' );
/** （用户）在别处登录 @var 4 */
define('LOGIN_STATUS_OTHER_LOGIN' , '4' );
/*
 * 登录状态 end
 */
/*
 * device_user 表 DEVICEUSER_STATUS 字头
* 登录状态 start
*/
/** 开启 @var 1 */
define('DEVICEUSER_STATUS_OPEN' , '1' );
/** 关闭 @var 2 */
define('DEVICEUSER_STATUS_CLOSE' , '2' );
/*
 * 登录状态 end
*/
/*
 * comment 表 COMMENT_STATUS 字头
* 评论状态 start
*/
/** 评论状态：正常 @var 1 */
define('COMMENT_STATUS_NORMAL' , '1' );
/** 评论：审核 @var 2 */
define('COMMENT_STATUS_AUDIT' , '2' );
/** 评论：忽略 @var 3 */
define('COMMENT_STATUS_IGNORE' , '3' );
/*
 * 评论状态 end
*/
/*
 * user 表 USER_TYPE 字头
* 用户类型 start
*/
/** 手机注册 @var 1 */
define('USER_TYPE_MOBILE' , '1' );
/** 第三方登录：QQ @var 2 */
define('USER_TYPE_QQ' , '2' );
/** 第三方登录：新浪微博 @var 3 */
define('USER_TYPE_SINA' , '3' );
/*
 * 用户类型 end
*/