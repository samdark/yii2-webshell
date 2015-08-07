<?php
namespace samdark\webshell;

use yii\web\AssetBundle;

/**
 * WebshellAsset is an asset bundle used to include custom overrides for terminal into the page.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class WebshellAsset extends AssetBundle
{
    public $sourcePath = '@samdark/webshell/assets';

    public $css = [
        'webshell.css',
    ];

    public $depends = [
        'samdark\webshell\JqueryTerminalAsset',
    ];
}
