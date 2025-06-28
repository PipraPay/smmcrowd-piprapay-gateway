         PipraPay Payment Module for SMMCrowd
============================================================

Integrate the PipraPay payment module into your SMMCrowd platform effortlessly by following the steps below.

------------------------------------------------------------
📁 STEP 1: Create the PipraPay Folder
------------------------------------------------------------

Create the following directory:

> application/app/Http/Controllers/Gateway/**PipraPay**

Make sure the folder name is exactly: **PipraPay**

------------------------------------------------------------
📄 STEP 2: Upload ProcessController.php
------------------------------------------------------------

Upload the file:  
> **ProcessController.php**

To the folder:  
> application/app/Http/Controllers/Gateway/**PipraPay**

------------------------------------------------------------
🔁 STEP 3: Register the IPN Route
------------------------------------------------------------

Open the file:  
> application/routes/**ipn.php**

Add the following line at the end of the file:

```php
Route::any('piprapay', 'PipraPay\ProcessController@ipn')->name('PipraPay');
