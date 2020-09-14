# Jobsity MovieDB Challenge  
  
Hi!  
This Repository contains a Drupal 9 site that feeds on [themoviedb.org](http://themoviedb.org/) API to display Movies, Artists and relationships between them.  
  
## Installation  
  
- Clone this repository.
- Run composer install from the repository root folder.
- Adjust your webserver and domain settings on your host machine.
- Adjust your database settings using the "/sites/default/settings.php" file.
- Add the hash_salt variable to settings.php
- Add the config_sync_directory variable to settings.php. It should look like this:
	- $settings['config_sync_directory'] =  '.\./config/sync';
- Run "drush site:install jobsity" from the web folder.
- Enable the custom jobsity_moviedb module "drush pm-enable
- Visit your newly created site.  
- You're all done!
