<?php

namespace j\view;

use j\di\PropertyProviderTrait;
use j\error\Error;
use j\event\TraitManager;

use j\view\tag\Css;
use j\view\tag\Js;
use j\view\tag\Head;
use j\view\tag\Seo;
use j\view\tag\Location;
use j\view\tag\Pager;
use j\view\tag\FlashMessage;
use j\view\tag\FlashMessageSession;

/**
 * Class View
 * @package j\view
 *
 * @property WidgetManager $widget
 * @property Css $css
 * @property Js $js
 * @property Js $jsHead
 * @property Head $head
 * @property Seo $seo
 * @property Pager $pager
 * @property Location $location
 * @property FlashMessage $message
 * @property FlashMessageSession $pageMessage
 *
 *  WidgetManager
 * @method string encode() encode(string $content, $enableHtml = false)
 *
 */
class View extends Base{

    use TraitManager;
    use PropertyProviderTrait;

	/**
	 * @var string
	 */
	public $tpl;
	public $layout = null;
    public $jsPrefix = "/js";
    public $cssPrefix = "/css";

    protected $content;

    const EVENT_BEGIN_BODY = 'beginBody';
    const EVENT_END_BODY = 'endBody';
    const EVENT_RENDER_BEFORE = 'renderBefore';
    const EVENT_RENDER_LAYOUT_BEFORE = 'LayoutRenderBefore';
    const EVENT_RENDER_AFTER = 'renderAfter';

    const PH_HEAD = '<![CDATA[JZF-BLOCK-HEAD]]>';
    const PH_BODY_BEGIN = '<![CDATA[JZF-BLOCK-BODY-BEGIN]]>';
    const PH_BODY_END = '<![CDATA[JZF-BLOCK-BODY-END]]>';

    protected function init() {
        parent::init();

        // dynamic property
        $this->regPropertyProvider([
            'widget' => function() {
                $wm = WidgetManager::getInstance();
                $wm->context = $this;
                return $wm;
            },
            'pager' => 'j\view\tag\Pager',
            'css' => 'j\view\tag\Css',
            'js' => 'j\view\tag\Js',
            'jsHead' => 'j\view\tag\Js',
            'seo' => 'j\view\tag\Seo',
            'location' => 'j\view\tag\Location',
            'head' => function() {
                return new Head($this->css, $this->jsHead, $this->seo);
            },
            'message' => 'j\view\tag\FlashMessage',
            'pageMessage' => 'j\view\tag\FlashMessageSession',
        ], null, true);
    }

    /**
     * @param $k
     * @return WidgetManager|null
     */
    public function __get($k) {
        if($this->hasProperty($k)){
            return $this->getPropertyObject($k);
        }

        return parent::__get($k);
    }

	/**
	 * @param string $tpl
	 */
	public function setTpl($tpl) {
		$this->tpl = $tpl;
	}

    /**
     * @param null $tpl
     * @param array $vars
     * @param object $context
     * @return string
     * @throws \Exception
     */
    public function render($tpl = null, $vars = [], $context = null){
        $this->trigger(self::EVENT_RENDER_BEFORE);

        // 1. render self
        $content = parent::parseTpl($tpl ?: $this->tpl, $vars, $context);

        // 2. extend, render parent
        if (isset($this->extendView) && $this->extendView) {
            $extend_view = $this->extendView;
            $this->extendView = null;
            $content = $this->parseTpl($extend_view, $vars);
        }

        // 3. layout
        if($this->layout){
            $this->trigger(self::EVENT_RENDER_LAYOUT_BEFORE);
            $this->content = $content;
            $content = $this->parseTpl($this->layout, $vars);
        }

        $this->trigger(self::EVENT_RENDER_AFTER, $content);
        return $content;
    }


    const EVENT_BEGIN_PAGE = 'beginPage';
    const EVENT_END_PAGE = 'endPage';


    /**
     * Page position hook
     * ---------------------
     * @return mixed
     */
    public function content(){
        return $this->content;
    }

    /**
     * Marks the beginning of a page.
     */
    public function beginPage() {
        ob_start();
        ob_implicit_flush(false);

        $this->trigger(self::EVENT_BEGIN_PAGE);
    }

    /**
     * Marks the position of an HTML head section.
     */
    public function hookHead() {
        echo self::PH_HEAD;
    }

    /**
     * Marks the beginning of an HTML body section.
     */
    public function beginBody() {
        echo self::PH_BODY_BEGIN;
        $this->trigger(self::EVENT_BEGIN_BODY);
    }

    /**
     * Marks the ending of an HTML body section.
     */
    public function endBody() {
        $this->trigger(self::EVENT_END_BODY);
        echo self::PH_BODY_END;
    }

    /**
     * Marks the ending of an HTML page.
     * @param boolean $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     */
    public function endPage($ajaxMode = false) {
        $this->trigger(self::EVENT_END_PAGE);
        $content = ob_get_clean();
        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
        ]);
    }

    /**
     * render css
     * @return string
     */
    protected function renderHeadHtml(){
        return $this->head;
    }

    protected function renderBodyBeginHtml(){
        // for override
        return '';
    }

    /**
     * render js
     * @param $ajaxMode
     * @return \j\view\tag\Js
     */
    protected function renderBodyEndHtml($ajaxMode) {
        return $this->js;
    }

    /**
     * extend and block support
     * --------------------------
     * @var string 继承视图路径
     */
    protected $extendView;

    protected function extend($view) {
        $this->extendView = $view;
    }

    /**
     * 块位置标识
     */
    const BLOCK_REPLACE = 'replace';
    const BLOCK_PREPEND = 'prepend';
    const BLOCK_APPEND = 'append';

    protected $blockContent = [];
    protected $blockStack = [];

    /**
     * 清除之前的块缓存
     */
    public function reset(){
        $this->blockContent = [];
        $this->blockStack = [];
    }

    /**
     * script
     */
    protected function tagScript() {
        ob_start();
    }

    protected function endTagScript($index = 0, $hasTag = true) {
        $output = ob_get_clean();
        $this->js->addCode($output, $index, $hasTag);
    }

    /**
     * style
     */
    protected function tagStyle() {
        ob_start();
    }

    protected function endTagStyle($index = 0) {
        $output = ob_get_clean();
        $this->css->addCode($output, $index, true);
    }

    /**
     * 开始块
     *
     * @param string $name
     * @param string $method
     * @return void
     */
    protected function block($name, $method = null) {
        $this->blockStack[] = [$name, $method ?: self::BLOCK_REPLACE];
        ob_start();
    }

    /**
     * @param $name
     * @param $content
     */
    public function setBlockContent($name, $content){
        $this->blockContent[$name] = $content;
    }

    /**
     * 结束块
     *
     * @return void
     */
    protected function endBlock() {
        if (!$this->blockStack) {
            return;
        }

        list($block_name, $block_method) = array_pop($this->blockStack);
        $output = ob_get_clean();

        if (isset($this->blockContent[$block_name])) {
            if ($block_method == self::BLOCK_PREPEND) {
                $output = $this->blockContent[$block_name] . $output;
            } elseif ($block_method == self::BLOCK_APPEND) {
                $output = $output . $this->blockContent[$block_name];
            } else {
                $output = $this->blockContent[$block_name];
            }
        }

        if ($this->extendView && !$this->blockStack) {
            $this->blockContent[$block_name] = $output;
        } else {
            unset($this->blockContent[$block_name]);
            echo $output;
        }
    }

    /**
     * @param $name
     * @param array $request
     * @return mixed
     */
    public function widget($name, array $request = []) {
        return $this->widget->call($name, $request);
    }

    /**
     * @param $name
     * @param array $request
     * @return mixed
     * @throws \j\called\CallerNotFoundException
     */
    public function __call($name, array $request = []){
        if(!$this->widget->widgetIsInvoke($name)){
	        // 直接注册的callback
            return $this->widget->call($name, $request);
        } else {
            return Error::error("Invalid call");
        }
    }
}
