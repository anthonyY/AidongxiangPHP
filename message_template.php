<?php

/*
 * 短信和推送模版配置文件。
 */
/**
 * 短信验证码。
 * 
 * @var String
 */
define('TEMPLATE_SMS_CAPTCHA', '您的验证码：%s，请尽快完成验证。如非本人操作，可不用理会！');

/*
 * 下面是推送的 为了方便调用，用数字做编号， 具体查看相关文档。
 */
/**
 * 抽奖产品被抽中
 */
define('TEMPLATE_PUSH_CONTENT_201', '恭喜您购买的【%s】被抽中了');
define('TEMPLATE_PUSH_TITLE_201', '中奖信息');

/**
 * 后台发货
 */
define('TEMPLATE_PUSH_CONTENT_202', '您购买的【%s】已发货，请注意查收');
define('TEMPLATE_PUSH_TITLE_202', '订单状态改变');

/**
 * 后台换货
 */
define('TEMPLATE_PUSH_CONTENT_203', '您申请的换货商品【%s】已发货，请注意查收');
define('TEMPLATE_PUSH_TITLE_203', '订单状态改变');

/**
 * 后台退货退款
 */
define('TEMPLATE_PUSH_CONTENT_204', '您申请的退货商品【%s】退款金额已退还回您的个人账户');
define('TEMPLATE_PUSH_TITLE_204', '猫券变动');

/**
 * 后台退货退款
 */
define('TEMPLATE_PUSH_CONTENT_205', '您申请的退款金额已退还回您的个人账户');
define('TEMPLATE_PUSH_TITLE_205', '退款');

/**
 * 后台审核财务通过
 */
define('TEMPLATE_PUSH_CONTENT_301', '您提现%s通用券的申请已通过审核');
define('TEMPLATE_PUSH_TITLE_301', '提现申请');
define('TEMPLATE_PUSH_CONTENT_3010', '您充值%s通用券%s银猫券的申请已通过审核');
define('TEMPLATE_PUSH_TITLE_3010', '充值申请');
define('TEMPLATE_PUSH_CONTENT_305', '您提现%s元的申请已通过审核');
define('TEMPLATE_PUSH_TITLE_305', '提现申请');
/**
 * 后台审核财务不通过
 */
define('TEMPLATE_PUSH_CONTENT_304', '您提现%s通用券的申请未通过审核，原因为：【%s】,现已退还回您的个人帐户!');
define('TEMPLATE_PUSH_TITLE_304', '提现申请');
define('TEMPLATE_PUSH_CONTENT_306', '您提现%s元的申请未通过审核，原因为：【%s】,现已退还回您的个人帐户!');
define('TEMPLATE_PUSH_TITLE_306', '提现申请');
define('TEMPLATE_PUSH_CONTENT_3040', '您充值%s通用券%s银猫券的申请未通过审核，原因为：【%s】');
define('TEMPLATE_PUSH_TITLE_3040', '充值申请');

/**
 * 后台产品审核不通过
 */
define('TEMPLATE_PUSH_CONTENT_302', '您发布的【%s】产品未通过审核,原因为：【%s】');
define('TEMPLATE_PUSH_TITLE_302', '产品审核');

/**
 * 后台产品审核通过
 */
define('TEMPLATE_PUSH_CONTENT_303', '您发布的【%s】产品已通过审核');
define('TEMPLATE_PUSH_TITLE_303', '产品审核');

/**
 * 商家产品推送到客户端接收，调出支付接口
 */
define('TEMPLATE_PUSH_CONTENT_1000', '您有个待支付的订单，等待付款！');
define('TEMPLATE_PUSH_TITLE_1000', '支付');



/**
 * 商家产品推送到客户端接收，调出支付接口
 */
define('TEMPLATE_PUSH_CONTENT_1001', '您有一个已支付的订单！');
define('TEMPLATE_PUSH_TITLE_1001', '支付');

/**
 * 商家产品推送到商家端接收，调出确认收款页面
 */
define('TEMPLATE_PUSH_CONTENT_1002', '您有一个确认收款的订单！');
define('TEMPLATE_PUSH_TITLE_1002', '确认收款');