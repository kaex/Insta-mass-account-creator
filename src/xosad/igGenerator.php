<?php

namespace xosad;

use Exception;

class igGenerator extends Common
{
    public $headers = [
        'accept: */*',
        'accept-encoding: gzip, deflate, br',
        'accept-language: en-GB,en-US;q=0.9,en;q=0.8',
        'cache-control: no-cache',
        'content-type: application/x-www-form-urlencoded',
        'origin: https://www.instagram.com',
        'pragma: no-cache',
        'referer: https://www.instagram.com/',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-origin',
        'x-ig-app-id: 936619743392459',
        'x-ig-www-claim: 0',
        'x-instagram-ajax: 61ee74a3c9f7',
        'x-requested-with: XMLHttpRequest'
    ];
	public $csrftoken;
	public $ig_did;
	public $mid;
	public $limit      = 1;
	public $name;
	public $email;
	public $username;
	public $proxy_file;
	public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36';

	/**
	 * igGenerator constructor.
	 *
	 * @param int    $limit
	 * @param string $proxy_file
	 */
	public function __construct($limit = 1, $proxy_file = '')
	{
		$this->limit      = $limit;
		$this->proxy_file = $proxy_file;
	}

	/**
	 * curl_close().
	 *
	 * Close curl when everything is done.
	 */
	public function __destruct()
	{
		self::destruct();
	}

	/**
	 * Generates a new instagram _mid token.
	 *
	 * @param null $proxy
	 *
	 * @return bool
	 */
	private function generateClientId($proxy = null): bool
	{
		$strUrl = 'https://www.instagram.com/web/__mid/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $strUrl);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		if ($proxy)
		{
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		$this->mid = $result;

		return true;
	}

	/**
	 * Generates a new instagram CSRF token.
	 *
	 * @param null $proxy
	 *
	 * @return bool
	 */
	private function generateCsrfToken($proxy = null): bool
	{
		$strUrl = 'https://www.instagram.com/data/shared_data/?__a=1';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $strUrl);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		if ($proxy)
		{
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($result, true);

		$this->csrftoken = $json['config']['csrf_token'];
		$this->ig_did    = $json['device_id'];

		return true;
	}

	/**
	 * https://randomuser.me/api/ generates name,username,email from randomuser.me api.
	 *
	 * @return bool
	 */
	private function getAccountData(): ?bool
	{
		$strUrl = 'https://randomuser.me/api/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $strUrl);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($result, true);

		$this->name     = $json['results']['0']['name']['first'] . ' ' . $json['results']['0']['name']['last'];
		$this->email    = $json['results']['0']['login']['username'] . 'krx' . '@' . 'gmail.com';
		$this->username = $json['results']['0']['login']['username'] . 'krx';

		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function generateInstagramAccount(): bool
	{
		for ($x = 1; $x <= (int)$this->limit; $x++)
		{
			$this->getAccountData();

			if ($this->proxy_file)
			{
				$f_contents = file(ROOTDIR . '/' . $this->proxy_file);
				$line       = trim($f_contents[random_int(0, count($f_contents) - 1)]);

				if ($line)
				{
					$this->generateClientId($line);
					$this->generateCsrfToken($line);

					$this->headers [] = 'X-CSRFToken: ' . $this->csrftoken;
					$this->headers [] = 'Cookie: ig_cb=1; mid=' . $this->mid . '; csrftoken=' . $this->csrftoken . '; ig_did='. $this->ig_did .'; rur=FRC';

					self::construct([
						CURLOPT_USERAGENT      => $this->user_agent,
						CURLOPT_HTTPHEADER     => $this->headers,
						CURLOPT_PROXYTYPE      => CURLPROXY_SOCKS5,
						CURLOPT_CONNECTTIMEOUT => 5,
						CURLOPT_PROXY          => $line
					]);
				}
			}
			else
			{
				$this->generateClientId();
				$this->generateCsrfToken();

				$this->headers [] = 'X-CSRFToken: ' . $this->csrftoken;
				$this->headers [] = 'Cookie: ig_cb=1; mid=' . $this->mid . '; csrftoken=' . $this->csrftoken . '; ig_did='. $this->ig_did .'; rur=FRC';

				self::construct([
					CURLOPT_USERAGENT  => $this->user_agent,
					CURLOPT_HTTPHEADER => $this->headers
				]);
			}

			$this->newUserFlow();

			$this->newUserFlow([
				'current_screen_key' => 'age_consent_two_button',
				'gdpr_s'             => '[0,0,0,null]',
				'updates'            => '{"age_consent_state":2}'
			]);

			$this->createUser([
				'email'                  => $this->email,
				'password'               => '123xosad123',
				'enc_password'           => '#PWD_INSTAGRAM_BROWSER:6:1574258758537:AfVQACD8ZMADsvhOqiQTLa7h4FOIwlld2iDWsLfKqCV1fJ55kh6IFcRcLLJYYhfndCjQDiELGrVMUeFTPid30bb8EyCSCZ/TUxX1dG8lLZ5NzKiQh5NlR1Y1TOapgLiROBov811PUDDYY3oOt4/I',
				'username'               => $this->username,
				'first_name'             => $this->name,
				'client_id'              => $this->mid,
				'seamless_login_enabled' => '1',
				'gdpr_s'                 => '[0,2,0,null]',
				'tos_version'            => 'eu',
				'opt_into_one_tap'       => 'false',
			]);
		}

		return true;
	}

	/**
	 * Requests for https://www.instagram.com/web/consent/new_user_flow/
	 *
	 * @param array $post
	 *
	 * @return bool
	 */
	public function newUserFlow(array $post = []): bool
	{
		if ($post)
		{
			self::request('https://www.instagram.com/web/consent/new_user_flow/', $post, true);

			return true;
		}

		self::request('https://www.instagram.com/web/consent/new_user_flow/', [
			'current_screen_key' => '',
			'gdpr_s'             => ''
		], true);

		return true;
	}

	/**
	 * Request for https://www.instagram.com/accounts/web_create_ajax/
	 *
	 * @param array $post
	 *
	 * @return bool
	 */
	public function createUser(array $post): bool
	{
		$request = self::request('https://www.instagram.com/accounts/web_create_ajax/', $post, true);

		$json = json_decode($request, true);

		if ($json !== null)
		{
			if ($json['account_created'] !== false)
			{
				$json = file_get_contents('accounts.json');

				$newAccount = [
					'created'  => true,
					'username' => $this->username,
					'password' => '123xosad123',
					'email'    => $this->email,
					'name'     => $this->name,
				];

				$accounts   = json_decode($json, true);
				$accounts[] = $newAccount;

				file_put_contents(ROOTDIR . '/accounts.json', json_encode($accounts));

				echo json_encode($newAccount) . PHP_EOL;
			}
			else
			{
				echo 'Could not create the account. Error: ' . $request . PHP_EOL;
			}
		}

        $X_CSRFToken = array_search('X-CSRFToken: ' . $this->csrftoken, $this->headers, true);
        $Cookie = array_search('Cookie: ig_cb=1; mid=' . $this->mid . '; csrftoken=' . $this->csrftoken . '; ig_did=22CCC17C-43CD-4F30-BF06-40126A80EF94; rur=FTW', $this->headers, true);
        unset($this->headers[$X_CSRFToken], $this->headers[$Cookie]);

		return true;
	}

	/**
	 * Follows the given array with the accounts created.
	 *
	 * @param array $accounts
	 *
	 * @throws Exception
	 */
	public function followAccounts(array $accounts = []): void
	{
		foreach ($accounts as $account)
		{
			if (empty(file_get_contents('accounts.json')))
			{
				system('clear');
				echo 'ACCOUNTS.JSON IS EMPTY, EXITING!';
				exit();
			}

			foreach (json_decode(file_get_contents('accounts.json'), true) as $created)
			{
				$this->generateCsrfToken();

				$this->headers [] = 'X-CSRFToken: ' . $this->csrftoken;

				if ($this->proxy_file)
				{
					$f_contents = file(ROOTDIR . '/' . $this->proxy_file);
					$line       = $f_contents[random_int(0, count($f_contents) - 1)];

					if ($line)
					{
						self::construct([
							CURLOPT_USERAGENT      => $this->user_agent,
							CURLOPT_HTTPHEADER     => $this->headers,
							CURLOPT_PROXYTYPE      => CURLPROXY_SOCKS5,
							CURLOPT_CONNECTTIMEOUT => 5,
							CURLOPT_PROXY          => $line,
							CURLOPT_COOKIEJAR      => 'cookies/' . $created['username'] . '.txt',
							CURLOPT_COOKIEFILE     => 'cookies/' . $created['username'] . '.txt',
						]);
					}
				}
				else
				{
					self::construct([
						CURLOPT_USERAGENT  => $this->user_agent,
						CURLOPT_HTTPHEADER => $this->headers,
						CURLOPT_COOKIEJAR  => 'cookies/' . $created['username'] . '.txt',
						CURLOPT_COOKIEFILE => 'cookies/' . $created['username'] . '.txt',
					]);
				}

				$request = self::request('https://www.instagram.com/accounts/login/ajax/', [
					'username'      => $created['username'],
					'password'      => '123xosad123',
					'queryParams'   => '{}',
					'optIntoOneTap' => 'true',
				], true);

				if ($request)
				{
					unset($this->headers[14]);

					$this->headers [] = 'X-CSRFToken: ' . $this->getAccountCSRFToken($created['username']);

					$result = self::request('https://www.instagram.com/web/friendships/' . $this->usr2id($account) . '/follow/', null, true);
					$json   = json_decode($result, true);

					if ($json['status'] !== 'fail' && $json)
					{
						echo json_encode([
							'status'            => true,
							'username_followed' => $account,
							'followed_by'       => $created['username'],
						]);
					}
					else
					{
						echo $result;
					}
				}
				unset($this->headers[14]);
			}
		}
	}

	/**
	 * Get's the CSRF Token of cookie.
	 *
	 * @param string $username
	 *
	 * @return mixed|string
	 */
	private function getAccountCSRFToken(string $username): ?string
	{
		$cookie    = file_get_contents(ROOTDIR . '/cookies/' . $username . '.txt');
		$csrftoken = '/csrftoken\s(.*)\s/mU';
		preg_match_all($csrftoken, $cookie, $csrftoken, PREG_SET_ORDER, 0);
		$csrftoken = $csrftoken[0][1];

		if ($csrftoken !== "")
		{
			return $csrftoken;
		}

		exit;
	}

	/**
	 * Function returns id of the @username
	 *
	 * @param string $username
	 *
	 * @return int|null
	 */
	private function usr2id(string $username): ?int
	{
		$user_id = file_get_contents('https://www.instagram.com/' . $username . '/');
		$re      = '/sharedData\s=\s(.*[^\"]);<.script>/ixU';

		preg_match_all($re, $user_id, $id_username, PREG_SET_ORDER);

		$data = json_decode($id_username[0][1], true);

		return $data['entry_data']['ProfilePage']['0']['graphql']['user']['id'];
	}
}