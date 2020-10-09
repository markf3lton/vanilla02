This directory structure contains the settings and configuration files specific
<<<<<<< HEAD
to your site or sites and is an integral part of multisite configuration.

The sites/all/ subdirectory structure should be used to place your custom and
downloaded extensions including modules, themes, and third party libraries.

Downloaded installation profiles should be placed in the /profiles directory
in the Drupal root.

In multisite configuration, extensions found in the sites/all directory
structure are available to all sites. Alternatively, the sites/your_site_name/
subdirectory pattern may be used to restrict extensions to a specific
site instance.

See the respective README.txt files in sites/all/themes and sites/all/modules
for additional information about obtaining and organizing extensions.

See INSTALL.txt in the Drupal root for information about single-site
installation or multisite configuration.
=======
to your site or sites and is an integral part of multisite configurations.

It is now recommended to place your custom and downloaded extensions in the
/modules, /themes, and /profiles directories located in the Drupal root. The
sites/all/ subdirectory structure, which was recommended in previous versions
of Drupal, is still supported.

See core/INSTALL.txt for information about single-site installation or
multisite configuration.
>>>>>>> 7e72eb7910eaa61a59bcbc0a67edbb7d418674db
