# timfox helpdesk

Custom helpdesk ticketing system

# Get started

1. Run `lando start`
2. Copy config-template.php and rename it to config.php. Populate local config there. Run `lando info` if you need the database information. Default is database/lamp/lamp/lamp.
3. Import the database using `lando db-import helpdesk.sql`
4. Go to http://timfox-helpdesk.lndo.site in your browser
5. Click corgi logo for login page
6. admin credentials are `admin/admin`
