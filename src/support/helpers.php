<?php

use Kubpro\Framework\Foundation\Application as app;

use eftec\bladeone\BladeOne as View;

if (! function_exists('asset')){
    /**
     * The asset function generates a URL for an asset using the current scheme of the request (HTTP or HTTPS):
     * -----------------
     * @param null $path
     * @return string
     */
    function asset($path=null){
       $protocol = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';

       $path = ($path)? $path : '';

       return $protocol. $_SERVER['HTTP_HOST'] . $path;
    }
}


if (! function_exists('base_path')){
    /**
     * Base Path application
     * @return string
     */
    function base_path(){
        return app::$basePath;
    }
}


if ((! function_exists('view'))){
    /**
     * framework view function
     *
     * @param $view
     * @param array $data
     * @throws Exception
     */
    function view($view, $data = []){

        $view = base_path()."/resources/views/";
        $cache = base_path()."/storage/framework/views";

        $blade = new View($view,$cache,View::MODE_AUTO);

        echo $blade->run($view,$data);

    }
}


