<?php

namespace Powerhouse\Routing;

use Powerhouse\Gate\Http;
use Exception;

class Find
{

    /**
     * The illegal keywords in the uri.
     * 
     * @var array
     */
    protected $illegalKeywords = [
        'app'
    ];

    /**
     * The number of parameters.
     * 
     * @var array
     */
    protected $parametersInfo = [];

    /**
     * The parameters.
     * 
     * @var array
     */
    protected $parameters = [];

    /**
     * Serve the matching route.
     */
    public function serve()
    {
        foreach (static::$routes as $route) {
            $this->resetParameters();

            if (http()->api($route['uri']) !== http()->api())
                continue;

            if (! $this->checkMethod($route['method']))
                continue;

            if (! $this->checkUri($route))
                continue;

            else {
                $this->executeRoute($route);
                break;
            }
        }
    }

    /**
     * Execute the route controller or callback.
     * 
     * @param  array  $route
     * @return void
     */
    protected function executeRoute(array $route)
    {
        $this->executeBridges($route['bridge']);

        if (is_callable($route['func']))
            $this->executeCallback($route['func']);

        else
            $this->executeController($route['func']);
    }

    /**
     * Execute bridges.
     * 
     * @param  array  $bridges
     * @return void
     */
    protected function executeBridges(array $bridges)
    {
        // General bridges
        foreach (config()->app->bridges->general as $bridge) {
            $obj = new $bridge();
            $obj->operation(request());
        }
    }

    /**
     * Execute callback.
     * 
     * @param  callback  $callback
     * @return void
     */
    protected function executeCallback(callable $callback)
    {
        $callback(request(), ...$this->parameters);
    }

    /**
     * Execute controller.
     * 
     * @param  string  $controller
     * @return void
     */
    protected function executeController(string $controller)
    {
        //
    }

    /**
     * Check the method of the route.
     * 
     * @param  string  $method
     * @return bool
     */
    protected function checkMethod(string $method)
    {
        if ($method === http()->method() || (! http()->api() && $method === request()->post('_method')))
            return true;

        return false;
    }

    /**
     * Check the uri.
     * 
     * @param  array  $route
     * @return bool
     */
    protected function checkUri(array $route)
    {
        $uri = $this->trimmer($route['uri']);
        $redirectUri = $this->trimmer(http()->getRedirectUri());

        if ($this->illegalUri($uri))
            return false;

        if (! $this->hasParameters($uri) && $uri === $redirectUri)
            return true;

        $uriTokens = explode('/', $uri);
        $redirectUriTokens = explode('/', $redirectUri);
        $info = $this->getParametersInfo($uriTokens);

        for ($i = 0; $i < count($uriTokens); $i++) {
            
            if (! (count($redirectUriTokens) >= count($uriTokens) - $this->parametersInfo['optional']))
                return false;
                
            if (! (count($redirectUriTokens) <= count($uriTokens)))
                return false;

            if ($info[$i] === false && $redirectUriTokens[$i] === $uriTokens[$i])
                continue;

            else if ($info[$i] === false)
                return false;

            else
                if (isset($route['pattern'][$info[$i]['name']])) {
                    $regex = $route['pattern'][$info[$i]['name']];

                    if (isset($redirectUriTokens[$i]))
                        if (preg_match('/'. $regex .'/', $redirectUriTokens[$i]) > 0) {
                            array_push($this->parameters, $redirectUriTokens[$i]);

                            return true;
                        } else
                            return false;

                    else
                        return true;
                        
                } else {
                    if (isset($redirectUriTokens[$i]))
                        array_push($this->parameters, $redirectUriTokens[$i]);

                    return true;
                }
        }
    }

    /**
     * Determine whether the uri contains illegal keywords.
     * 
     * @param  string  $uri
     * @return bool
     */
    protected function illegalUri(string $uri)
    {
        return in_array(strtok($this->trimmer($uri), '/'), array_merge([config()->app->api_prefix], $this->illegalKeywords));
    }

    /**
     * Determine whether the uri has parameters.
     * 
     * @param  string  $uri
     * @param  string  $type
     * @param  bool  $return
     * @return bool|array
     */
    protected function hasParameters(string $uri, $type = 'all', $return = false)
    {
        switch ($type) {
            case 'all':
                $delimiter = '([?]{0,1})';
                break;
            case 'required':
                $delimiter = '([?]{0})';
                break;
            case 'optional':
                $delimiter = '([?]{1})';
                break;
            default:
                throw new Exception("The parameter type is invalid.");
        }

        $result = preg_match('/{([a-z_]+)'. $delimiter .'}/', $uri, $matches);
        if (! $return)
            return $result > 0 ? true : false;

        return $result > 0 ? $matches : false;
    }

    /**
     * Get the parameters.
     * 
     * @param 
     */

    /**
     * Get the information of uri parameters.
     * 
     * @param  array  $tokens
     * @return array
     */
    protected function getParametersInfo(array $tokens)
    {
        $this->resetParametersInfo();

        $parameters = array_map(function ($item) {
            $result = $this->hasParameters($item, 'all', true);

            if ($result !== false) {
                $isRequired = $result[2] === '?' ? false : true;

                if ($isRequired)
                    $this->parametersInfo['required']++;
                else
                    $this->parametersInfo['optional']++;

                $this->parametersInfo['all']++;

                return [
                    'name' => $result[1],
                    'required' => $isRequired
                ];
            }  

            return false;
        }, $tokens);

        return $parameters;
    }

    /**
     * Uri Trimmer.
     * 
     * @param  string  $uri
     * @return string
     */
    protected function trimmer(string $uri)
    {
        return trim($uri, '/');
    }

    /**
     * Reset parameters.
     */
    protected function resetParameters()
    {
        $this->parameters = [];
    }

    /**
     * Reset parameters info.
     */
    protected function resetParametersInfo()
    {
        $this->parametersInfo = [
            'optional' => 0,
            'required' => 0,
            'all' => 0,
        ];
    }

}
