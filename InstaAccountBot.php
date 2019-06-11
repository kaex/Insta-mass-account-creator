<?php
namespace xosad;


class InstaAccountBot
{
    private $csrftoken;
    private $name;
    private $email;
    private $username;
    private $password;

    private $headers = array();
    private $user_agent = "Mozilla/5.0 (Linux; Android 6.0.1; SM-G935T Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36";

    private function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 15; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    private function randstring($length){
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    private function generateCsrfToken()
    {
        $strUrl = 'https://www.instagram.com/data/shared_data/?__a=1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$strUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close ($ch);

        $json = json_decode($result , true);
        return $json['config']['csrf_token'];
    }

    private function getAccountData()
    {
        $strUrl = 'https://randomuser.me/api/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$strUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close ($ch);

        $json = json_decode($result , true);

        $this->name = $json['results']['0']['name']['first'].' '.$json['results']['0']['name']['last'];
        $this->email = $json['results']['0']['login']['username'].'we3z'.'@'.'gmail.com';
        $this->username = $json['results']['0']['login']['username'].'we3z';

        return true;
    }

    public function createAccount($proxy = null)
    {
        $this->csrftoken = $this->generateCsrfToken();
        $this->password  = $this->randomPassword();
        $this->getAccountData();

        $headers = $this->headers;
        $headers[] = 'accept: */*';
        $headers[] = 'accept-encoding: gzip, deflate, br';
        $headers[] = 'accept-language: en-GB,en-US;q=0.9,en;q=0.8';
        $headers[] = 'content-type: application/x-www-form-urlencoded';
        $headers[] = 'origin: https://www.instagram.com';
        $headers[] = 'referer: https://www.instagram.com/';
        $headers[] = 'x-csrftoken: '.$this->csrftoken;
        $headers[] = 'x-ig-app-id: 936619743392459';
        $headers[] = 'x-instagram-ajax: 1';
        $headers[] = 'x-requested-with: XMLHttpRequest';

        $arrPostData = array();
        $arrPostData['email'] = $this->email;
        $arrPostData['password'] = $this->password;
        $arrPostData['username'] = $this->username;
        $arrPostData['first_name'] = $this->name;
        $arrPostData['client_id'] = $this->randstring(28);
        $arrPostData['seamless_login_enabled'] = '1';
        $arrPostData['gdpr_s'] = '%5B0%2C2%2C0%2Cnull%5D';
        $arrPostData['tos_version'] = 'eu';
        $arrPostData['opt_into_one_tap'] = 'false';

        $strUrl = 'https://www.instagram.com/accounts/web_create_ajax/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$strUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_COOKIE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if(!empty(file('proxy.txt')) OR $proxy !== null)
        {
            curl_setopt($ch, CURLOPT_PROXY , $proxy);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrPostData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);

        if(curl_errno($ch) !== 28 AND $result !== false)
        {
            $json = json_decode($result, true);

            if($json['account_created'] !== false AND $json['status'] !== 'fail')
            {
                $data =
                    [
                        'created' => true,
                        'username' => $this->username,
                        'password' => $this->password,
                        'email' => $this->email,
                        'name' => $this->name,
                    ];

                $json_data = json_encode($data);

                $fp = fopen('accounts.txt', 'a');
                fwrite($fp, $json_data.PHP_EOL);
                fclose($fp);

                curl_close ($ch);
                return $json_data.PHP_EOL;
            }else {
                curl_close ($ch);
                return $result.PHP_EOL;
            }
        }else{
            curl_close ($ch);
            return 'Proxy is dead! & curl_exec bool(false)'.PHP_EOL;
        }
    }
}