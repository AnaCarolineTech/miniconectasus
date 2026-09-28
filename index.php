<?php
/**
 * PROJETO: CONECTASUS - MÓDULO DE TRIAGEM E AUDITORIA DE IMUNIZAÇÃO
 * Foco: Demonstrar POO, Consultas SQL (Simuladas via PDO), Lógica de Saúde e Diagnóstico.
 */

class CarteiraVacinacao {
    private $nomeCidadao;
    private $idade;
    private $historicoVacinas = [];
    private $logsErros = []; 

    public function __construct($nome, $idade) {
        $this->nomeCidadao = $nome;
        $this->idade = $idade;
    }

    // SIMULAÇÃO DE BANCO DE DADOS (MariaDB / SQL exigido na vaga)
    public function buscarHistoricoDoBanco($prontuarioId) {
        // Exemplo da Query SQL exata que seria executada no banco de dados MariaDB:
        $sqlQueryExemplo = "SELECT v.nome, p.doses_aplicadas, v.doses_obrigatorias 
                            FROM paciente_vacinas p 
                            INNER JOIN vacinas v ON p.vacina_id = v.id 
                            WHERE p.prontuario_id = :prontuarioId";
        
        // Simulando a resposta estruturada que viria do banco de dados após o execute()
        $resultadoBancoMariaDB = [
            ["nome" => "Covid-19 Bivalente", "doses" => 2, "obrigatorias" => 2],
            ["nome" => "Influenza (Gripe)", "doses" => 1, "obrigatorias" => 1],
            ["nome" => "Febre Amarela",     "doses" => 0, "obrigatorias" => 1],
            ["nome" => "Tríplice Viral",    "doses" => 3, "obrigatorias" => 2] // Inconsistência injetada
        ];

        // Alimentando a classe com os dados vindos da "Consulta SQL"
        foreach ($resultadoBancoMariaDB as $linha) {
            $this->adicionarVacina($linha["nome"], $linha["doses"], $linha["obrigatorias"]);
        }
    }

    private function adicionarVacina($nome, $dosesTomadas, $dosesObrigatorias) {
        if ($dosesTomadas > $dosesObrigatorias) {
            $this->logsErros[] = "ALERTA [SLA-Triagem]: Inconsistência no imunizante '$nome'. Doses aplicadas ($dosesTomadas) excedem o limite obrigatório ($dosesObrigatorias).";
        }

        $this->historicoVacinas[] = [
            "nome" => $nome,
            "doses" => $dosesTomadas,
            "obrigatorias" => $dosesObrigatorias
        ];
    }

    public function calcularProgresso() {
        $totalObrigatorias = 0;
        $totalTomadas = 0;

        foreach ($this->historicoVacinas as $vacina) {
            $totalObrigatorias += $vacina["obrigatorias"];
            $totalTomadas += $vacina["doses"];
        }

        if ($totalObrigatorias == 0) return 0;
        // Limitando a barra em 100% mesmo se houver erro de inserção excessiva
        $calculo = ($totalTomadas / $totalObrigatorias) * 100;
        return $calculo > 100 ? 100 : $calculo;
    }

    public function getNome() { return $this->nomeCidadao; }
    public function getIdade() { return $this->idade; }
    public function getVacinas() { return $this->historicoVacinas; }
    public function getLogs() { return $this->logsErros; }
}

// Instanciando a Classe
$paciente = new CarteiraVacinacao("Carlos Silva", 24);

// Executando o método que simula a busca via Query SQL no MariaDB
$paciente->buscarHistoricoDoBanco(1042); 

$porcentagem = $paciente->calcularProgresso();
$logsSistema = $paciente->getLogs();
?>

<!-- FRONT-END (HTML5 + TAILWIND CSS) -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Cidadão - Validação de Dados SaaS</title>
    <script src="https://tailwindcss.com" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800">

    <header class="bg-blue-700 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-wide">ConectaSUS <span class="font-light text-blue-200">SaaS Municipal</span></h1>
            <span class="text-xs bg-blue-800 px-3 py-1 rounded border border-blue-500 font-mono">Ambiente de Triagem (SLA Ativo)</span>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- CARD 1: Dados do Cidadão -->
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit">
            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Dados Básicos do Prontuário</h2>
            <div class="space-y-2">
                <p class="text-lg font-bold text-slate-900"><?= $paciente->getNome() ?></p>
                <p class="text-sm text-slate-600">Idade: <?= $paciente->getIdade() ?> anos</p>
                <p class="text-xs text-slate-400">ID do Prontuário (DB): #1042</p>
            </div>
            
            <div class="mt-6 pt-4 border-t border-slate-100">
                <div class="flex justify-between text-xs font-medium mb-1">
                    <span>Progresso de Cobertura</span>
                    <span><?= round($porcentagem) ?>%</span>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-full" style="width: <?= $porcentagem ?>%"></div>
                </div>
            </div>
        </section>

        <!-- CARD 2 & 3: Tabela de Dados e Painel de Diagnóstico -->
        <section class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold text-sm text-slate-700">
                    Histórico Clínico (Consultado via Banco de Dados)
                </div>
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-100/50 text-slate-500 text-xs font-semibold border-b border-slate-200">
                            <th class="p-3 pl-6">Imunizante</th>
                            <th class="p-3 text-center">Doses Aplicadas / Obrigatórias</th>
                            <th class="p-3 pr-6 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($paciente->getVacinas() as $vacina): ?>
                        <tr>
                            <td class="p-3 pl-6 font-medium"><?= $vacina["nome"] ?></td>
                            <td class="p-3 text-center text-slate-600"><?= $vacina["doses"] ?> / <?= $vacina["obrigatorias"] ?></td>
                            <td class="p-3 pr-6 text-right">
                                <?php if ($vacina["doses"] == 0): ?>
                                    <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">Pendente</span>
                                <?php elseif ($vacina["doses"] > $vacina["obrigatorias"]): ?>
                                    <span class="text-xs px-2 py-0.5 rounded bg-purple-100 text-purple-700 font-bold">Inconsistente ⚠️</span>
                                <?php else: ?>
                                    <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Regularizado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Console de Diagnóstico -->
            <div class="bg-slate-900 text-slate-200 rounded-xl shadow-lg border border-slate-800 p-6 font-mono text-xs">
                <div class="flex justify-between items-center mb-3 border-b border-slate-800 pb-2 text-slate-400 font-sans">
                    <span class="font-bold uppercase tracking-wider text-amber-500">🛡️ Console de Investigação & Suporte L2</span>
                    <span>Fila: Bugs_Validacao</span>
                </div>
                
                <?php if (empty($logsSistema)): ?>
                    <p class="text-green-400">✓ Nenhum comportamento inesperado detectado.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <p class="text-amber-400 font-bold font-sans text-sm">Falhas encontradas na Consulta de Prontuário:</p>
                        <?php foreach ($logsSistema as $log): ?>
                            <p class="bg-slate-950 p-2.5 rounded border-l-4 border-amber-500 text-slate-300"><?= $log ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </section>
    </main>

</body>
</html>



