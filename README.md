# Portal SI

Projeto de comunicação do curso de Sistemas de Informação, desenvolvido em PHP nativo com PostgreSQL e arquitetura Mini-MVC.

## Organização da aplicação

O fluxo principal segue esta ordem:

```text
Usuário envia e-mail e senha
          ↓
AuthController
Recebe os campos e chama o serviço
          ↓
AuthService
Solicita a busca do usuário
          ↓
Usuario (Model)
Executa SELECT no PostgreSQL e retorna o usuário
          ↓
AuthService
Confere a senha e se a conta pode entrar
          ↓
AuthController
Estabelece a sessão com os helpers e redireciona
          ↓
Dashboard (View)
Exibe o nome e o perfil do usuário
```

### Responsabilidades

- **Controller:** recebe as requisições do navegador, chama os serviços e decide respostas e redirecionamentos.
- **Service:** concentra as regras de negócio, como validação, autenticação e geração de hash.
- **Model:** concentra as operações de dados e os comandos SQL parametrizados via PDO.
- **View:** exibe formulários, mensagens e páginas HTML para o usuário.
- **Helpers:** reúnem funções compartilhadas de segurança, sessão, validação e escape de HTML.

Assim, uma alteração na consulta fica no Model, uma alteração na regra fica no Service e uma alteração na apresentação fica na View.

## Estrutura inicial

```text
portal-si/
├── public/                 # Única pasta exposta pelo servidor web
│   └── assets/             # CSS e imagens públicas
├── app/
│   ├── controllers/        # Orquestração das requisições
│   ├── services/           # Regras de negócio
│   ├── models/             # Acesso aos dados
│   ├── helpers/            # Funções auxiliares e segurança
│   └── views/              # Telas e templates
├── config/                 # Configurações da aplicação
├── database/               # Scripts schema.sql e seed.sql
└── docs/                   # Documentação complementar
```

## Próximas etapas

1. Executar `database/schema.sql` e `database/seed.sql` no PostgreSQL.
2. Configurar o PHP/Apache e a extensão `pdo_pgsql`.
3. Executar os testes da Aula 08 no navegador.
4. Gerar o pacote final da equipe.
