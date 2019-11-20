<?php

namespace xosad;

class Common
{
	public static $curl_options = [
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TCP_NODELAY    => true,
		CURLOPT_FORBID_REUSE   => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST           => true,
		CURLOPT_ENCODING       => '',
	];
	public static $curl;

	/**
	 * curl_init()
	 *
	 * @param array $curl_options
	 */
	public static function construct(array $curl_options = []): void
	{
		self::$curl         = curl_init();
		self::$curl_options = array_replace(self::$curl_options, $curl_options);
	}

	/**
	 * request function for instagram.
	 *
	 *
	 * @param string      $url
	 * @param array       $params
	 * @param bool        $post
	 * @param string|null $extra_params_string
	 *
	 * @return string|null
	 */
	public static function request(string $url, array $params = [], bool $post = true, ?string $extra_params_string = ''): ?string
	{
		self::$curl_options[CURLOPT_POST] = $post;

		if (!empty($params))
		{
			if ($post === true)
			{
				self::$curl_options[CURLOPT_POSTFIELDS] = http_build_query($params) . ($extra_params_string ? '&' . $extra_params_string : '');
			}
			else
			{
				$url .= '?' . http_build_query($params) . ($extra_params_string ? '&' . $extra_params_string : '');
			}
		}

		self::$curl_options[CURLOPT_URL] = $url;
		curl_setopt_array(self::$curl, self::$curl_options);

		return curl_exec(self::$curl);
	}

	/**
	 * curl_close()
	 */
	public static function destruct(): void
	{
		if (is_resource(self::$curl))
		{
			curl_close(self::$curl);
		}
	}
}