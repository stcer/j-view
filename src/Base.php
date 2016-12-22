<?php

namespace j\view;

use Exception;
use j\base\JObject;

/**
 * Class Base
 * @package j\view
 *
 * @property string|array $dir
 */
class Base extends JObject {
    /**
     * @var Theme
     */
    public $theme;

	/**
	 * @var LoaderInterface
	 */
	protected $loader;

    /**
     * @var array
     */
    protected $vars = [];

	/**
	 * @param $k
	 * @param null $v
	 */
    public function assign($k, $v = null){
        if(is_array($k)){
            $this->vars = array_merge($this->vars, $k);
        }else{
            $this->vars[$k] = $v;
        }
    }

	/**
	 * @param $key
	 * @return bool
	 */
	public function hasVar($key){
		return isset($this->vars[$key]);
	}

    public function __get($k){
        if(isset($this->vars[$k])){
            return $this->vars[$k];
        }

        return null;
    }

    public function __set($key, $value){
	    switch($key){
		    case 'dir' :
		    	$this->getLoader()->setPaths($value);
			    break;
		    default :
		    	$this->{$key} = $value;
	    }
    }

	public function getLoader() {
		if(!isset($this->loader)){
			$this->loader = new loader\Filesystem();
		}
		return $this->loader;
	}

    /**
     * @param string $tpl
     * @param array $vars
     * @param object $context
     * @return string
     * @throws Exception
     */
    public function parseTpl($tpl, $vars, $context = null){
        $tplFile = $this->getFile($tpl, $context);

        if ($this->theme !== null) {
            // 应用主题
            $tplFile = $this->theme->applyTo($tplFile);
        }

		return $this->renderPhp($tplFile, $vars);
    }


	/**
	 * @param $tpl
	 * @param object $context
	 * @return string
	 */
	protected function getFile($tpl, $context = null){
		if(!$tpl || strncmp($tpl, '/', 1) === 0){
			return $tpl;
		}

		if($context instanceof ViewContextInterface){
			$tpl = $context->getViewPath() . DIRECTORY_SEPARATOR . $tpl;
		}

		return $this->getLoader()->getSource($tpl);
	}

	/**
	 * @param $file
	 * @param $vars
	 * @return string
	 * @throws Exception
	 */
    protected function renderPhp($file, $vars){
	    if(!$file || !is_file($file)){
		    throw(new Exception("Template not found($file)"));
	    }

	    $level = ob_get_level();
	    ob_start();
	    try {
		    extract($this->vars, EXTR_REFS);
		    $ARGS = $vars;
		    if(is_array($vars)){
			    extract($vars, EXTR_REFS);
		    }

		    include($file);
	    } catch (\Exception $exception) {
		    while (ob_get_level() > $level) {
			    ob_end_clean();
		    }
		    throw $exception;
	    }

	    $content = ob_get_clean();
	    return $content;
    }

    public function exist($tpl = null, $context = null){
        if(!$tpl && !($tpl = $this->tpl)) {
            return false;
        }

	    if($context instanceof ViewContextInterface){
		    $tpl = $context->getViewPath() . DIRECTORY_SEPARATOR . $tpl;
	    }

        return $this->getLoader()->exists($tpl);
    }

    /**
     * @param mixed $dir
     */
    public function setDir($dir) {
	    $this->getLoader()->setPaths($dir);
    }
}
