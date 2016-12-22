<?php

namespace j\view\tag;

use j\base\SingletonTrait;

/**
 * Class Pager
 * @package j\view\tag
 */
class Pager  {

    use SingletonTrait;

    public $nums;
    public $start;
    public $currentPage;

    public $argName = "page";
    public $ignoreFirstPage = true;

    /**
     * put your comment there...
     *
     * @var mixed
     */
    protected $total = 0;
    protected $pageTotal = 0;
    protected $displayPages = 10;

    protected $urlCallback;

    /**
     * Pager constructor.
     * @param int $nums
     * @param int $total
     * @param int $page
     */
    public function __construct($nums = 20, $total = 0, $page = 0){
        if($page <= 0){
            $page = isset($_REQUEST[$this->argName]) ? intval($_REQUEST[$this->argName]) : 1;
        }

        $this->currentPage = $page;

        if($this->currentPage < 1){
            $this->currentPage = 1;
        }

        if (intval($nums) > 0) {
            $this->setNums($nums);
        }else{
            $this->setNums(20);
        }

        if (intval($total) > 0) {
            $this->setTotal($total) ;
        }
    }

    /**
     *
     */
    public function setNums($nums){
        $this->nums = intval($nums);
        if($this->nums <= 0){
            $this->nums = 20;
        }

        $this->start = ($this->currentPage - 1) * $this->nums;
        $this->start < 0 && $this->start = 0;
        return $this;
    }

    /**
     * @param $total
     * @return $this
     */
    public function setTotal($total){
        $this->total = intval($total);
        $this->pageTotal = ceil($this->total/$this->nums);
        if($this->currentPage > $this->pageTotal){
            $this->currentPage = $this->pageTotal;
            $this->start = ($this->currentPage - 1) * $this->nums;
            $this->start < 0 && $this->start = 0;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal() {
        return $this->total;
    }

    function getTotalPage(){
        return $this->pageTotal;
    }

    /**
     * @param int $displayPages
     */
    public function setDisplayPages($displayPages) {
        $this->displayPages = $displayPages;
    }

    /**
     * @param string $total
     * @return string
     */
    function fetch($total = ''){
        if($total){
            $this->setTotal($total);
        }

        if($this->total <= 1){
            return '';
        }

        if(!isset($this->urlCallback)){
            $this->urlCallback = array(new PagerUrl($this->argName, $this->ignoreFirstPage), 'url');
        }

        $pageBuffer = "";
        // get pageInfo forward
        if($this->currentPage > 1 ){
            $pageBuffer .= $this->mkLink(1, 'home');
            $pageBuffer .= $this->mkLink($this->currentPage - 1, "pre");
        }

        // get pageInfo center
        $nextNum = ceil($this->currentPage / $this->displayPages) * $this->displayPages;
        if($nextNum == $this->currentPage){
            $nextNum = $this->currentPage + $this->displayPages;
        }

        if($nextNum > $this->pageTotal){
            $nextNum = $this->pageTotal;
        }

        $nextStart = $nextNum - $this->displayPages;
        if($nextStart <= 0 ){
            $nextStart = 1;
        }

        for($i = $nextStart; $i <= $nextNum; $i++){
            if($i == $this->currentPage){
                $pageBuffer .= $this->mkLink($i, 'current');
            }else{
                $pageBuffer .= $this->mkLink($i, 'default');
            }
        }

        // get pageInfo end
        if($this->currentPage < $this->pageTotal){
            $pageBuffer .=  $this->mkLink($this->currentPage + 1, 'next');
        }

        // get count pageInfo
        if ($this->currentPage >= $this->pageTotal){
            $y = $this->total % $this->nums;
            $endId = $this->start + $y == 0 ? $this->nums : $this->start + $y;
        }else{
            $endId = $this->start + $this->nums;
        }

        $pages = sprintf($this->getHtml('pages'), $pageBuffer);
        $desc = sprintf($this->getHtml('desc'), $this->total, $this->start, $endId);

        return $pages. $desc ;
    }

    protected function mkLink($page, $title, $attr = ''){
        if(!$title){
            if($page > 3){
                $title = "nofollow";
            }else{
                $title = "default";
            }
        }
        $html = $this->getHtml($title);
        $url = call_user_func($this->urlCallback, $page);
        $html = str_replace(['{url}', '{page}'], [$url, $page], $html);
        return $html;
    }

    function setUrlCallback($callback){
        $this->urlCallback = $callback;
    }

    function __toString() {
        return $this->fetch();
    }

    protected $htmlMaker;
    public function getHtml($key){
        return $this->getBuilder()->getHtml($key);
    }

    public function getBuilder(){
        if(!isset($this->htmlMaker)){
            $this->htmlMaker = new PagerHtml();
        }
        return $this->htmlMaker;
    }
}

class PagerHtml{

    /**
     * @var array
     */
    protected $html = [
        'home' => '<a href="{url}" class="pageIndex">首页</a>',
        'pre' => '<a href="{url}" class="pagePrev">上一页</a>',
        'next' => '<a href="{url}" class="pageNext">下一页</a>',
        'last' => '<a href="{url}" class="pageLast">尾页</a>',
        'current' => '<span class="red"><strong>{page}</strong></span>',
        'default' => ' <a href="{url}">{page}</a> ',

        'nofollow' => '<a href="{url}"  rel="nofollow">{page}</a>',
        'desc' => '<p class="desc">共%d条, 当前显示%d -- %d</p>',
        'pages' => '<div class="pages">%s</div>',
    ];

    /**
     * @param $key
     * @param null $value
     */
    public function setHtml($key, $value = null) {
        if(is_array($key)){
            $this->html = array_merge($this->html, $key);
        }else{
            $this->html[$key] = $value;
        }
    }

    public function getHtml($key){
        return $this->html[$key];
    }
}

class PagerUrl{
    protected $_url;
    protected $_urlParsed = false;
    protected $_urlInfo;

    protected $ignoreFirstPage;
    protected $argName;

    function __construct($argName, $ignoreFirstPage = true) {
        $this->argName = $argName;
        $this->ignoreFirstPage = $ignoreFirstPage;
    }

    public function setUrl($url) {
        $this->_url = preg_replace('/(\?|&)(jt=n|noStatic=yes|cnf=y|apiDebug=1|sqltest=hj)/', '', $url);
        $this->_urlParsed = false;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setParam($key, $value){
        $this->_url = $this->_setParam($key, $value);
        $this->_urlParsed = false;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected function _setParam($key, $value){
        if(!isset($this->_url)){
            $this->setUrl($_SERVER['REQUEST_URI']);
        }

        if(!$this->_urlParsed){
            $this->_urlInfo = parse_url($this->_url);
            $this->_urlParsed = true;
        }
        $urlInfo = $this->_urlInfo;

        $query = '';
        $isFirst = $this->ignoreFirstPage && ($key == $this->argName && $value == 1);
        if(isset($urlInfo['query'])){
            parse_str($urlInfo['query'], $queryInfo);
            if($isFirst){
                unset($queryInfo[$key]);
            }else{
                $queryInfo[$key] = $value;
            }
            $query = http_build_query($queryInfo);
        }elseif(!$isFirst){
            $query = "{$key}={$value}";
        }
        return $urlInfo['path']  . ($query ? '?' . $query  : $query);
    }


    function url($page){
        return $this->_setParam($this->argName, $page);
    }
}