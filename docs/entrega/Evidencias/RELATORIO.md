# Relatório de validação da entrega

Rodada em 12/11/2026, Windows, Chrome, Apache/XAMPP, PHP 8.2.12 e PostgreSQL 18.4 local.

Foram aprovados 44 casos de sistema, incluindo verificações extras de mídia, por HTTP, Chrome e consultas ao banco. Os resultados individuais estão em `matriz_resultados.json`. Contas, publicações e arquivos temporários foram removidos. As verificações de contraste e teclado são amostrais, e não uma certificação completa de acessibilidade. O iframe foi validado sem homologar a reprodução no serviço externo.

As três rotinas de integração/regressão passaram e constam em `Rotinas/`. A sintaxe de 55 arquivos PHP passou em `php -l`. `verificacao_banco.json` confirma nove tabelas e 20 publicações após a limpeza.

O dump completo foi restaurado em base isolada; as contagens e o acesso com a nova credencial foram conferidos. Essa base de validação foi removida ao final. O banco em uso e suas credenciais não foram substituídos.

Os dados públicos são fictícios e as fotos ilustrativas. A cópia de avaliação anonimiza contas e exclui auditoria original. A senha de avaliação está fora do código e do Git, em arquivo reservado do pacote. O pacote não inclui as credenciais PostgreSQL do computador da equipe.

Os documentos foram preparados em cópias dos modelos. Nome de equipe mantido vazio por solicitação do usuário. Revisão humana, ensaio e submissão pelo canal da disciplina continuam sendo atividades da equipe.
