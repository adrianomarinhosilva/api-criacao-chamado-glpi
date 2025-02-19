Sistema de Abertura de Chamados GLPI
Visão Geral do Projeto
O projeto é uma interface web personalizada para abertura de chamados no sistema GLPI, desenvolvido para simplificar e agilizar o processo de criação de tickets por usuários.
Características Principais
Interface de Usuário

Formulário web responsivo
Design limpo e intuitivo
Integração com Bootstrap 5
Campos obrigatórios para abertura de chamado

Funcionalidades Chave

Busca Dinâmica de Usuários

Pesquisa em tempo real de usuários cadastrados
Autocompletar com sugestões
Seleção automática do ID do usuário


Integração com API do GLPI

Recuperação de lista de computadores
Busca de usuários
Abertura de chamados via API REST


Campos do Formulário

ID da Entidade
Usuário Requerente
Título do Chamado
Descrição
Hostname
Telefone



Fluxo de Trabalho
Autenticação e Conexão

Uso de tokens de sessão e aplicação
Autenticação via cabeçalhos HTTP
Conexão segura com a API do GLPI

Processo de Abertura de Chamado

Usuário preenche formulário
Sistema valida campos obrigatórios
Busca informações adicionais do usuário
Envia chamado para API do GLPI
Exibe resultado (sucesso ou erro)

Recursos Técnicos
Frontend

HTML5
Bootstrap 5
jQuery
CSS personalizado
JavaScript para busca dinâmica

Backend

PHP
cURL para requisições à API
Tratamento de erros
Validação de campos

Detalhes de Implementação
Busca de Usuários

Filtro em tempo real
Lista de sugestões
Seleção automática do ID

Tratamento de Chamados

Adiciona informações extras na descrição
Vincula hostname ao ticket
Configura notificações

Segurança e Configuração
Pontos de Atenção

Substituir URLs e tokens
Configurar corretamente tokens de API
Validar campos de entrada
Implementar camadas adicionais de segurança

Possíveis Melhorias

Validação de campos mais robusta
Mascara para telefone
Integração com múltiplas entidades
Cache de usuários e computadores
Tratamento de erros mais detalhado

Tecnologias Utilizadas

PHP
cURL
HTML5
Bootstrap
JavaScript
jQuery

Considerações Finais
O projeto oferece uma interface simplificada para abertura de chamados, integrando-se perfeitamente com o sistema GLPI, facilitando o processo para usuários e técnicos.
