# Passo a passo da implementação

## 1. Estrutura de pastas

Foi criada a estrutura Mini-MVC do Portal SI, separando arquivos públicos, configuração, banco, regras de negócio, acesso a dados, telas e documentação.

## 2. Schema do banco

O arquivo `database/schema.sql` cria as oito tabelas exigidas, relacionamentos, restrições, índices e triggers.

## 3. Conexão, sessão e segurança

`config/database.php` centraliza a conexão PDO com PostgreSQL. `config/session.php` configura cookies restritos, inicializa sessões e controla inatividade. Os helpers centralizam autenticação, escape HTML, CSRF e validação de cadastro.

## 4. Cadastro, login, logout e dashboard

O `Usuario` concentra SQL. O `AuthService` aplica validações, hash e regras de sessão. O `AuthController` orquestra as requisições. As Views exibem cadastro, login, logout e dashboard protegido.

## 5. Dados iniciais e testes da Aula 08

O schema e o `seed.sql` foram aplicados no banco `portal_si`, confirmando as oito tabelas, cinco triggers, seis categorias e o administrador inicial. Os dez testes da Aula 08 foram executados pelo Apache do XAMPP e passaram. O detalhamento está em `docs/TESTES_AULA08.md`.
