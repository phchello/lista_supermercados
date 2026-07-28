<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lista_supermercados');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Em caso de falha de conexão, podemos tentar criar o banco de dados e as tabelas
                // se for a primeira execução do sistema.
                try {
                    $dsnNoDb = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                    $tempPdo = new PDO($dsnNoDb, DB_USER, DB_PASS);
                    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    
                    // Conecta agora ao banco recém-criado
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                    
                    // Importa o schema se o arquivo existir
                    $schemaFile = dirname(__DIR__) . '/database/schema.sql';
                    if (file_exists($schemaFile)) {
                        $sql = file_get_contents($schemaFile);
                        // Executa múltiplos statements (separados por ;)
                        self::$instance->exec($sql);
                    }
                } catch (PDOException $e2) {
                    die("Falha na conexão com o banco de dados: " . $e2->getMessage());
                }
            }
        }
        return self::$instance;
    }
}
