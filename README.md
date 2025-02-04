# Ollama Extension for MailWizz

This is a MailWizz extension that intercepts an email before it is sent, sends the email body and subject to **Ollama**, and uses any model on Ollama to modify the content. The extension allows you to enhance and refine email content before sending it out, utilizing powerful AI models like **deepseek-r1:1.5b**.

---

## Table of Contents
1. [Installation](#installation)
2. [Usage](#usage)
3. [Configuration](#configuration)
4. [Contributing](#contributing)
5. [License](#license)
6. [Contact](#contact)
7. [FAQ](#faq)

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

This extension uses the `deepseek-r1:1.5b` model by default. If you want to use a different model, you'll need to change the model name in the extension configuration file.

1. After installing Ollama, open your terminal or command line.
2. Run the following command to start the `deepseek-r1:1.5b` model (or your chosen model):

   ```bash
   ollama run deepseek-r1:1.5b
