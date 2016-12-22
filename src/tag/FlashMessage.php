<?php

namespace j\view\tag;

/**
 * Class FlashMessage
 * @package j\view\tag
 */
class FlashMessage{
    /**
     * @var array
     */
    protected $box = array();
    protected static $instance;

    /**
     * @param string $type
     * @return FlashMessage
     */
    static function factory($type = 'current'){
        if(isset(self::$instance[$type])){
            return self::$instance[$type];
        }

        if($type == 'page'){
            self::$instance[$type] = new FlashMessageSession();
        }else{
            self::$instance[$type] = new self();
        }

        return  self::$instance[$type];
    }

    function add($type, $message){
        $this->box[$type][] = $message;
    }

    function msg($msg){
        $this->add('info', $msg);
    }

    function success($msg){
        $this->add('success', $msg);
    }

    function error($msg){
        $this->add('danger', $msg);
    }

    function alert($msg){
        $this->add('info', $msg);
    }

    public function getLastMessage(){
        if($this->box){
            return current($this->box)[0];
        }
        return '';
    }

    public function getMessage($all = false){
        if($all){
            return $this->box;
        }
        $box = [];
        if($this->box){
            foreach($this->box as $k => $v){
                $k = str_replace('alert-', '', $k);
                $box[$k] = implode("\n", $v) . "";
            }
        }
        return $box;
    }

    public function getMessageJson($all = false){
        $rs = json_encode($this->getMessage($all));
        $this->reset();
        return $rs;
    }

    protected function render($type, $box){
        $msg = implode('<br />', $box);
        return <<<STR
<div class="alert alert-{$type}" role="alert">
    <button type="button" class="close" data-dismiss="alert">×</button>
    {$msg}
</div>

STR;
    }

    function __toString(){
        $html = [];
        foreach($this->box as $type => $box){
            $html[]= $this->render($type, $box);
        }

        $this->reset();
        return implode("\n", $html) . "";
    }

    function reset(){
        $this->box = array();
    }
}
