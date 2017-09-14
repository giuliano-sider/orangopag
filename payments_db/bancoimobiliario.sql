-- comandos para criação do BD do Banco Imobiliario, o grande maestro do sistema financeiro
-- usando PostgreSQL.
-- tipo TIMESTAMP no PostgreSQL é o DATETIME do MySQL

-- usar psql e executar o comando '\i bancoimobiliario.sql'

create database bancoimobiliario;

\connect bancoimobiliario;



create table pessoa (
    -- CPF ou CNPJ da pessoa
    id bigint,

    tipo varchar(32) not null,

    check (tipo in ('física',
                    'jurídica')),

    telefone bigint not null,
    endereco varchar(255) not null,
    nome varchar(255) not null,

    -- data de inserção da pessoa nesse banco de dados
    data_horario_criacao TIMESTAMP not null,

    primary key (id)
);

create table conta (
    banco bigint,
    agencia bigint,
    conta bigint,

    id_pessoa bigint not null,

    saldo money not null,

    data_horario_criacao TIMESTAMP not null,

    primary key (banco, agencia, conta),

    foreign key (id_pessoa)
        references pessoa (id)
        on update cascade
        on delete no action
);

create table cartao (
    numero bigint,

    nome varchar(255) not null,
    data_expiracao date not null,
    codigo_verificacao bigint not null,
    endereco_cobranca varchar(255) not null,
    telefone bigint not null,
    
    id_pessoa bigint not null,
    
    tipo varchar(32) not null,

    check (tipo in ('débito',
                    'crédito')),

    data_horario_criacao TIMESTAMP not null,

    banco bigint not null,
    agencia bigint not null,
    conta bigint not null,

    primary key (numero),
    
    foreign key (id_pessoa)
        references pessoa (id)
        on update cascade
        on delete no action,

    foreign key (banco, agencia, conta)
        references conta (banco, agencia, conta)
        on update cascade
        on delete no action
);

create table cartao_credito (
    numero_cartao bigint,

    estado_cartao varchar(32) not null

    check (estado_cartao in ('liberado',
                             'bloqueado')),

    credito_disponivel money not null,

    primary key (numero_cartao),

    foreign key (numero_cartao)
        references cartao (numero)
        on update cascade
        on delete no action
);

create table lancamento_conta ( -- conta tem lançamento
    banco bigint,
    agencia bigint,
    conta bigint,

    id bigint,

    valor money not null,

    data_horario_compensacao TIMESTAMP not null,

    primary key (banco, agencia, conta, id),

    foreign key (banco, agencia, conta)
        references conta (banco, agencia, conta)
        on update cascade
        on delete no action
);

create table lancamento_cartao ( -- cartão de cŕedito tem lançamento
    numero_cartao bigint,

    id bigint,

    valor money not null,

    prazo date not null,

    -- data_horario_pagamento é null enquanto o valor não for pago
    data_horario_pagamento TIMESTAMP
        default null,

    primary key (numero_cartao, id),
    
    foreign key (numero_cartao)
        references cartao_credito (numero_cartao)
        on update cascade
        on delete no action
);

create table log_atividades (
    id bigint primary key,

    tipo_atividade varchar(255) not null,
    data_horario TIMESTAMP not null,
    dados_atividade json not null,
    
    mensagem_erro varchar(255)
        default null
);