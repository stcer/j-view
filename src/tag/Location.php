<?php

namespace j\view\tag;

use j\base\SingletonTrait;
use j\base\OptionsTrait;

/**
 * Class Location
 * @package j\view\tag
 */
class Location {

    use SingletonTrait;
    use OptionsTrait;

    protected $items = array(
    );

    function add($name, $url = null) {
        if(!$name){
            return $this;
        }
        $this->items[] = array(
            'name' => $name,
            'url' => $url,
        );
        return $this;
    }

    function __toString(){
        if(!$this->items){
            return null;
        }

        $class = $this->getOption('class');
        if($class){
            $class = ' class="' . $class . '"';
        }

        $html = '';
        $sp = $this->getOption('sp', ' > ');

        foreach ($this->items as $item) {
            if($item['url']) {
                $html .=  <<<ST
 <a href="{$item['url']}"{$class}>{$item['name']}</a> {$sp}
ST;
            } else {
                $html .=  "&nbsp" . $item['name'];
            }
        }

        if($html && $this->getOption('wrap')){
            $html = '<nav>'. $html . '</nav>';
        }
        return $html;
    }
}