<?php

namespace app\components;

use app\components\ResponseComponent as Response;
use app\components\TokenComponent as Token;
use app\models\Book;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Yii;

class BookCoverComponent
{
    public function __construct(
        private mixed $bookCover
    )
    {
    }

    public function create()
    {
        Token::verify();

        $fileName = 'book-cover';

        $files = $_FILES[$fileName] ?? [];

        if (! $files) {
            Response::api(400, 'Nome do arquivo inválido.', status: false);
        }

        if ((int) $files['error'] !== 0) {
            Response::api(500, 'Erro ao fazer upload.', status: false);
        }

        $bookID = Yii::$app->request->getQueryParam('bookID');

        if (! $bookID) {
            Response::api(400, 'O Parâmetro bookID deve ser informado.', status: false);
        }

        if (! Book::findOne($bookID)) {
            return Response::api(404, 'Livro não cadastrado.', status: false);
        }

        $extensionFile = pathinfo($files['name'], PATHINFO_EXTENSION);
        $fileNameNew = uniqid() . '.' . $extensionFile;
        $fileDir = __DIR__ . '/../runtime/uploads/book-covers/';
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
                'Key' => S3_KEY_BOOK_COVERS . '/' . $fileNameNew,
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

        $bookCover = new $this->bookCover;
        $bookCover->book_id = $bookID;
        $bookCover->cover = $objectUrl;

        if (! $bookCover->save()) {
            return Response::api(500, 'Erro durante a gravação dos dados.', status: false);
        }

        $data = [
            'id' => $bookCover->id,
            'book_id' => $bookID,
            'cover' => $objectUrl,
        ];
        
        Response::api(201, data: $data);
    }
}