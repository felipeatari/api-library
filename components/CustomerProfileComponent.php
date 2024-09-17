<?php

namespace app\components;

use app\components\ResponseComponent as Response;
use app\components\TokenComponent as Token;
use app\models\Customer;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Yii;

class CustomerProfileComponent
{
    public function __construct(
        private mixed $cutumerProfile
    )
    {
    }

    public function create()
    {
        Token::verify();

        $fileName = 'customer-profile';

        $files = $_FILES[$fileName] ?? [];

        if (! $files) {
            Response::api(400, 'Nome do arquivo inválido.', status: false);
        }

        if ((int) $files['error'] !== 0) {
            Response::api(500, 'Erro ao fazer upload.', status: false);
        }

        $customerID = Yii::$app->request->getQueryParam('customerID');

        if (! $customerID) {
            Response::api(400, 'O Parâmetro customerID deve ser informado.', status: false);
        }

        if (! Customer::findOne($customerID)) {
            return Response::api(404, 'Cliente não cadastrado.', status: false);
        }

        $extensionFile = pathinfo($files['name'], PATHINFO_EXTENSION);
        $fileNameNew = uniqid() . '.' . $extensionFile;
        $fileDir = __DIR__ . '/../runtime/uploads/customer-profiles/';
        $fileDirUpload = $fileDir . $fileNameNew;
        $fileTmp = $files['tmp_name'];

        $maximumSize = 2 * 1024 * 1024;

        if ((int) $files['size'] > $maximumSize) {
            Response::api(400, 'A imagem ultrapassa 2MB.', status: false);
        }

        if (! in_array($extensionFile, ['jpg','png'])) {
            Response::api(400, 'A extenção do arquivo não é permitida. Tente: jpg ou png.', status: false);
        }

        if (! is_dir($fileDir)) {
            mkdir($fileDir, 0755, true);
        }

        if (! move_uploaded_file($fileTmp, $fileDirUpload)) {
            Response::api(500, 'Falha ao realizar o download.', status: false);
        }

        try {
            $s3Client = new S3Client([
                'region'  => S3_REGION,
                'version' => S3_VERSION,
                'credentials' => [
                    'key' => S3_CREDENTIAL_KEY,
                    'secret' => S3_CREDENTIAL_SECRET,
                ],
            ]);
    
            $response = $s3Client->putObject([
                'Bucket' => S3_BUCKET,
                'Key' => S3_KEY_CUSTOMER_PROFILES . '/' . $fileNameNew,
                'SourceFile' => $fileDirUpload,
                'ACL'=> S3_ACL,
            ]);
        }
        catch (S3Exception $e) {
            Response::api(500, $e->getMessage(), status: false);
        }
        catch (Exception $e) {
            Response::api(500, $e->getMessage(), status: false);
        }

        $objectUrl ??= $response['ObjectURL'];

        // Não mantém a imagem no servidor
        unlink($fileDirUpload);

        if (! $objectUrl) {
            Response::api(500, 'URL do objeto indisponível.', status: false);
        }

        $cutumerProfile = new $this->cutumerProfile;
        $cutumerProfile->customer_id = $customerID;
        $cutumerProfile->profile = $objectUrl;

        if (! $cutumerProfile->save()) {
            return Response::api(500, 'Erro durante a gravação dos dados.', status: false);
        }

        $data = [
            'id' => $cutumerProfile->id,
            'customer_id' => $customerID,
            'profile' => $objectUrl,
        ];
        
        Response::api(201, data: $data);
    }
}