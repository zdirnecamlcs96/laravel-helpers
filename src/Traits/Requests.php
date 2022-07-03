<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Zdirnecamlcs96\Helpers\Http\Resources\ApiResource;

trait Requests
{

    use Env;

    /**
     * Return json with success status
     *
     * @param null|array $debug
     * @param bool $status
     * @param mixed $message Display json response message
     * @param null|int $code Indicate the response status eg. 200,500,404,422...
     * @param null|array $data Return a json data
     *
     * @return array
     */
    private function __api(array $debug = null, bool $status, $message, int $code = 0, $data = null): array
    {
        $response = [
            "status" => $status,
            /**
             * Instead of using __() Laravel helper,
             *
             * Get bindings from "Illuminate\Translation\TranslationServiceProvider"
             */
            "message" => Container::__translate($message),
            "code" => $code,
            "data" => $data
        ];

        if($this->__isDebug()){
            $response['debug'] = $debug;
        }

        return $response;
    }

    /**
     * Return json with success status
     *
     * @param array $data
     * @param int $total
     * @param int $draw
     * @param null|int $filteredTotal
     *
     * @return array
     */
    private function __ajaxDatatable($data, int $total, int $draw = 1, $filteredTotal = null): array
    {
        return [
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" =>  $filteredTotal !== null && is_int($filteredTotal) ? $filteredTotal : $total,
            "data" => $data
        ];
    }

    function __currentDomain($prefix = "//")
    {
        return $prefix . Container::__request()->getHost();
    }

    /**
     * Determine if the request contains a non-empty value for an input
     *
     * @param  array|string $name
     * @param  mixed $default
     * @return mixed
     */
    function __requestFilled($name, $default = null)
    {
        return (Container::__request()->filled($name) && isset($name)) ? Container::__request()->get($name) : $default;
    }

    /**
     * "when" condition in request
     *
     * @param  mixed $condition
     * @param  array|string $name
     * @param  mixed $default
     * @return void
     */
    function __requestWhen($condition, $name, $default = null)
    {
        return $condition ? $this->__requestFilled($name) : $default;
    }

    /**
     * Make request input lowercase
     *
     * @param  array|string $name
     * @param  mixed $default
     * @return mixed
     */
    function __requestLowerCase($name, $default = null)
    {
        return Str::lower($this->__requestFilled($name, $default));
    }

    function __isApi($type = null)
    {
        $host = Container::__request()->getHost();
        return $type
                ? $host == Container::__config("app.{$type}_url")
                : ($host == Container::__config('app.api_url') || $host == Container::__config('app.driver_url'));
    }

    function __isWeb()
    {
        $host = Container::__request()->getHost();
        return $host == Container::__config('appurl') || $host == Container::__config('app.web_app_url');
    }

    function __isAdmin()
    {
        return Container::__request()->getHost() == Container::__config('app.admin_url');
    }

    function __isMerchant()
    {
        return Container::__request()->getHost() == Container::__config('app.merchant_url');
    }

    function __isEndpoint()
    {
        return Container::__request()->getHost() == Container::__config('app.endpoint_url');
    }

    /**
     * Return json with "success" status
     *
     * @param mixed $message Display json response message
     * @param null|array $data Return a json data
     * @param null|int $code Indicate the response status eg. 200,500,404,422...
     * @param mixed $debug
     *
     * @return \App\Http\Resources\ApiResource
     */
    function __apiSuccess($message, $data = null, ?int $code = 200, ?array $debug = null)
    {
        return new ApiResource($this->__api($debug, true, $message, $code, $data));
    }

    /**
     * Return json with "fail" status
     *
     * @param mixed $message Display json response message
     * @param null|array $data Return a json data
     * @param null|int $code Indicate the response status eg. 200,500,404,422...
     * @param mixed $debug
     *
     * @return \App\Http\Resources\ApiResource
     */
    function __apiFailed($message, $data = null, ?int $code = 500, ?array $debug = null)
    {
        return new ApiResource($this->__api($debug, false, $message, $code, $data));
    }

    function __apiNotFound($message, $data = null, ?int $code = 404, ?array $debug = null)
    {
        return $this->__apiFailed($message, $data, $code, $debug);
    }

    function __apiMethodNotAllowed($message, $data = null, int $code = 405, $debug = null)
    {
        return $this->__apiFailed($message, $data, $code, $debug);
    }

    function __apiNotAuth($message, $data = null, int $code = 401, $returnArray = true)
    {
        if($returnArray){
            return new Response(
                $this->__toStr($this->__api(null, false, $message, $code, $data)),
                $code,
                ["Content-Type" => "application/json"]
            );
        }
        return $this->__apiFailed($message, $data, $code, null);
    }

    /**
     * Return json response
     *
     * @param  mixed $message
     * @param  bool $status
     * @param  int $code
     * @param  mixed $data
     * @param  array $debug     Data used for debugging purpose
     * @return \Illuminate\Http\JsonResponse
     */
    function __jsonRsponse($message, bool $status = true, ?int $code = 500, $data = null, ?array $debug = null)
    {
        return response()->json($this->__api($debug, $status, $message, $code, $data), $code);
    }

    /**
     * Return Datatable result in json format
     *
     * @param  mixed $data
     * @param  int $total
     * @param  int $draw
     * @param  mixed $filteredTotal
     * @return \App\Http\Resources\ApiResource
     */
    function __apiDataTable($data, int $total, int $draw = 1, $filteredTotal = null)
    {
        return new ApiResource($this->__ajaxDatatable($data, $total, $draw, $filteredTotal));
    }

    /**
     * Check whether the request expecting json response
     *
     * @return void
     */
    function __expectsJson()
    {
        return Container::__request()->expectsJson() || $this->__isApi() || $this->__isEndpoint() || Container::__request()->is('api/*');
    }

    public function __getCurl($url) {
        $client = new Client();
        $request = $client->get($url, ['verify' => false]);
        return $request->getBody();
    }

    public function __postCurl($url, $body) {
        $client = new Client();
        $response = $client->createRequest("POST", $url, ['body'=>$body]);
        return $client->send($response);
    }
}