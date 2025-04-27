<?php

namespace App\Controllers;

use App\Controllers\BaseController; // Corrected namespace

class ClientsController extends BaseController {

    public function index() {
        // Load the view for the clients page
        $this->view('clients/index');
    }

    // Add other methods for client-related actions if needed
    // e.g., freelance(), main_employment() if those sub-pages are required

}
