# Painel administrativo

Módulo administrativo isolado do Portal SI.

## Organização

- `assets/css/`: estilos exclusivos do painel;
- `controllers/`: coordenação das requisições administrativas;
- `database/`: scripts específicos do módulo, quando necessários;
- `includes/`: inicialização, autenticação e componentes compartilhados;
- `models/`: acesso aos dados do painel;
- `services/`: validações e regras de negócio;
- `uploads/`: arquivos enviados pelo painel;
- `views/`: páginas e componentes visuais internos.

O CRUD e a autenticação administrativa serão adicionados gradualmente nas próximas etapas.
