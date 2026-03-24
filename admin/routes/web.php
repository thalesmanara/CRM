<?php

declare(strict_types=1);

use Revita\Crm\Controllers\AuthController;
use Revita\Crm\Controllers\CategoryController;
use Revita\Crm\Controllers\DashboardController;
use Revita\Crm\Controllers\UserController;

return [
    'GET' => [
        '/' => [AuthController::class, 'root'],
        '/login' => [AuthController::class, 'showLogin'],
        '/logout' => [AuthController::class, 'logout'],
        '/forgot-password' => [AuthController::class, 'showForgotPassword'],
        '/reset-password' => [AuthController::class, 'showResetPassword'],
        '/dashboard' => [DashboardController::class, 'index'],
        '/users' => [UserController::class, 'index'],
        '/users/create' => [UserController::class, 'createForm'],
        '/users/edit' => [UserController::class, 'editForm'],
        '/categories' => [CategoryController::class, 'index'],
        '/categories/create' => [CategoryController::class, 'createCategoryForm'],
        '/categories/edit' => [CategoryController::class, 'editCategoryForm'],
        '/subcategories/create' => [CategoryController::class, 'createSubcategoryForm'],
        '/subcategories/edit' => [CategoryController::class, 'editSubcategoryForm'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/forgot-password' => [AuthController::class, 'sendResetLink'],
        '/reset-password' => [AuthController::class, 'resetPassword'],
        '/users/store' => [UserController::class, 'store'],
        '/users/update' => [UserController::class, 'update'],
        '/users/delete' => [UserController::class, 'delete'],
        '/categories/store' => [CategoryController::class, 'storeCategory'],
        '/categories/update' => [CategoryController::class, 'updateCategory'],
        '/categories/delete' => [CategoryController::class, 'deleteCategory'],
        '/subcategories/store' => [CategoryController::class, 'storeSubcategory'],
        '/subcategories/update' => [CategoryController::class, 'updateSubcategory'],
        '/subcategories/delete' => [CategoryController::class, 'deleteSubcategory'],
    ],
];
