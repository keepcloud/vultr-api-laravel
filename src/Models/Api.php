<?php
/**
 * Curl wrapper
 *
 * @package keepcloud/vultr-api-laravel
 * @author Edison Costa <edison@keepcloud.io>
 */
namespace KeepCloud\Vultr\Models;

class Api
{
    protected $endpoint;
    protected $headers;
    protected $timeout;

    /**
     * Constructor
     *
     * Get config for endpoint and set global headers
     *
     * @return void
     */
    public function __construct()
    {
        $this->endpoint = rtrim(config('vultr.endpoint', 'https://api.vultr.com/v2/'), '/') . '/';
        $this->timeout = (int) config('vultr.timeout', 30);
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer '.config('vultr.token')
        ];
    }

    /**
     * Get method
     *
     * @var string $url
     * @var array $query
     * @return string|false
     */
    public function get(string $url, array $query = [])
    {
        if (!empty($query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        return $this->request('GET', $url);
    }

    /**
     * Post method
     *
     * @var string $url
     * @var array $data
     * @return string|false
     */
    public function post(string $url, array $data = [])
    {
        return $this->request('POST', $url, $data);
    }

    /**
     * Put method
     *
     * @var string $url
     * @var array $data
     * @return string|false
     */
    public function put(string $url, array $data = [])
    {
        return $this->request('PUT', $url, $data);
    }

    /**
     * Patch method
     *
     * @var string $url
     * @var array $data
     * @return string|false
     */
    public function patch(string $url, array $data = [])
    {
        return $this->request('PATCH', $url, $data);
    }

    /**
     * Delete method
     *
     * @var string $url
     * @return string|false
     */
    public function delete(string $url)
    {
        return $this->request('DELETE', $url);
    }

    /**
     * Send the request
     *
     * @var string $method
     * @var string $url
     * @var array|null $data
     * @return string|false
     */
    protected function request(string $method, string $url, $data = null)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->endpoint . ltrim($url, '/'),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $this->timeout,
        ];

        if ($data !== null) {
            // An empty body must be sent as a JSON object, not an empty array.
            $options[CURLOPT_POSTFIELDS] = empty($data) ? '{}' : json_encode($data);
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        // curl_close() is a no-op since PHP 8.0 and deprecated since PHP 8.5.
        if (PHP_VERSION_ID < 80000) {
            curl_close($curl);
        }

        return $response;
    }
}
