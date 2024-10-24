<?php

use App\Http\Controllers\AssetModelsController;
use App\Http\Controllers\AssetModelsFilesController;
use App\Http\Controllers\BulkAssetModelsController;
use Illuminate\Support\Facades\Route;

// Asset Model Management


Route::group(['prefix' => 'family', 'middleware' => ['auth']], function () {

    Route::post('{modelID}/upload',
        [AssetModelsFilesController::class, 'store']
    )->name('upload/models');

    Route::get('{modelID}/showfile/{fileId}/{download?}',
        [AssetModelsFilesController::class, 'show']
    )->name('show/modelfile');

    Route::delete('{modelID}/showfile/{fileId}/delete',
        [AssetModelsFilesController::class, 'destroy']
    )->name('delete/modelfile');

    Route::get(
        '{modelId}/clone',
        [
            AssetModelsController::class, 
            'getClone'
        ]
    )->name('models.clone.create');

    Route::post(
        '{modelId}/clone',
        [
            AssetModelsController::class, 
            'postCreate'
        ]
    )->name('models.clone.store');

    Route::get(
        '{modelId}/view',
        [
            AssetModelsController::class, 
            'getView'
        ]
    )->name('view/model');

    Route::post(
        '{modelID}/restore',
        [
            AssetModelsController::class, 
            'getRestore'
        ]
    )->name('models.restore.store');

    Route::get(
        '{modelId}/custom_fields',
        [
            AssetModelsController::class, 
            'getCustomFields'
        ]
    )->name('custom_fields/model');

    Route::post(
        'bulkedit',
        [
            BulkAssetModelsController::class, 
            'edit'
        ]
    )->name('models.bulkedit.index');

    Route::post(
        'bulksave',
        [
            BulkAssetModelsController::class, 
            'update'
        ]
    )->name('models.bulkedit.store');

    Route::post(
        'bulkdelete',
        [
            BulkAssetModelsController::class, 
            'destroy'
        ]
    )->name('models.bulkdelete.store');



});

Route::resource('family', AssetModelsController::class, [
    'middleware' => ['auth'],
    'parameters' => ['model' => 'model_id'],
]);

// route baru

// Route::middleware(['auth'])->group(function () {
//     Route::get('/family', [AssetModelsController::class, 'index'])->name('family.index');
//     Route::get('/family/create', [AssetModelsController::class, 'create'])->name('family.create');
//     Route::get('/family/{modelId}/edit', [AssetModelsController::class, 'edit'])->name('family.edit');
//     Route::get('/family/{modelId}', [AssetModelsController::class, 'show'])->name('family.show');
//     Route::post('/family/bulkedit', [AssetModelsController::class, 'postBulkEdit'])->name('family.bulkedit');

// });




