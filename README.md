Web App: Job Searching Network using Drupal 8 as project for California State University - Fullerton

With Implementation of Google Material Design and Angular JS

### Requirements:

1. Composer https://getcomposer.org/

2. PHP and MySQL in your machine

3. Drupal 8 System Requirements https://www.drupal.org/requirements

### Instruction

1. Install composer IF you don't have it in your machine

2. Run composer install inside the project root folder

3. Create your local mysql database

4. Copy project/sites/default/settings.php as settings.php

5. Import the database.sql to your local database

6. Edit your host file to point to the project root

7. Add the database connection in settings.php

```
$databases['default']['default'] = array (
  'database' => 'dbname',
  'username' => 'dbuser',
  'password' => 'dbpassword',
  'prefix' => '',
  'host' => 'dbhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['install_profile'] = 'standard';
```


