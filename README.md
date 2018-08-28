# Yearplan Management reporter
Reporter module for SEARCA's nth Year Operational Planning Information System

![image](https://upload.wikimedia.org/wikipedia/commons/9/9b/Social_Network_Analysis_Visualization.png)
### Installation

##### Clone repository
> git clone https://github.com/SEARCAPhil/yearplan-reporter.git   
git checkout develop

##### Generate APP KEY
> php artisan key:generate
   

##### Configure database
> update `.env` with your database connection
   

Go to the first URL if you are running your own websever or the latter if you dont. If you like to use PHP's built-in development server, you may use the  serve Artisan command:

> php artisan serve
* http://localhost/yearplan-reporter/public/   
* http://127.0.0.1:8000

   

##### Create sample database and seed data
> IMPORTANT: This should not be used in your production server.   
 Your data will be deleted if you do this!
```php
php artisan migrate:fresh -v --seed
```


### AST Inspector
Line Item
> /yearplan-reporter/public/inspector/line_item/{fyid}/{user_id}

Fiscal Year
>/yearplan-reporter/public/inspector/fiscal_year/{fyid}/{user_id}

