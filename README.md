# Jobsity MovieDB Challenge  
  
Hi!  
This Repository contains a Drupal 9 site that feeds on [themoviedb.org](http://themoviedb.org/) API to display Movies, Artists and relationships between them.  
  
## Installation  
  
- Clone this repository.
- Run composer install from the repository root folder.
- Adjust your webserver and domain settings on your host machine.
- Adjust your database settings using "/sites/default/settings.php".
- Add a Hash Salt to settings.php
- Add the config_sync_directory variable to your settings.php like this: $settings['config_sync_directory'] =  '.\./config/sync';
- Run drush site:install --existing-config from the docroot.
- Visit your newly created site.  
- You're all done!  
  
## Content  
  
I approached the content issue by feeding the site on demand. This means I fetched the most popular movies and actors first, but as soon as you browse through them, more content will become available.  
  
## Efficiency  
  
This is by no means a final version of the code. Left TODO's in some functions.
