<?php

namespace j\view\tag;

use j\base\OptionsTrait;
use j\base\SingletonTrait;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class Js
 * @package j\view\tag
 */
class Js  {
    use OptionsTrait;
    use SingletonTrait;

    protected $files = [];
    protected $fileIndex = [];
    protected $codes = [];
    protected $codeIndex = [];
    protected $codeTagOptions = [];

    /**
     * @var Packages
     */
    public $assetPackages = null;
    public $assetTypeName = 'js';

    /**
     * @var string
     */
    public static $labelFile = '<script type="text/javascript" src="%s"></script>';
    public static $label = '<script type="text/javascript">%s</script>';

    protected $maxFiles = 100;
    protected $priority = 100;

    /**
     * @param $file
     * @param int $priority
     * @return $this
     */
    function addFile($file, $priority = 0){
        if(!$file){
            return $this;
        }

        $key = count($this->files);
        foreach((array)$file as $f){
            $this->files[] = $f;
            $this->fileIndex[$key++] = ($this->priority--) +  $priority * $this->maxFiles;
        }

        return $this;
    }

    /**
     * @param $code
     * @param int $priority
     * @param boolean $hasTag
     * @return $this
     */
    function addCode($code, $priority = 0, $hasTag = false){
        $key = count($this->codes);
        $this->codes[] = $code;
        $this->codeIndex[$key] = $priority;
        $this->codeTagOptions[$key] = $hasTag;
        return $this;
    }

    /**
     * @param $data
     * @param $index
     * @return array
     */
    protected function sort($data, $index){
        arsort($index);

        $tmp = [];
        foreach($index as $key => $i){
            $tmp[$key] = $data[$key];
        }

        return $tmp;
    }

    /**
     * @return string
     */
    function getImportHtml(){
        if(!$this->files){
            return '';
        }

        $html = [];
        $files = $this->sort($this->files, $this->fileIndex);
        $files = array_unique($files);
        foreach ($files as $file) {
            if(isset($this->assetPackages)){
                $file = $this->assetPackages->getUrl($file, $this->assetTypeName);
            }
            $html[] = sprintf(static::$labelFile, $file);
        }

        return implode("\r\n", $html) . "\n";
    }

    /**
     * @return string
     */
    function getSnippetHtml(){
        if(!$this->codes){
            return '';
        }

        $codes = $this->sort($this->codes, $this->codeIndex);
        $html = '';

        foreach($codes as  $key => $c){
            if($this->codeTagOptions[$key]){
                $html .= $c. "\n";
            } else {
                $html .= sprintf(static::$label, $c) . "\n";
            }
        }
        return $html;
    }

    function __toString(){
        return $this->getImportHtml() . $this->getSnippetHtml();
    }
}