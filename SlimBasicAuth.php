<?php
namespace kitbs;

class SlimBasicAuth extends \Slim\Middleware
{
    /**
     * @var string
     */
    protected $realm;

    /**
     * Constructor
     *
     * @param   string  $realm      The HTTP Authentication realm
     */
    public function __construct($realm = 'Protected Area')
    {
        $this->realm = $realm;
    }

    /**
     * Deny Access
     *
     */   
    public function deny_access() {
        $res = $this->app->response();
        $res->status(401);
        $res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
    }

    /**
     * Authenticate
     *
     * @param   string  $username   The HTTP Authentication username
     * @param   string  $password   The HTTP Authentication password    
     *
     */
    public function authenticate($username, $password) {
        if(!ctype_alnum($username))
            return false;

        if(isset($username) && isset($password)) {

            $password = md5($password);

            //Uses Idiorm
            $user = \ORM::for_table('uom_users')
            ->select('id')
            ->where('username', $username)
            ->where('password', $password)
            ->find_one();

            return $user;

        }
        else
            return false;
    }

    /**
     * Call
     *
     * This method will check the HTTP request headers for previous authentication. If
     * the request has already authenticated, the next middleware is called. Otherwise,
     * a 401 Authentication Required response is returned to the client.
     */
    public function call()
    {
        $req = $this->app->request();
        $res = $this->app->response();
        $authUser = $req->headers('PHP_AUTH_USER');
        $authPass = $req->headers('PHP_AUTH_PW');

        if ($this->authenticate($authUser, $authPass)) {
            $this->next->call();
        } else {
            $this->deny_access();
        }
    }
}
