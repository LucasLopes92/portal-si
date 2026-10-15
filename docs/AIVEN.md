# PostgreSQL no Aiven

O portal roda no Apache local e pode usar um PostgreSQL remoto via PDO e TLS.

Configuracao realizada: veja o [checklist validado](AIVEN_CHECKLIST.md).

1. No Aiven, crie um servico PostgreSQL no plano Free e aguarde `Running`.
2. Abra `Quick connect` e copie o Service URI do seu proprio servico.
3. Salve somente esse URI em `config/aiven-uri.local.txt`, sem aspas e sem
   comandos como `psql`. Esse arquivo e ignorado pelo Git e bloqueado por HTTP.
4. Crie um banco `portal_si` no servico e use esse nome no caminho do URI.
5. Antes de mudar a conexao, migre o schema e os dados existentes para o banco
   remoto vazio. Preserve IDs, hashes de senha, relacionamentos e sequencias.
   Nao execute o seed sobre os usuarios migrados: ele redefine a senha do admin.
6. Execute `C:\xampp\php\php.exe database\verificar_banco.php` e confira banco,
   servidor e `ssl: true`. Depois execute `tests\painel_admin.php` e abra a Home.

O URI tem este formato (os campos abaixo sao apenas exemplos):

```text
postgresql://avnadmin:SENHA@HOST.aivencloud.com:PORTA/portal_si?sslmode=require
```

Tambem e possivel usar `PORTAL_DATABASE_URL`. As variaveis `PORTAL_DB_HOST`,
`PORTAL_DB_PORT`, `PORTAL_DB_NAME`, `PORTAL_DB_USER`, `PORTAL_DB_PASSWORD` e
`PORTAL_DB_SSLMODE` sobrescrevem os campos correspondentes. Para validar o
certificado com `verify-full`, informe `PORTAL_DB_SSLROOTCERT` com o caminho
do certificado CA do servico. Como alternativa, salve o certificado baixado
do proprio servico em `config/aiven-ca.local.pem`: ele sera carregado
automaticamente quando houver um URI remoto. Nesse caso, use
`sslmode=verify-full` no URI para validar tambem a identidade do servidor.

O arquivo privado e lido tanto no PHP CLI quanto no Apache, sem exigir reinicio.
Quando o URI remoto esta configurado, uma falha da conexao remota gera erro;
o portal nao tenta usar o PostgreSQL local como alternativa.

Nunca publique o URI real, a senha ou dumps do banco. O banco local pode ser
mantido como copia de seguranca durante a migracao.
