# DokuWiki-Slack-Integration

A DokuWiki plugin that notifies a Slack channel room of wiki edits.

Setup
-----

1. Clone repository into your DokuWiku plugins folder, making the target folder name 'dokuwiki-slack-integration'

```
$ git clone https://github.com/littleiffel/dokuwiki-slack-integration.git dokuwiki-slack-integration
```

2. To fetch the required dependencies, run:

```
$ composer install
```

3. In your DokuWiki Configuration Settings, enter an API token, channel name, and the name you want the notifications to appear from in Slack.


4. Upload via FTP to /lib/plugins/slackhq

5. Create an INCOMING WEBHOOK on Slack and under COnfigurations in Doku Wiki paste the webhook URL in field for SlackHQ Webhook. Set Icon (EMOJI) and channel, name..etc.
6. A Post to Slack with link is posted to your slack channel when you save a page.



Requirements
------------

* DokuWiki
