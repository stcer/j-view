<?php

namespace j\view\tag;

use j\di\Container;

/**
 * Class FlashMessageSession
 * @package j\view\tag
 */
class FlashMessageSession extends FlashMessage{
    /**
     * @var \j\session\Session
     */
    protected $session;

    protected $key = 'page_msg';

    /**
     * MessageQueue constructor.
    * @param \j\session\Session $session
    */
    function __construct($session = null){
        $this->session = $session
            ? $session
            : Container::getInstance()->get('session')
            ;
        $this->box = $this->session->get($this->key, array());
    }

    function add($type, $message){
        parent::add($type, $message);
        $this->session->set($this->key, $this->box);
    }

    function reset(){
        parent::reset();
        $this->session->clear($this->key);
    }
}