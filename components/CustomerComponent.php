<?php

namespace app\components;

use app\components\ResponseComponent as Response;
use app\components\TokenComponent as Token;
use app\services\BrasilApiService as BrasilApi;
use yii\data\Sort;
use Yii;

class CustomerComponent
{
    public function __construct(
        private mixed $customer
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
        $filterNome = Yii::$app->request->getQueryParam('filterNome') ?? '';
        $filterCpf = Yii::$app->request->getQueryParam('filterCpf') ?? '';
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

        if ($filterNome !== '') {
            $filterBy['nome'] = $filterNome;
        } 
        elseif ($filterCpf !== '') {
            $filterCpf = $this->validateCpf($filterCpf);
            
            $filterBy['cpf'] = $filterCpf;
        }

        if ($filterBy) {
            $filter = array_keys($filterBy)[0];
        }

        if (is_string($field) and $field) {
            $field = explode(',', $field);
        }

        $customers = $this->customer::find()
            ->select($field)
            ->where($filterBy)
            ->offset($offset)
            ->limit($limit)
            ->orderBy($orderBy)
            ->all();
        
        if (! $customers) {
            return Response::api(404, 'Cliente(s) não encontrado(s).', status: false);
        }
        
        $customers = array_map(fn($customer) => $customer->toArray(), $customers);

        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $data['filter'] = $filter;
        $data['customers'] = $customers;

        return Response::api(200, data: $data);
    }

    public function create()
    {
        Token::verify();

        $request = Yii::$app->request->post();

        if (! isset($request['nome']) or ! $request['nome']) {
            return Response::api(400, 'Verifique o campo nome.', status: false);
        }

        if (! isset($request['cpf']) or ! $request['cpf']) {
            return Response::api(400, 'Verifique o campo cpf.', status: false);
        }

        if (! isset($request['endereco']['logradouro']) or ! $request['endereco']['logradouro']) {
            return Response::api(400, 'Verifique o campo logradouro.', status: false);
        }

        if (! isset($request['endereco']['numero'])) {
            return Response::api(400, 'Verifique o campo numero.', status: false);
        }

        if (! isset($request['endereco']['cidade']) or ! $request['endereco']['cidade']) {
            return Response::api(400, 'Verifique o campo cidade.', status: false);
        }

        if (! isset($request['endereco']['estado']) or ! $request['endereco']['estado']) {
            return Response::api(400, 'Verifique o campo estado.', status: false);
        }

        if (! isset($request['endereco']['complemento'])) {
            return Response::api(400, 'Verifique o campo cep.', status: false);
        }

        $request['cpf'] = $this->validateCpf($request['cpf']);

        $request['endereco']['cep'] = $this->validateCep($request['endereco']['cep'], true);

        $customer = new $this->customer;

        if ($customer->findOne(['cpf' => $request['cpf']])) {
            return Response::api(400, 'Cliente já cadastrado.', status: false);
        }

        $customer->nome = $request['nome'];
        $customer->cpf = $request['cpf'];
        $customer->cep = $request['endereco']['cep'];
        $customer->logradouro = $request['endereco']['logradouro'];
        $customer->numero = $request['endereco']['numero'] ?? '';
        $customer->cidade = $request['endereco']['cidade'];
        $customer->estado = $request['endereco']['estado'];
        $customer->complemento = $request['endereco']['complemento'] ?? '';
        
        if (! $customer->save()) {
            return Response::api(500, 'Erro durante a gravação dos dados.', status: false);
        }
    
        $data = [
            'customer' => [
                'id' => $customer->id,
                'nome' => $customer->nome,
                'cpf' => $customer->cpf,
                'endereco' => [
                    'cep' => $customer->cep,
                    'logradouro' => $customer->logradouro,
                    'numero' => $customer->numero,
                    'cidade' => $customer->cidade,
                    'estado' => $customer->estado,
                    'complemento' => $customer->complemento,
                ]
            ],
        ];

        return Response::api(201, data: $data);
    }

    /**
     * Gera a senha do cliente de forma segura para ser salva
     *
     * @param string $senha Sem encripitação
     * 
     * @return string Senha encripitada
     */
    private function securePassword(string $senha): string
    {
        return Yii::$app->getSecurity()->generatePasswordHash($senha);
    }

    /**
     * Valida o CPF
     *
     * @param string $cpf CPF que o usuário informou
     * 
     * @return Response|string Em caso de erro retorna a classe Response e sucesso retorna o CPF formatado
     */
    private function validateCpf(string $cpf): Response|string
    {
        $cpfValidateWithPunctuation = preg_match('/^([\d]{3})\.([\d]{3})\.([\d]{3})\-([\d]{2})$/', $cpf, $matches);
        $cpfValidateNoPunctuation = preg_match('/^([\d]{11})$/', $cpf, $matches);

        if (! $cpfValidateWithPunctuation and ! $cpfValidateNoPunctuation) {
            return Response::api(400, 'CPF inválido. Considere: XXX.XXX.XXX-XX ou XXXXXXXXXXX');
        }

        if ($cpfValidateNoPunctuation) {
            $cpf = preg_replace('/^([\d]{3})([\d]{3})([\d]{3})([\d]{2})$/', '$1.$2.$3-$4', $cpf);
        }

        return $cpf;
    }

    /**
     * Valida o CEP
     *
     * @param string $cep CEP que o usuário informou
     * @param boolean $validateTheApi Valida na API Brasil API ou não
     * 
     * @return Response|string Em caso de erro retorna a classe Response e sucesso retorna o CEP formatado
     */
    private function validateCep(string $cep, bool $validateTheApi = false): Response|string
    {
        /*
         Obs.: API Brasil API já valida o CEP. Porém é importante validar antes por questão de performance,
         pois não faz sentido mandar um CEP inválido, mal formatado ou com caracters à mais, à menos ou inválidos.
         Isso porque a API tem um certo custo e gastar e arriscar esse custo com uma validação que pode ser feita no sistema não é o ideal.
        */
        $cepValidateWithPunctuation = preg_match('/^([\d]{5})\-([\d]{3})$/', $cep, $matches);
        $cepValidateNoPunctuation = preg_match('/^([\d]{8})$/', $cep, $matches);

        if (! $cepValidateWithPunctuation and ! $cepValidateNoPunctuation) {
            return Response::api(400, 'CEP inválido. Considere: XXXXX-XXX ou XXXXXXXX', status: false);
        }

        if ($cepValidateNoPunctuation) {
            $cep = preg_replace('/^([\d]{5})([\d]{3})$/', '$1-$2', $cep);
        }

        // Verifica se o CEP existe
        if ($validateTheApi) {
            if (! BrasilApi::cep($cep)) {
                return Response::api(404, 'CEP não encontrado. Verifique se digitou corretamente.', status: false);
            }
        }

        return $cep;
    }
}