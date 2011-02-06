<?php

	function API_Request ($method, $url, $params=null, $username=null, $password=null, $packet=null, $cacheAge=0) {
		
		$filePath = APPPATH."cache/apirequest_".md5($url.serialize($params).$method);
		
		// Make Cache Directory IF Does Not Exist
		if (!is_file($filePath)) touch($filePath);
		
		// Check for Cached File
		if ( file_exists($filePath) != TRUE || (time() - @filemtime($filePath)) > $cacheAge  ) {
			
			// GET NEW FILE
			
			$ch = curl_init();
					
			if (is_array($params)) {
				$url .= '?';
				foreach ($params as $pid=>$pval) {
					$url .= $pid.'='.urlencode($pval);
					if ($pval != end($params)) $url .= '&';
				}
			}
			
			
			if ($method == 'POST' && $params == null) curl_setopt($ch, CURLOPT_POSTFIELDS, $packet);
	
			
			curl_setopt($ch, CURLOPT_URL, $url);
			if (! is_null($username) && ! is_null($password) ) {
				curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'G LAB Studios/API Helper v1'); 
			$data = curl_exec($ch);
			curl_close($ch);
			
			// Save to Cache
			file_put_contents($filePath,$data);
		} else $data = file_get_contents($filePath);
		
		return $data;
	}
	
	function Feed_Request ($url,$params=null,$cacheAge=1800) {
		return API_Request('GET', $url, $params, null, null, null, $cacheAge);
	}