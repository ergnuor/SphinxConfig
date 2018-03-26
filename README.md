# SphinxConfig
This is the Sphinx configuration helper, which extends block inheritance and allows you to integrate configuration into your application through the possibility of using placeholders.
## Usage
```php
use Ergnuor\SphinxConfig\SphinxConfig;
$config = new SphinxConfig();
$config
   ->setSrcPath('/path/to/config/source')
   ->make('preciousConfig');
   ```
This example will create Sphinx configuration file `/path/to/config/source/preciosConfig.conf`. But first you need to describe a source [configuration](#configuration-structure).
## Prerequisites
SphinxConfig has no dependencies except for PHP 5.6+
## Structure
### Configuration structure
Configuration is described by associative arrays.
Here is a simple example of configuration structure:
```php
<?php
return [
   'source' => [
       'main' => [
           'sql_query_pre' => [
               'SET NAMES utf8',
               'SET CHARACTER SET utf8'
           ],
           // ... all other setting
       ],
   ],

   'index' => [
       'main' => [
           'source' => 'main',
           'path' => '/path/to/main',
           // ... all other setting
       ],
   ],

   'indexer' => [
       'mem_limit' => '1024M',
       // ... all other setting
   ],

   'searchd' => [
       'listen' => 'localhost:9306:mysql41',
       // ... all other setting
   ],

   'common' => [
       'lemmatizer_base' => '/usr/local/share/sphinx/dicts/',
       // ... all other setting
   ],
];
```
`Source` and `index` sections consist of blocks. Blocks, as well as `indexer`, `searchd` and `common` sections, contain parameters.
### File structure convention
Here is a typical full file structure of configuration:

    ├── preciousConfig
    │   ├── sectionName
    │   │   └── blockName.php      # Block is located in a separate file
    │   └── sectionName.php        # Section is located in a separate file
    └── preciousConfig.php         # Whole configuration is located in one file

You don't have to describe a structure of configuration as comprehensively as shown above. You can choose one of the following options or combine them at your own discretion:
- An entire configuration is located in one file.  
For example: `/path/to/configs/preciousConfig.php`,
- Section is located in a separate file.  
For example: `/path/to/configs/preciousConfig/sectionName.php`,
- Block is located in a separate file.  
For example: `/path/to/configs/preciousConfig/sectionName/blockName.php`.
This option may be useful for storing common parameters used by different configurations. For more information see [inheritance between configurations](#inheritance-between-configurations) section.




## Inheritance
To inherit blocks you should use `'extends'` parameter with the name of parent block as a value.  
For example:
```php
<?php
return [
   'source' => [
       'main' => [
           // ... other block settings
       ],
       'delta' => [
           'extends' => 'main',
           // ... pther block settings
       ]
   ],
];
```
### Inheritance of multi-value parameters
Values of a multi-value parameter are appended to values of a parent block parameter by default. To ignore values of the parent block, you must use `:clear` modifier.  
For example:
```php
'sql_query_pre:clear' => [
   'SET NAMES utf8',
   // ... other pre queries
],
```
You can also specify an alias for the value of the multi-value parameter. So, you can refer to the value in child blocks and override it.  
For example:
```php
<?php
return [
    'source' => [
        'main' => [
            'sql_query_pre' => [
                'SET NAMES utf8',
                // ... other pre queries
                'sph_counter' => 'INSERT INTO sphCounter  (indexingStartedAt, caption) \
                    VALUES(NOW(), \'main\') \
                    ON DUPLICATE KEY UPDATE indexingStartedAt = now()'
            ],
            // ... other block settings
        ],
        'delta' => [
            'extends' => 'main',
            'sql_query_pre' => [
                'sph_counter' => 'INSERT INTO sphCounter  (indexingStartedAt, caption) \
                    VALUES(NOW(), \'delta\') \
                    ON DUPLICATE KEY UPDATE indexingStartedAt = now()'
             ],
             // ... other block settings
        ]
    ],
];
```

And get the following configuration as a result:
```
source main {
    sql_query_pre = SET NAMES utf8
    // ... other pre queries
    sql_query_pre = INSERT INTO sphCounter  (indexingStartedAt, caption) \
        VALUES(NOW(), 'main') \
        ON DUPLICATE KEY UPDATE indexingStartedAt = now()
    // ... other block settings
}

source delta : main {
    sql_query_pre = SET NAMES utf8
    // ... other pre queries
    sql_query_pre = INSERT INTO sphCounter  (indexingStartedAt, caption) \
        VALUES(NOW(), 'delta') \
        ON DUPLICATE KEY UPDATE indexingStartedAt = now()
    // ... other block settings
}
```
With this approach you don’t need to copy all values of the multi-value parameter from the parent block. This example can be made even shorter by using [placeholders](#placeholders).
### Inheritance between configurations
You can refer to the blocks from other configurations. For that you need to specify the configuration name and separate it from the block name with `@` symbol. Note that configurations should be located in the same directory.

The example of usage is storing database connection settings in `/path/to/configs/common/source/connection.php` block and referring to it from other configurations via `'extends' => 'common@connection'` parameter.

Note that in internal representation `indexer`, `searchd` and `common` sections are converted into sections containing self-titled blocks. This allows them to be used in the same way as `source` and `index` sections. So, as in the last example, you can store common settings for `indexer`, `searchd` and `common` sections in separate blocks and inherit from them.
## Placeholders
Each value of Sphinx parameter may contain a placeholder in `::path.to.value:`: format. Placeholder uses dot notation, so it is possible to extract values from multidimensional arrays.

There are two ways of passing values:
- You can pass global values for entire configuration using `setPlaceholderValues()` method.  
This may be useful for passing values from your application, for example such as database connection parameters.
- Values could be specified locally for each block using `'placeholderValues'` parameter,  
Using this feature we can simplify the example from [inheritance of multi-value parameters](#inheritance-of-multi-value-parameters) section:
```php
return [
   'source' => [
       'main' => [
           'sql_query_pre' => [
               'SET NAMES utf8',
               // ... other queries
               'INSERT INTO sphCounter  (indexingStartedAt, caption) \
                   VALUES(NOW(), \'::sourceName::\') \
                   ON DUPLICATE KEY UPDATE indexingStartedAt = now()'
           ],
           'placeholderValues' => [
               'sourceName' => 'main',
           ],
           // ... other block settings
       ],
       'delta' => [
           'extends' => 'main',
           'placeholderValues' => [
               'sourceName' => 'delta',
           ],
           // ... other block settings
       ]
   ],
];
```

And get the following configuration as a result:
```
source main {
    sql_query_pre = SET NAMES utf8
    // ... other pre queries
    sql_query_pre = INSERT INTO sphCounter  (indexingStartedAt, caption) \
        VALUES(NOW(), 'main') \
        ON DUPLICATE KEY UPDATE indexingStartedAt = now()
    // ... other block settings
}

source delta : main {
    sql_query_pre = SET NAMES utf8
    // ... other pre queries
    sql_query_pre = INSERT INTO sphCounter  (indexingStartedAt, caption) \
        VALUES(NOW(), 'delta') \
        ON DUPLICATE KEY UPDATE indexingStartedAt = now()
    // ... other block settings
}
```
## Method list
- **```setSrcPath($srcPath)```**  
Sets the directory containing source configurations,
- **```setDstPath($dstPath)```**  
Sets the directory where resulting Sphinx configuration will be created. It is equal to `srcPath` by default,
- **```make($configName)```**  
Generates Sphinx configuration in the `dstPath` directory,
- **```setDefaultSrcPath($srcPath)```**  
Sets the default directory containing source configurations. Applies to all instances of SphinxConfig,
- **```setDefaultDstPath($dstPath)```**  
Sets the default directory where resulting Sphinx configuration will be created. Applies to all instances of SphinxConfig,
## Configuration parameters list
- **```'extends'```**  
Specifies the name of the parent block. Can refer to a block from another configuration located in the same directory. For that you must specify the configuration name separated from the block name with `@` symbol.  
`'extends' => 'blockName'`  
`'extends' => 'otherConfig@blockName'`
- **```'placeholderValues'```**  
Values for placeholders,
- **```'isPseudo'```**  
Marks the block as a pseudo block. Pseudo blocks don`t get into the resulting configuration. This may be useful for inheritance purposes if you want to create a container-block just for storing parameters that are common to other blocks.
## License
This project is licensed under the Apache 2.0 - see the [LICENSE](LICENSE) file for details