<?php

// Função para buscar os computadores (hostname) da API do GLPI
function getComputers($url, $session_token, $app_token) {
    $headers = [
        'Content-Type: application/json',
        'Session-Token: ' . $session_token,
        'App-Token: ' . $app_token
    ];

    $ch = curl_init($url . '/Computer/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro ao buscar ativos: ' . curl_error($ch);
        return [];
    }

    $response_data = json_decode($response, true);
    curl_close($ch);

    return isset($response_data) ? $response_data : [];
}

// Busca os computadores/ativos
$computers = getComputers("http://endereco do seu glpi/apirest.php", "sua session token", "seu app token");

// Inicializa o cURL para buscar usuários da API do GLPI
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "http://endereco do seu glpi/apirest.php/user",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "App-Token: seu app token",
        "Authorization: seu authorization token",
        "Content-Type: application/json",
        "Session-Token: sua session token"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    // Decodifica o JSON recebido da API
    $users = json_decode($response, true);
}

// Lidar com a abertura do chamado
$ticketNumber = null; // Variável para armazenar o número do ticket
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entityId = $_POST['entityId']; // ID da entidade fornecida pelo usuário
    $userIdRequester = $_POST['userIdRequester']; // ID do usuário selecionado
    $title = $_POST['title'];
    $description = $_POST['description'];
    $hostname = $_POST['hostname']; // Recebe o hostname (computador) digitado pelo usuário
    $telefone = $_POST['telefone']; // Recebe o telefone digitado pelo usuário

    // Obtém o nome do usuário a partir do ID
    $userName = '';
    foreach ($users as $user) {
        if ($user['id'] == $userIdRequester) {
            $userName = $user['name'];
            break;
        }
    }
    
    // Defina as variáveis do setor
    $setorfield = ''; // Preencha conforme necessário
    $computer_id = ''; // ID do computador a ser vinculado
    $request_source_id = 8; // ID da Origem da Requisição criada no GLPI
    $email = ''; // E-mail do usuário
    $observador = ''; // E-mail do observador, se necessário

    // Validação dos campos
    if (empty($entityId) || empty($userIdRequester) || empty($title) || empty($description) || empty($hostname) || empty($telefone)) {
        $error = "Todos os campos devem ser preenchidos.";
    } else {
        // Adiciona o hostname, telefone e requerente na descrição
        $description .= "\n\n<br><b>hostname:</b> " . $hostname;
        $description .= "\n<br><b>Telefone:</b> " . $telefone;
        $description .= "\n<br><b>Requerente:</b> " . $userName; // Adiciona o requerente aqui

        // Dados para abrir um novo chamado
        $ticket_data = [
            'input' => [
                'name' => $title,
                'content' => $description,
                'type' => 1, // Tipo do ticket
                'status' => 1, // Status do ticket
                'setorfield' => $setorfield,
                'entities_id' => intval($entityId), // Agora o ID da entidade é enviado corretamente
                'requerentefield' => intval($userIdRequester), // Adiciona o requerente aqui
                'telefonefield' => $telefone, // Adiciona o telefone aqui
                'items_id' => [
                    'Computer' => [$hostname] // Vincula o hostname (computador) ao ticket
                ],
                'requesttypes_id' => $request_source_id,
                '_users_id_requester' => intval($userIdRequester), // Vincula o ID do usuário como requerente
                '_users_id_requester_notif' => [
                    'use_notification' => 1,
                    'alternative_email' => [$email] // E-mail fornecido pelo usuário
                ],
                '_users_id_observer' => 0,
                '_users_id_observer_notif' => [
                    'use_notification' => 1,
                    'alternative_email' => [$observador]
                ],
            ]
        ];

        // Inicializa o cURL para abrir um novo chamado
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://endereco do seu glpi/apirest.php/Ticket",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($ticket_data),
            CURLOPT_HTTPHEADER => [
                "App-Token: seu app token",
                "Authorization: seu authorization token",
                "Content-Type: application/json",
                "Session-Token: sua session token"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $error = "Erro ao abrir o chamado: " . $err;
        } else {
            $responseData = json_decode($response, true);
            if (isset($responseData['id'])) {
                $ticketNumber = $responseData['id']; // Captura o ID do ticket aberto
                $success = "Chamado aberto com sucesso! Número do ticket: " . $ticketNumber;
            } else {
                $error = "Erro ao abrir o chamado: resposta inesperada.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abertura de Chamado - GLPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .suggestion-list {
            display: none;
            position: absolute;
            width: 100%;
            max-height: 150px;
            overflow-y: auto;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            z-index: 1000;
        }

        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center"><div class="text-center">
    <a href="#" onclick="location.reload();">
        <img src="sua logo" alt="Logo GLPI" />
    </a>
</div></h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="entityId" class="form-label">ID da Entidade</label>
                <input type="number" class="form-control" id="entityId" name="entityId" required>
            </div>
            
            <div class="mb-3">
                <label for="userSearch" class="form-label">Pesquisar Usuário</label>
                <div class="position-relative">
                    <input type="text" class="form-control" id="userSearch" required autocomplete="off" onkeyup="searchUsers(this.value)">
                    <div class="suggestion-list" id="suggestionList"></div>
                </div>
            </div>

            <input type="hidden" id="userIdRequester" name="userIdRequester">

            <div class="mb-3">
                <label for="title" class="form-label">Título</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="hostname" class="form-label">hostname</label>
                <input type="text" class="form-control" id="hostname" name="hostname" required>
            </div>

            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" required>
            </div>

            <button type="submit" class="btn btn-primary">Abrir Chamado</button>
        </form>
    </div>

    <script>
        function searchUsers(query) {
            const users = <?php echo json_encode($users); ?>;
            const suggestionList = document.getElementById('suggestionList');
            suggestionList.innerHTML = '';
            suggestionList.style.display = 'none';

            if (query.length === 0) return;

            const filteredUsers = users.filter(user => user.name.toLowerCase().includes(query.toLowerCase()));

            if (filteredUsers.length === 0) return;

            filteredUsers.forEach(user => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = user.name;
                item.onclick = function() {
                    document.getElementById('userSearch').value = user.name;
                    document.getElementById('userIdRequester').value = user.id;
                    suggestionList.style.display = 'none';
                };
                suggestionList.appendChild(item);
            });

            suggestionList.style.display = 'block';
        }
    </script>
</body>
</html>
