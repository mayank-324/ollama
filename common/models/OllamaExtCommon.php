<?php defined('MW_PATH') || exit('No direct script access allowed');

/*
 * This model is used for storing and validating the extension settings.
 */
class OllamaExtCommon extends FormModel
{
    public $enabled = 'no';
    public $server_url = 'http://localhost:11434/api/chat';
    public $model_name = 'deepseek-r1:1.5b';    

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('server_url, model_name', 'safe'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels.
     */
    public function attributeLabels()
    {
        $labels = array(
            'enabled'    => Yii::t('app', 'Enabled'),
            'server_url' => Yii::t('app', 'Server URL'),
            'model_name' => Yii::t('app', 'Model Name'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array attribute placeholders
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'server_url' => 'http://localhost:11434/api/chat',
            'model_name' => 'deepseek-r1:1.5b',
        ];
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array attribute help texts
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'       => Yii::t('app', 'Whether the Ollama email refinement is enabled'),
            'server_url' => Yii::t('app', 'The Enpoint where your ollama is hosted. eg : http://34.131.120.224/api/chat'),
            'model_name' => Yii::t('app', 'The name of the model from ollama library, make sure to pull it first in your server.'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * Save the settings.
     */
    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = ['enabled', 'server_url', 'model_name'];
        foreach ($attributes as $name) {
            $extension->setOption($name, $this->$name);
        }
        return $this;
    }

    /**
     * Populate the model with stored options.
     */
    public function populate()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = ['enabled', 'server_url', 'model_name'];
        foreach ($attributes as $name) {
            $this->$name = $extension->getOption($name, $this->$name);
        }
        return $this;
    }

    /**
     * Returns the instance of the extension.
     */
    public function getExtensionInstance()
    {
        return Yii::app()->extensionsManager->getExtensionInstance('ollama');
    }
}
