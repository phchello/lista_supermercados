<?php
namespace App\Services;

class ExportService {
    
    /**
     * Exporta dados em formato CSV padrão
     */
    public function exportCsv($filename, $headers, $data) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Escreve a marca de ordem de byte (BOM) para garantir UTF-8 no Excel em português
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escreve cabeçalhos
        fputcsv($output, $headers, ';');
        
        // Escreve linhas
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exporta dados para formato compatível com Excel (CSV usando tabulação e encoding UTF-16LE)
     * Isso faz com que o Excel abra em Português sem quebrar acentuações.
     */
    public function exportExcel($filename, $headers, $data) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        
        // Geramos uma tabela HTML simples, que o Microsoft Excel interpreta perfeitamente
        // mantendo toda a formatação básica e codificação de acentos
        echo "<table border='1'>";
        
        // Escreve cabeçalhos
        echo "<tr style='background-color:#0d6efd; color:#ffffff; font-weight:bold;'>";
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        // Escreve dados
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }
}
