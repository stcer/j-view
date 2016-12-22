<?php

namespace j\view;

use j\base\JObject;
use j\security\Cleaner;
use j\called\CalledTrait;
use j\base\SingletonTrait;
use j\view\tag\Pager;
use ReflectionClass;

use j\view\tag\Css;
use j\view\tag\Js;
use j\view\tag\Head;
use j\view\tag\Seo;
use j\view\tag\Location;

/**
 * Class WidgetManager
 * Dynamic method and property
 *
 * @package j\framework
 *
 * @method string clean($content)
 *
 * @property Css $css
 * @property Js $js
 * @property Head $head
 * @property Seo $seo
 * @property Location $location
 * @property Pager $pager
 *
 */
class WidgetManager extends JObject {

    use CalledTrait;
    use SingletonTrait;

    /**
     * @var View
     */
    public $context;

    /**
     * 
     */
    protected function init(){
        $this->regCall('clean', function($content){
            return Cleaner::clear($content);
        });

        $this->regCall('encode', function($content, $decodeHtml = false) {
            if($decodeHtml){
                $content = stripslashes($content);
                $content = html_entity_decode($content);
            }
            return Cleaner::clear($content);
        });
    }

    /**
     * @param $name
     * @return bool
     */
    public function widgetIsInvoke($name){
        if(!isset($this->__calls[$name])){
            return true;
        }

        if($this->__calls[$name] instanceof \Closure){
            return false;
        }

        return is_object($this->__calls[$name]);
    }
}