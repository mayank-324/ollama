<?php defined('MW_PATH') || exit('No direct script access allowed');

/*
 * This model is used for storing and validating the extension settings.
 */
class OllamaExtCommon extends FormModel
{
    public $enabled = 'no';
    public $ollama_prompt = '';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('ollama_prompt', 'safe'),
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
            'enabled'       => Yii::t('app', 'Enabled'),
            'ollama_prompt' => Yii::t('app', 'Ollama Prompt'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array attribute placeholders
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'ollama_prompt' => Yii::t('app', 'Enter your prompt for Ollama here...'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array attribute help texts
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'       => Yii::t('app', 'Whether the Ollama email refinement is enabled'),
            'ollama_prompt' => Yii::t('app', 'The prompt that will be passed to Ollama to refine the email subject and body'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * Save the settings.
     */
    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('enabled', 'ollama_prompt');
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
        $attributes = array('enabled', 'ollama_prompt');
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
