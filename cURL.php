<?php

class cURL
{
	
	private CurlHandle $curl;
	
	public int $code;
	public string $error;
	public array $request;
	public array $response;
	
	public function __construct()
	{
		return $this->init();
	}
	
	public function __destruct()
	{
		curl_close($this->curl);
	}
	
	public function init(): cURL
	{
		if(isset($this->curl))
		{
			curl_close($this->curl);
		}
		
		$this->curl = curl_init();
		
		curl_setopt($this->curl, CURLOPT_ENCODING, '');
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, '');
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		
		$this->code = 0;
		$this->error = '';
		$this->request = [];
		$this->response = [];
		
		return $this;
	}
	
	public function post(array|string $post, bool $json = false): cURL
	{
		curl_setopt($this->curl, CURLOPT_POST, true);
		
		if($json)
		{
			$this->header('Content-Type', 'application/json');
		}
		
		if(is_array($post))
		{
			$post = $json ? json_encode($post) : http_build_query($post);
		}
		
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
		
		$this->header('Content-Length', mb_strlen($post, '8bit'));
		
		return $this;
	}
	
	public function cookie(string $cookie): cURL
	{
		curl_setopt($this->curl, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $cookie);
		
		return $this;
	}
	
	public function referer(string $referer): cURL
	{
		$this->header('Referer', $referer);
		
		if($url = parse_url($referer))
		{
			$this->header('Origin', $url['scheme'].'://'.$url['host']);
		}
		
		return $this;
	}
	
	public function useragent(string $useragent = null): cURL
	{
		if(empty($useragent))
		{
			$useragent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		}
		
		$this->header('User-Agent', $useragent);
		
		return $this;
	}
	
	public function header(string $key, string $value): cURL
	{
		$this->request[$key] = $value;
		
		return $this;
	}
	
	public function headers(array $headers): cURL
	{
		foreach($headers as $key => $value)
		{
			$this->request[$key] = $value;
		}
		
		return $this;
	}
	
	public function exec(string $url, string $file = null): string
	{
		curl_setopt($this->curl, CURLOPT_URL, $url);
		
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_map(fn($k, $v) => $k.': '.$v, array_keys($this->request), array_values($this->request)));
		
		if(empty($file))
		{
			curl_setopt($this->curl, CURLOPT_HEADER, true);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			
			$response = $this->body(curl_exec($this->curl));
		}
		else
		{
			$handle = fopen($file, 'w');
			
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_FILE, $handle);
			
			$response = curl_exec($this->curl);
			
			fclose($handle);
		}
		
		$this->code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		
		$this->error = curl_strerror(curl_errno($this->curl));
		
		return $response;
	}
	
	private function body(string $response): string
	{
		$size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		
		$headers = mb_substr($response, 0, $size, '8bit');
		
		preg_match_all('#\n([^:]+): ([^\r]+)\r#', $headers, $matches);
		
		foreach($matches[1] as $key => $name)
		{
			$this->response[$name] = $matches[2][$key];
		}
		
		return mb_substr($response, $size, null, '8bit');
	}
}
