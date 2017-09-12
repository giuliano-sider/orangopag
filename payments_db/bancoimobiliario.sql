-- comandos para criação do BD do Banco Imobiliario, o grande maestro do sistema financeiro
-- usando PostgreSQL.
-- tipo TIMESTAMP no PostgreSQL é o DATETIME do MySQL

-- usar psql e executar o comando '\i bancoimobiliario.sql'

create database bancoimobiliario;

\connect bancoimobiliario;



create table pessoa (
    id bigint primary key,
        -- CPF ou CNPJ da pessoa

    tipo varchar(32) not null,

    check (tipo in ('física',
                    'jurídica')),

    telefone bigint not null,
    endereco varchar(255) not null,
    nome varchar(255) not null
);

create table conta (
    banco bigint,
    agencia bigint,
    conta bigint,

    primary key (banco, agencia, conta),

    id_pessoa bigint not null,

    foreign key (id_pessoa)
        references pessoa (id)
        on update cascade
        on delete no action,

    saldo money not null
);

create table cartao (
    numero bigint primary key,

    nome varchar(255) not null,
    data_expiracao date not null,
    codigo_verificacao bigint not null,
    endereco_cobranca varchar(255) not null,
    telefone bigint not null,
    
    id_pessoa bigint not null,

    foreign key (id_pessoa)
        references pessoa (id)
        on update cascade
        on delete no action,
    
    tipo varchar(32) not null,

    check (tipo in ('débito',
                    'crédito')),

    banco bigint not null,
    agencia bigint not null,
    conta bigint not null,

    foreign key (banco, agencia, conta)
        references conta (banco, agencia, conta)
        on update cascade
        on delete no action
);

create table cartao_credito (
    numero_cartao bigint primary key,

    foreign key (numero_cartao)
        references cartao (numero)
        on update cascade
        on delete no action,

    estado_cartao varchar(32) not null

    check (estado_cartao in ('liberado',
                             'bloqueado')),

    credito_disponivel money not null
);

create table lancamento_conta ( -- conta tem lançamento
    banco bigint,
    agencia bigint,
    conta bigint,

    id bigint,

    primary key (banco, agencia, conta, id),

    foreign key (banco, agencia, conta)
        references conta (banco, agencia, conta)
        on update cascade
        on delete no action,

    valor money not null,

    data_horario_compensacao TIMESTAMP not null
);

create table lancamento_cartao ( -- cartão de cŕedito tem lançamento
    numero_cartao bigint,

    foreign key (numero_cartao)
        references cartao_credito (numero_cartao)
        on update cascade
        on delete no action,

    valor money not null,

    prazo date not null,

    data_horario_pagamento TIMESTAMP
        default null
    -- data_horario_pagamento é null enquanto o valor não for pago
);

create table log_atividades (
    id bigint not null primary key,

    tipo_atividade varchar(255) not null,
    data_horario TIMESTAMP not null,
    dados_atividade json not null,
    
    mensagem_erro varchar(255)
        default null
);