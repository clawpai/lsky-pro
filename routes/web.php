<?php

use App\Http\Controllers\SendImageController;
use App\Http\Controllers\SendThemeController;
use Illuminate\Support\Facades\Route;

// 依靠本地储存的符号链接，实现直接访问文件
// 服务器如果未找到静态资源(没有符号链接或者没有文件)，则会流转到路由，由程序进行处理
$regex = '^(?!admin).*?\.(?i:' . implode('|', array_values(AppService::getAllImageTypes())) . ')$';
Route::get('/{path}', SendImageController::class)->where('path', $regex);

Route::any('/{any}', SendThemeController::class)->where('any', '.*');