# Geek Hub - Sistema de Cadastro de Filmes e Usuários para uma Locadora

O **Geek Hub** é uma aplicação web desenvolvida em PHP com foco no gerenciamento de uma locadora de filmes, jogos, mangás e produtos do universo geek. O sistema foi criado com o objetivo de facilitar e padronizar o processo de cadastro e controle dos itens disponíveis para aluguel/empréstimo, oferecendo uma interface simples, organizada e eficiente para os funcionários da locadora.

A plataforma permite realizar operações essenciais de gerenciamento, como cadastro, listagem, edição e exclusão de registros, utilizando o conceito de CRUD integrado a um banco de dados PostgreSQL. Dessa forma, o sistema auxilia no controle das informações dos produtos e usuários, tornando o atendimento mais rápido, prático e organizado.

---

## Especificação de Requisitos de Software
Especificação dos Requisistos de Software (SRE)  
Estrutura Baseada na ISO/IEC/IEEE 29148:2018

## 1. Introdução

#### 1.1 Escopo do Sistema
Este documento define os requisitos para a aplicação **Geek Hub** que tem como escopo o gerenciamento básico de uma locadora geek por meio de uma aplicação web. A plataforma será responsável pelo controle dos itens disponíveis no acervo, permitindo operações de cadastro, consulta, atualização e remoção de registros.  

O sistema também permitirá o gerenciamento de empréstimos, possibilitando consultar a disponibilidade de produtos e registrar informações relacionadas aos usuários da locadora. Entre os itens gerenciados estão filmes, jogos, mangás e outros produtos do universo geek.  

A aplicação será desenvolvida utilizando **PHP** para o back-end e **PostgreSQL** para o armazenamento dos dados, oferecendo uma estrutura organizada e funcional para auxiliar no controle das operações da locadora.

#### 1.2 Propósito
O projeto Geek Hub tem como propósito desenvolver uma aplicação web capaz de auxiliar no gerenciamento de uma locadora geek, tornando o controle de itens e empréstimos mais prático, organizado e eficiente.  

Além disso, o sistema busca aplicar na prática conceitos de desenvolvimento web, integração com banco de dados e operações CRUD utilizando PHP e PostgreSQL.

---

## 2. Descrição Global

#### 2.1 Funções do Sistema
O sistema deve realizar as seguintes funções principais:
* Cadastrar funcionários/gerentes da locadora.
* Realizar o login de funcionários/gerentes da locadora.
* Cadastrar novos títulos/produtos disponíveis.
* Consultar todos os títulos/produtos disponíveis.
* Realizar o empréstimo dos títulos/produtos.
* Alterar o estado do título/produto, se está disponível ou não.
* Deletar um título/produto com sistema de verificação.
* Cadastrar novos membros (clientes).
* Alterar o estado do membro, se está ativo ou não (se possuí algum empréstimo ou não).
* Consultar todos os membros cadastrados.
* Deletar membros com sistema de verificação.

#### 2.2 Características do Usuários
| Usuário | Descrição |
| :--- | :--- | 
| **Gerente (Adm)** | Usuário que possui total acesso ao sistema, pode realizar todas as operações de consulta e cadastro e exclusão de títulos/produtos disponíveis, porém precisa autenticar e confirmar com sua senha de adm. |
| **Funcionário Comum** | Usuário não possui acesso livre a todas as operações do sistema. Pode realizar cadastro e consulta porém não pode realizar a exclusão de nenhum título/produto. |

---

## 3. Requisitos do Sistema 

### 3.1 Requisitos Funcionais

#### Módulo de Acesso e Segurança
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RF01** | Autenticação de Utilizadores | O sistema deve possuir uma tela de login para validar as credenciais de acesso, identificando se o utilizador logado é um "Gerente (Adm)" ou um "Funcionário Comum". | Alta |
| **RF02** | Cadastro de Colaboradores | O sistema deve possuir uma interface que permita o registo de novos gerentes e funcionários no banco de dados. | Alta |
| **RF03** | Controle de Permissões de Exclusão | O sistema deve bloquear o acesso à função de exclusão de produtos/títulos para os utilizadores com o perfil de "Funcionário Comum". | Alta |
| **RF04** | Verificação Administrativa | O sistema deve exigir a confirmação explícita da senha do "Gerente (Adm)" antes de concluir qualquer operação de exclusão no sistema. | Alta |
---
#### Módulo de Gestão de Acervo 
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RF05** | Cadastro de Produtos | O sistema deve fornecer um formulário para inserir novos títulos no acervo (filmes, jogos, mangás e outros produtos geeks e a quantidade de um mesmo produto que será cadastrado). | Alta |
| **RF06** | Consulta de Produtos | O sistema deve listar todos os títulos e produtos cadastrados, permitindo a leitura e visualização de todas as informações do acervo. | Alta |
| **RF07** | Alteração de Status do Produto | O sistema deve permitir a atualização do estado do produto, indicando claramente se a sua situação atual é "Disponível" ou "Indisponível". | Alta |
| **RF08** | Exclusão Segura de Produtos | O sistema deve permitir a remoção de um título do banco de dados mediante um sistema de verificação (confirmação em duas etapas) para evitar apagamentos acidentais. | Alta |
---
#### Módulo de Gestão de Clientes e Empréstimos
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RF09** | Cadastro de Membros | O sistema deve permitir o registo de novos membros (clientes) que irão frequentar a locadora. | Média | 
| **RF10** | Consulta de Membros | O sistema deve listar todos os membros cadastrados na plataforma para fácil visualização por parte dos funcionários. | Média |
| **RF11** | Atualização de Status do Membro | O sistema deve possuir a capacidade de alterar o estado do membro (ex: Ativo ou Inativo), com base na regra de negócio que verifica se ele possui ou não um empréstimo em andamento. | Média | 
| **RF12** | Exclusão Segura de Membros | O sistema deve permitir apagar o registo de um membro do banco de dados, utilizando também um sistema de verificação e confirmação de segurança. | Média | 
| **RF13** | Registo de Empréstimo | O sistema deve disponibilizar uma funcionalidade que permita realizar e gravar o empréstimo de um produto específico para um membro cadastrado. | Alta |
---
#### Módulo de Empréstimo 
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RF14** | Controle de Datas | O sistema deve registrar automaticamente a *data_inicio* (data e hora atuais do momento do aluguel) e permitir que o funcionário defina a *data_fim_prevista* (quando o cliente promete devolver). | Média |
| **RF15** | Definição de Valor da Diária | O sistema deve associar um valor financeiro de diária ao empréstimo. (Lançamentos podem ter diárias mais caras que itens de catálogo antigo). | Média |
| **RF16** | Registro de Devolução | O sistema deve possuir uma tela ou botão para registrar a "Devolução", capturando a *data_devolucao_real*. | Média |
| **RF17** | Cálculo Automático de Multa e Total | No momento da devolução, o sistema deve calcular o valor total a ser pago (Dias alugados $\times$ Valor da diária) e somar uma multa caso a *data_devolucao_real* seja maior que a *data_fim_prevista*. | Média |
---

### 3.2 Requisitos Não Funcionais

#### Módulo de Ambiente e Arquitetura
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RNF01** | Tecnologias Base | O back-end do sistema deve ser desenvolvido estritamente na linguagem **PHP** e utilizar o SGBD **PostgreSQL** para o armazenamento de dados. | Alta |
| **RNF02** | Padrão de Arquitetura | O sistema deve ser estruturado de forma organizada, separando responsabilidades de conexão e utilizando o padrão CRUD (Create, Read, Update, Delete) para as operações no banco. | Alta |
| **RNF03** | Compatibilidade Web | A aplicação deve ser acessível através de navegadores web modernos (Google Chrome, Firefox, Edge) sem a necessidade de instalação de software adicional nas máquinas da locadora. | Alta |
---
#### Módulo de Segurança e Integridada
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RNF04** | Proteção de Banco de Dados | A comunicação entre o PHP e o PostgreSQL deve ser feita obrigatoriamente utilizando a extensão **PDO** (PHP Data Objects) com Prepared Statements, para evitar ataques de injeção de SQL (SQL Injection). | Alta | 
| **RNF05** | Criptografia de Senhas | As senhas dos usuários ("Gerente" e "Funcionário Comum") nunca devem ser salvas em texto limpo. O sistema deve utilizar algoritmos de hash seguros nativos do PHP (como o *password_hash()*) antes de gravá-las no banco. | Alta |
| **RNF06** | Gestão de Sessões | O controle de acesso e a diferenciação entre os perfis de usuário (Gerente vs. Funcionário) devem ser gerenciados através do uso seguro de sessões do PHP (*$_SESSION*). | Alta |
---
#### Módulo de Usabilidade e Desempenho
| ID | Título | Descrição | Prioridade |
| :--- | :--- | :--- | :--- |
| **RNF07** | Interface Intuitiva | A interface gráfica (HTML/CSS) deve ser simples, limpa e padronizada, garantindo que os funcionários consigam operar o sistema (cadastros e empréstimos) com o mínimo de treinamento prévio. | Média |
| **RNF08** | Responsividade Básica | O layout das telas principais deve adaptar-se de forma razoável a diferentes tamanhos de tela (como monitores de balcão e tablets), facilitando o uso pelos funcionários enquanto verificam o acervo nas prateleiras. | Baixa |
| **RNF09** | Feedback do Sistema | O sistema deve fornecer mensagens de aviso claras e objetivas em caso de erro, sucesso ou validação negada (ex: "Produto excluído com sucesso" ou "Acesso negado"). | Média | 
---

### 3.3 Regras de Negócio

#### Módulo de Controle de Acervo e Membros 
| ID | Título | Regra / Condição de Execução |
| :--- | :--- | :--- | 
| **RN01** | Restrição de Exclusão de Produtos | Um produto/título não pode ser deletado do banco de dados se possuir cópias atualmente alugadas. O sistema deve bloquear a ação e exibir um aviso. | 
| **RN02** | Restrição de Exclusão de Membros | Um membro (cliente) não pode ser excluído do sistema se possuir empréstimos em andamento ou multas não pagas. | 
| **RN03** | Status Automático do Membro | O status de um membro deve refletir a sua situação atual: se possui um empréstimo não devolvido ou em atraso, a sua conta deve indicar isso, podendo restringir novos aluguéis. | 
| **RN04** | Exclusividade de Privilégios | Apenas contas com o nível de acesso "Gerente (Adm)" podem acessar as rotas (URLs) e botões de exclusão. Se um "Funcionário Comum" tentar acessar, o sistema deve redirecioná-lo e bloquear a ação. | 
---
#### Módulo de Operações de Empréstimo
| ID | Título | Regra / Condição de Execução |
| :--- | :--- | :--- | 
| **RN05** | Bloqueio de Título Indisponível | O sistema não pode permitir a abertura de um empréstimo para um título cujo status seja "Indisponível" ou cuja quantidade em estoque seja zero. |
| **RN06** | Congelamento do Valor da Diária | O valor da diária registrado no momento do empréstimo não pode ser alterado retroativamente, mesmo que o gerente atualize o preço do produto no catálogo durante o período do aluguel. | 
| **RN07** | Aplicação de Multa por Atraso | A multa só deve ser aplicada se a *data_devolucao_real* for estritamente maior que a *data_fim_prevista*. O cálculo final deve ser: (Dias Previstos $\times$ Diária) + (Dias de Atraso $\times$ Diária) + Taxa Fixa de Multa. |
| **RN08** | Autenticação Dupla para Deletes | Para efetivar a exclusão de qualquer registro (membro ou produto), não basta estar logado como Gerente; o sistema deve exigir a digitação da senha novamente na tela de exclusão. | 
---

### 3.4 Estruturação do Banco de Dados 
O sistema utiliza um banco de dados relacional composto por 4 tabelas principais. As relações garantem a integridade referencial exigida pelas Regras de Negócio.

#### ENTIDADE: *`usuarios`* (Funcionários e Gerentes)
*Armazena as credenciais de acesso do sistema.*
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| *`id`* | SERIAL | PRIMARY KEY | Identificador único do funcionário. | 
| *`nome`* | VARCHAR(100) | NOT NULL | Nome completo do colaborador. |
| *`email`* | VARCHAR(100) | UNIQUE, NOT NULL | E-mail usado para o login. | 
| *`senha_hash`* | VARCHAR(255) | NOT NULL | Senha criptografada (RNF05). |
| *`perfil_acesso`* | VARCHAR(20) | NOT NULL | Define se é 'Gerente' ou 'Funcionario' (RF01, RN04). |
---
#### ENTIDADE: *`membros`* (Clientes da Locadora)
*Armazena os dados dos clientes que realizam os empréstimos.*
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| *`id`* | SERIAL | PRIMARY KEY | Identificador único do membro. |
| *`nome`* | VARCHAR(100) | NOT NULL | Nome completo do cliente. |
| *`cpf`* | VARCHAR(14) | UNIQUE, NOT NULL | Documento de identificação. |
| *`telefone`* | VARCHAR(20) | | Telefone para contato. |
| *`status_ativo`* | BOOLEAN | DEFAULT TRUE | `true` = Ativo, `false` = Inativo/Bloqueado (RN03). |
---
#### ENTIDADE: *`produtos`* (Acervo)
*Armazena as informações de filmes, jogos, mangás e outros itens.*
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| *`id`* | SERIAL | PRIMARY KEY | Identificador único do produto. |
| *`titulo`* | VARCHAR(150) | NOT NULL | Nome da obra. |
| *`categoria`* | VARCHAR(50) | NOT NULL | Ex: Filme, Jogo, Mangá. |
| *`quantidade`* | INT | NOT NULL, DEFAULT 0 | Quantidade de cópias físicas em estoque (RF05). |
| *`valor_diaria`* | DECIMAL(10,2) | NOT NULL | Valor base cobrado por dia de aluguel (RF15). |
| *`disponivel`* | BOOLEAN | DEFAULT TRUE | `true` = Disponível, `false` = Indisponível (RF07, RN05). |
---
#### ENTIDADE: *`emprestimos`* (Contrato de Aluguéis)
*Entidade que relaciona as entidades `membros`, `produtos` e `usuarios` (quem fez o empréstimo).*
| Campo | Tipo | Restrições | Descrição |
| :--- | :--- | :--- | :--- |
| *`id`* | SERIAL | PRIMARY KEY | Número do contrato de aluguel. |
| *`produto_id`* | INT | FOREIGN KEY, RESTRICT | ID do produto. O RESTRICT impede exclusão se houver aluguel (RN01). |
| *`membro_id`* | INT | FOREIGN KEY, RESTRICT | ID do membro. O RESTRICT impede exclusão do membro (RN02). |
| *`usuario_id`* | INT | FOREIGN KEY, RESTRICT | ID do funcionário que registrou a saída. |
| *`data_inicio`* | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Data e hora exatas da saída (RF14). |
| *`data_fim_prevista`* | DATE | NOT NULL | Data combinada para devolução (RF14). | 
| *`data_devolucao`* | DATE | NULL | Data em que o item foi realmente entregue (RF16). | 
| *`valor_diaria_cobrado`* | DECIMAL(10,2) | NOT NULL | Preço da diária congelado no momento da saída (RN06). | 
| *`multa_aplicada`* | DECIMAL(10,2) | DEFAULT 0.00 | Valor da multa caso haja atraso na entrega (RF17). |
| *`status`* | VARCHAR(20) | DEFAULT 'Pendente' | Situação: 'Pendente', 'Concluído', 'Atrasado'. |

