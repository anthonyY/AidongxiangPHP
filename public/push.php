<?php
use Core\System\AiiUtility\AiiPush\AiiPush;

chdir(dirname(__DIR__));

include __DIR__ . '/../vendor/Core/System/AiiUtility/AiiPush/Autoload.php';
include __DIR__ . '/../config.php';

if ($_REQUEST) {
    $push = new AiiPush();
    
    $device_token = $_REQUEST['device_token'];
    $type = $_REQUEST['type'];
    $content = $_REQUEST['content'];
    $title = $_REQUEST['title'];
    $args = json_decode($_REQUEST['args'], true);
    $args || $args = array();
    $action = $_REQUEST['action'];
    $environment = $_REQUEST['environment'];
    
    $args['action'] = $action;
    $result = $push->pushSingleDevice($device_token, $type, $content, $title, $args, 0 , $environment);
    unset($args['action']);
    var_dump($result);
}
?>
<form>
<table>
<tr><td>设备号：</td><td><input type="text" name="device_token" value="<?php echo isset($device_token) ? $device_token : ''?>"/></td></tr>
<tr><td>设备类型：</td><td><select name="type">
<option value="1" <?php echo isset($type) && $type == 1 ? 'selected="selected"' : ''?>>iOS</option>
<option value="2" <?php echo isset($type) && $type == 2 ? 'selected="selected"' : ''?>>安卓</option>
</select></td></tr>
<tr><td>开发模式：</td><td><select name="environment">
<option value="1" <?php echo isset($environment) && $environment == 1 ? 'selected="selected"' : ''?>>生产</option>
<option value="2" <?php echo isset($environment) && $environment == 2 ? 'selected="selected"' : ''?>>开发</option>
</select> iOS有效</td></tr>
<tr><td>消息类型：</td><td><select name="action">
<option value="1" <?php echo isset($action) && $action == 1 ? 'selected="selected"' : ''?>>通知</option>
<option value="2" <?php echo isset($action) && $action == 2 ? 'selected="selected"' : ''?>>透传消息</option>
</select> 安卓有效</td></tr>
<tr><td>内容：</td><td><input type="text" name="content" value="<?php echo isset($content) ? $content : ''?>"/></td></tr>
<tr><td>标题：</td><td><input type="text" name="title" value="<?php echo isset($title) ? $title : ''?>"/></td></tr>
<tr><td>自定义参数：</td><td><input type="text" name="args" value="<?php echo isset($args) ? htmlentities(json_encode($args,JSON_UNESCAPED_UNICODE)) : ''?>"/> json字符串格式</td></tr>
<tr><td></td><td><input type="submit" name="" value="提交"/></td></tr>
</table>
</form>