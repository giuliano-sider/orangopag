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
$msg_email_invalido = "Erro: o email deve ser válido, do formato 'X@Y.Z'";

function is_valid_email(string $email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$msg_usuario_existente = "Erro: o usuário com este username já existe";

function get_admin_by_user(string $usuario) {
    $query = sprintf("select * "
                    ."from admin as a join "
                    ."     acesso_admin as aa "
                    ."     on a.usuario = aa.usuario_admin "
                    ."where usuario = %s",
                    !!!SECURE_MYSQL_ESCAPE($usuario));
    $result = execute_db_query($query);
    /* result será NULL quando não houver resultados, espero... */
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
        log_error_and_die(array('message' => $msg_username_invalido, 'activity' => 'create_admin_account',
                               'data' => $admin, 'error_type' => 'user_input_error'));
    }
    $admin['estado_registro'] = 'desativado'; // usuário deve acessar o link no seu email para ativar a conta
    $admin['cnpj_loja'] = NULL; // usuário deve ser convidado por um admin atual da loja da qual participa ou ser colocado como 'admin responsável' no momento de cadastro da loja
    
    if (!is_valid_nome($admin['nome'])) {
        log_error_and_die(array('message' => $msg_nome_invalido, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }
    if (!is_valid_cpf($admin['cpf'])) {
        log_error_and_die(array('message' => $msg_cpf_invalido, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }
    if (!is_valid_telefone($admin['telefone'])) {
        log_error_and_die(array('message' => $msg_telefone_invalido, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }
    if (!is_valid_endereco($admin['endereco'])) {
        log_error_and_die(array('message' => $msg_endereco_invalido, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }
    if (!is_valid_senha($admin['senha'])) {
        log_error_and_die(array('message' => $msg_senha_invalida, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }
    if (!is_valid_email($admin['email'])) {
        log_error_and_die(array('message' => $msg_email_invalido, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }

    if ( ($check_if_admin_exists = get_admin_by_user($admin['usuario'])) != NULL) {
        log_error_and_die(array('message' => $msg_usuario_existente, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'user_input_error'));
    }
    
    admin['codigo_verificacao'] = generate_unique_admin_verification_code($admin);
    

    if (!send_email(array('to' => admin['email'],
                          'body' => "Favor validar a sua conta do OrangoPag neste link:\n"
                                    . $url_verificacao_admin . "?usuario=" 
                                    . $admin['usuario'] . "&codverificao=" 
                                    . $admin['codigo_verificacao']))) 
    {
        log_error_and_die(array('message' => $msg_falha_mandar_email, 
                                'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'internal_server_error'));
    }

    if (!db_insert_admin($admin)) { // criado registro com as informações dadas. ou retorna false se falhou
        log_error_and_die(array('message' => $msg_falha_insercao, 'activity' => 'create_admin_account',
                                'data' => $admin, 'error_type' => 'internal_server_error'));
    }

    /* logar criação de conta */
    log_success('activity' => 'create_admin_account',
                'data' => $admin);
    
    successful_client_response(array('activity' => 'create_admin_account',
                                     'data' => $admin));
}

/* trata acesso a link para validar conta de admin via email */
function activate_admin_account($usuario, $unique_verification_code) {
    if ( ($admin = get_admin_by_user($usuario)) == NULL) {
        log_error_and_die(array('message' => $msg_page_not_found, 'activity' => 'activate_admin_account',
                                'data' => array('usuario' => $usuario, 'code' => $unique_verification_code),
                                'error_type' => 'invalid_admin_activation_link'));
    }
    if ($admin['estado_registro'] != 'desativado') {
        log_error_and_die(array('message' => $msg_page_not_found, 'activity' => 'activate_admin_account',
                                'data' => array('usuario' => $usuario, 'code' => $unique_verification_code),
                                'error_type' => 'invalid_admin_activation_link'));
    }
    if ($admin['codigo_verificacao'] != $unique_verification_code) {
        log_error_and_die(array('message' => $msg_page_not_found, 'activity' => 'activate_admin_account',
                                'data' => array('usuario' => $usuario, 'code' => $unique_verification_code),
                                'error_type' => 'invalid_admin_activation_link'));
    }
    db_activate_admin($admin); // seta o estado_registro para 'ativado'
    successful_client_response(array('activity' => 'activate_admin_account',
                                     'data' => array('usuario' => $usuario, 
                                                     'code' => $unique_verification_code)));
}

$loja_exemplo = array(
    'usuario_admin_responsavel' => 'chapolin',
    'estado_registro' => 'desativado', // chapolin (admin responsavel) deve acessar o link no seu email para ativar a conta da loja
    'nome' => 'Churros do Seu Madruga Ltda',
    'cnpj' => '00.000.001/0000-55',
    'telefone' => '19 87654321',
    'endereco' => 'Rua Osiris, 444\n'
                . 'Luxor, Egito',
    // nos é que geramos... 'codigo_acesso_api' => 'a vinganca nunca eh plena mata a alma e envenena',
    'url_msgbox_api' => 'http://churros.madruga.com.mx/api_inbox' // para mensagens de retorno do api
    
    // info para contas a pagar da loja
    'cartao' => array('numero_cartao' => '7171 7171 7171 7171',
                      'nome_cartao' => 'Churros do Seu Madruga Ltda',
                      'endereco_cobranca' => 'Rua Osiris, 71\n'
                                           . 'Luxor, Egito',
                      'tipo' => 'credito',
                      'telefone_cartao' => '19 87654321',
                      'data_expiracao_mes' => '08',
                      'data_expiracao_ano' => '2036',
                      'codigo_verificacao' => '717'),
    
    // info para contas a receber da loja
    'conta_bancaria' => array('banco' => '071',
                              'agencia' => '71717',
                              'conta' => '001121271717')
);

function create_loja_account(array $loja) {
    // validar usuario (tem que ser um usuario existente sem uma loja associada)
    // setar estado_registro para 'desativado' (ativação ocorre via email do admin responsável)
    // validar nome 
    // validar cnpj (digitos de verificao. nao pode ser um cnpj de uma loja ja existente)
    // validar telefone
    // validar endereco
    // validar cartao:
    if (!banco_is_valid_card($loja['cartao']) {
        log_error_and_die(array('message' => $msg_cartao_invalido, 'activity' => 'create_loja_account',
                                'data' => $loja, 'error_type' => 'user_input_error'));
    }
    // validar conta bancária:
    if (!banco_is_valid_account($loja['conta_bancaria']) {
        log_error_and_die(array('message' => $msg_conta_bancaria_invalida, 'activity' => 'create_loja_account',
                                'data' => $loja, 'error_type' => 'user_input_error'));
    }

    // validar url_msgbox_api (tem que ser URL. tem que ser unico na base de dados)
    // faz uma requisicao ao url do api msgbox da loja, para ver se ela responde de forma valida. se nao responder, error and die. se responder, continua.

    loja['codigo_verificacao'] = generate_unique_loja_verification_code($loja);

    if ( ($loja['admin_info'] = get_admin_by_user($loja['usuario_admin_responsavel'])) == NULL) {
        log_error_and_die(array('message' => $msg_usuario_invalido, 'activity' => 'create_loja_account',
                                'data' => $loja, 'error_type' => 'user_input_error'));
    }

    if (loja['admin_info']['cnpj_loja'] != NULL) { // se o admin ja tiver loja associada...
        log_error_and_die(array('message' => $msg_usuario_invalido, 'activity' => 'create_loja_account',
                                'data' => $loja, 'error_type' => 'user_input_error'));
    }

    if (!send_email(array('to' => $loja['admin_info']['email'],
                          'body' => "Favor validar a sua conta do OrangoPag neste link:\n"
                                    . $url_verificacao_loja . "?cnpj_loja=" 
                                    . $loja['cnpj'] . "&codverificao=" 
                                    . $loja['codigo_verificacao']))) 
    {
        log_error_and_die(array('message' => $msg_falha_mandar_email, 
                                'activity' => 'create_loja_account',
                                'data' => $loja, 'error_type' => 'internal_server_error'));
    }

    if (!db_insert_loja($loja)) { // criado registro com as informações dadas. ou retorna false se falhou
        log_error_and_die(array('message' => $msg_falha_insercao, 'activity' => 'create_loja_account',
                                'data' => $loja, 'error_type' => 'internal_server_error'));
    }

    /* logar criação de conta */
    log_success('activity' => 'create_loja_account',
                'data' => $loja);
    
    successful_client_response(array('activity' => 'create_loja_account',
                                     'data' => $loja));

}

/* trata acesso a link para validar conta de loja via email do admin */
function activate_loja_account($cnpj_loja, $unique_verification_code) {
    if ( ($loja = get_loja_by_cnpj($cnpj_loja)) == NULL) {
        log_error_and_die(array('message' => $msg_page_not_found, 'activity' => 'activate_loja_account',
                                'data' => array('cnpj_loja' => $cnpj_loja, 'code' => $unique_verification_code),
                                'error_type' => 'invalid_loja_activation_link'));
    }
    if ($loja['estado_registro'] != 'desativado') {
        log_error_and_die(array('message' => $msg_page_not_found, 'activity' => 'activate_admin_account',
                                'data' => array('usuario' => $usuario, 'code' => $unique_verification_code),
                                'error_type' => 'invalid_admin_activation_link'));
    }
    if ($loja['codigo_verificacao'] != $unique_verification_code) {
        log_error_and_die(array('message' => $msg_page_not_found, 'activity' => 'activate_loja_account',
                                'data' => array('usuario' => $usuario, 'code' => $unique_verification_code),
                                'error_type' => 'invalid_loja_activation_link'));
    }
    $loja['codigo_acesso_api'] = generate_unique_codigo_acesso_api($loja);

    db_activate_loja($loja); // seta o estado_registro para 'ativado', atualiza código acesso api da loja
    successful_client_response(array('activity' => 'activate_admin_account',
                                     'data' => array('usuario' => $cnpj_loja, 
                                                     'code' => $unique_verification_code)));
}

$req1_nova_transacao_exemplo_boleto = array(
    // id será gerado pelo OrangoPag
    'cnpj_loja' => '00.000.001/0000-55',
    // estado transação será mantido pelo OrangoPag
    'tipo_pagamento' => 'boleto',
    'parcelas' => array(array('prazo' => '01/10/2017',
                              'valor' => '7171.71')),
    'info_pagamento' => array('nome_sacado' => 'Chaves',
                              'cpf_sacado' => '000.111.000-44'),
    'codigo_acesso_api' => 'fu3hf3834fb38b8u347hf29fh238fhfh'
);
$devolucao1_req1 = array(

);

$req2_nova_transacao_exemplo_cartao_debito = array(
    // id será gerado pelo OrangoPag
    'cnpj_loja' => '00.000.001/0000-55',
    // estado transação será mantido pelo OrangoPag
    'tipo_pagamento' => 'cartão de débito',
    'parcelas' => array(array('prazo' => '01/10/2017',
                              'valor' => '717171.71')),
    'info_pagamento' => array('numero_cartao' => '7171 7171 7171 7100',
                              'nome_cartao' => 'Churros do Seu Madruga Ltda',
                              'endereco_cobranca' => 'Rua Osiris, 71\n'
                                                   . 'Luxor, Egito',
                              'tipo' => 'débito',
                              'telefone_cartao' => '19 87654321',
                              'data_expiracao_mes' => '08',
                              'data_expiracao_ano' => '2036',
                              'codigo_verificacao' => '717'),
    'codigo_acesso_api' => 'fu3hf3834fb38b8u347hf29fh238fhfh'
);
$devolucao1_req2 = array(

);

$req3_nova_transacao_exemplo_cartao_credito = array(
    // id será gerado pelo OrangoPag
    'cnpj_loja' => '00.000.001/0000-55',
    // estado transação será mantido pelo OrangoPag
    'tipo_pagamento' => 'cartão de débito',
    'parcelas' => array(array('prazo' => '01/10/2017',
                              'valor' => '717171.71')),
    'info_pagamento' => array('numero_cartao' => '7171 7171 7171 7171',
                              'nome_cartao' => 'Churros do Seu Madruga Ltda',
                              'endereco_cobranca' => 'Rua Osiris, 71\n'
                                                   . 'Luxor, Egito',
                              'tipo' => 'crédito',
                              'telefone_cartao' => '19 87654321',
                              'data_expiracao_mes' => '08',
                              'data_expiracao_ano' => '2036',
                              'codigo_verificacao' => '717'),
    'codigo_acesso_api' => 'fu3hf3834fb38b8u347hf29fh238fhfh'
);
$devolucao1_req2 = array(

);

$req4_nova_transacao_exemplo_cartao_credito_prazo = array(
    // id será gerado pelo OrangoPag
    'cnpj_loja' => '00.000.001/0000-55',
    // estado transação será mantido pelo OrangoPag
    'tipo_pagamento' => 'cartão de débito',
    'parcelas' => array(array('prazo' => '01/10/2017',
                              'valor' => '717171.71'),
                        array('prazo' => '01/11/2017',
                              'valor' => '717171.71'),
                        array('prazo' => '01/12/2017',
                              'valor' => '717171.71'),
                        array('prazo' => '01/01/2018',
                              'valor' => '717171.71')),
    'info_pagamento' => array('numero_cartao' => '7171 7171 7171 7171',
                              'nome_cartao' => 'Churros do Seu Madruga Ltda',
                              'endereco_cobranca' => 'Rua Osiris, 71\n'
                                                   . 'Luxor, Egito',
                              'tipo' => 'crédito',
                              'telefone_cartao' => '19 87654321',
                              'data_expiracao_mes' => '08',
                              'data_expiracao_ano' => '2036',
                              'codigo_verificacao' => '717'),
    'codigo_acesso_api' => 'fu3hf3834fb38b8u347hf29fh238fhfh'
);
$devolucao1_req2 = array(

);



function create_transaction($transacao) {

}

/* pára o processamento do request, loga o que aconteceu, e reporta um erro ao cliente */
function log_error_and_die(array $error_report) {
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

function log_sucess(array $log) {
    $dados_atividade = array('dados do servidor' => $_SERVER,
                             'dados da atividade' => $log['data']);
    $dados_atividade = json_encode($dados_atividade);

    // talvez hajam métodos melhores de fazer query...
    $query = sprintf("insert into "
                    ."log_atividades (data_horario, tipo_atividade, dados_atividade) "
                    ."values         (NOW(),        '%s',           '%s')",
                    $log['activity'],
                    !!!SECURE_MYSQL_ESCAPE($dados_atividade));

    execute_db_query($query);
}