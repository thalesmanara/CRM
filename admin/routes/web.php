<?php

declare(strict_types=1);

use Revita\Crm\Controllers\AuthController;
use Revita\Crm\Controllers\CategoryController;
use Revita\Crm\Controllers\DashboardController;
use Revita\Crm\Controllers\PageController;
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
        '/pages' => [PageController::class, 'index'],
        '/pages/create' => [PageController::class, 'createForm'],
        '/pages/edit' => [PageController::class, 'editForm'],
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
        '/pages/store' => [PageController::class, 'store'],
        '/pages/update-meta' => [PageController::class, 'updateMeta'],
        '/pages/update-content' => [PageController::class, 'updateContent'],
        '/pages/add-field' => [PageController::class, 'addField'],
        '/pages/delete-field' => [PageController::class, 'deleteField'],
        '/pages/reorder-fields' => [PageController::class, 'reorderFields'],
        '/pages/delete' => [PageController::class, 'delete'],
        '/pages/rep-add-sub' => [PageController::class, 'repeaterAddSubfield'],
        '/pages/rep-del-sub' => [PageController::class, 'repeaterDeleteSubfield'],
        '/pages/rep-add-item' => [PageController::class, 'repeaterAddItem'],
        '/pages/rep-del-item' => [PageController::class, 'repeaterDeleteItem'],
        '/pages/rep-reorder-items' => [PageController::class, 'repeaterReorderItems'],
    ],
];
