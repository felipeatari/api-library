<?php

namespace app\components;

use app\components\BrasilApiComponent as BrasilApi;
use app\components\TokenComponent as Token;
use app\components\ResponseComponent as Response;
use yii\data\Sort;
use Yii;

class BookComponent
{
    public function __construct(
        private mixed $book
    )
    {
    }

    public function index()
    {
        Token::verify();

        $offset = Yii::$app->request->getQueryParam('offset') ?? '0';
        $limit = Yii::$app->request->getQueryParam('limit') ?? '1';
        $order = Yii::$app->request->getQueryParam('order') ?? '';
        $sort = Yii::$app->request->getQueryParam('sort') ?? '';
        $filterTitulo = Yii::$app->request->getQueryParam('filterTitulo') ?? '';
        $filterAutor = Yii::$app->request->getQueryParam('filterAutor') ?? '';
        $filterIsbn = Yii::$app->request->getQueryParam('filterIsbn') ?? '';
        $field = Yii::$app->request->getQueryParam('field') ?? [];

        $orderBy = [];
        $filterBy = [];
        $filter = '';

        if ($order === 'nome' or $order === 'cpf' or $order === 'cidade') {
            if ($sort === 'asc') {
                $orderBy[$order] = SORT_ASC;
            } 
            elseif ($sort === 'desc') {
                $orderBy[$order] = SORT_DESC;
            }
        }

        if ($filterTitulo !== '') {
            $filterBy['titulo'] = $filterTitulo;
        } 
        if ($filterAutor !== '') {
            $filterBy['titulo'] = $filterAutor;
        } 
        elseif ($filterIsbn !== '') {
            $filterIsbn = $this->validateIsbn($filterIsbn);
            
            $filterBy['cpf'] = $filterIsbn;
        }

        if ($filterBy) {
            $filter = array_keys($filterBy)[0];
        }

        if (is_string($field) and $field) {
            $field = explode(',', $field);
        }

        $books = $this->book::find()
            ->select($field)
            ->where($filterBy)
            ->offset($offset)
            ->limit($limit)
            ->orderBy($orderBy)
            ->all();
        
        if (! $books) {
            return Response::api(404, 'Livro(s) não encontrado(s).', status: false);
        }
        
        $books = array_map(fn($book) => $book->toArray(), $books);

        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $data['filter'] = $filter;
        $data['books'] = $books;

        return Response::api(200, data: $data);
    }

    public function create()
    {
        Token::verify();

        $request = Yii::$app->request->post();

        if (! isset($request['isbn']) or ! $request['isbn']) {
            return Response::api(400, 'Verifique o campo isbn.', status: false);
        }

        if (! isset($request['titulo']) or ! $request['titulo']) {
            return Response::api(400, 'Verifique o campo titulo.', status: false);
        }

        if (! isset($request['autor']) or ! $request['autor']) {
            return Response::api(400, 'Verifique o campo autor.', status: false);
        }

        if (! isset($request['preco']) or ! $request['preco']) {
            return Response::api(400, 'Verifique o campo preco.', status: false);
        }

        if (! isset($request['estoque']) or ! $request['estoque']) {
            return Response::api(400, 'Verifique o campo estoque.', status: false);
        }

        $request['isbn'] = $this->validateIsbn($request['isbn'], true);

        $book = new $this->book;
        
        if ($book->findOne(['isbn' => $request['isbn']])) {
            return Response::api(400, 'Livro já foi cadastrado.', status: false);
        }

        $book->isbn = $request['isbn'];
        $book->titulo = $request['titulo'];
        $book->autor = $request['autor'];
        $book->preco = $request['preco'];
        $book->estoque = $request['estoque'];
        
        if (! $book->save()) {
            return Response::api(500, 'Erro durante a gravação dos dados.', status: false);
        }
    
        $data = [
            'book' => [
                'id' => $book->id,
                'isbn' => $book->isbn,
                'titulo' => $book->titulo,
                'autor' => $book->autor,
                'preco' => $book->preco,
                'estoque' => $book->estoque,
            ],
        ];

        return Response::api(201, data: $data);
    }

    /**
     * Valida o ISBN
     *
     * @param string $isbn ISBN que o usuário informou
     * @param boolean $validateTheApi Valida na API Brasil API ou não
     * 
     * @return Response|string Em caso de erro retorna a classe Response e sucesso retorna o ISBN formatado
     */
    private function validateIsbn(string $isbn, bool $validateTheApi = false): Response|string
    {
        /*
         Obs.: API Brasil API já valida o ISBN. Porém é importante validar antes por questão de performance,
         pois não faz sentido mandar um ISBN inválido, mal formatado ou com caracters à mais, à menos ou inválidos.
         Isso porque a API tem um certo custo e gastar e arriscar esse custo com uma validação que pode ser feita no sistema não é o ideal.
        */
        // ISBN: 10 - Obsoleto
        $isbnValidateWithPunctuation10 = preg_match('/^([\d]{1})\-([\d]{3})\-([\d]{5})\-([\d]{1})$/', $isbn, $matches);
        $isbnValidateNoPunctuation10 = preg_match('/^([\d]{10})$/', $isbn, $matches);

        // ISBN: 13 - Atual
        $isbnValidateWithPunctuation13 = preg_match('/^([\d]{3})\-([\d]{1})\-([\d]{3})\-([\d]{5})\-([\d]{1})$/', $isbn, $matches);
        $isbnValidateNoPunctuation13 = preg_match('/^([\d]{13})$/', $isbn, $matches);

        if (! $isbnValidateWithPunctuation10 and ! $isbnValidateNoPunctuation10 and ! $isbnValidateWithPunctuation13 and ! $isbnValidateNoPunctuation13) {
            $message = 'ISBN inválido. Para ISBN 10 considere: X-XXX-XXXXX-X ou XXXXXXXXXX.';
            $message .= 'Para ISBN 13 considere: XXX-X-XXX-XXXXX-X ou XXXXXXXXXXXXX.';
            return Response::api(400, $message, status: false);
        }

        if ($isbnValidateNoPunctuation10) {
            $isbn = preg_replace('/^([\d]{1})([\d]{3})([\d]{5})([\d]{1})$/', '$1-$2-$3-$4', $isbn);
        }
        
        if ($isbnValidateNoPunctuation13) {
            $isbn = preg_replace('/^([\d]{3})([\d]{1})([\d]{3})([\d]{5})([\d]{1})$/', '$1-$2-$3-$4-$5', $isbn);
        }

        // Verifica se o ISBN existe
        if ($validateTheApi) {
            if (! BrasilApi::isbn($isbn)) {
                return Response::api(404, 'ISBN não encontrado. Verifique se digitou corretamente.', status: false);
            }
        }

        return $isbn;
    }
}