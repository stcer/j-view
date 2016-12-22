<?php

namespace j\view\tag;

/**
 * Class Css
 * @package j\view\tag
 */
class Css extends Js  {
    protected static $instance;
    public static $labelFile = '<link rel="stylesheet" type="text/css" href="%s" />';
    public static $label = '<style>%s</style>';
}