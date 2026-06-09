-- 1. Criação da Tabela de Usuários (Funcionários/Gerentes)
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil_acesso VARCHAR(20) NOT NULL CHECK (perfil_acesso IN ('Gerente', 'Comum'))
);

-- 2. Criação da Tabela de Membros (Clientes)
CREATE TABLE membros (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    status_ativo BOOLEAN DEFAULT TRUE
);

-- 3. Criação da Tabela de Produtos (Acervo)
CREATE TABLE produtos (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    valor_diaria DECIMAL(10,2) NOT NULL,
    imagem_capa VARCHAR(255), -- Aqui está o nosso novo campo para a foto!
    disponivel BOOLEAN DEFAULT TRUE
);

-- 4. Criação da Tabela de Empréstimos (Transações)
CREATE TABLE emprestimos (
    id SERIAL PRIMARY KEY,
    produto_id INT NOT NULL,
    membro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fim_prevista DATE NOT NULL,
    data_devolucao DATE,
    valor_diaria_cobrado DECIMAL(10,2) NOT NULL,
    multa_aplicada DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'Pendente',
    
    -- Chaves Estrangeiras (Relacionamentos) com proteção RESTRICT
    CONSTRAINT fk_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_membro FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE RESTRICT,
    CONSTRAINT fk_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);