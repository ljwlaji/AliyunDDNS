<?php

$GLOBALS['AccessKeyId'] = '阿里云 AccessKeyId';  //填Access Key Id
$GLOBALS['AccessKeySecret'] = '阿里云 AccessKeySecret'; //填Access Key Secret
$GLOBALS['DomainName'] = '域名 xxxx.com'; //填域名

date_default_timezone_set("UTC");//设置时区为utc

(new IPUpdater)->Index();

class IPUpdater
{
	static $ErrorFunction = -1;
	public function Index()
	{
		$Args = $this->GetArgs();
        $Func = $this->GetKey("func", $Args);
        if ($Func && method_exists($this, $Func) == 1)
        {
            echo $this->$Func($Args);
        }
        else
        {
            echo static::$ErrorFunction;
        }
	}
	
	private function updateip($Args)
	{
		$subHostName = $this->GetKey("sub", $Args);
		$record_info_url = $this->get_url(array(
        'Action' => 'DescribeDomainRecords',
        'DomainName' => $GLOBALS['DomainName']));
		
		$record_info = $this->request_get($record_info_url);//获取域名记录列表
		$records = $record_info['DomainRecords']['Record'];
		$Record_ID = 0;
		for($i = 0; $i < count($records); ++$i)
		{
			if ($records[$i]['RR'] == $subHostName)
			{
				$Record_ID = $records[$i]['RecordId'];
				break;
			}
		}
		$ip = trim(file_get_contents('http://ip.cip.cc'));
		if ($Record_ID == 0)
			$this->Add_New_Record($subHostName, $GLOBALS['DomainName'], $ip);
		else
			$this->Update_Record($Record_ID, $ip, $subHostName);
		
	}
	
	
	private function get_url($param=array())
	{
		$aliyun='alidns.aliyuncs.com/?';
		$rand_num=rand(10000000,999999999);
		$time=time();
		$common=array(
			'Format'=>'JSON',
			'Version'=>'2015-01-09',
				'Timestamp'=>date('Y-m-d\TH:i:s\Z',$time),
			'SignatureMethod'=>'HMAC-SHA1',
			'AccessKeyId' => $GLOBALS['AccessKeyId'],
			'SignatureVersion'=>'1.0',
			'SignatureNonce'=>$rand_num,
		);
		$all=array_merge($common,$param);
		ksort($all,SORT_NATURAL);
		$ur=http_build_query($all);
		$uri=rawurlencode($ur);
		$final_url='GET&'.rawurlencode('/').'&'.$uri;
		$sign = base64_encode(hash_hmac('sha1', $final_url, $GLOBALS['AccessKeySecret'] . '&', true));
		$all['Signature']=$sign;
		$uurl=$aliyun.http_build_query($all);
		return $uurl;
	}

	private function delete_record($recordId)
	{
		$Delete_Param['Action']='DeleteDomainRecord';
		$Delete_Param['RecordId']=$recordId;
		$this->sendRequest($Delete_Param);
	}

	private function Add_New_Record($RR, $url, $ip)
	{
		$update_param['Action']='AddDomainRecord';
		$update_param['RR']=$RR;
		$update_param['Type']='A';
		$update_param['Value']=$ip;
		$update_param['DomainName']=$url;
		$this->sendRequest($update_param);
	}

	private function Update_Record($RecordId ,$ip, $subHostName)
	{
		$update_param['Action']='UpdateDomainRecord';
		$update_param['RecordId']=$RecordId;
		$update_param['RR']= $subHostName;
		$update_param['Type']='A';
		$update_param['Value']=$ip;
		$this->sendRequest($update_param);
	}

	
	private function request_get($url)
	{
		$curl=curl_init();//初始化curl
		curl_setopt($curl,CURLOPT_URL,$url);
		//referer头
		curl_setopt($curl,CURLOPT_AUTOREFERER,true);
		curl_setopt($curl,CURLOPT_HEADER,false);//不处理响应头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);//返回响应结果，不直接输出
		$response=curl_exec($curl);//发出请求
		return json_decode($response,true);
	}
	
	private function sendRequest($param)
	{
		$update_url = $this->get_url($param);
		$res = $this->request_get($update_url);
		$ret = "";
		if ($this->GetKey("Message", $res) == null)
		{
			$ret = "Success Update Ip To : ";
		}
		else
		{
			$ret = $res['Message'];
		}
		echo $ret.$param['Value'];
	}
	
	
	private function GetCurrentArgs($Input, $FetchArray)
	{
		$datas = explode('=', $Input);
		return $datas;
	}
	
	private function GetArgs() 
	{
		$Args = [];
		$test = $_SERVER['QUERY_STRING'];
		$data = explode('&', $test);
		for ($i = 0; $i < sizeof($data); $i++)
		{
			$OutPut = IPUpdater::GetCurrentArgs($data[$i], $Args);
			$Args[$OutPut[0]] = $OutPut[1];
		}
		return $Args;
	}
	
	private function GetKey($Key, $Array)
    {
        if (array_key_exists($Key, $Array) == 1)
            return $Array[$Key];
        return null;
    }
}