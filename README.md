Xenforo-Csync2StreamWrapper
======================

Provides a csync2:// stream for synchronous replication. 

Supports ChrisD's Add-on Install & Upgrade addon, abd Waindigo's Install and Upgrade addon

Notifications of Deletes/Writes to this stream are pushed to Csync2 to distribute to pre-configured Csync2 config. 

Requires:
- php 5.6
- Csync2 1.34
- Csync2's key file for the group used in syncing must be read/writeable by the user php is running under.
- Csync2's sqlite database *folder* (/var/lib/csync2/) must be writeable by the user php is running under.
    - chown php-user:php-user /var/lib/csync2
    - php-user also requires access to the key file for the group sync config.
- Don't sync the sqlite database!
    

