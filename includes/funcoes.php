<?php
/**
 * Funções auxiliares utilizadas em todo o sistema.
 */

function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function formatarData(?string $dataIso): string
{
    if (!$dataIso) {
        return '';
    }
    $partes = explode('-', $dataIso);
    if (count($partes) !== 3) {
        return e($dataIso);
    }
    return sprintf('%02d/%02d/%04d', (int) $partes[2], (int) $partes[1], (int) $partes[0]);
}

function formatarHora(?string $hora): string
{
    if (!$hora) {
        return '';
    }
    return substr($hora, 0, 5);
}

function definirMensagem(string $tipo, string $texto): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensagem'] = ['tipo' => $tipo, 'texto' => $texto];
}

function obterMensagem(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function inteiroValido($valor): bool
{
    return $valor !== null && $valor !== '' && filter_var($valor, FILTER_VALIDATE_INT) !== false;
}

function dataValida(?string $data): bool
{
    if (!$data) {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

function horaValida(?string $hora): bool
{
    if (!$hora) {
        return false;
    }
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora);
}

/**
 * Verifica se uma PDOException representa violação de FK (ON DELETE RESTRICT)
 * ou de UNIQUE, para exibirmos mensagens amigáveis em vez do erro cru do MySQL.
 */
function eErroDeIntegridade(PDOException $e): bool
{
    return $e->getCode() === '23000';
}
