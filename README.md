<h3 align="center">Wallet</h3>

<p align="center">
   API Rest que simula transações financeiras entre carteiras de usuários.
</p>

## :pushpin: Requisitos
- Docker
- Docker Compose

## :pushpin: Instalação

Faça o download dos arquivos ou o clone desse repositório: <br>

`git clone https://github.com/4ngelica/wallet.git`

Renomeie o arquivo .env.example para .env e preencha as variáveis de ambiente:

    APP_KEY=
    APP_DEBUG='true'
    APP_ENV=local
    APP_URL=http://{{IP}}:93
    DB_CONNECTION=mysql
    DB_DATABASE=wallet
    DB_HOST={{IP}}
    DB_PASSWORD=root
    DB_PORT='3304'
    DB_USERNAME=root
    QUEUE_CONNECTION=database

Na raíz do diretório, rode o comando a seguir para buildar e subir os containers, instalar as dependências, gerar a APP_KEY, executar as migrations e testes unitários. O banco será populado com dados de usuários de exemplo. Todos os usuários utilizarão a senha padrão (1234).<br>

```sh
sudo make install
```

## :pushpin: Documentação
[Acesse a collection no Postman](https://www.postman.com/4ngelica/wallet/overview)


## :pushpin: Abordagem

Este projeto foi desenvolvido utilizando Laravel 10 como framework, Docker para conteinerização e MySql para armazenamento dos dados e gerenciamento de filas.

O fluxo principal do código é baseado em uma API que recebe um POST com os dados necessários para realizar uma transferência entre duas carteiras de usuários. É possível fazer transações imediatas ou agendadas. Para agendar uma transferência, deve-se informar uma data no campo opcional scheduled_date.

Visto que os critérios de aceite mencionam a existência de senha na model do usuário, foi implementada uma autenticação simples utilizando o pacote do Sanctum, que já vem instalado no Laravel.

Uma vez autenticado, quem utiliza a API não precisa se identificar no corpo da requisição, já que essa informação pode ser extraída do token do usuário. Além disso, ao evitar o envio do identificador do usuário pela API, evita-se também a necessidade de validar se o valor recebido no body corresponde ao usuário autenticado, resultando em uma resposta mais rápida.

Para o sistema de filas, foi utilizada a abordagem baseada em banco de dados do Laravel. Essa escolha simplifica a infraestrutura mantendo todas as informações centralizadas. Para garantir a execução foi utilizado o Supervisor como monitor de processos, configurado para reiniciar automaticamente as workers em caso de falhas.

## :pushpin: Fluxogramas

Fluxo da API:
<p align="center"><img width="80%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/API.png"></p>

ProcessTransaction Job:
<p align="center"><img width="80%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/ProcessTransaction.png"></p>

NotifyUser Job:
<p align="center"><img width="80%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/NotifyUser.png"></p>

## :pushpin: Modelagem dos Dados

As principais entidades relacionais são Carteira (wallet), Usuário (user) e Transação (transaction). Usuário e carteira possuem uma relação de 1:1 e Usuário e Transação possuem uma relação de N:N. Quando um novo registro de usuário é criado via seeder, uma carteira é associada a esse usuário, carregando como chave estrangeira a chave primária do usuário (user_id).

Uma transação tem duas chaves estrangeiras: payer_id e payee_id. Essas chaves correspondem às chaves primárias do usuário que envia o dinheiro e o que recebe, respectivamente.

Além disso, as tabelas job e failed_jobs são responsáveis por armazenar os Jobs referentes às filas default (usada no fluxo de agendamento de transferência) e notify (usada para envio da notificação de transferência recebida).

<p align="center"><img width="80%" src="https://raw.githubusercontent.com/4ngelica/wallet/refs/heads/master/storage/images/ERD.png"></p>

## :pushpin: Próximos passos e possíveis melhorias
 
- Incluir tabela de logs para armazenar informações dos jobs executados.
- Outra opção para execução das filas seria utilizar o Redis, visando um processamento mais rápido em situações de overload;
- Implementação de métodos destroy (e possivelmente update) para cancelar ou alterar transações agendadas. Esses métodos devem buscar e verificar o status das transações antes de performar qualquer alteração. Isso adiciona complexidade pois envolve identificar o job despachado.
- Limitar o tamanho alocado para os campos VARCHAR do banco de dados, reduzindo disperdício e o tamanho total do banco;
- Criar versionamento para a API

## :pushpin: Referências
- [Laravel 10](https://laravel.com/docs/10.x)
- [Supervisor](https://laravel.com/docs/10.x/queues#supervisor-configuration)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)