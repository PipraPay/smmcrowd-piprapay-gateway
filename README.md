============================================================
           PipraPay Payment Module for SMMCrowd
============================================================

Integrating the PipraPay payment module into your SMMCrowd platform is simple and straightforward. Follow the steps below to complete the setup.

------------------------------------------------------------
1. Create PipraPay Folder
------------------------------------------------------------
Create a new folder named:
PipraPay

Path:
application/app/Http/Controllers/Gateway/PipraPay

------------------------------------------------------------
2. Upload ProcessController.php
------------------------------------------------------------
Upload the file named:
ProcessController.php

To the folder:
application/app/Http/Controllers/Gateway/PipraPay

------------------------------------------------------------
3. Update Routes (ipn.php)
------------------------------------------------------------
Open the file:
application/routes/ipn.php

Add the following line at the end of the file:

Route::any('piprapay', 'PipraPay\ProcessController@ipn')->name('PipraPay');

------------------------------------------------------------
4. Import Database.sql
------------------------------------------------------------
Open PhpMyAdmin and select your SMM Panel Database.

Import the provided file:
database.sql

This will create all required tables and settings for PipraPay to function properly.

------------------------------------------------------------
✅ Done!
------------------------------------------------------------
You have successfully integrated the PipraPay module into SMMCrowd. Enjoy simplified and reliable payment processing.

For any support or updates, please contact the module provider.

