<?php
	class SlackHTTP {

		public static function get($url, $headers=array()){

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL            => $url,
				CURLOPT_HTTPHEADER     => SlackHTTP::prepare_outgoing_headers($headers),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLINFO_HEADER_OUT    => true,
				CURLOPT_HEADER         => true
			));

			$raw = curl_exec($ch);
			$info = curl_getinfo($ch);

			curl_close($ch);

			return SlackHTTP::parse_response($raw, $info);
		}

		public static function post($url, $params=array(), $headers=array()){

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL            => $url,
				CURLOPT_HTTPHEADER     => SlackHTTP::prepare_outgoing_headers($headers),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLINFO_HEADER_OUT    => true,
				CURLOPT_HEADER         => true,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $params
			));

			$raw = curl_exec($ch);
			$info = curl_getinfo($ch);

			curl_close($ch);

			return SlackHTTP::parse_response($raw, $info);
		}

		private static function parse_response($raw, $info){

			list($head, $body) = explode("\r\n\r\n", $raw, 2);
			list($head_out, $body_out) = explode("\r\n\r\n", $info['request_header'], 2);
			unset($info['request_header']);

			$headers_in = SlackHTTP::parse_headers($head, '_status');
			$headers_out = SlackHTTP::parse_headers($head_out, '_request');

			preg_match("/^([A-Z]+)\s/", $headers_out['_request'], $m);
			$method = $m[1];

			# log_notice("http", "{$method} {$url}", $end-$start);

			# http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.2
			# http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#2xx_Success (note HTTP 207 WTF)

			$status = $info['http_code'];

			if (($status < 200) || ($status > 299)){

				return array(
					'ok'		=> false,
					'error'		=> 'http_failed',
					'code'		=> $info['http_code'],
					'method'	=> $method,
					'url'		=> $info['url'],
					'info'		=> $info,
					'req_headers'	=> $headers_out,
					'headers'	=> $headers_in,
					'body'		=> $body,
				);
			}

			return array(
				'ok'		=> true,
				'code'		=> $info['http_code'],
				'method'	=> $method,
				'url'		=> $info['url'],
				'info'		=> $info,
				'req_headers'	=> $headers_out,
				'headers'	=> $headers_in,
				'body'		=> $body,
			);
		}

		private static function parse_headers($raw, $first){

			#
			# first, deal with folded lines
			#

			$raw_lines = explode("\r\n", $raw);

			$lines = array();
			$lines[] = array_shift($raw_lines);

			foreach ($raw_lines as $line){
				if (preg_match("!^[ \t]!", $line)){
					$lines[count($lines)-1] .= ' '.trim($line);
				}else{
					$lines[] = trim($line);
				}
			}


			#
			# now split them out
			#

			$out = array(
				$first => array_shift($lines),
			);

			foreach ($lines as $line){
				list($k, $v) = explode(':', $line, 2);
				$out[StrToLower($k)] = trim($v);
			}

			return $out;
		}

		private static function prepare_outgoing_headers($headers=array()){

			$prepped = array();

			if (!isset($headers['Expect'])){
				$headers['Expect'] = '';	# Get around error 417
			}

			foreach ($headers as $key => $value){
				$prepped[] = "{$key}: {$value}";
			}

			return $prepped;
		}

	}
