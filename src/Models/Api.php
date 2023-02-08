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

    /**
     * Constructor
     *
     * Get config for endpoint and set global headers
     *
     * @return void
     */
    public function __construct()
    {
        $this->endpoint = config('vultr.endpoint') ;
        $this->headers = [
            'Content-Type: application/json',
            'Authorization: Bearer '.config('vultr.token')
        ];
    }

    /**
     * Get method
     *
     * @var string $url
     * @var array $query
     * @return json
     * 
     */
    public function get(string $url, array $query=[])
    {
        $url = $this->endpoint . $url;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => json_encode($query),
            CURLOPT_HTTPHEADER => $this->headers
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
	
    /**
     * Post method
     *
     * @var string $url
     * @var array $data	 
     * @return json
     */
    public function post(string $url, array $data=[])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->endpoint . $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->headers
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    /**
     * Put method
     *
     * @var string $url
     * @var array $data	 
     * @return json
     */
    public function put(string $url, array $data=[])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->endpoint . $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->headers
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }
	
    /**
     * delete method
     *
     * @var string $url
     * @return json
     */
    public function delete($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->endpoint . $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $this->headers
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

}