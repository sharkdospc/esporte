<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$esportes = [
    ['id' => 1, 'nome' => 'futebol', 'jogadores' => 11, 'olimpico' => true],
    ['id' => 2, 'nome' => 'basquete', 'jogadores' => 5, 'olimpico' => true],
    ['id' => 3, 'nome' => 'vôlei', 'jogadores' => 6, 'olimpico' => true],
    ['id' => 4, 'nome' => 'tênis', 'jogadores' => 2, 'olimpico' => true],
    ['id' => 5, 'nome' => 'natação', 'jogadores' => 1, 'olimpico' => true]
];

$app->get('/esportes', function ($request, $response) use ($esportes) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;

    if ($nome) {
        $filtrados = array_filter(
            $esportes,
            fn($item) =>
            str_contains(mb_strtolower($item['nome']), mb_strtolower($nome))
        );
        $response->getBody()->write(json_encode(array_values($filtrados)));
    } else {
        $response->getBody()->write(json_encode($esportes));
    }

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/esportes/{id}', function ($request, $response, $args) use ($esportes) {
    $id = (int) $args['id'];
    
    $esporteEncontrado = null;
    foreach ($esportes as $esporte) {
        if ($esporte['id'] === $id) {
            $esporteEncontrado = $esporte;
            break;
        }
    }
    
    if ($esporteEncontrado) {
        $response->getBody()->write(json_encode($esporteEncontrado));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(['error' => 'Esporte não encontrado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

$app->post('/esportes', function ($request, $response) use (&$esportes) {
    $data = $request->getParsedBody();
    
    if (!isset($data['nome']) || empty($data['nome'])) {
        $response->getBody()->write(json_encode(['error' => 'Campo "nome" é obrigatório']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    
    $novoId = count($esportes) + 1;
    $novoEsporte = [
        'id' => $novoId,
        'nome' => $data['nome'],
        'jogadores' => isset($data['jogadores']) ? (int) $data['jogadores'] : 1,
        'olimpico' => isset($data['olimpico']) ? (bool) $data['olimpico'] : false
    ];
    
    $esportes[] = $novoEsporte;
    
    $response->getBody()->write(json_encode($novoEsporte));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->run();