<?php


$msg_username_invalido = "Erro: o campo usuário deve começar com letra ou com '_'. "
                         . "Os outros caracteres podem ser letras, números ou '_'. "
                         . "O comprimento do campo é de 8 a 32 caracteres.";

function is_valid_username(string $admin):bool {
    if (preg_match('/^[A-Za-z_][A-Za-z0-9_]{7,31}$/', $admin)) {
        return true;
    } else {
        return false;
    }
}


$msg_nome_invalido = "Erro: o campo nome não deve ser vazio.";

function is_valid_nome(string $nome):bool {
    return strlen($nome) > 0;
}


/* extrai somente os números de um campo, descartando o resto */
function extract_digits(string $s):string {
    return preg_replace('/[^0-9]/', '', $s);
}


/** consultar o site da receita federal:
        http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/funcoes.js
    para mais informações sobre validação de CPF.
*/
$msg_cpf_invalido = "Erro: o campo CPF deve ser preenchido da forma '###.###.###-##', onde '#' é um dígito."
                  . "O CPF fornecido é inválido.";

function is_valid_cpf(string $cpf):bool {
    $cpf = extract_digits($cpf);
    
    if (strlen($cpf) != 11) {
        return false;
    }
    if ($cpf == "00000000000") { // tem no código ai em cima da receita federal...
        return false;
    }

    /* checar o primeiro dígito de verificação */
    $checksum = 0;
    for ($i = 0; $i < 9; $i++) {
        $checksum += (int)$cpf[$i] * (10 - $i);
    }
    $checksum = (($checksum * 10) % 11) % 10;
    if ($checksum != (int)$cpf[9]) {
        return false;
    }

    /* checar o segundo dígito de verificação */
    $checksum = 0;
    for ($i = 0; $i < 10; $i++) {
        $checksum += (int)$cpf[$i] * (11 - $i);
    }
    $checksum = (($checksum * 10) % 11) % 10;
    if ($checksum != (int)$cpf[10]) {
        return false;
    }

    return true;
}


/**
    validação de CNPJ:
      http://www.macoratti.net/alg_cnpj.htm
    (algoritmo usado aqui é matematicamente igual)
*/

$msg_cnpj_invalido = "Erro: o campo CNPJ deve ser preenchido da forma '##.###.###/####-##', onde '#' é um dígito."
                   . "O CNPJ fornecido é inválido.";

function is_valid_cnpj(string $cnpj):bool {
    $cnpj = extract_digits($cnpj);
    
    if (strlen($cnpj) != 14) {
        return false;
    }

    /* checar o primeiro dígito de verificação */
    $checksum = 5*(int)$cnpj[0] + 4*(int)$cnpj[1] + 3*(int)$cnpj[2] + 2*(int)$cnpj[3];
    $checksum += 9*(int)$cnpj[4] + 8*(int)$cnpj[5] + 7*(int)$cnpj[6] + 6*(int)$cnpj[7];
    $checksum += 5*(int)$cnpj[8] + 4*(int)$cnpj[9] + 3*(int)$cnpj[10] + 2*(int)$cnpj[11];
    $checksum = (($checksum * 10) % 11) % 10;
    if ($checksum != (int)$cnpj[12]) {
        return false;
    }

    /* checar o segundo dígito de verificação */
    $checksum = 6*(int)$cnpj[0] + 5*(int)$cnpj[1] + 4*(int)$cnpj[2] + 3*(int)$cnpj[3] + 2*(int)$cnpj[4];
    $checksum += 9*(int)$cnpj[5] + 8*(int)$cnpj[6] + 7*(int)$cnpj[7] + 6*(int)$cnpj[8];
    $checksum += 5*(int)$cnpj[9] + 4*(int)$cnpj[10] + 3*(int)$cnpj[11] + 2*(int)$cnpj[12];
    $checksum = (($checksum * 10) % 11) % 10;
    if ($checksum != (int)$cnpj[13]) {
        return false;
    }

    return true;
}


$msg_telefone_invalido = "Erro: o campo telefone deve ser composto de números e não deve ser vazio.";

function is_valid_telefone(string $telefone) {
    $telefone = extract_digits($telefone);
    return strlen($telefone) > 0;
}

$msg_endereco_invalido = "Erro: o campo endereço não deve ter de 1 a 255 caracteres.";

function is_valid_endereco(string $endereco) {
    $l = strlen($endereco);
    return l >= 1 && l <= 255;
}

$msg_senha_invalida = "Erro: a senha deve possuir entre 8 e 64 caracteres";

function is_valid_senha(string $senha) {
    $l = strlen($senha);
    return $s >= 8 && $s <= 64;
}

/* email será usado para confirmar a conta */
$msg_email_invalido = "Erro: o email deve ser válido, do formato 'X@Y.Z'" 

function is_valid_email(string $email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


$admin_exemplo = array(
    'usuario' => 'chapolin',
    'estado_registro' => 'desativado', // chapolin deve acessar o link no seu email para ativar a conta
    'nome' => 'Chapolin Colorado',
    'cpf' => '000.111.000-44',
    'telefone' => '19 12345678',
    'endereco' => 'Rua Osiris, 33444\n'
                . 'Luxor, Egito',
    'cnpj_loja' => NULL // chapolin ainda não está associado a uma loja no momento de seu cadastro.
                        // ele deve ser convidado por um admin atual da loja, 
                        // ou colocado como 'admin responsável' no momento de cadastro da loja
    'senha' => 'nao contavam com a minha astucia',
    'email' => 'chapolin.colorado@televisa.com.mx'
);

/* trata submissão de formulário para criação de nova conta de admin */
function create_admin_account(array $admin) {
    if (!is_valid_username($admin['usuario'])) {
        user_input_error(array('message' => $msg_username_invalido, 'activity' => 'create_admin_account'));
    }
    $admin['estado_registro']; // usuário deve acessar o link no seu email para ativar a conta
    $admin['cnpj_loja'] = NULL; // usuário deve ser convidado por um admin atual da loja da qual participa ou ser colocado como 'admin responsável' no momento de cadastro da loja
    if (!is_valid_nome($admin['nome'])) {
        user_input_error(array('message' => $msg_nome_invalido, 'activity' => 'create_admin_account',
                               'data' => $admin));
    }
    // ...
}

function create_loja_account(array $loja) {

}

/* pára o processamento do request, loga o que aconteceu, e reporta um erro ao cliente */
function user_input_error(array $error_report) {
    $dados_atividade = array('dados do servidor' => $_SERVER,
                             'dados da atividade' => $error_report['data']);
    $dados_atividade = json_encode($dados_atividade);

    // talvez hajam métodos melhores de fazer query...
    $query = sprintf("insert into "
                    ."log_atividades (data_horario, tipo_atividade, dados_atividade, mensagem_erro) "
                    ."values         (NOW(),        '%s',           '%s',            '%s')",
                    $error_report['activity'],
                    !!!SECURE_MYSQL_ESCAPE($dados_atividade),
                    $error_report['message']);

    execute_db_query($query);

    generate_client_error_response_and_die($error_report);
}