<?php

namespace j\view;

use j\base\CallableInterface;
use j\base\ContextInterface;
use j\base\JObject;

/**
 * Class Widget
 * @package j\view
 */
abstract class Widget extends JObject implements CallableInterface, ContextInterface {
    /**
     * @var View
     * 可以通过 bindHandle注入
     */
    public $view;
    public $templatePrefix = 'widget/';

    /**
     * @param $name
     * @param array $args
     * @return string
     * @throws \Exception
     */
    function render($name, $args = []){
        $tplFile = $this->getTemplate($name, $args);
        return $this->view->parseTpl($tplFile, $args, $this);
    }

    /**
     * 实现接口 j\view\ViewContextInterface::getViewPath，
     * 以重写目录地址
     *
     * @param $name
     * @param array $args
     * @return string
     */
    protected function getTemplate($name, $args = []){
        $tpl = $name . (isset($args['tpl']) ? '_' . $args['tpl'] : '') . '.php';
        return $this->templatePrefix . $tpl;
    }

    /**
     * @param $context
     */
    function setContext($context) {
        if($context instanceof WidgetManager){
            $this->view = $context->context;
        } else {
            $this->view = $context;
        }
    }

    /**
     * @return mixed
     */
    function __invoke() {
        if(method_exists($this, 'run')){
            return call_user_func_array([$this, 'run'], func_get_args());
        }
        return null;
    }
}