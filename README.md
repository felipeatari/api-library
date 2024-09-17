**NOTA:**  
- Este é meu primeiro contato com o Yii2 Framework, então possivelmente posso ter encontrado algumas limitações em relação ao framework.  
- Porém, já adianto que gostei bastante dessa ferramenta. Como já venho do Laravel e CakePHP, foi mais simples entender como ela funciona. Achei bem versátil e flexível.  
- Recomendo fortemente utilizar o PHP 8+, pois estou utilizando alguns recursos dessa versão.  
- Desenvolvi o projeto no Ubuntu como subsistema do Windows 11 via WSL 2. O MySQL rodei em um container Docker e testei as requisições da API via Postman.
- Deixei o projeto o mais "enxuto" possível. Removi alguns arquivos e pastas e adicionei outros, pois não gosto de me limitar a ferramenta. Todos as novas classes estão na pasta "components". 
- Como o projeto é simples e utilizei o token JWT, resolvi não salvar o token no banco de dados.
- No Bucket S3, criei duas pastas dentro do bucket. As pastas foram nomeadas como "customer-profiles" e "book-covers", e o bucket foi nomeado como "library". No arquivo "config/env.php" há os campos para configura-los.
- Optei por utilizar esses nomes porque o teste extra dizia para criar um sistema de uploads de imagens para o cadastro de livros e clientes. Como não foi especificado, deduzi que a imagem do cliente seria sua foto de perfil e a imagem do livro seria a capa do mesmo.
- Esses nomes também utilizei na regra de negócios. Optei por deixar o código em inglês, mas os campos das tabelas em português.

## Pré-requisitos e versões das ferramentas:
1. Ubuntu: 22.04.5 LTS;
2. PHP: 8.2.23 (cli) (built: Aug 30 2024 09:21:47) (NTS);
3. Composer: 2.7.8 2024-08-22 15:28:36;
4. MySQL: 8.0.39 for Linux on x86_64 (MySQL Community Server - GPL).

## Configurações e execução do projeto:
Primeiro baixe o repositório do projeto no Github.

Inicialmente renomeie:
```php
config/env.php.example
```
Para:
```php
config/env.php
```
Depois configure os dados do token JWT como chave secreta, tempo de expiração do token e do refresh token.

O tempo de expiração de ambos deve ser em timestamp e como inteiro.

É importante que o refresh token tenha um tempo de expiração maior que o token.

Obs.: É interessante manter uma copia do arquivo ".env.php.example".

---

Credenciais do Banco de Dados:
```php
config/db.php
```
Essa parte é importante para prosseguir com o projeto. Depois, é só rodar as migrations.

---

Baixe todas as dependências do projeto.
```bash
composer install
```

---

Após "installar o projeto", rode o servidor:
```bash
php yii serve
```

---

Se não apresentou nenhum erro até aqui, basta acessar a URL base do projeto. Aparecerá algo como:
```json
{
  "code": 200,
  "status": "success",
  "data": {
    "message": "Hello at my API Library!",
    "author": "Dev Luiz Felipe"
  }
}
```

---

Se o servidor for executado com sucesso, rode as migrations para criar as tabelas do banco de dados:
```bash
php yii migrate
```
Obs.: Deve-se criar o banco de dados antes.

---

É hora de criar o usuário. Acesse:
```php
commands/CreateUserController.php
```
Há duas variáveis no método `actionIndex()` que são `$nome` e `$senha`. Já existem dados padrão de teste, mas podem ser alterados.

Agora basta rodar no terminal o seguinte comando para criar o usuário:
```bash
php yii create-user
```

É possível listar os usuários cadastrados. Basta rodar o seguinte comando:
```bash
php yii list-user
```

---

## Executando as APIs:
**NOTA:**  
- O projeto possui cinco APIs: Token, Customer, Book, Customer Profile e Book Cover.
- Todas seguem o padrão REST.
- Os métodos HTTP seguem corretamente suas respectivas ações.
- Todos os HTTP status codes retornam um número de acordo com a resposta.
- As APIs de Customer, Book, Customer Profile e Book Cover. recebem no Header, obrigatoriamente, o Bearer token.
- Os tokens são no formato JWT e possuem tempo de expiração.
- Temos o Token principal e o Refresh Token.

### Execução:
**API Token**
- Criar: POST
```bash
curl --location '{{base}}/tokens' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "nome": "Admin",
    "senha": "12345"
}'
```

- Renovar: PUT
```bash
curl --location --request PUT '{{base}}/tokens/:userID' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "refresh_token": "{{refresh_token}}"
}'
```

---

**API Customer**
- Criar: POST
```bash
curl --location '{{base}}/customers' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer {{token}}' \
--data '{
    "nome": "Luiz Felipe",
    "cpf": "123.456.789-01",
    "endereco": {
        "cep": "12345-678",
        "logradouro": "Macajuba",
        "numero": "123",
        "cidade": "Star City",
        "estado": "CE",
        "complemento": ""
    }
}'
```

Obs.: Não cadastra cliente com mesmo CPF.

- Buscar Cliente(s)
```bash
curl --location '{{base}}/customers' \
--header 'Authorization: Bearer {{token}}'
```

Obs.: Se o parâmetro "limit" não for especificado, então a busca traz o primeiro registro.

#### Para buscas inteligentes e otimizadas, considere utilizar os seguintes parâmetros via query strings:
1. offset: De onde a busca deve partir. Nota: Para paginação, mas também pode ser usado para trazer um único registro.
2. limit: Limita a quantidade de registros por página. Nota: Para paginação.
3. order: Ordena por nome, CPF, ou cidade. Nota: Utilizar junto com sort.
4. sort: Ordena os dados em ordem crescente (asc) ou decrescente (desc). Nota: Utilizar junto com order.
5. filterNome: Filtra pelo nome.
6. filterCpf: Filtra pelo CPF.
7. field: Traz somente os campos desejados. Nota: Importante para otimizar buscas.

---

**API Book**
- Criar: POST
```bash
curl --location '{{base}}/books' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer {{token}}' \
--data '{
    "isbn": "9-788-54570-287-0",
    "titulo": "As crônicas de Gelo e Fogo 1",
    "autor": "George R.R. Martin",
    "preco": 25.99,
    "estoque": 10
}'
```

Obs.: Não cadastra livro com mesmo ISBN.

- Buscar: GET
```bash
curl --location '{{base}}/books' \
--header 'Authorization: Bearer {{token}}'
```

Obs.: Se o parâmetro "limit" não for especificado, então a busca traz o primeiro registro.

#### Para buscas inteligentes e otimizadas, considere utilizar os seguintes parâmetros via query strings:
1. offset: De onde a busca deve partir. Nota: Para paginação, mas também pode ser usado para trazer um único registro.
2. limit: Limita a quantidade de registros por página. Nota: Para paginação.
3. order: Ordena por título ou preço. Nota: Utilizar junto com sort.
4. sort: Ordena os dados em ordem crescente (asc) ou decrescente (desc). Nota: Utilizar junto com order.
5. filterTitulo: Filtra pelo título.
6. filterAutor: Filtra pelo autor.
7. filterIsbn: Filtra pelo ISBN.
8. field: Traz somente os campos desejados. Nota: Importante para otimizar buscas.


---

**API Customer Profile**
- Criar: POST
```bash
curl --location '{{base}}/customer-profiles?customerID=1' \
--header 'Content-Type: multipart/form-data' \
--header 'Authorization: Bearer {{token}}' \
--form 'customer-profile=@"{{file_path}}"'
```

---

**API Book Cover**
- Criar: POST
```bash
curl --location '{{base}}/book-covers?customerID=1' \
--header 'Content-Type: multipart/form-data' \
--header 'Authorization: Bearer {{token}}' \
--form 'book-cover=@"{{file_path}}"'
```

---

#### Disponibilizei um modelo pré-defido do Postman para testar as requisições via Google Drive: [Baixar](https://drive.google.com/file/d/14FB81ZNKR0LErqJtLnloYvFcwXc40Y6t/view?usp=drive_link)
