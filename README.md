![Open Source Love](https://img.shields.io/badge/open-source-lightgrey?style=for-the-badge&logo=github)
![](https://img.shields.io/badge/Magento-2.4.x-orange?style=for-the-badge&logo=magento)
![](https://img.shields.io/badge/Maintained-yes-gren?style=for-the-badge&logo=magento)
![](https://img.shields.io/badge/PHP-7.4.x-blue?style=for-the-badge&logo=php)
![](https://img.shields.io/badge/version-BETA-yellowgreen?style=for-the-badge)

# Product Collective Packages Module for Magento 2 / Adobe Commerce

### HYVA Compatible - yes

[![hyva-logo-360.png](https://i.postimg.cc/8PRgt2z7/hyva-logo-360.png)](https://postimg.cc/ZC5QqD3m)
#### (BETA version)

[![Screenshot-at-22-00-40.png](https://i.postimg.cc/HWtnvbMT/Screenshot-at-22-00-40.png)](https://postimg.cc/t7Y9YZDc)

This module enhances the functionality of Adobe Commerce by providing a new feature for product collective
packages. With this module, merchants will be able to easily create and manage packages made up of multiple 
quantities of the same products. 
Each package can include different number of product units and the prices will be automatically calculated based on the product count.

Merchants can specify 2 types of dicounts 
* fixed amount discount
* percentage discount

**Important**

This module only works for simple products.

Create and manage packages made up of multiple products
Automatically calculate package prices based on the combined price of the included products
Ability to include products with different variations in a single package
Easy-to-use interface for creating and managing packages
This module is a must-have for merchants looking to simplify their product offerings and increase sales. Give it a try today! 

### Installation

Installation is via `composer`
```
composer require enanobots/m2-product-pack
```

After installing the packages just run:
```
php bin/magento setup:upgrade
```

### Requirements:
* `PHP 7.4` and higher
* `Magento 2.4.x` and higher

### Tested on:
* `Magento 2.4.x` OpenSource

## Development of this module and versions:
* `1.0.0` - Initial release of this module
* `1.1.0` - (`In Development`) adding support for GraphQL and REST API
* `1.2.0` - (`Future Development`) adding support for other product types products

