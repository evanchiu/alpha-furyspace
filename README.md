alpha-furyspace
===============

Furyspace is a small group, space-themed strategy game. The play is all online, so it can be accessed anytime, from anywhere, enabling a group to keep in regular contact despite geographic separation.

The is the initial PHP5 implementation of Furyspace, [alpha.furyspace.com](http://alpha.furyspace.com)

Vagrant Setup
-------------

1. Install [Vagrant](http://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/)
1. Start up machine
```bash
  vagrant up
```
1. Once the virutal machine starts up, it should be accessible at [localhost:1080](http://localhost:1080/)
1. The registration code 'furious' has been added so you can create the first user, using the default, commander@furyspace.com, or add your own in `web/configuration.php`
1. Once that account exists, other accounts can be added by using administrative tasks / add codes to add more alpha codes, allowing other accounts to register
1. New games can be created by administrators

Alternate Setup
---------------

1. Any [*amp](http://en.wikipedia.org/wiki/List_of_AMP_packages) setup will work
1. Make sure PHP has access to the GD (graphics) libraries, and `short_open_tag` is `On`
1. Create a MySQL database and load the schema from `db/furyspace.sql`
1. Update the configuration parameters in `web/configuration.php`
1. Add a code into the `codes` table, use it to register

License
-------
MIT
