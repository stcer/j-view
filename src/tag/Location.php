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

        $ol = '<ol class="breadcrumb">%s</ol>';
        $li = '<li>%s</li>';
        $a  = '<a href="%s">%s</a>';

        $html = '';
        foreach ($this->items as $item) {
            if($item['url']) {
                $link = sprintf($a, $item['url'], $item['name']);
            } else {
                $link = $item['name'];
            }
            $html .= sprintf($li, $link);
        }

        return sprintf($ol, $html);
    }
}
