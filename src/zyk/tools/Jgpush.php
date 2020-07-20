<?php
/**
 * 极光链接地址
 */
namespace zyk\tools;
use JPush\Client as JPush;
use JPush\Exceptions\JPushException;

class Jgpush extends ServiceBase {

    /**
     * 设置别名
     * @author lwh 2019-12-31
     * @param $jpushId
     * @param $alias
     * @return array
     */
    static public function setAlias($jpushId,$alias) {
        try {
            $client = new JPush(config('jpush.key'), config('jpush.secret'));
            $device = $client->device();
            // 查询指定设备的别名与标签
            $device->getDevices($jpushId);
            // 更新 Alias
            $res = $device->updateAlias($jpushId, $alias);
            return [RESULT_SUCCESS, '操作成功', '', $res['body']];
        } catch (JPushException $APIRequestException) {
            return [RESULT_ERROR, $APIRequestException->getMessage()];
        }
    }

    /**
     * 删除别名
     * @author lwh 2019-12-31
     * @param $alias
     * @return bool
     */
    static public function delAlias($alias) {
        $client = new JPush(config('jpush.key'), config('jpush.secret'));
        $device = $client->device();
        // 删除别名
        $device->deleteAlias($alias);
        return true;
    }

    /**
     * 获取指定别名下的设备
     * @author lwh 2019-12-31
     * @param $alias
     * @return bool|array|mixed
     */
    static public function getAliasDevices($alias) {
        $client = new JPush(config('jpush.key'), config('jpush.secret'));
        $device = $client->device();
        // 获取指定别名下的设备
        return  $device->getAliasDevices($alias);
    }

    /**
     * 查询指定设备的别名与标签
     * @author lwh 2019-12-31
     * @param $jpushId
     * @return array
     */
    static public function getDevices($jpushId) {
        try {
            $client = new JPush(config('jpush.key'), config('jpush.secret'));
            $device = $client->device();
            // 查询指定设备的别名与标签
            $res = $device->getDevices($jpushId);
            return [RESULT_SUCCESS, '操作成功', '', $res['body']];
        } catch (\Exception $exception) {
            return [RESULT_ERROR, $exception->getMessage()];

        }
    }

    /**
     * 广播推送消息给android和ios
     * @author lwh 2019-12-31
     * @param $type
     * @param $msg
     * @param $title
     * @param $url
     * @return mixed
     */
    static public function pushAll($type='all',$msg,$title,$url) {
        $client = new JPush(config('jpush.key'), config('jpush.secret'));
        try {
            $res = $client->push()
                ->setPlatform($type)
                ->addAllAudience()
                ->setNotificationAlert($msg)
                ->iosNotification($msg, array(
                    'sound' => 'sound.caf',
                    'category' => 'jiguang',
                    'extras' => array(
                        'url' => $url,
                    ),
                ))
                ->androidNotification($msg, array(
                    'title' => $title,
                    'extras' => array(
                        'url' => $url,
                    ),
                ))
                ->message($msg, array(
                    'title' => $title,
                    'extras' => array(
                        'url' => $url,
                    ),
                ))
                ->options(array(
                    'apns_production' => config('jpush.apns_production'),
                ))
                ->setSmsMessage(array(
                    'delay_time' => 3600,
                    'signid' => 154,
                    'temp_id' => 1,
                    'temp_para' => array(
                        'code' => 357
                    ),
                    'active_filter' => false
                ))
                ->send();
            if($res['http_code'] == 200) {
                return $res['body'];
            }else {
                abort(0,'消息通知推送错误');
            }

        } catch (\JPush\Exceptions\APIConnectionException $e) {
            abort(0,'消息通知推送错误');
        } catch (\JPush\Exceptions\APIRequestException $e) {
            trace($e,'error');
        }
    }

    /**
     * 根据别名推送消息
     * @author lwh 2020-01-02
     * @modify LYJ 2020.06.30 增加未读消息数量
     * @param $alias
     * @param string $msg
     * @param string $title
     * @param string $url
     * @return mixed
     */
    static public function pushAlias($alias, $msg, $title, $url, $ext = [], $num = 0) {

        $client = new JPush(config('jpush.key'), config('jpush.secret'));
        try {
            $alert = ['title' => $title, 'body' => $msg];
            $res= $client->push()
                ->setPlatform(array('ios', 'android'))
                ->addAlias($alias)
                ->setNotificationAlert($msg)
                ->iosNotification($alert, array(
                    'sound' => 'sound.caf',
                    'category' => 'jiguang',
                    'extras' => array(
                        'key' => 'value',
                        'url' => $url,
                        'ext' => $ext,
                        'num' => $num,
                    ),
                ))
                ->androidNotification($msg, array(
                    'title' => $title,
                    'extras' => array(
                        'url' => $url,
                        'ext' => $ext,
                        'num' => $num
                    ),
                ))
                ->message($msg, array(
                    'title' => $title,
                    'content_type' => 'text',
                    'extras' => array(
                        'url' => $url,
                        'ext' => $ext,
                        'num' => $num
                    ),
                ))
                ->options(array(
                    'apns_production' => config('jpush.apns_production'),
                ))
                ->setSmsMessage(array(
                    'delay_time' => 3600,
                    'signid' => 154,
                    'temp_id' => 1,
                    'temp_para' => array(
                        'code' => 357
                    ),
                    'active_filter' => false
                ))
                ->send();
            if($res['http_code'] == 200) {
                return [RESULT_SUCCESS, '操作成功', '', $res['body']];
            }else {
                return [RESULT_ERROR, '消息通知推送错误'];
            }
        } catch (JPushException $APIRequestException) {
            return [RESULT_ERROR, $APIRequestException->getMessage()];
        }
    }
}
