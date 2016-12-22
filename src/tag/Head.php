<?php

namespace j\view\tag;

use j\base\OptionsTrait;
use j\base\SingletonTrait;

class Head {
    use OptionsTrait;
    use SingletonTrait;

    /**
     * @var Js
     */
    private $js;

    /**
     * @var Css
     */
    private $css;

    /**
     * @var Seo
     */
    private $seo;

    private $charset;
    private $meta = [];

    /**
     * @var bool
     */
    protected static $sended = false;

    /**
     * Head constructor.
     * @param Css $css
     * @param Js $js
     * @param Seo $seo
     */
    public function __construct($css = null, $js = null, $seo = null) {
        $this->js = $js ?: new Js();
        $this->css = $css ?: new Css();
        $this->seo = $seo ?: new Seo();
    }

    public function addCssFile($file, $index = 0){
        $this->css->addFile($file, $index);
        return $this;
    }

    public function addJsFile($file, $index = 0){
        $this->js->addFile($file, $index);
        return $this;
    }

    public function charset($charset){
        $this->charset = $charset;
        return $this;
    }

    public function addMeta($string){
        $this->meta[] = $string;
        return $this;
    }

    /**
     * @param $string
     * @param null $index
     * @return $this
     */
    public function addHtml($string, $index = null){
        if($index){
            $this->meta[$index] = $string;
        }else{
            $this->meta[] = $string;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(){
        self::$sended = true;

        $html = $this->meta;
        if(isset($this->charset) && $this->charset){
            $html[] = '<meta charset="' . $this->charset . '">';
        }

        $html[] = $this->js;
        $html[] = $this->css;
        $html[] = $this->seo;

        return implode("\r\n", $html) . "";
    }

    public static function isSend(){
        return self::$sended;
    }
}