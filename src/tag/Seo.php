<?php

namespace j\view\tag;
use j\base\SingletonTrait;
use j\base\ClassNameTrait;

class Seo{
    use SingletonTrait;
    use ClassNameTrait;

    private $vars = array();
    private $values = array(
        'sp' => " - ",
        'sp2' => '_',
        'sp3' => '-',
        'sp4' => '|',
        );
    private $pattern = array(
        'title' => []
        );
    
    private $siteName = '';

    function __construct(){
    }

    /**
     * put your comment there...
     *
     * @param mixed $pattern
     * @return string
     */
    private function genString($pattern){
        $string = $pattern;
        foreach ($this->vars as $k) {
            if(!isset($this->values[$k])){
                continue;
            }
            $string = str_replace("{{$k}}", $this->values[$k], $string);
        }

        $string = preg_replace('/\{.+?\}/', '', $string);
        $string = preg_replace("#({$this->values['sp']}){1,}#", $this->values['sp'], $string);
        $string = trim($string, $this->values['sp']);

        $string = preg_replace("#({$this->values['sp2']}){1,}#", $this->values['sp2'], $string);
        $string = trim($string, $this->values['sp2']);

        $string = preg_replace("#({$this->values['sp3']}){1,}#", $this->values['sp3'], $string);
        $string = trim($string, $this->values['sp3']);
        
        $string = preg_replace("#({\|}){1,}#", $this->values['sp4'], $string);
        $string = trim($string, $this->values['sp4']);
        return $string;
    }

    function setPattern($pattern, $key = null){
        if(is_array($pattern)){
            $this->pattern = $pattern;
        }elseif($key){
            $this->pattern[$key] = $pattern;
        }
    }

    function setVar($k, $value){
        if($value){
            $this->values[$k] = $value;
        }
        if(!in_array($k, $this->vars)){
            $this->vars[] = $k;
        }
    }

    /**
     * @param $title
     */
    public function setTitle($title, $append = false){
        if($append){
            $this->pattern['title'][] = $title;
        }else{
            $this->pattern['title'] = array($title);
        }
    }

    public function setSiteName($siteName){
        $this->siteName = $siteName;
    }

    public function setKeywords($keywords){
        $this->pattern['keywords'] = $keywords;
    }

    public function setDescription($desc){
        $this->pattern['describe'] = $desc;
    }

    /**
     * put your comment there...
     *
     */
    function getTitle()   {
        if(!$this->pattern['title']){
            return '';
        }

        $title = $this->genString(implode($this->values['sp'], $this->pattern['title']));
        if(!$title){
            return $this->siteName;
        }

        if($this->siteName){
            return $title . $this->values['sp'] . $this->siteName;
        }

        return $title;
    }

    function getDescribe(){
        if(!isset($this->pattern['describe'])){
            return '';
        }
        return $this->genString($this->pattern['describe']);
    }

    function getKeywords(){
        if(!isset($this->pattern['keywords'])){
            return '';
        }
        return $this->genString($this->pattern['keywords']);
    }

    function __toString(){
        $html = '';
        if($this->disrobots){
            $html .= '<meta name="robots" content="noindex,nofollow">';
            $html .= "\r\n";
        }

        if($string = $this->getTitle()){
            $html .= "<title>" .  $string . "</title>";
            $html .= "\r\n";
        }

        if($string = $this->getKeywords()){
            $html .= '<meta name="keywords" content="' .  $string . '" />';
            $html .= "\r\n";
        }

        if($string = $this->getDescribe()){
            $html .= '<meta name="description" content="' .  $string . '" />';
            $html .= "\r\n";
        }

        return $html;
    }

    protected $disrobots = false;
    function disrobots($flag = null) {
        if(!is_null($flag)){
            $this->disrobots = true;
        }

        return $this->disrobots;
    }
}