<?php

/**
 * Created by PhpStorm.
 * User: karmadon
 * Date: 29.01.16
 * Time: 13:08
 */
class Controller
{
    public
        $httpParams,
        $postData,
        $page,
        $loggedin;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->httpParams = getParamsFromURI();
        $this->postData = getPOSTparams();

        $this->page = new Page();

    }

    private function mainRouter()
    {
        switch ($this->httpParams['section']) {
            case 'order': {
                loadClass('Order');
                
                $order = new Order($this->httpParams['action'], $this->postData);
                
                $this->page->setPageHeader($order->htmlHeader);
                $this->page->addToBody($order->output);
                
                break;
            }
            case 'user': {
                loadClass('User');
                $user = new User($this->httpParams['action'], $this->postData);



                break;
            }
            default: {

                //Echo "<script> location.replace('/dashboard/view/'); </script>";
            }

                Echo 'Такой Страницы нифига нет';
        }

    }

    public function showSite()
    {
        $this->mainRouter();
        $this->page->displayFullPage();
    }
    public function showLogin()
    {
        $this->httpParams['action']='login';
        $this->httpParams['section']='user';


        $this->mainRouter();
    }

    public function isLoggedUser()
    {
        if (isset ($_SESSION ['login'])) {
            return $this->loggedin = 1;
        } else {
            return $this->loggedin = 0;
        }
    }


    function __destruct()
    {
        if (DEBUG)
            echo 'Site OBJ is closed';

    }
}