<?php
namespace App\Controllers;

use App\Services\OcrService;
use App\Repositories\MarketRepository;

class ReceiptController extends BaseController {
    private $ocrService;
    private $marketRepo;

    public function __construct() {
        $this->ocrService = new OcrService();
        $this->marketRepo = new MarketRepository();
    }

    public function index() {
        $markets = $this->marketRepo->all(true);
        $this->render('upload_receipt', [
            'title' => 'Importar Cupom Fiscal',
            'markets' => $markets
        ]);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Caso 1: Importação via XML NFC-e
            if (isset($_FILES['xml_file']) && $_FILES['xml_file']['error'] == UPLOAD_ERR_OK) {
                $xmlContent = file_get_contents($_FILES['xml_file']['tmp_name']);
                
                $result = $this->ocrService->parseNfceXml($xmlContent);
                
                if ($result['success']) {
                    $message = "Cupom importado com sucesso! " . count($result['items']) . " preços atualizados no mercado " . $result['market_name'] . ".";
                    $this->render('upload_receipt', [
                        'title' => 'Importar Cupom Fiscal',
                        'markets' => $this->marketRepo->all(true),
                        'success' => $message
                    ]);
                    exit;
                } else {
                    $this->render('upload_receipt', [
                        'title' => 'Importar Cupom Fiscal',
                        'markets' => $this->marketRepo->all(true),
                        'error' => "Erro ao processar XML: " . $result['error']
                    ]);
                    exit;
                }
            }
        }
        
        $this->redirect('receipt/upload');
    }
}
