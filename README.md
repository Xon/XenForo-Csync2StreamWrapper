Xenforo-SVCsync2StreamWrapper
======================

Provides a csync2:// stream for synchronous replication. 

Notifications of Deletes/Writes to this stream are pushed to Csync2 to distribute to pre-configured Csync2 config. 

Requires:
- php 5.5
- Csync2 1.34
- Csync2's sqlite database *folder* (/var/lib/csync2/) must be writeable by the user php is running under.
    - Don't sync the database!

    chown php-user:php-user /var/lib/csync2
