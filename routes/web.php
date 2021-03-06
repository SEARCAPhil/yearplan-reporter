<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

# inspectors
Route::get('/inspector/fiscal_year/{fy}/{id}', 'Inspectors\FiscalYear@inspect');
Route::get('/inspector/line_item/{fy}/{id}', 'Inspectors\LineItem@show');
Route::get('/inspector/line_item_merge/{fy}/{id}', 'Inspectors\LineItemMerge@show');
Route::get('/inspector/cost_center/{fy}/{id}', 'Inspectors\CostCenter@show');
Route::get('/inspector/cost_center_merge/{fy}/{id}', 'Inspectors\CostCenterMerge@show');


Route::get('/inspector/item/{fy}/{id}/{itemId}', 'Inspectors\Item@show');
Route::get('/inspector/item_merge/{fy}/{id}/{itemId}', 'Inspectors\ItemMerge@show');

# reports
Route::get('/reports/line_item/{fy}/{id}', 'Reports\LineItemMerge@print');
Route::get('/reports/cost_center/{fy}/{options?}', 'Reports\CostCenter@print');