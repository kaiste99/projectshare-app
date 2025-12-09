<?php



namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): string
    {
        return $this->view('pages/home', [
            'title' => 'Project Communication Made Simple',
            'metaDescription' => 'Share project plans, track acknowledgements, and keep stakeholders informed. The better way to communicate with clients, tenants, and contractors.',
            'bodyClass' => 'page-home',
        ]);
    }
}
