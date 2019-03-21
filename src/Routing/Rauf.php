<?php

namespace Kubpro\Framework\Routing;


class Rauf
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected static $routes = [];

    /**
     * Callback parameters
     * @var array
     */
    protected static $callback = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected static $params = [];



    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public static function add($route, $callback){





        $callback = self::getCallBack($callback);

        $route = preg_replace('/\//', '\\/', trim($route,'/'));

        $route = preg_replace("/{([0-9a-zA-Z]+)}/", "([0-9a-zA-Z]+)", $route);;

        $route = '/' . $route . '$/i';

        self::$routes[$route] = $callback;


    }




    protected function getCallBack($callback){

        $results = [];

        if (is_callable($callback)){
            $results['callback']= true;
            $results['function'] = $callback;

        }else{

            $callback_ex = explode('\\',$callback);

            $sl = (count($callback_ex)==1)? '': "\\";

            $results['namespace'] = str_replace($sl.end($callback_ex), '',$callback);

            $controller = explode('@',end($callback_ex));

            $results['controller'] = $controller[0];

            $results['action'] = $controller[1];

            $results['callback']= false;


        }
        return $results;
    }



    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return self::$callback;
    }


    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    public function match($url)
    {
        foreach (self::$routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {

                self::getVariable($matches);

                self::$callback = $params;

                return true;
            }

        }
        return false;
    }


    /**
     * @param array $matches
     */
    protected function getVariable(array $matches){

        unset($matches[0]);

        return self::$params = $matches;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $url The route URL
     *
     * @return void
     */
    public static function dispatch($url)
    {
        $url = self::removeQueryStringVariables($url);

        if (self::match($url)) {


            if (self::$callback['callback']){

                call_user_func_array(self::$callback['function'], self::$params);

            }else{

                $controller = self::$callback['controller'];
                $controller = self::convertToStudlyCaps($controller);

                $controller = self::getNamespace() . $controller;

                //echo $controller;
                if (class_exists($controller)) {


                    $controller_object = new $controller(self::$params);
                    $action = self::$callback['action'];
                    $action = self::convertToCamelCase($action);
                    if (is_callable([$controller_object, $action])) {
                        $controller_object->$action();
                    } else {
                        throw new \Exception("Method $action (in controller $controller) not found");
                    }
                } else {
                    throw new \Exception("Controller class $controller not found");
                }

            }

        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected static function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected static function  convertToCamelCase($string)
    {
        return lcfirst(self::convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    public function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     *
     * @return string The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\';

        if (array_key_exists('namespace', self::$callback) && strlen(self::$callback['namespace'])>1) {
            $namespace .= self::$callback['namespace']."\\";
        }

        return $namespace;
    }


}