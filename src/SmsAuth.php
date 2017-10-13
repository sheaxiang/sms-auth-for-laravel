<?php
namespace Sheaxiang\SmsAuth;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;

class SmsAuth
{
    /**
     * @var string
     */
    protected $target = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';

    /**
     * @var mixed
     */
    protected $config;

    /**
     * @var int
     */
    protected $expire = 5;

    /**
     * @var
     */
    protected $tpl;
    /**
     * SmsAuth constructor.
     * @param array $config
     */
    public function __construct(array $config,$tpl)
    {
        $this->config = config('sms.ihuyi');
        $this->tpl = $tpl;
    }

    /**
     * 发送验证码
     *
     * @param $mobile
     */
    public function send($mobile,$purpose)
    {
        $search = [
            '%var_1%'
        ];
        $replace = [
            $this->random(6, 1)
        ];
        return $this->manager_send(config('sms.ihuyi.tpl.'.$purpose), $search, $replace, $mobile, 1,['key' => $purpose.'_'.$mobile, 'value' => $replace]);
    }

    public function check($mobile,$code)
    {
        return $this->manager_check($mobile, $code);
    }

    /*public function manager_check($key, $random, $clear = true)
    {
        if (Cache::get($key) != $random) {
            return false;
        }
        if ($clear)
            Cache::put($key, null);
        return true;
    }*/

    public function manager_check($mobile, $random)
    {
        if ($mobile != session('mobile') || $random != session('random')) {
            return false;
        }
        session(['mobile' => '', 'random' => '']);
        return true;
    }

    public function manager_send($tpl, $search, $replace, $mobile, $check = 0,$cache = [])
    {
        if (empty($mobile)) {
            throw new HttpResponseException('The cell phone number cannot be empty');
        }
        $content = str_replace($search, $replace, $tpl);
        $post_data = "account=" . $this->config->account . "&password=" . $this->config->password . "&mobile=" . $mobile . "&content=" . rawurlencode($content);
        $gets = $this->xmlToArray($this->post($post_data, config('sms.ihuyi.target')));
        if ($gets['SubmitResult']['code'] == 2) {
            if ($check) {
                session(['mobile' => $mobile, 'random' => $replace[$check - 1]]);
                //Cache::put($cache['key'], $replace[$check - 1], $this->expire);
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
        $arr = '';
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xmlToArray($subxml);
                } else {
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