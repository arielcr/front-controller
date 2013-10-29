<?php

namespace Digital;

use Symfony\Component\Yaml\Yaml;

class Front {

    private $path_info = NULL;
    private $routes = NULL;

    public function __construct()
    {
        $this->path_info = $this->parsePath();
        $this->routes = Yaml::parse(file_get_contents('src/Digital/routes.yml'));
    }

    private function parsePath()
    {
        $path = array();

        if (isset($_SERVER['REQUEST_URI'])) {

            $request_path = explode('?', $_SERVER['REQUEST_URI']);
            $path['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
            $path['call_utf8'] = substr(urldecode($request_path[0]), strlen($path['base']) + 1);
            $path['call'] = utf8_decode($path['call_utf8']);

            if ($path['call'] == basename($_SERVER['PHP_SELF'])) {
                $path['call'] = '';
            }

            $path['call_parts'] = explode('/', $path['call']);
            $path['query_utf8'] = isset($request_path[1]) ? urldecode($request_path[1]) : array();
            $path['query'] = isset($request_path[1]) ? utf8_decode(urldecode($request_path[1])) : "";

            $vars = explode('&', $path['query']);
            foreach ($vars as $var) {
                $t = explode('=', $var);
                $path['query_vars'][$t[0]] = isset($t[1]) ? $t[1] : array();
            }

        }

        return $path;
    }

    public function dispatch()
    {
        global $data;
        $page = !empty($this->path_info['call_parts'][0]) ? $this->path_info['call_parts'][0] : 'home';

        if (array_key_exists($page, $this->routes)){
            include $this->routes[$page];
        } else {
            include '404.html';
        }
    }


}