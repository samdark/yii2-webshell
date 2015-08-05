<?php


namespace samdark\webshell;


use yii\web\AssetBundle;

class WebshellAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery.terminal';

    public $js = [
        'js/jquery.terminal-min.js',
    ];

    public $css = [
        'css/jquery.terminal.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
