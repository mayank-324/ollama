# Ollama Extension for MailWizz

This is a MailWizz extension that intercepts an email before it is sent, sends the email body and subject to **Ollama**, and uses any model on Ollama to modify the content. The extension allows you to enhance and refine email content before sending it out, utilizing powerful AI models like **deepseek-r1:1.5b**.

---

## Table of Contents
1. [Installation](#installation)
2. [Usage](#usage)
3. [Configuration](#configuration)
4. [Contributing](#contributing)
5. [License](#license)
6. [Contact](#contact)=

---
## Installation

### Step 1: Download the Extension
1. **Clone this repository** or download it as a ZIP file.
2. **Create a ZIP file** containing the extension's files (the entire repository or the files inside the extension folder) and upload it via the backend panel of your MailWizz installation.

### Step 2: Install Ollama
1. Go to the official [Ollama website](https://ollama.com/download) to download and install Ollama on your system.
2. Follow the installation instructions provided on the Ollama website.

---
## Usage
### Step 1: Run the Ollama Model
This extension uses the deepseek-r1:1.5b model by default. If you want to use a different model, you'll need to change the model name in the extension configuration file.

After installing Ollama, open your terminal or command line.

Run the following command to start the deepseek-r1:1.5b model (or your chosen model):

```bash
   ollama run deepseek-r1:1.5b
```
If you want to use a different model, replace deepseek-r1:1.5b with the model name. For example:

```bash
ollama run <model_name>
```
### Step 2: Modify the Extension Configuration
1. Open the file OllamaExt.php located in your extension folder.
2. On line 106, replace deepseek-r1:1.5b with the name of the model you want to use.
### Step 3: Upload and Activate the Extension in MailWizz
1. Go to the Extension List in MailWizz and upload the ZIP file of the extension.
2. After uploading, activate the extension from the list.
3. Go to Settings in the extension panel:
   Set Activate to Yes.
   Enter your desired prompt for Ollama in the input box (this will be sent along with the email content to the Ollama model).
4. Click Save.
Once this is done, the extension will intercept outgoing emails, send them to Ollama for modification, and return the processed email content.
---
## Configuration
You can modify the behavior of the extension and its interaction with the Ollama model by adjusting the following:

   **Model Selection**: As mentioned, change the model name on line 106 of the OllamaExt.php file if you want to use a model other than deepseek-r1:1.5b.
   **Ollama Prompt**: The prompt you enter in the extension settings in MailWizz will be passed to Ollama along with the email content. This allows you to provide context or specific instructions to guide the AI.
---
## Contributing
We welcome contributions to this extension! If you would like to improve or add features, feel free to:

1. Fork the repository.
2. Create a new branch (git checkout -b feature-branch).
3. Make your changes and commit them (git commit -m 'Add feature').
4. Push to your fork (git push origin feature-branch).
5. Create a pull request from your fork.
---
## License
This project is licensed under the MIT License. See the LICENSE file for more details.
---
## Contact
If you encounter any issues or need help setting up the extension, feel free to contact us at:
Email: ai@perceptsystems.com
