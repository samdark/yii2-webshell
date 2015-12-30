<?php
namespace samdark\webshell;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * This is the main module class for the web shell module.
 *
 * To use web shell, include it as a module in the application configuration like the following:
 *
 * ~~~
 * return [
 *     'modules' => [
 *         'webshell' => ['class' => 'samdark\webshell\Module'],
 *     ],
 * ]
 * ~~~
 *
 * With the above configuration, you will be able to access web shell in your browser using
 * the URL `http://localhost/path/to/index.php?r=webshell`
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'samdark\webshell\controllers';

    /**
     * @var string console greetings
     */
    public $greetings = 'Yii 2.0 web shell';

    /**
     * @var array URL to use for `quit` command. If not set, `quit` command will do nothing.
     */
    public $quitUrl;

    /**
     * @var string path to `yii` script
     */
    public $yiiScript = '@app/yii';

    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];

    /**
     * @var callable a PHP callable whose return value determines when allowed to access this module.
     */
    public $allowedCallback;
	
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        set_time_limit(0);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app instanceof \yii\web\Application && !$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        return true;
    }

    /**
     * @return boolean whether the module can be accessed by the current user
     */
    protected function checkAccess()
    {
        if($this->allowedCallback !== null && call_user_func($this->allowedCallback)) {
            return true;
        }
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        Yii::warning('Access to web shell is denied due to IP address restriction. The requested IP is ' . $ip, __METHOD__);

        return false;
    }
}
