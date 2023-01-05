<?php
class CheckUsername{

    protected $cookies = "fragment.cookies";
    protected $fhash = "fragment.hash";
    protected $dev = false;
    private $hash;


    public function __construct($similar_found = false){
        $this->sf = $similar_found;
        if(!file_exists($this->fhash)) $this->cookie();
    }

    private function cookie($ul = false){
        if($ul) { @unlink($this->cookies); @unlink($this->fhash); }
        $curl = $this->curl(false, "/");
        $this->hash = $this->getStr("?hash=", "\"", $curl);
        @file_put_contents($this->fhash, $this->hash);
        return $this->hash;
    }

    public function check($username){
        while(true){
            $result = $this->curl("type=usernames&query=$username&method=searchAuctions");
            if($result) return $this->parse(@json_decode($result, true)['html']);
            $this->cookie();
        }
    }

    private function curl($body = false, $path = false){
        $hash = $this->hash ? : @file_get_contents($this->fhash);
        $path = $path ? $path : "/api?hash=$hash";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fragment.com'.$path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($body){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $headers[] = 'X-Requested-With: XMLHttpRequest';
        }else
        $headers = array();
        $headers[] = 'Host: fragment.com';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.5304.107 Safari/537.36';
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookies);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookies);
        $result = curl_exec($ch);
        curl_close($ch);
        $json = @json_decode($result, true) ? : array();
        if(array_key_exists("error", $json)) return false;
        else return $result;
    }

    private function getStr($a, $b, $c){ return @explode($b, @explode($a, $c)[1])[0]; }

    private function parse($res){
        if(empty($res)) exit("error.");
        $res = @explode("<div class=\"table-cell-value tm-value\">@", $res);
        $a = -1;
        $u = array();
        foreach($res as $r){
            $a++;
            if($a == 0) continue;
            $data = @explode("tm-status-", $r);
            $u[$a-1]['username'] = @explode("</div>", $data[0])[0];
            $u[$a-1]['available'] = @explode("\">", $data[1])[0] == "unavail" ? true : false;
        }
        $p = $u[0]['username'];
        $ps = $u[0]['available'];
        array_splice($u, 0, 1);
        $z = array("username" => $p, "available" => $ps);
        if($this->sf) $z["similar_found"] = array("count" => count($u), "data" => $u);
        return @json_encode($z);
    }
}
