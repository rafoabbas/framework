<?php

namespace Kubpro\Framework\View;


class View
{

    /**
     * Render a view file
     *
     * @param string $view  The view file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);


        $root = $_SERVER['DOCUMENT_ROOT'].'/resources/views';
        $file = $root.$view;  // relative to Core directory


        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template  The template file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;

        $root = $_SERVER['DOCUMENT_ROOT'].'/resources/views';

        if ($twig === null) {
            $loader = new \Twig_Loader_Filesystem($root);
            $twig = new \Twig_Environment($loader);
        }


        $template = str_replace(array('/','.'),'/',$template).".html";

        echo $twig->render($template, $args);

    }
}