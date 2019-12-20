<?php

namespace Powerhouse\Gate\Http;

use Exception;

class Request
{

    /**
     * Repository.
     * 
     * @var array
     */
    private static $repository = [
        'input' => []
    ];
    
    /**
     * Indicates whether the object has already been initialized.
     * 
     * @var bool
     */
    private static $init = false;

    /**
     * Create a new instance of Request.
     */
    public function __construct()
    {
        if (! self::$init) {
            parse_str(file_get_contents("php://input"), self::$repository['input']);
            self::$init = true;
        }
    }

    /**
     * Get the http GET request variables.
     * 
     * @param  string  $name
     * @return string
     */
    public function get(string $name)
    {
        return $_GET[$name] ?? null;
    }

    /**
     * Get the http POST request variables.
     * 
     * @param  string  $name
     * @return string
     */
    public function post(string $name)
    {
        return $_POST[$name] ?? null;
    }

    /**
     * Get the http INPUT request variables.
     * 
     * @param  string  $name
     * @return string
     */
    public function input(string $name = null)
    {
        if ($name === null)
            return self::$repository['input'];

        return self::$repository['input'][$name] ?? null;
    }

    /**
     * Get the http FILE request data.
     * 
     * @param  string  $name
     * @return string
     */
    public function file(string $name)
    {
        return $_FILES[$name] ?? null;
    }

    /**
     * Change request variables.
     * 
     * @param  string  $name
     * @param  mixed  $value
     * @param  string  $method
     * @return void
     */
    public function change(string $name, $value, string $method)
    {
        $method = strtoupper($method);

        if ($method === 'GET')
            $_GET[$name] = $value;
        else if ($method === 'POST')
            $_POST[$name] = $value;
        else if ($method === 'INPUT')
            self::$repository['input'][$name] = $value;
        else
            throw new Exception("The request method can only be `GET`, `POST` & `INPUT`.");
    }

    /**
     * Get the http request variables.
     * 
     * @param  string  $name
     * @return string
     */
    public function __get(string $name)
    {
        switch (http()->method()) {
            case 'GET':
                return $this->get($name);
            case 'POST':
                return $this->post($name);
            default:
                return $this->input($name);
        }
    }

}