<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Controller file for Ollama settings.
 */

class Ext_ollama_settingsController extends Controller
{
    public $extension;

    // Override the view path.
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-ollama.backend.views.settings');
    }

    /**
     * Displays and processes the settings form.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = new OllamaExtCommon();
        $model->populate();

        if ($request->isPostRequest) {
            $model->attributes = (array)$request->getPost($model->modelName, array());
            if ($model->validate()) {
                $model->save();
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'    => $this->data->pageMetaTitle . ' | ' . $this->extension->t('Ollama Email Refiner'),
            'pageHeading'      => $this->extension->t('Ollama Email Refiner Settings'),
            'pageBreadcrumbs'  => array(
                Yii::t('app', 'Extensions') => $this->createUrl('extensions/index'),
                $this->extension->t('Ollama Email Refiner') => $this->createUrl('ext_ollama_settings/index'),
            ),
        ));

        $this->render('index', compact('model'));
    }
}
