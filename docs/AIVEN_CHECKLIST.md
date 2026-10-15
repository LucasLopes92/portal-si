# Checklist Aiven — Portal SI

Validado em 15/10/2026.

## Conta e servico

- [x] Conta existente acessada pelo navegador autenticado.
- [x] Projeto `portal-si-esucri` confirmado.
- [x] Servico `portal-si-postgres` criado no plano **Free**, sem plano pago.
- [x] PostgreSQL 18.6 com estado **Running**.
- [x] Host: `portal-si-postgres-portal-si-esucri.d.aivencloud.com`.
- [x] Porta: `26157`; usuario de conexao: `avnadmin`.
- [x] Senha e Service URI guardados somente em `config/aiven-uri.local.txt`, ignorado pelo Git e bloqueado por HTTP.

## Banco e migracao

- [x] Banco `portal_si` criado no servico.
- [x] Conexao remota confirmada por `SELECT version()` e `SELECT current_database()`.
- [x] As 8 tabelas do banco local foram migradas, com seus indices, restricoes e gatilhos.
- [x] Todos os registros foram comparados entre origem e destino: 4 usuarios e 6 categorias; demais tabelas vazias na migracao.
- [x] Hashes de senha e IDs preservados; as 7 sequencias foram comparadas antes de ativar a conexao remota.
- [x] Backups anteriores a migracao mantidos em `database/backups/`, fora do Git e bloqueados por HTTP.
- [x] Banco local preservado. O seed nao foi reaplicado sobre os usuarios existentes.

## PHP e portal

- [x] Apache do XAMPP iniciado; PHP, PDO e PDO_PGSQL disponiveis.
- [x] PHP CLI conectado a Aiven com `ssl: true`.
- [x] TLS com `sslmode=verify-full`, validando o certificado e o nome do servidor com `config/aiven-ca.local.pem`.
- [x] `database/verificar_banco.php` confirmou estrutura valida, versao, banco e registros.
- [x] Os 11 testes de `tests/painel_admin.php` passaram no banco remoto. Os registros de teste foram revertidos.
- [x] Home pelo Apache respondeu HTTP 200 e exibiu as categorias consultadas do banco remoto.
- [x] Acesso HTTP aos arquivos de conexao, certificado e backup negado com HTTP 403.

O navegador acessa o portal e o console administrativo da Aiven. Quem se
conecta ao PostgreSQL e o PHP, usando a configuracao privada no servidor.
O portal continua em `http://localhost/portal-si/`; o banco agora fica na nuvem.

O plano Free pode desligar o servico apos inatividade. Se isso ocorrer,
abra o console da Aiven e religue o servico.

Para voltar ao banco local, retire o arquivo `config/aiven-uri.local.txt`
da pasta `config` e confira que nao ha variaveis `PORTAL_DATABASE_URL` ou
`PORTAL_DB_*` apontando ao remoto. Isso nao sincroniza alteracoes feitas
na Aiven depois da migracao.
