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
            'greetings' => $this->module->greetings,
            'module' => $this->module,
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
	            $cmd = Yii::getAlias($this->module->yiiScript) . ' '
		        . implode(' ', $options['params'])
		        . ' 2>&1';
                list ($status, $output) = $this->runConsole($cmd);
                return ['result' => $output];
	        case 'composer':
                if (!$this->module->composerEnabled) {
                    return ['result' => 'no composer support'];
                }

                $cmd = $this->module->composerCommand . ' '
		    	    . implode(' ', $options['params'])
			        . ' -d='.Yii::getAlias($this->module->composerWorkingDirectory)
                    . ' 2>&1';
		            list ($status, $output) = $this->runConsole($cmd);
		            return ['result' => $output];
        }
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
        $handler = popen($command, 'r');
        $output = '';
        while (!feof($handler)) {
            $output .= fgets($handler);
        }

        $output = trim($output);
        $status = pclose($handler);

        return [$status, $output];
    }
}
