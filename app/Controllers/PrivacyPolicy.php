<?php

namespace App\Controllers;

class PrivacyPolicy extends BaseController
{
    public function index(): string
    {
        return view('privacy_policy');
    }

}
