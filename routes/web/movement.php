<?php

use App\Http\Controllers\AssetMaintenancesController;
use App\Http\Controllers\Movement\MovementController;
use App\Http\Controllers\Assets\BulkAssetsController;
use App\Http\Controllers\Assets\AssetCheckoutController;
use App\Http\Controllers\Assets\AssetCheckinController;
use App\Http\Controllers\Assets\AssetFilesController;
use App\Http\Controllers\Movement\CompaniesMovementController;
use App\Http\Controllers\Movement\ProjectMovementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Asset Routes
|--------------------------------------------------------------------------
|
| Register all the asset routes.
|
*/

// Route::group(
//     [
//         'prefix' => '/movement',
//         'as' => 'movement.',
//         'middleware' => ['auth'],
//     ],
//     function () {
//         Route::get(
//             '{assetId}/view',
//             [AssetsController::class, 'show']
//         )->name('view');
//         Route::get(
//             '{assetId}/qr_code',
//             [AssetsController::class, 'getQrCode']
//         )->name('qr_code/movement');
//         Route::get(
//             '{assetId}/barcode',
//             [AssetsController::class, 'getBarCode']
//         )->name('barcode/movement');
//         Route::post(
//             '{assetId}/restore',
//             [AssetsController::class, 'getRestore']
//         )->name('restore/movement');
//         Route::post(
//             '{assetId}/upload',
//             [AssetFilesController::class, 'store']
//         )->name('upload/asset');
//         Route::get(
//             '{assetId}/showfile/{fileId}/{download?}',
//             [AssetFilesController::class, 'show']
//         )->name('show/assetfile');
//         Route::delete(
//             '{assetId}/showfile/{fileId}/delete',
//             [AssetFilesController::class, 'destroy']
//         )->name('delete/assetfile');
//         Route::post(
//             'bulkedit',
//             [BulkAssetsController::class, 'edit']
//         )->name('movement/bulkedit');
//         Route::post(
//             'bulkdelete',
//             [BulkAssetsController::class, 'destroy']
//         )->name('movement/bulkdelete');
//         Route::post(
//             'bulkrestore',
//             [BulkAssetsController::class, 'restore']
//         )->name('movement/bulkrestore');
//         Route::post(
//             'bulksave',
//             [BulkAssetsController::class, 'update']
//         )->name('movement/bulksave');

//         // Bulk checkout / checkin
//         Route::get(
//             'bulkcheckout',
//             [BulkAssetsController::class, 'showCheckout']
//         )->name('movement.bulkcheckout.show');

//         Route::post(
//             'bulkcheckout',
//             [BulkAssetsController::class, 'storeCheckout']
//         )->name('movement.bulkcheckout.store');
//     } // <-- Closing brace for the Route::group closure

// );

Route::middleware('auth')->get('save-new-formatDate', [MovementController::class, 'saveFormatDate'])->name('movement.saveFormatDate');
Route::middleware('auth')->get('invoices-movement', [MovementController::class, 'printInvoice'])->name('movement.invoice');
Route::middleware('auth')->get('invoices-movement-index', [MovementController::class, 'printInvoiceIndex'])->name('movement.invoice.index');
Route::middleware('auth')->post('saveMovement', [MovementController::class, 'saveMovement'])->name('movement.saveMovement');
Route::middleware('auth')->post('updateMovement', [MovementController::class, 'updateMovement'])->name('movement.updateMovement');
Route::middleware('auth')->post('changeStatus', [MovementController::class, 'changeStatus'])->name('movement.changeStatus');
Route::middleware('auth')->post('changeMovementDatetime/update', [MovementController::class, 'changeMovementDatetime'])->name('movement.changeDatetime');
Route::middleware('auth')->get('showProject/{id}', [MovementController::class, 'showProject'])->name('movement.showProject');
Route::middleware('auth')->get('return-movement/partial/{id}', [MovementController::class, 'returnMovementPartial'])->name('movement.returnMovementPartial');
Route::resource(
    'movement',
    MovementController::class,
    [
        'middleware' => ['auth'],
        // 'parameters' => ['asset' => 'asset_id'],
    ]
)->except(['store', 'update', 'delete']);

Route::middleware('auth')->get('project-movement/getAddressByProgram', [ProjectMovementController::class, 'getAddressByProjectId'])->name('project-movement.getAddressByProjectId');
Route::middleware('auth')->delete('project-movement/address/{id}/delete', [ProjectMovementController::class, 'destroyAddress'])->name('project-movement.deleteAddress');
Route::middleware('auth')->put('project-movement/address/{id}/update', [ProjectMovementController::class, 'updatedAddress'])->name('project-movement.updateAddress');
Route::middleware('auth')->resource('project-movement', ProjectMovementController::class)->except(['show']);

//
Route::middleware('auth')->get('company-movement/{id}/showAssignedMovement', [CompaniesMovementController::class, 'showMovementByCompanyBranch'])->name('company-movement.showAssingnedMovement');
Route::middleware('auth')->resource('company-movement', CompaniesMovementController::class)->except('show');
