<?php
namespace samdark\webshell\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * DefaultController
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 *
 * @property \samdark\webshell\Module $module
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::$app->request->enableCsrfValidation = false;
        parent::init();
    }

    /**
     * Displays initial HTML markup
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'shell';
        return $this->render('index', [
            'quitUrl' => $this->module->quitUrl ? Url::toRoute($this->module->quitUrl) : null,
            'greetings' => $this->module->greetings
        ]);
    }

    /**
     * RPC handler
     * @return array
     */
    public function actionRpc()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $options = Json::decode(Yii::$app->request->getRawBody());

        switch ($options['method']) {
            case 'yii':
                $exitCode = 1;
                $output = null;
                $params = explode(' ', $options['params']);
    
                if (!empty($params) and in_array($params[0], ['-wp', '--runInWebProcess'])) {
                    array_shift($params); // remove runInWebProcess option parameter

                    // mimicking yii command behaviour
                    // just typing 'yii' in console defaults to 'yii help'
                    if (empty($params)) 
                        $params[] = 'help';

                    list($exitCode, $output) = $this->runYiiConsoleCommandsInWebProcess($params);
                }
                else
                    list ($exitCode, $output) = $this->runConsole(implode(' ', $params));

                return [
                    'exitCode' => $exitCode,
                    'output' => $output
                ];
        }
    }

    // popen might be disabled for security reasons in the php.ini.
    // this is common on shared hosting.
    // read http://www.cyberciti.biz/faq/linux-unix-apache-lighttpd-phpini-disable-functions/
    // refer to http://php.net/manual/en/ini.core.php#ini.disable-functions
    private function runYiiConsoleCommandsInWebProcess(array $requestParams)
    {
        // remember current web app
        // inspired by https://github.com/tebazil/yii2-console-runner/blob/master/src/ConsoleCommandRunner.php
        $webApp = Yii::$app;

        try {
            //use console request to resolve command and arguments
            $request = new \yii\console\Request;
            $request->setParams($requestParams);
            list ($route, $params) = $request->resolve();

            /*  redefine STDOUT and STDERR streams
                to write to the memory stream which is readable.
                this might cause a PHP Notice because of global constant
                redefinition if they're previously defined or this method
                is called mor than once per HTTP request. */
            define('STDOUT', fopen('php://memory', 'w+'));
            define('STDERR', STDOUT);
            ob_start(); // aditionally buffer output, echo() and printf() write to this
            // set app context
            Yii::$app = new \yii\console\Application(require(Yii::getAlias($this->module->consoleConfig)));
            $exitCode = Yii::$app->runAction($route, $params);
            rewind(STDOUT);
            $output = stream_get_contents(STDOUT); // whatever has been written to STDOUT and STDERR
            $output .= ob_get_clean(); // and what has been echoed and printed
            fclose(STDOUT);
        }
        catch (\Exception $ex) {
            $output = $webApp->errorHandler->convertExceptionToString($ex);
            $exitCode = 1;
        }

        Yii::$app = $webApp; // revert to web app context
        return [$exitCode, $output];
    }

    /**
     * Runs console command
     *
     * @param string $command
     *
     * @return array [status, output]
     */
    private function runConsole($command)
    {
        $cmd = Yii::getAlias($this->module->yiiScript) . ' ' . $command . ' 2>&1';

        $handler = popen($cmd, 'r');
        $output = '';
        while (!feof($handler)) {
            $output .= fgets($handler);
        }

        $output = trim($output);
        $status = pclose($handler);

        return [$status, $output];
    }
}