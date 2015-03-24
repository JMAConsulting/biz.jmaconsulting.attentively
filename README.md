biz.jmaconsulting.attentively
=============================

Installation
------------

3. As part of your general CiviCRM installation, you should set up a cron job following the instructions at http://wiki.civicrm.org/confluence/display/CRMDOC/Managing+Scheduled+Jobs#ManagingScheduledJobs-Command-lineSyntaxforRunningJobs
4. As part of your general CiviCRM installation, you should set a CiviCRM Extensions Directory at Administer >> System Settings >> Directories.
2. As part of your general CiviCRM installation, you should set an Extension Resource URL at Administer >> System Settings >> Resource URLs.
4. Navigate to Administer >> System Settings >> Manage Extensions.
4. Beside Attentive.ly Social Media Integration click Install.
5. Review the Terms and Conditions, click 'I have read and accept the terms and conditions', then click Enable.
6. Once you have been redirected to the Attentive.ly Sign Up page, either create a new account or login to your existing Attentive.ly account.
7. Your browser will be redirected back to your CiviCRM instance, which will now be connected to your Attentive.ly account.

The extension will synchronize your contacts to Attentive.ly during the first day the extension is enabled. Attentive.ly will take a number of days to gradually match them to social media accounts, and then CiviCRM will get the information from Attentive.ly.

NB: Make sure to disable the extension on testing and staging sites, or contacts and their groups that are added or deleted in one instance like staging will, through synching, end up changing data on another, like production.
