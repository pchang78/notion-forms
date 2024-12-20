# notion-forms

## Description
Wordpress Plugin for converting your Notion Database to a form on your Wordpress site.  This plugin assumes you have some knowledge of [Notion](https://notion.so/) and know how to install Wordpress plugins.

___

## How it Works
Using the Notion API, the plugin reads the propeties (columns) of a Notion database and make them available as form fields in your form.  Once a form is submitted, the plugin will save the data to the Notion Database.  

___

## Requirements

### Wordpress Website
[Wordpress](https://wordpress.org/)

### Notion Integration Token
You will need to setup a Notion 
[Notion API Integration](https://www.notion.so/my-integrations)

---

## Installation

1. Install plugin and activate plugin in Wordpress
2. Go to Notion Content -> Setup in the Wordpress admin.
3. Enter in the Notion API Key (aka Internal Integration Token)
4. Enter in the link to the Notion Database (Not ID.  The plugin will parse the URL). 


## Usage
1. Go to Notion Content and click on the "Refresh Field" button.
2. Build your form using the available fields.
3. Copy and Paste the shortcode to be used in your Wordpress Post or Page.


---

## Supported Notion Database Properties (columns)
- Rich Text
- Select
- Phone Number
- Email

---

## Custom Styles
You can add CSS using your own custom classes manually, or by using the AI Prompt Helper tool.  The AI Prompt Helper tool will help you create a prompt to be pasted into your preferred AI tool to generate CSS. 

---

## Coming Soon
- More Notion Database Properties
- Conditional Form Fields
