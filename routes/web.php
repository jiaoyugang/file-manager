<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2021-05-19 13:10:18
 * @LastEditors: sueRimn
 * @LastEditTime: 2021-05-19 14:46:21
 */

use Encore\FileManager\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('file', FileManagerController::class.'@index')->name('file-manager');
Route::delete('file/delete', FileManagerController::class.'@delete')->name('file-delete');
Route::post('file/upload', FileManagerController::class.'@upload')->name('file-upload');
Route::post('file/move', FileManagerController::class.'@move')->name('file-move');
Route::post('file/folder', FileManagerController::class.'@newFolder')->name('file-new-folder');
