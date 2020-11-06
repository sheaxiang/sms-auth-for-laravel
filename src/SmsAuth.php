<?php
namespace SheaXiang\SmsAuth;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;

class SmsAuth
{
    /**
     * @var string
     */
    protected $target = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';

    /**
     * @var int
     */
    protected $expire = 5;

    /**
     * 发送验证码
     *
     * @param $mobile
     */
    public static function send($mobile,$purpose)
    {
        $search = [
            '%var_1%'
        ];
        $random =  (new static)->random(6, 1);
        $replace = [$random];
        return (new static)->manager_send(config('sms-auth.ihuyi.tpl.'.$purpose), $search, $replace, $mobile, 1,['key' => $purpose.'_'.$mobile, 'value' => $random]);
    }

    public static function check($phone, $code, $key)
    {
        $key = $key.'_'.$phone;
        $verifyData = Cache::get($key);

        if ($verifyData !== $code) {
            return false;
        }
        // 清除验证码缓存
        Cache::forget($key);
        return true;
    }

    public function manager_send($tpl, $search, $replace, $mobile, $check = 0,$cache = [])
    {
        if (empty($mobile)) {
            throw new HttpResponseException('The cell phone number cannot be empty');
        }
        $content = str_replace($search, $replace, $tpl);
        $post_data = "account=" . config('sms-auth.ihuyi.account'). "&password=" . config('sms-auth.ihuyi.password'). "&mobile=" . $mobile . "&content=" . rawurlencode($content);

        $gets = $this->xmlToArray($this->post($post_data, $this->target));

        if ($gets['SubmitResult']['code'] == 2) {
            if ($check) {
                Cache::put($cache['key'],$cache['value'],now()->addMinutes($this->expire));
            }
            return true;
        }
        return false;
    }

    public function post($curlPost, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    function xmlToArray($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml )){
                    $arr[$key] = $this->xmlToArray( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    public function random($length = 6, $numeric = 0)
    {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }
}
