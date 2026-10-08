# Preparação do banco administrativo

O painel utiliza a conexão central de `config/database.php` e o mesmo banco da
Home pública. Não há credenciais duplicadas dentro de `administrador/`.

## Migração do banco legado

O arquivo `migrar_schema_legado.sql` atualiza de forma aditiva as três tabelas
antigas encontradas no banco `portal_si`, preservando suas colunas e registros.
Ele também cria as cinco tabelas complementares exigidas pelo projeto.

Antes da primeira execução foi criada a cópia integral
`portal_si_backup_pre_admin_20261008`.

## Verificação

Execute na raiz do projeto:

```powershell
C:\xampp\php\php.exe administrador\database\verificar_banco.php
```

O comando não altera dados e retorna `"valido": true` quando o banco está
compatível com a aplicação.
