# PHP ePub generator

PHPePub allows a php script to generate ePub Electronic books on the fly, and send them to the user as downloads.

PHPePub support most of the ePub 2.01 specification, and enough of the new ePub3 specification to make valid ePub 3 books as well.

The projects is also hosted on PHPClasses.org at the addresses:
http://www.phpclasses.org/package/6115

PHPePub is meant to be easy to use for small projects, and still allow for comples and complete e-books should the need arise.

The Zip.php class in this project originates from http://www.phpclasses.org/package/6110

or on Github: git://github.com/Grandt/PHPZip.git

See the examples for example usage. The php files have "some" doumentation in them in the form of Javadoc style function headers.

## Installation

### Import
Add this requirement to your `composer.json` file:
```json
    "grandt/phpepub": ">=4.0.3"
```

### Composer
If you already have Composer installed, skip this part.

[Packagist](https://packagist.org/), the main composer repository has a neat and very short guide.
Or you can look at the guide at the [Composer site](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).
 
The easiest for first time users, is to have the composer installed in the same directory as your composer.json file, though there are better options.

Run this from the command line:
```
php -r "readfile('https://getcomposer.org/installer');" | php
```

This will check your PHP installation, and download the `composer.phar`, which is the composer binary. This file is not needed on the server though.

Once composer is installed you can create the `composer.json` file to import this package.
```json
{
    "require": {
        "grandt/phpepub": ">=4.0.3",
        "php": ">=5.3.0"
    }
}
```

Followed by telling Composer to install the dependencies.
```
php composer.phar install
```

this will download and place all dependencies defined in your `composer.json` file in the `vendor` directory.

Finally, you include the `autoload.php` file in the new `vendor` directory.
```php
<?php
    require 'vendor/autoload.php';
    .
    .
    .
```

## EPub class documentation
This class has been designed for ease of use, and to enable ePub book creation on the fly.

The ePub standard contains a lot of parameters that can be set by the user, but in this implementation we are only relying on some of them, the important ones so to speak.

### Mandatory Fields:
* Title: setTitle(\$title), where \$title is a text string.
* Language: setLanguage(\$language), where \$language is a RFC3066 Language code, such as "en", "da", "fr" etc. Language is "en" by default.
* Identifier: setIdentifier(\$identifier, \$identifierType), where both arguments are text strings. The \$identifier should be unique for the book. If you don't have anything unique you can use the _createUUID_ function mentioned later in the documentation.
  * The \$identifierType must be one of these:
    * "EPub::IDENTIFIER_URI": When using the page URL as \$identifier.
    * "EPub::IDENTIFIER_ISBN": Usually used for published books, where the books unique ISBN number are available.
    * "EPub::IDENTIFIER_UUID": A generated or random UUID string on the form c5bc871d-a20a-fc48-ccb4-bb134ae6c564

### Optional Fields:
* Description: A book description or synopsis
* Author: Book author or creator. setAuthor(\$author, \$authorSortKey) has two arguments, where the sort key can be left blank with ''. The \$authorSortKey is basically how the name is to be sorted, usually it's "Lastname, First names" where the $author is the straight "Firstnames Lastname"
* Publisher: Book publisher Information, use setPublisher($publisherName, $publisherURL) to set the name and URL of the publisher.
* Date: The publishing date of the book, as a timestamp. If left blank the current date/time will be used.
* Rights: Text string with the licence and copyrights that may apply to the book.
* Source URL: The web address for the book, if any are available, but this eBook must be downloadable from somewhere. This is usually also the address used as an identifier, if that parameter is using the "URI" identifier type. 

## TODO:
* The goal being to encompass the majority of the features in the ePub 2.0 and 3.0 specifications, except the Daisy type files.
* Add better handling of Reference structures.
* Improve handling of media types and linked files.
* A/V content is allowed, but definitely not recommended, and MUST have a fallback chain ending in a valid file. If no such chain is provided, the content should not be added.
* Documentation, no one reads it, but everyone complains if it is missing.
* Better examples to fully cover the capabilities of the EPub classes.
* more TODO's.
