<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * NoAccess Controller
 * Handles access denied scenarios for unauthorized users
 * Created: 28/01/26
 */
class NoAccess extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Access Denied',
            'message' => 'You do not have permission to access this resource.',
            'user_role' => session()->get('role') ?? 'Unknown',
            'user_name' => session()->get('user_name') ?? 'Unknown User'
        ];
        
        return view('errors/access_denied', $data);
    }
}
