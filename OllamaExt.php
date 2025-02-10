<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Ollama Extension
 *
 * @package MailWizz EMA
 * @subpackage Ollama
 * @author Mayank
 * @link http://www.perceptsystems.com/
 * @copyright 2025
 * @license http://www.mailwizz.com/license/
 */

class OllamaExt extends ExtensionInit
{
    // name of the extension as shown in the backend panel
    public $name = 'Ollama Email Refiner';

    // description of the extension as shown in backend panel
    public $description = 'Intercepts outgoing emails and refines the subject and body via Ollama.';

    // current version of this extension
    public $version = '1.0';

    // minimum app version
    public $minAppVersion = '1.3.6.2';

    // the author name
    public $author = 'Percept Dev Team';

    // author website
    public $website = 'http://www.perceptsystems.com/';

    // contact email address
    public $email = 'ai@perceptsystems.com';

    // allowed apps
    public $allowedApps = array('*');

    // not allowed apps
    public $notAllowedApps = array();

    // cli enabled
    public $cliEnabled = true;

    // can this extension be deleted?
    protected $_canBeDeleted = true;

    // can this extension be disabled?
    protected $_canBeDisabled = true;
    
    public function run()
    {
        Yii::import('ext-ollama.common.models.*');

        // Register backend settings controller and URL rules if we are in the backend app.
        if ($this->isAppName('backend')) {
            Yii::app()->urlManager->addRules(array(
                array('ext_ollama_settings/index', 'pattern' => 'extensions/ollama/settings'),
                array('ext_ollama_settings/<action>', 'pattern' => 'extensions/ollama/settings/*'),
            ));

            Yii::app()->controllerMap['ext_ollama_settings'] = array(
                'class'     => 'ext-ollama.backend.controllers.Ext_ollama_settingsController',
                'extension' => $this,
            );
        }

        // Only hook into email sending if the extension is enabled.
        if ($this->getOption('enabled', 'no') != 'yes') {
            return;
        }

        Yii::app()->hooks->addAction('controller_action_save_data', function($collection) {
            if ($collection->controller->id !== 'campaigns') {
                return;
            }
            
            $campaign = $collection->itemAt('campaign');
            if (!$campaign || !$campaign->campaign_id) {
                return;
            }
            
            // Log entire POST data for debugging:
            $logFile = '/var/www/html/mailwizz-new/mailwizz-extension-debug.log';
            file_put_contents($logFile, print_r($_POST, true), FILE_APPEND);
            
            // Retrieve custom fields:
            $useLLM    = Yii::app()->request->getPost('use_llm', 'no');
            $llmPrompt = Yii::app()->request->getPost('llm_prompt', '');
            
            $logData = "----- Debug Log -----\n";
            $logData .= "yesno: " . $useLLM . "\n";
            $logData .= "prompt: " . $llmPrompt . "\n";
            $logData .= "----------------------\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
            
            // Save to CampaignOption, for example:
            // $option = CampaignOption::model()->findByAttributes(['campaign_id' => (int)$campaign->campaign_id]);
            // if (!$option) {
            //     $option = new CampaignOption();
            //     $option->campaign_id = (int)$campaign->campaign_id;
            // }
            // $option->use_llm    = $useLLM;
            // $option->llm_prompt = $llmPrompt;
            // if (!$option->save()) {
            //     Yii::log(print_r($option->getErrors(), true), CLogger::LEVEL_ERROR);
            // }
        });
        

        Yii::app()->hooks->addAction('before_active_form_fields', function($collection) {
            // Retrieve the controller and form from the hook collection.
            $controller = $collection->itemAt('controller');
            $form = $collection->itemAt('form');
            
            // Output your custom HTML. For example, two additional input fields:
            ?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo CHtml::label('Use LLM for this campaign', 'use_llm'); ?>
                        <?php echo CHtml::dropDownList('use_llm', 'no', ['yes' => 'Yes', 'no' => 'No'], ['class' => 'form-control']); ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo CHtml::label('LLM Prompt', 'llm_prompt'); ?>
                        <?php echo CHtml::textField('llm_prompt', '', ['class' => 'form-control', 'placeholder' => 'Enter your prompt']); ?>
                    </div>
                </div>
            </div>
            <?php
        });

        // Hook into the email delivery process.
        Yii::app()->hooks->addFilter('delivery_server_before_send_email', function($params, $server) {
            if (isset($params['body'], $params['subject'])) {
                $logFile = '/var/www/html/mailwizz-new/mailwizz-extension-debug.log';
                $extension = Yii::app()->extensionsManager->getExtensionInstance('ollama');

                $subject = $params['subject'];
                $body = $params['body'];

                $subscriberFullName = 'nothing';
                if (!empty($params['subscriberUid'])) {
                    // Make sure the ListSubscriber class is imported. If not, import it:
                    Yii::import('common.models.ListSubscriber');
                    $subscriber = ListSubscriber::model()->findByAttributes([
                        'subscriber_uid' => $params['subscriberUid']
                    ]);
                    if ($subscriber) {
                        // Use the getFullName() method from ListSubscriber.
                        $subscriberFullName = $subscriber->getFullName();
                    }
                }
                
                file_put_contents($logFile, $subscriberFullName);
                
                $promptContent = <<<EOT
                    You are an expert email editor.

                    Your task is to refine the following email to make it more engaging, persuasive, and professional while preserving the original HTML structure, inline styles, and formatting.

                    ### **Rules:**
                    - Modify only the **text content** of the email; do **not** change any HTML, CSS, or inline styles.
                    - Do **not** add or remove any `<html>`, `<head>`, `<body>`, `<style>`, `<div>`, `<p>`, `<table>`, or other structural tags.
                    - Ensure that all hyperlinks (`<a>` tags), image sources (`<img>` tags), and styling remain exactly the same.
                    - Only improve the **visible text** within these tags while keeping the email's structure intact.
                    - The **subject** should be refined but remain similar in meaning.

                    ### **Input:**
                    - **Subject:** 
                        "$subject"
                    - **HTML Body:** 
                        "$body"
                    EOT;

                $systemPrompt = <<<EOT
                    You are an expert email editor. Your task is to refine an email’s subject and body. 
                    The subject is provided as plain text, and the body is provided as HTML/CSS code. 
                    Improve the language to be more engaging, persuasive, and professional while keeping the original meaning intact.
                    For the HTML/CSS email body, modify only the visible text content—do not alter any HTML tags, attributes, inline CSS, or the overall layout.

                    Return your result in a valid JSON object with two keys: "refined_subject" for the improved subject and "refined_body" for the improved email body (HTML/CSS preserved exactly as provided, except for the refined text).

                    EOT;
                
                $payload = json_encode([
                    "model" => "llama3.2:3b",
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt],
                        ["role" => "user", "content" => $promptContent]
                    ],
                    "stream" => false,
                    "options" => [
                        "temperature" => 0.6,
                        "num_ctx" => 40000,
                    ],
                    "format" => [
                        "type" => "object",
                        "properties" => [
                            "refined_subject" => ["type" => "string"],
                            "refined_body" => ["type" => "string"]
                        ],
                        "required" => ["refined_subject", "refined_body"]
                    ]
                ]);
        
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://localhost:11434/api/chat");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        
                $response = curl_exec($ch);
        
                if ($response === false) {
                    $error_message = curl_error($ch);
                    $params['subject'] = "Error: $error_message";
                    $params['body'] = "Error: $error_message";
                } else {
                    $response_data = json_decode($response, true);
                    if (isset($response_data['message']['content'])) {
                        $parsed_content = json_decode($response_data['message']['content'], true);
                        if (isset($parsed_content['refined_subject'], $parsed_content['refined_body'])) {
                            $params['subject'] = $parsed_content['refined_subject'];

                            $originalBody = $params['body'];

                            $params['body'] = $parsed_content['refined_body'];

                        } else {
                            $params['subject'] = "Error refining subject.";
                            $params['body'] = "Error refining body.";
                        }
                    } else {
                        $params['subject'] = "Error: No content in response.";
                        $params['body'] = "Error: No content in response.";
                    }
                }
        
                curl_close($ch);
            }
            return $params;
        }, 10, 2);
    }

    // Returns the URL to the extension settings page.
    public function getPageUrl()
    {
        return Yii::app()->createUrl('ext_ollama_settings/index');
    }
}
