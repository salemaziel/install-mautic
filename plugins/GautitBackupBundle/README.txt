You Gautit!!  Thank you for getting the Mautic Backup plugin from Gautit.

Notice: The free version does not include remote storage of the files, only local.

The premium version includes the ability to backup directly to remote drives such as Dropbox, Amazon S3 and we are adding more.

The most current documenation with screen shots and step by step instructions can be found at: https://gautit.com/gautitbackupdoc/

Copy the GautitBackupBundle folder to the folder plugins, in the Mautic installation.  The plugins folder will be in the root or first level of the installation.

Clear the cache folder from Mautic using the standard procedure:

Now open Mautic and login.

On the dashboard click the gear icon in the top right.   This opens the configuration menu, click plugins.

Click the install/upgrade button on the plugins page.

Select the Gautit Backup plugin by clicking on it.

Then select yes under Published to make the plugin active.   Enter your license key to enable extra features, such as remote storage.

Using the plugin:
Now a new menu item is on the settings menu (the little gear icon in the top right)

Clicking the menu item will get you into the Gautit Backup page.

Here, you can enter a name for your backup, something like “before plugin install”, or “before import”.   If you have the free version, backup local is your only option, if you have a license see below for instructions on setting up your remote drive.

Click Backup Now and your backup will start immediately.  Log messages will be shown on the screen.  Three files are created for each backup, the Mautic directory and files, the mysql dump and a log file.

You can click on the buttons, file, database or log to download the files to your computer.  If you don’t have a license, the files are backed up to your server into a folder in the cache directory.

Backups will NOT backup previous backups.  This is to keep file storage down.

Use the left drop down to delete a backup set.  Deleting will remove all three files for that backup set.

The most current documenation can be found at: https://gautit.com/gautitbackupdoc/

for support: email: team@gautit.com