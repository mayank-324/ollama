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

        // Hook into the email delivery process.
        Yii::app()->hooks->addFilter('delivery_server_before_send_email', function($params, $server) {
            if (isset($params['body'], $params['subject'])) {
                $logFile = '/var/www/html/mailwizz-new/mailwizz-extension-debug.log';
                 
                // Prepare the log data
                $logData = "----- Debug Log -----\n";
                $logData .= "Subject: " . $params['subject'] . "\n";
                $logData .= "Body:\n" . $params['body'] . "\n";
                $logData .= "----------------------\n";
                
                // Write to the log file, replacing existing content
                file_put_contents($logFile, $logData);
                // Get the Ollama prompt from the extension options.
                $extension = Yii::app()->extensionsManager->getExtensionInstance('ollama');
                $ollamaPrompt = $extension->getOption('ollama_prompt', '');

                $subject = $params['subject'];
                $body = $params['body'];

                if (empty($ollamaPrompt)) {
                    $params['subject'] .= ' [no prompt provided]';
                    $params['body']    .= "\n\n-- No Ollama prompt was provided.";
                    return $params;
                }
                
                $promptContent = <<<EOT
                    You are an expert email editor.

                    Your task is to refine the following email to make it more engaging, persuasive, and professional while preserving the original HTML structure, inline styles, and formatting.

                    Follow these custom instructions: "$ollamaPrompt".

                    ### **Rules:**
                    - Modify only the **text content** of the email; do **not** change any HTML, CSS, or inline styles.
                    - Do **not** add or remove any `<html>`, `<head>`, `<body>`, `<style>`, `<div>`, `<p>`, `<table>`, or other structural tags.
                    - Ensure that all hyperlinks (`<a>` tags), image sources (`<img>` tags), and styling remain exactly the same.
                    - Only improve the **visible text** within these tags while keeping the email's structure intact.
                    - The **subject** should be refined but remain similar in meaning.

                    ### **Input:**
                    - **Subject:** "$subject"
                    - **HTML Body:** 
                    "$body"
                    EOT;
                
                $payload = json_encode([
                    "model" => "deepseek-custom2",
                    "messages" => [
                        ["role" => "user", "content" => $promptContent]
                    ],
                    "stream" => false,
                    // "options" => [
                    //     "temperature" => 0.6,
                    //     "num_ctx" => 40000,
                    // ],
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
