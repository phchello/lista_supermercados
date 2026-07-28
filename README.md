# Sistema de Comparação de Preços de Supermercados - MercadoPoupe

Este é um sistema desenvolvido em PHP Orientado a Objetos utilizando a arquitetura MVC (Model-View-Controller) com Repositories e Services, sem a necessidade de frameworks pesados, rodando perfeitamente no ambiente padrão do XAMPP no Windows.

## Funcionalidades Principais

1. **Dashboard Moderno**: Resumo de estatísticas, detecção automática de promoções em tempo real e gráficos de evolução.
2. **Comparador Inteligente**: Permite criar listas de compras e calcula instantaneamente a diferença de preços.
3. **Inteligência de Compra (Roteiro Otimizado)**: Cria um roteiro detalhado dividindo os itens por supermercado para gerar a maior economia possível.
4. **Importador de Cupons Fiscais (XML NFC-e e OCR)**:
   - Permite upload do XML de cupons fiscais para cadastro automático de produtos e registro imediato de preços.
   - Integração com **Tesseract.js** no frontend para processamento OCR local e seguro no navegador a partir de fotos/PDFs.
5. **Normalização Inteligente de Nomes**: Identifica produtos duplicados sem EAN por análise estatística de similaridade de tokens e medidas físicas.
6. **Agendamento de Coleta (Cron)**: Script pronto para disparar a sincronização programada dos preços.
7. **Relatórios**: Exportação em CSV, Excel (.xls) e suporte nativo à impressão do navegador configurada com estilos CSS `@media print`.

---

## Como Instalar e Rodar (XAMPP no Windows)

1. **Mover a pasta**:
   Certifique-se de que a pasta `lista_supermercados` esteja dentro do diretório `C:\xampp\htdocs\`.
   A estrutura deve ficar: `C:\xampp\htdocs\lista_supermercados\`.

2. **Iniciar o Apache e o MySQL**:
   - Abra o painel de controle do XAMPP (`XAMPP Control Panel`).
   - Clique em **Start** ao lado do Apache.
   - Clique em **Start** ao lado do MySQL.

3. **Criação do Banco de Dados**:
   - O sistema possui um mecanismo de **auto-setup**.
   - Basta acessar o sistema pelo navegador, e o banco `lista_supermercados` será criado e populado automaticamente com dados de sementes (seed) de exemplo.
   - Caso prefira importar manualmente, o script está localizado em `database/schema.sql`.

4. **Acessar o Sistema**:
   Abra seu navegador e acesse diretamente:
   [http://localhost/lista_supermercados/](http://localhost/lista_supermercados/)
   O sistema irá redirecionar automaticamente para a pasta `/public/` onde o painel iniciará.


---

## Estrutura do Código Fonte

- `/config/`: Arquivos de conexões e configurações de ambiente.
- `/database/`: Esquemas de banco e scripts de população.
- `/src/Controllers/`: Controladores da arquitetura MVC.
- `/src/Repositories/`: Camada de acesso direto ao banco de dados utilizando PDO com SQL bruto e prepared statements.
- `/src/Services/`: Camada contendo a inteligência de negócios (Otimização de listas, parsing de OCR, normalização de nomes).
- `/src/Views/`: Interfaces visuais separadas por blocos reutilizáveis.
- `/public/`: Raiz de acesso web contendo o roteador principal, CSS, imagens e JavaScripts.
- `/cron/`: Script PHP CLI para agendamentos.

---

## Agendamento automático (Windows Task Scheduler)

Para automatizar a busca de preços às 08:00 e 20:00 (ou horários desejados):
1. Abra o **Agendador de Tarefas** (Task Scheduler) do Windows.
2. Crie uma nova tarefa básica.
3. Configure o gatilho para os horários que desejar.
4. Na ação, selecione **Iniciar um Programa**:
   - No programa/script coloque o executável do PHP do XAMPP: `C:\xampp\php\php.exe`
   - Nos argumentos adicione o caminho do price collector: `C:\xampp\htdocs\lista_supermercados\cron\price_collector.php`
