Symfony Command Chain
========================
To add command to chain define it as services with ```pm.chain_command_to``` tag.
Eample:
```
services:
    barbundle.command.hi:
        class: BarBundle\Command\HiCommand
        tags:
            - {name: 'pm.chain_command_to', command: 'foo:hello'}
            - {name: 'console.command'}
```
You can chain any non chained application command to any application command that have no chained commands yet.

This code provides an example of chain command functionality. To run it you need to:

 - install composer libraries ```composer install```
 - run ```foo:hello``` command (```php ./bin/console foo:hello```)
 
Bundle also provides a ```debug:chain``` command, that allows you to see all configured chains.
 
Also bundle have phpunit tests. That could be runned by ```./vendor/bin/phpunit``` command after composer libraries would be installed.

Logging system implemented via evens. By default logger configured to write logs to /var/logs/pm.chaincommandbundle.chain.log file
