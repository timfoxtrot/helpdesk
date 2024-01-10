# timfox helpdesk

Custom helpdesk ticketing system
# get started

### Lando
1. Run `lando start`
2. Copy `config-template.php` and rename it to `config.php`. Populate local config there. Run `lando info` if you need the database information. Default is database/lamp/lamp/lamp.
3. Import the database using `lando db-import helpdesk.sql`
4. Go to http://timfox-helpdesk.lndo.site in your browser
5. Click corgi logo for login page
6. admin credentials are `admin/admin`


### Other

1. Requirements
   2. PHP 5.4
   3. MySQL database
2. Copy `config-template.php` and rename it to `config.php`
3. Populate local config with database address, name,  and credentials
4. Import `helpdesk.sql` into your datase
5. Go to http://localhost/helpdesk
6. Click corgi logo for login page
7. admin credentials are `admin/admin`
