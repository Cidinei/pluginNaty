# pluginNaty
 Plugin WordPress

 Claro! Vamos criar uma documentação básica para o seu plugin.

---

## Documentação: Plugin Naty App Oficial

### Descrição:
Este plugin permite a integração com a plataforma Naty para envio de notificações via WhatsApp. 

### Instalação:
1. Faça o upload da pasta `naty_app_oficial` para o diretório `/wp-content/plugins/` do seu WordPress.
2. Ative o plugin através do menu 'Plugins' no WordPress.

### Funcionalidades:

#### Configuração Geral:

- **Chave de Autorização**: Campo para inserir sua chave de autorização da plataforma Naty.
  
- **WhatsApp ID**: Selecione o ID do WhatsApp a ser usado para o envio das notificações.
  
- **Msg Modelo Formulário 1 e 2**: Personalize o texto das mensagens que serão enviadas pela Naty App para o usuário.

- **Atraso (Delay) em Segundos**: Configure o atraso antes de enviar a notificação.

#### Submenu "Inside":

- **Configurações Inside**: Personalize o link do servidor e o controle de disparo do formulário.
  
- **Servidor**: Endereço do servidor para a integração.

- **Porta**: Porta para a integração.

#### Chamadas da API:
O plugin registra todas as chamadas feitas para a plataforma Naty e exibe-as em uma tabela no submenu "Disparos Realizados".

#### Shortcodes:
- `[naty_app_elementor_form]`: Exibe um formulário com campos para Nome e Número.

- `[naty_app_elementor_form_with_phone_document]`: Exibe um formulário com campos para Telefone e Documento.

### Uso:

1. Vá para o painel de administração e clique no menu "Naty App" no lado esquerdo.
2. Configure suas opções conforme necessário.
3. Use os shortcodes nas páginas ou postagens onde deseja exibir os formulários.

### Suporte:

Para suporte, entre em contato com o autor do plugin, Cidinei Julho, através do site [seediti.org](https://seediti.org/).

---

Lembre-se de que esta é uma documentação básica. Dependendo da complexidade do seu plugin e das funcionalidades adicionais, você pode precisar expandir ou adicionar mais seções à documentação. Se precisar de mais detalhes ou seções adicionais, por favor, me avise!
