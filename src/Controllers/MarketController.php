<?php
namespace App\Controllers;

use App\Repositories\MarketRepository;

class MarketController extends BaseController {
    private $marketRepo;

    public function __construct() {
        $this->marketRepo = new MarketRepository();
    }

    public function index() {
        $markets = $this->marketRepo->getStats();
        
        $this->render('markets', [
            'title' => 'Supermercados Monitorados',
            'markets' => $markets
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = trim($_POST['name'] ?? '');
            $logoUrl = trim($_POST['logo_url'] ?? '');
            $websiteUrl = trim($_POST['website_url'] ?? '');
            $active = isset($_POST['active']) ? 1 : 0;

            if (empty($name)) {
                $this->redirect('markets');
            }

            $data = [
                'name' => $name,
                'logo_url' => $logoUrl ?: null,
                'website_url' => $websiteUrl ?: null,
                'active' => $active
            ];

            if ($id > 0) {
                $data['id'] = $id;
            }

            $this->marketRepo->save($data);
        }

        $this->redirect('markets');
    }

    public function toggle() {
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $this->marketRepo->toggleActive($id);
        }
        $this->redirect('markets');
    }
}
