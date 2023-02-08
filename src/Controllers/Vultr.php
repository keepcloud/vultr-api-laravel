<?php
/**
 * Vultr api controller
 *
 * @package keepcloud/vultr-api-laravel
 * @author Edison Costa <edison@keepcloud.io>
 */    
namespace KeepCloud\Vultr\Controllers;

use App\Http\Controllers\Controller;
use KeepCloud\Vultr\Models\Api;

class Vultr extends Controller
{
    protected $api ;
    protected $token ;
    
    /**
     * Constructor
     *
     * Get config and and create new api
     *
     * @return void
     */
    public function __construct()
    {
        $this->token = config('vultr.token') ;
        $this->api = new Api ;
    }
    
    /**
     * Overload static methods for facade 
     *
     * @var string $name
     * @var array $arguments	 
     * @return json|array|object
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $api = new Api ;
        $response = call_user_func_array([$api,$name], $arguments) ;
        $response = json_decode($response) ;
        return $response ;
    }
    
    /**
     * Overload methods 
     *
     * @var string $url
     * @var array $data	 
     * @return json|array|object
     */
    public function __call($name, $arguments)
    {
        $response = call_user_func_array([$this->api,$name], $arguments) ;
        $response = json_decode($response) ;
        return $response ;
    }
}