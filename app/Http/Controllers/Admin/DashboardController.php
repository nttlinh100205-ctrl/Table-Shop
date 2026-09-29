<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Trang dashboard dành cho Admin.
     */
    public function index()
    {
        $stats = [
            'products'   => Product::count(),
            'categories' => Category::count(),
            'users'      => User::where('role', 'user')->count(),
            'admins'     => User::where('role', 'admin')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
