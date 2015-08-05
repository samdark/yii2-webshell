<?php


namespace samdark\webshell\controllers;


use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $this->layout = 'shell';
        return $this->render('index');
    }

    /**
     * ./yii proxy
     */
    public function actionYii()
    {

    }
}