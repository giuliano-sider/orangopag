-- comandos para criação do BD do intermediador de pagamentos OrangoPag
-- usando PostgreSQL.
-- tipo TIMESTAMP no PostgreSQL é o DATETIME do MySQL

-- usar psql e executar o comando '\i orangopag.sql'
-- usar mysql e executar o comando 'source orangopag.sql'
create database orangopag;

-- postgresql
-- \connect orangopag; 

-- mysql
use orangopag; 



create table cartao (
    numero bigint,

    nome varchar(255) not null,
    data_expiracao date not null,
    codigo_verificacao bigint not null,
    endereco_cobranca varchar(255) not null,
    telefone bigint not null,
    tipo varchar(32) not null,

    check (tipo in ('débito', 'crédito')),

    primary key (numero)
);

create table loja (
    cnpj bigint,

    nome varchar(255) not null,
    telefone varchar(255) not null,
    endereco varchar(255) not null,

    -- quando a loja apaga a conta, o registro é desativado
    estado_registro varchar(255) not null
        default 'ativado',

    check (estado_registro in ('ativado',
                               'desativado')),

    numero_cartao bigint not null,

    primary key (cnpj),

    foreign key (numero_cartao)
        references cartao (numero)
        on update cascade
        on delete no action
);

create table acesso_loja (
    cnpj_loja bigint,

    codigo_acesso_api varchar(255) unique not null,
        -- código usado pela loja para fazer requisições e consultas pelo API
    url_msgbox_api varchar(255) unique not null,
        -- mensagens sobre eventos de pagamento serão POSTadas aqui pelo OrangoPag
    
    usuario_admin_responsavel varchar(255) not null,

    primary key (cnpj_loja),
    
    foreign key (cnpj_loja)
        references loja (cnpj)
        on update cascade
        on delete no action

    -- restrição de chave estrangeira do admin responsável será colocada adiante
);


-- conta bancária para depósito
create table conta_bancaria_loja (
    cnpj_loja bigint primary key,

    banco bigint not null,
    agencia bigint not null,
    conta bigint not null,

    foreign key (cnpj_loja)
        references loja (cnpj)
        on update cascade
        on delete no action
);

create table admin (
    usuario varchar(255),

    -- quando o admin apaga sua conta, o registro é desativado
    estado_registro varchar(255) not null default 'ativado',

    check (estado_registro in ('ativado',
                               'desativado')),

    nome varchar(255) not null,
    cpf bigint not null,
    telefone varchar(255) not null,
    endereco varchar(255) not null,

    -- se for null, o admin não está associado a uma loja ainda
    cnpj_loja bigint,

    primary key (usuario),

    foreign key (cnpj_loja)
        references loja (cnpj)
);

create table acesso_admin (
    usuario_admin varchar(255),

    senha_admin varchar(255) not null,
    email_admin varchar(255) not null,

    primary key (usuario_admin),

    foreign key (usuario_admin)
        references admin (usuario)
        on update cascade
        on delete no action
);

alter table acesso_loja add foreign key (usuario_admin_responsavel)
        references admin (usuario)
        on update cascade
        on delete no action;



create table transacao ( -- loja requisita transacao
    id bigint,
    cnpj_loja bigint,

    estado_transacao varchar(32) not null
        default 'aguardando aprovação',

    check (estado_transacao in ('aguardando aprovação',
                                'aprovado',
                                'recusado',
                                'cancelado')),

    tipo_pagamento varchar(32) not null,
    
    check (tipo_pagamento in ('boleto',
                              'cartão de débito',
                              'cartão de crédito à vista',
                              'cartão de débito à prazo')),

    data_horario_criacao datetime not null,

    primary key (id, cnpj_loja),

    foreign key (cnpj_loja)
        references loja (cnpj)
        on update cascade
        on delete no action
);

create table parcela ( -- transação é feita de parcelas
    prazo datetime,
    id_transacao bigint,
    cnpj_loja bigint,

    valor decimal(15,2) not null,

    -- data_horario_pagamento é null enquanto a parcela não for paga
    data_horario_pagamento datetime
        default null,

    primary key (prazo, id_transacao, cnpj_loja),

    foreign key (id_transacao, cnpj_loja)
        references transacao (id, cnpj_loja)
        on update cascade
        on delete no action
);

create table pagamento_boleto (
    id_transacao bigint,
    cnpj_loja bigint,

    nome_sacado varchar(255),
    cpf_sacado bigint,

    primary key (id_transacao, cnpj_loja),

    foreign key (id_transacao, cnpj_loja)
        references transacao (id, cnpj_loja)
        on update cascade
        on delete no action
);

create table pagamento_cartao (
    id_transacao bigint,
    cnpj_loja bigint,

    numero_cartao bigint not null,
    
    primary key (id_transacao, cnpj_loja),

    foreign key (id_transacao, cnpj_loja)
        references transacao (id, cnpj_loja)
        on update cascade
        on delete no action,

    foreign key (numero_cartao)
        references cartao (numero)
);



-- tudo será logado
create table log_atividades (
    id bigint primary key,

    tipo_atividade varchar(255) not null,
    data_horario datetime not null,

    dados_atividade text not null,
    mensagem_erro varchar(255)
        default null
);
