# Roteiro de testes do painel

Data da consolidação: 8 de outubro de 2026.

## Verificação automática

Execute a partir da raiz do projeto:

```powershell
C:\xampp\php\php.exe administrador\tests\executar.php
```

O teste termina com código `0` somente quando todos os casos passam. Os registros temporários são protegidos por transação e removidos com `rollback`, inclusive quando alguma etapa falha.

Casos verificados:

1. presença das tabelas e colunas obrigatórias;
2. rejeição de senha incorreta;
3. acesso permitido para `admin` e `editor` e negado para `aluno`;
4. geração e validação do token CSRF;
5. escape de HTML informado pelo usuário;
6. rejeição dos campos inválidos do cadastro;
7. cadastro de conteúdo publicado e listagem paginada;
8. exibição do conteúdo publicado na Home pública;
9. atualização dos indicadores do dashboard;
10. edição e arquivamento, com retirada da Home;
11. exclusão e tratamento de registro já removido;
12. presença do controle de acesso em todas as rotas protegidas;
13. uso de `POST` e CSRF na exclusão;
14. remoção dos registros temporários ao final.

## Conferência manual no navegador

1. Entre em `administrador/login.php` com uma conta `admin` ou `editor`.
2. Confira os cartões da visão geral.
3. Abra **Conteúdos** e confirme a tabela e a paginação.
4. Cadastre um rascunho e confirme que ele não aparece na Home.
5. Edite o registro, altere o status para **Publicado** e confirme sua presença na Home.
6. Arquive o registro e confirme que ele deixou de aparecer na Home.
7. Abra **Excluir**, cancele uma vez e confirme que nada foi apagado.
8. Repita a exclusão, confirme a ação e confira a mensagem de sucesso.
9. Encerre a sessão e tente abrir diretamente `administrador/index.php`; o sistema deve voltar ao login.

O upload da imagem de capa não faz parte desta versão do painel.
