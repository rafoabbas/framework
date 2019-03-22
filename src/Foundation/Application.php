<?php
/**
 * Created by PhpStorm.
 * User: coder
 * Date: 21.03.2019
 * Time: 12:56
 */
namespace Kubpro\Framework\Foundation;

use Dotenv\Dotenv as ENV;
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler as ErrorHandler;
use Illuminate\Database\Capsule\Manager as Capsule;

class Application
{

    /**
     * The Laravel framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * The base path for the Application installation.
     *
     * @var string
     */
    public static $basePath;



    public function __construct($basePath)
    {

        if ($basePath) {
            $this->setBasePath($basePath);
        }

    }

    public function register($register){

        switch ($register){
            case 'woop':
                $this->registerWhoop();
                break;
            case 'database':
                $this->registerDatabase();
                break;
            case 'env':
                $this->registerEnv();
                break;

            default:
                throw new \Exception("Method {$register} dont't registered");
                break;
        }


    }





    /**
     *  Whoops\Run registered
     */
    public function registerWhoop(){
        $handler = new ErrorHandler();
        $handler->blacklist('_ENV','DB_PASSWORD');
        $handler->blacklist('_ENV','DB_USERNAME');
        $handler->blacklist('_ENV','DB_DATABASE');
        //$handler->getEditorHref('')
        $handler->setApplicationRootPath(self::$basePath);
        $handler->setPageTitle('KUBPRO ERROR');
        $whoops = new Whoops;
        $whoops->writeToOutput(env('APP_DEBUG'));
        $whoops->allowQuit(true);

        $whoops->pushHandler($handler);

        $whoops->register();



    }


    /**
     * Laravel illinmunate database package
     */
    public function registerDatabase(){

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]),
        ]);

        //Make this Capsule instance available globally.
        $capsule->setAsGlobal();


        // Setup the Eloquent ORM.
        $capsule->bootEloquent();


    }

    /**
     * Whoops\Handler\PrettyPageHandler registered
     */
    public function registerEnv(){
        $dotenv = Env::create($_SERVER['DOCUMENT_ROOT']);
        $dotenv->load();
    }


    /**
     * Set the base path for the application.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        self::$basePath = rtrim($basePath, '\/');

    }


    /**
     * get the base path for the application.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function getBasePath()
    {
        return self::$basePath;
    }

}