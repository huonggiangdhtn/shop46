<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register'=>false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Admin dashboard

Route::group( ['prefix'=>'admin/','middleware'=>'auth' ],function(){
    Route::get('/',[ \App\Http\Controllers\AdminController::class,'admin'])->name('admin');
});
Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']],
 function () { \UniSharp\LaravelFilemanager\Lfm::routes();});

Route::get('unauthorized',[\App\Http\Controllers\Controller::class,'unauthorized'])->name('unauthorized');

Route::middleware(['manager'])->group(function () {
    ///Banner section
    Route::resource('banner', \App\Http\Controllers\BannerController::class);
    Route::post('banner_status',[\App\Http\Controllers\BannerController::class,'bannerStatus'])->name('banner.status');
    Route::get('banner_search',[\App\Http\Controllers\BannerController::class,'bannerSearch'])->name('banner.search');

    ///Category section
    Route::resource('category', \App\Http\Controllers\CategoryController::class);
    Route::post('category_status',[\App\Http\Controllers\CategoryController::class,'categoryStatus'])->name('category.status');
    Route::get('category_search',[\App\Http\Controllers\CategoryController::class,'categorySearch'])->name('category.search');

    ///Brand section
    Route::resource('brand', \App\Http\Controllers\BrandController::class);
    Route::post('brand_status',[\App\Http\Controllers\BrandController::class,'brandStatus'])->name('brand.status');
    Route::get('brand_search',[\App\Http\Controllers\BrandController::class,'brandSearch'])->name('brand.search');


    ///Product section
    Route::resource('product', \App\Http\Controllers\ProductController::class);
    Route::post('product_status',[\App\Http\Controllers\ProductController::class,'productStatus'])->name('product.status');
    Route::get('product_search',[\App\Http\Controllers\ProductController::class,'productSearch'])->name('product.search');
    Route::get('product_sort',[\App\Http\Controllers\ProductController::class,'productSort'])->name('product.sort');
    Route::get('product_jsearch',[\App\Http\Controllers\ProductController::class,'productJsearch'])->name('product.jsearch');
    Route::get('product_stock_quantity',[\App\Http\Controllers\ProductController::class,'productStock_quantity'])->name('product.stock_quantity');
    Route::get('product_jsearchwi',[\App\Http\Controllers\ProductController::class,'productJsearchwi'])->name('product.jsearchwi');
    Route::get('product_groupprice',[\App\Http\Controllers\ProductController::class,'productGPriceSearch'])->name('product.groupprice');
    Route::get('product_jsearchwo',[\App\Http\Controllers\ProductController::class,'productJsearchwo'])->name('product.jsearchwo');
    Route::post('product_add',[\App\Http\Controllers\ProductController::class,'productAdd'])->name('product.add');
    Route::get('product_jsearchwf',[\App\Http\Controllers\ProductController::class,'productJsearchwf'])->name('product.jsearchwf');
    Route::get('product_tsearch',[\App\Http\Controllers\ProductController::class,'productTsearch'])->name('product.tsearch');
    Route::get('product_msearch',[\App\Http\Controllers\ProductController::class,'productMsearch'])->name('product.msearch');
    Route::post('product_addm',[\App\Http\Controllers\ProductController::class,'productAddm'])->name('product.addm');
    Route::get('product_jsearchms',[\App\Http\Controllers\ProductController::class,'productJsearchms'])->name('product.jsearchms');

    
    //User section
    Route::resource('user', \App\Http\Controllers\UserController::class);
    Route::post('user_status',[\App\Http\Controllers\UserController::class,'userStatus'])->name('user.status');
    Route::get('user_search',[\App\Http\Controllers\UserController::class,'userSearch'])->name('user.search');
    Route::get('user_sort',[\App\Http\Controllers\UserController::class,'userSort'])->name('user.sort');
    Route::post('user_detail',[\App\Http\Controllers\UserController::class,'userDetail'])->name('user.detail');

    ///UGroup section
    Route::resource('ugroup', \App\Http\Controllers\UGroupController::class);
    Route::post('ugroup_status',[\App\Http\Controllers\UGroupController::class,'ugroupStatus'])->name('ugroup.status');
    Route::get('ugroup_search',[\App\Http\Controllers\UGroupController::class,'ugroupSearch'])->name('ugroup.search');

    ///Warehouse section
    Route::resource('warehouse', \App\Http\Controllers\WarehouseController::class);
    Route::post('warehouse_status',[\App\Http\Controllers\WarehouseController::class,'warehouseStatus'])->name('warehouse.status');
    Route::get('warehouse_search',[\App\Http\Controllers\WarehouseController::class,'warehouseSearch'])->name('warehouse.search');

    ///Log section
    Route::resource('log', \App\Http\Controllers\LogController::class);

    ///BeginInventory section
    Route::resource('binventory', \App\Http\Controllers\BInventoryController::class);
    Route::get('binventory_search',[\App\Http\Controllers\BInventoryController::class,'binventorySearch'])->name('binventory.search');
    Route::get('binventory_sort',[\App\Http\Controllers\BInventoryController::class,'binventorySort'])->name('binventory.sort');


    /// Inventory section
    Route::resource('inventory', \App\Http\Controllers\InventoryController::class);
    Route::get('inventory_search',[\App\Http\Controllers\InventoryController::class,'inventorySearch'])->name('inventory.search');
    Route::get('inventory_sort',[\App\Http\Controllers\InventoryController::class,'inventorySort'])->name('inventory.sort');

    /// Bankaccount section
    Route::resource('bankaccount', \App\Http\Controllers\BankController::class);
    Route::post('bankaccount_status',[\App\Http\Controllers\BankController::class,'bankaccountStatus'])->name('bankaccount.status');
    Route::get('banktrans_view',[\App\Http\Controllers\BankController::class,'banktransView'])->name('bankaccount.viewtrans');
    Route::get('banktrans_sort',[\App\Http\Controllers\BankController::class,'banktransSort'])->name('banktransaction.sort');
    Route::get('bankaccount_transfer/{id}',[\App\Http\Controllers\BankController::class,'bankaccountTransfer'])->name('bankaccount.transfer');
    Route::post('bankaccount_transfer_save',[\App\Http\Controllers\BankController::class,'bankaccountTransferSave'])->name('bankaccount.savetransfer');
    Route::get('banktrans_show/{id}',[\App\Http\Controllers\BankController::class,'banktransShow'])->name('banktrans.show');

    /// warehousein section
    Route::resource('warehousein', \App\Http\Controllers\WarehouseinController::class);
    Route::get('warehousein_search',[\App\Http\Controllers\WarehouseinController::class,'warehouseinSearch'])->name('warehousein.search');
    Route::get('warehousein_getProductList',[\App\Http\Controllers\WarehouseinController::class,'getProductList'])->name('warehousein.getProductList');
    Route::get('warehousein_paid/{id}',[\App\Http\Controllers\WarehouseinController::class,'warehouseinPaid'])->name('warehousein.paid');
    Route::post('warehousein_storepaid',[\App\Http\Controllers\WarehouseinController::class,'warehouseinSavePaid'])->name('warehousein.storepaid');
    Route::post('warehousein_return',[\App\Http\Controllers\WarehouseinController::class,'warehouseinReturn'])->name('warehousein.return');

    /// Supplier section
    Route::resource('supplier', \App\Http\Controllers\SupplierController::class);
    Route::get('supplier_search',[\App\Http\Controllers\SupplierController::class,'supplierSearch'])->name('supplier.search');
    Route::get('supplier_jsearch',[\App\Http\Controllers\SupplierController::class,'supplierJsearch'])->name('supplier.jsearch');
    Route::get('supplier_paid/{id}',[\App\Http\Controllers\SupplierController::class,'supplierPaid'])->name('supplier.paid');
    Route::post('supplier_storepaid',[\App\Http\Controllers\SupplierController::class,'supplierSavePaid'])->name('supplier.storepaid');
    Route::get('supplier_balance/{id}',[\App\Http\Controllers\SupplierController::class,'supplierMakeBalance'])->name('supplier.balance');
    Route::post('supplier_storereceived',[\App\Http\Controllers\SupplierController::class,'supplierSaveReceived'])->name('supplier.storereceived');
    Route::get('supplier_received/{id}',[\App\Http\Controllers\SupplierController::class,'supplierReceived'])->name('supplier.received');

    Route::get('supplier_sort',[\App\Http\Controllers\SupplierController::class,'supplierSort'])->name('supplier.sort');
    Route::post('supplier_status',[\App\Http\Controllers\SupplierController::class,'supplierStatus'])->name('supplier.status');
    Route::post('supplier_add',[\App\Http\Controllers\SupplierController::class,'supplierAdd'])->name('supplier.add');

    /// FreeTransaction section
    Route::resource('freetransaction', \App\Http\Controllers\FreeTransactionController::class);

    /// SupTransaction section
    Route::resource('suptransaction', \App\Http\Controllers\SupTransactionController::class);

    /// Delivery section
    Route::resource('delivery', \App\Http\Controllers\DeliveryController::class);
    Route::get('delivery_search',[\App\Http\Controllers\DeliveryController::class,'deliverySearch'])->name('delivery.search');
    Route::get('delivery_jsearch',[\App\Http\Controllers\DeliveryController::class,'deliveryJsearch'])->name('delivery.jsearch');
    Route::get('delivery_sort',[\App\Http\Controllers\DeliveryController::class,'deliverySort'])->name('delivery.sort');
    Route::post('delivery_status',[\App\Http\Controllers\DeliveryController::class,'deliveryStatus'])->name('delivery.status');

    /// warehouseout section
    Route::resource('warehouseout', \App\Http\Controllers\WarehouseoutController::class);
    Route::get('warehouseout_search',[\App\Http\Controllers\WarehouseoutController::class,'warehouseoutSearch'])->name('warehouseout.search');
    Route::get('warehouseout_paid/{id}',[\App\Http\Controllers\WarehouseoutController::class,'warehouseoutPaid'])->name('warehouseout.paid');
    Route::post('warehouseout_storepaid',[\App\Http\Controllers\WarehouseoutController::class,'warehouseoutSavePaid'])->name('warehouseout.storepaid');
    Route::get('warehouseout_getProductList',[\App\Http\Controllers\WarehouseoutController::class,'getProductList'])->name('warehouseout.getProductList');
    Route::get('warehouseout_deprint/{id}',[\App\Http\Controllers\WarehouseoutController::class,'deliveryPrint'])->name('warehouseout.deprint');
    Route::post('warehouseout_return',[\App\Http\Controllers\WarehouseoutController::class,'warehouseoutReturn'])->name('warehouseout.return');
    Route::get('warehouseout_returnall',[\App\Http\Controllers\WarehouseoutController::class,'warehouseoutReturnall'])->name('warehouseout.returnall');
    Route::post('warehouseout_savereturnall',[\App\Http\Controllers\WarehouseoutController::class,'warehouseoutSaveReturnall'])->name('warehouseout.savereturnall');


    /// Customer section
    Route::resource('customer', \App\Http\Controllers\CustomerController::class);
    Route::get('customer_search',[\App\Http\Controllers\CustomerController::class,'customerSearch'])->name('customer.search');
    Route::get('customer_jsearch',[\App\Http\Controllers\CustomerController::class,'customerJsearch'])->name('customer.jsearch');
    Route::get('customer_paid/{id}',[\App\Http\Controllers\CustomerController::class,'customerPaid'])->name('customer.paid');
    Route::post('customer_storepaid',[\App\Http\Controllers\CustomerController::class,'customerSavePaid'])->name('customer.storepaid');
    Route::get('customer_balance/{id}',[\App\Http\Controllers\CustomerController::class,'customerMakeBalance'])->name('customer.balance');
    Route::post('customer_add',[\App\Http\Controllers\CustomerController::class,'customerAdd'])->name('customer.add');

    Route::get('customer_sort',[\App\Http\Controllers\CustomerController::class,'customerSort'])->name('customer.sort');
    Route::post('customer_status',[\App\Http\Controllers\CustomerController::class,'customerStatus'])->name('customer.status');
    Route::post('customer_storereceived',[\App\Http\Controllers\CustomerController::class,'customerSaveReceived'])->name('customer.storereceived');
    Route::get('customer_received/{id}',[\App\Http\Controllers\CustomerController::class,'customerReceived'])->name('customer.received');

    /// Setting  section
    Route::resource('setting', \App\Http\Controllers\SettingController::class);


    /// order section
    Route::resource('order', \App\Http\Controllers\OrderController::class);
    Route::get('order_search',[\App\Http\Controllers\OrderController::class,'orderSearch'])->name('order.search');
    Route::get('order_getProductList',[\App\Http\Controllers\OrderController::class,'getProductList'])->name('order.getProductList');
    Route::get('order_out/{id}',[\App\Http\Controllers\OrderController::class,'orderOut'])->name('order.out');
    Route::post('order_outupdate',[\App\Http\Controllers\OrderController::class,'orderOutUpdate'])->name('order.outupdate');

     /// warehousetransfer section
    Route::resource('warehousetransfer', \App\Http\Controllers\WarehousetransferController::class);
    Route::get('warehousetrans_getProductList',[\App\Http\Controllers\WarehousetransferController::class,'getProductList'])->name('warehousetrans.getProductList');
    Route::get('warehousetrans_deprint/{id}',[\App\Http\Controllers\WarehousetransferController::class,'deliveryPrint'])->name('warehousetransfer.deprint');
  
     /// warehousetomaintain section
     Route::resource('warehousetomaintain', \App\Http\Controllers\WarehousetomaintainController::class);
    ///maitain
    Route::get('maintain_inv',[\App\Http\Controllers\InventoryMaintenanceController::class,'index'])->name('inventorymaintenance.index');
    Route::get('maintain_search',[\App\Http\Controllers\InventoryMaintenanceController::class,'inventorySearch'])->name('inventorymaintenance.search');
    Route::get('maintain_sort',[\App\Http\Controllers\InventoryMaintenanceController::class,'inventorySort'])->name('inventorymaintenance.sort');
    ///maintainin
    Route::resource('maintainin', \App\Http\Controllers\MaintainInController::class);
    
    ///maintainsent
    Route::resource('maintainsent', \App\Http\Controllers\MaintainSentController::class);
    Route::get('maintainsent_getProductList',[\App\Http\Controllers\MaintainSentController::class,'getProductList'])->name('maintainsent.getProductList');
    Route::get('maintainsent_deprint/{id}',[\App\Http\Controllers\MaintainSentController::class,'deliveryPrint'])->name('maintainsent.deprint');
  
     ///maintainsent
     Route::resource('maintainback', \App\Http\Controllers\MaintainBackController::class);
     Route::get('maintainback_getProductList',[\App\Http\Controllers\MaintainBackController::class,'getProductList'])->name('maintainback.getProductList');
     Route::get('maintainback_deprint/{id}',[\App\Http\Controllers\MaintainBackController::class,'deliveryPrint'])->name('maintainback.deprint');
     Route::get('maintainback_paid/{id}',[\App\Http\Controllers\MaintainBackController::class,'maintainbackPaid'])->name('maintainback.paid');
    
});
