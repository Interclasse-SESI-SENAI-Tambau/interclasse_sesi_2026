<?php
/**
 * Progressão automática de chaveamento: quando um confronto recebe resultado,
 * o vencedor avança para o confronto da próxima fase que corresponde ao seu
 * par (os dois confrontos são pareados pela ordem de cadastro dentro da
 * mesma chave/fase). Se o confronto da próxima fase ainda não existir, ele é
 * criado com o time 2 em aberto, aguardando o vencedor do par.
 */

/**
 * Avança o vencedor do confronto informado (se já tiver resultado) para o
 * confronto correspondente da próxima fase, criando-o caso ainda não exista.
 */
function avancarVencedor(PDO $pdo, int $conId): void
{
    $stmtCon = $pdo->prepare('SELECT CON_ID, FK_CHA_ID, FK_FAS_ID FROM CONFRONTOS WHERE CON_ID = :id');
    $stmtCon->execute(['id' => $conId]);
    $confronto = $stmtCon->fetch();
    if (!$confronto) {
        return;
    }

    $stmtRes = $pdo->prepare('SELECT FK_TIM_VENCEDOR_ID FROM RESULTADOS WHERE FK_CON_ID = :id');
    $stmtRes->execute(['id' => $conId]);
    $resultado = $stmtRes->fetch();
    if (!$resultado) {
        return;
    }
    $vencedor = (int) $resultado['FK_TIM_VENCEDOR_ID'];

    $stmtFase = $pdo->prepare('SELECT FAS_ORDEM FROM FASES WHERE FAS_ID = :id');
    $stmtFase->execute(['id' => $confronto['FK_FAS_ID']]);
    $faseAtual = $stmtFase->fetch();
    if (!$faseAtual) {
        return;
    }

    $stmtProxFase = $pdo->prepare('SELECT FAS_ID FROM FASES WHERE FAS_ORDEM = :ordem');
    $stmtProxFase->execute(['ordem' => (int) $faseAtual['FAS_ORDEM'] + 1]);
    $proximaFase = $stmtProxFase->fetch();
    if (!$proximaFase) {
        // Não há próxima fase cadastrada: este confronto é a final.
        return;
    }

    // Ordem de cadastro dos confrontos desta chave/fase, usada para parear irmãos
    // (jogo 1 + jogo 2 -> par 0, jogo 3 + jogo 4 -> par 1, e assim por diante).
    $stmtOrdem = $pdo->prepare(
        'SELECT CON_ID FROM CONFRONTOS WHERE FK_CHA_ID = :cha AND FK_FAS_ID = :fas ORDER BY CON_ID ASC'
    );
    $stmtOrdem->execute(['cha' => $confronto['FK_CHA_ID'], 'fas' => $confronto['FK_FAS_ID']]);
    $idsNaOrdem = array_map('intval', array_column($stmtOrdem->fetchAll(), 'CON_ID'));

    $posicao = array_search($conId, $idsNaOrdem, true);
    if ($posicao === false) {
        return;
    }

    $par = intdiv($posicao, 2);
    $posImpar = $par * 2;
    $posPar = $par * 2 + 1;

    if (!isset($idsNaOrdem[$posImpar]) || !isset($idsNaOrdem[$posPar])) {
        // Número ímpar de confrontos nesta fase: este jogo não tem par para
        // avançar automaticamente (precisa de intervenção manual do admin).
        return;
    }

    $origem1 = $idsNaOrdem[$posImpar];
    $origem2 = $idsNaOrdem[$posPar];
    $slot = $conId === $origem1 ? 1 : 2;

    $stmtProximo = $pdo->prepare(
        'SELECT CON_ID, FK_TIM_1_ID, FK_TIM_2_ID FROM CONFRONTOS
         WHERE FK_CON_ORIGEM_1_ID = :o1 AND FK_CON_ORIGEM_2_ID = :o2'
    );
    $stmtProximo->execute(['o1' => $origem1, 'o2' => $origem2]);
    $proximoConfronto = $stmtProximo->fetch();

    if (!$proximoConfronto) {
        $stmtInserir = $pdo->prepare(
            'INSERT INTO CONFRONTOS (FK_CHA_ID, FK_FAS_ID, FK_TIM_1_ID, FK_TIM_2_ID, FK_CON_ORIGEM_1_ID, FK_CON_ORIGEM_2_ID, CON_DATA, CON_HORA)
             VALUES (:cha, :fas, :t1, :t2, :o1, :o2, NULL, NULL)'
        );
        $stmtInserir->execute([
            'cha' => $confronto['FK_CHA_ID'],
            'fas' => $proximaFase['FAS_ID'],
            't1' => $slot === 1 ? $vencedor : null,
            't2' => $slot === 2 ? $vencedor : null,
            'o1' => $origem1,
            'o2' => $origem2,
        ]);
        return;
    }

    $campo = $slot === 1 ? 'FK_TIM_1_ID' : 'FK_TIM_2_ID';
    $valorAtual = $proximoConfronto[$campo] !== null ? (int) $proximoConfronto[$campo] : null;

    if ($valorAtual === $vencedor) {
        return;
    }

    if ($valorAtual !== null) {
        // O vencedor deste confronto mudou (edição de um resultado já
        // registrado): invalida tudo que já avançou a partir do valor antigo.
        resetarProgressao($pdo, (int) $proximoConfronto['CON_ID']);
    }

    $stmtAtualizar = $pdo->prepare("UPDATE CONFRONTOS SET $campo = :vencedor WHERE CON_ID = :id");
    $stmtAtualizar->execute(['vencedor' => $vencedor, 'id' => $proximoConfronto['CON_ID']]);
}

/**
 * Remove o resultado do confronto informado (se houver) e limpa, em cascata,
 * qualquer progressão que já tenha sido feita a partir dele — usado quando
 * uma edição de resultado muda o vencedor de um confronto já avançado.
 */
function resetarProgressao(PDO $pdo, int $conId): void
{
    $pdo->prepare('DELETE FROM RESULTADOS WHERE FK_CON_ID = :id')->execute(['id' => $conId]);

    $stmtProximo = $pdo->prepare(
        'SELECT CON_ID, FK_CON_ORIGEM_1_ID FROM CONFRONTOS
         WHERE FK_CON_ORIGEM_1_ID = :id OR FK_CON_ORIGEM_2_ID = :id2'
    );
    $stmtProximo->execute(['id' => $conId, 'id2' => $conId]);
    $proximo = $stmtProximo->fetch();

    if (!$proximo) {
        return;
    }

    $campo = (int) $proximo['FK_CON_ORIGEM_1_ID'] === $conId ? 'FK_TIM_1_ID' : 'FK_TIM_2_ID';
    $pdo->prepare("UPDATE CONFRONTOS SET $campo = NULL WHERE CON_ID = :id")
        ->execute(['id' => $proximo['CON_ID']]);

    resetarProgressao($pdo, (int) $proximo['CON_ID']);
}

/**
 * Exclui um confronto com segurança: remove primeiro seu resultado (se
 * houver) e desfaz, em cascata, qualquer progressão de chaveamento já feita
 * a partir dele (via resetarProgressao), para então poder apagar a linha.
 */
function excluirConfronto(PDO $pdo, int $conId): void
{
    resetarProgressao($pdo, $conId);
    $pdo->prepare('DELETE FROM CONFRONTOS WHERE CON_ID = :id')->execute(['id' => $conId]);
}

/**
 * Exclui um time: apaga primeiro todos os confrontos em que ele participa
 * (com sua progressão em cascata) e então remove o time.
 */
function excluirTime(PDO $pdo, int $timId): void
{
    $stmt = $pdo->prepare('SELECT CON_ID FROM CONFRONTOS WHERE FK_TIM_1_ID = :id OR FK_TIM_2_ID = :id2');
    $stmt->execute(['id' => $timId, 'id2' => $timId]);
    foreach ($stmt->fetchAll() as $row) {
        excluirConfronto($pdo, (int) $row['CON_ID']);
    }

    $pdo->prepare('DELETE FROM TIMES WHERE TIM_ID = :id')->execute(['id' => $timId]);
}

/**
 * Exclui uma turma: apaga primeiro todos os times vinculados a ela (com seus
 * confrontos e progressão em cascata) e então remove a turma.
 */
function excluirTurma(PDO $pdo, int $turId): void
{
    $stmt = $pdo->prepare('SELECT TIM_ID FROM TIMES WHERE FK_TUR_ID = :id');
    $stmt->execute(['id' => $turId]);
    foreach ($stmt->fetchAll() as $row) {
        excluirTime($pdo, (int) $row['TIM_ID']);
    }

    $pdo->prepare('DELETE FROM TURMAS WHERE TUR_ID = :id')->execute(['id' => $turId]);
}
