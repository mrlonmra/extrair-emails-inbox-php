<?php

// EXEMPLO DE COMO COLOCAR NO ARQUIVO TXT - imap.kinghost.net:993/imap/ssl, suporte@ileva.com.br, FD5gd5

// Verifica se o formulário foi submetido
if (isset($_POST['submitFlag'])) {

    // Verifica se um arquivo foi enviado e não há erros no upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Processa o arquivo enviado
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        $lines = explode("\n", $fileContent);

        // Itera sobre as linhas do arquivo
        foreach ($lines as $line) {
            $credentials = explode(',', $line);

            // Extrai informações de servidor IMAP de cada linha
            $mailbox = trim($credentials[0]);
            $login = trim($credentials[1]);
            $password = trim($credentials[2]);

            // Processa os e-mails usando as credenciais IMAP
            processEmails($mailbox, $login, $password);
        }
    } else {
        echo 'Erro ao processar o arquivo.'; // Mensagem de erro se houver falha no processamento do arquivo
    }
}

// Função para extrair e-mails de um determinado conteúdo
function extractEmail($content)
{
    $regexp = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
    preg_match_all($regexp, $content, $m);
    return isset($m[0]) ? $m[0] : array();
}

// Função para obter texto de endereço a partir de um objeto de e-mail
function getAddressText(&$emailList, &$nameList, $addressObject)
{
    $emailList = '';
    $nameList = '';
    foreach ($addressObject as $object) {
        $emailList .= ';';
        if (isset($object->personal)) {
            $emailList .= $object->personal;
        }
        $nameList .= ';';
        if (isset($object->mailbox) && isset($object->host)) {
            $nameList .= $object->mailbox . "@" . $object->host;
        }
    }
    $emailList = ltrim($emailList, ';');
    $nameList = ltrim($nameList, ';');
}

// Função para processar uma mensagem de e-mail
function processMessage($mbox, $messageNumber)
{
    echo $messageNumber;
    // Obtém o cabeçalho da mensagem e converte para array
    $header = imap_rfc822_parse_headers(imap_fetchheader($mbox, $messageNumber));
    $fromEmailList = '';
    $fromNameList = '';
    if (isset($header->from)) {
        getAddressText($fromEmailList, $fromNameList, $header->from);
    }
    $toEmailList = '';
    $toNameList = '';
    if (isset($header->to)) {
        getAddressText($toEmailList, $toNameList, $header->to);
    }
    $body = imap_fetchbody($mbox, $messageNumber, 1);
    $bodyEmailList = implode(';', extractEmail($body));
    print_r(
        ',' . $fromEmailList . ',' . $fromNameList
        . ',' . $toEmailList . ',' . $toNameList
        . ',' . $bodyEmailList . "\n"
    );
}

// Função para processar e-mails de um servidor IMAP
function processEmails($mailbox, $login, $password)
{
    // Define a quantidade máxima de e-mails a serem processados
    define("MAX_EMAIL_COUNT", isset($_POST['maxcount']) ? intval($_POST['maxcount']) : 10);

    // Abre a caixa de correio IMAP
    if (!$mbox = imap_open("{{$mailbox}}", $login, $password)) {
        die('NÃO CONSEGUI CONECTAR NO EMAIL, DEU RUIM!!!');
    }

    // Obtém informações sobre a caixa de correio
    if ($hdr = imap_check($mbox)) {
        $msgCount = $hdr->Nmsgs;
    } else {
        echo "Falha ao obter e-mails";
        exit;
    }

    echo "<pre>";
    echo 'QUANTIDADE DE EMAIL ENCONTRADOS=' . $msgCount . "\n\n\n";
    echo "número do registro,lista de e-mails do remetente,lista de nomes do remetente,lista de e-mails do destinatário,lista de nomes do destinatário,e-mails extraídos do corpo\n";

    // Itera sobre as mensagens de e-mail
    for ($X = 1; $X <= min($msgCount, MAX_EMAIL_COUNT); $X++) {
        processMessage($mbox, $X);
    }
    echo "</pre>";

    // Fecha a conexão com a caixa de correio IMAP
    imap_close($mbox);
}

?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" accept=".txt">
    <select name="maxcount" id="maxcount">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="30">30</option>
        <option value="40">40</option>
        <option value="50">50</option>
        <option value="60">60</option>
        <option value="70">70</option>
        <option value="80">80</option>
        <option value="90">90</option>
        <option value="100">100</option>
        <!-- Adicione mais opções conforme necessário -->
    </select>
    <input type="submit" name="submitFlag" value="PEGA REX!">
</form>
