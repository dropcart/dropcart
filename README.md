# Dropcart

## What?
Dropcart is a new ecommerce platform. Dropcart is perfect for dropshipment: no need to carry your own product stock and instead focus on attracting new customers. Dropcart focuses on smart, automatic, API integrations and an easy to use system. Stock, prices and product descriptions are all read from an API and are easily customizable. New orders are automatically send to the supplier for further processing.

## Why?
Setting up a dropshipment store is very cumbersome. It requires coding knowledge for implementing an awkward XML feed, new orders need to manually be placed and product specifications are usually very limited. It gets even worse when you have more than one supplier. Dropcart hopes to fix this.

## How?
There is two sides to Dropcart; the shop owners and the suppliers. The code you are looking at right now is to connect with our API's — both reading and writing. We build these by reading feeds from the suppliers.

# Installation instructions
Download the latest *stable* release from [Github](https://github.com/dropcart/dropcart).

## Requirements
(this list is incomplete)

- PHP Version: **5.3.+**
- PHP's [money_format()](http://php.net/manual/en/function.money-format.php)

## Instructions
- Unzip the .zip file
- Create a new database and save the credentials
- Insert the database found in the folder */_upgrade/* is a *v{versionnumber}-initial-install.sql* file
- Enter your database credentials in the file *includes/php/dc_connect.php*
- Upload everything to your root (or sub-)domain
- Navigate to *yourwebsite.com/beheer* and login with *admin*/*inktweb*
- In the *Settings* menu add the required information (including your *api_key*)
- Navigate to *yourwebsite.com* and test if everything works
- Setup a cronjob for `/beheer/cronjobs/dc_orderstatus.php`. This checks the order status from the supplier and updates the customer (e.g. if the order is shipped). Min: daily. Recommended: hourly.

### Files that still need customization
- In the CMS delete the default `admin` account and add your own
- In the CMS customize your emails / add logos
- Change the default images in the */images* folder
- Change/customize the email template(s) in *includes/templates* both the *.tpl* and *.html* file

