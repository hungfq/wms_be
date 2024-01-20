<?php

namespace App\Modules\Documents\Controllers;

use App\Entities\Document;
use App\Http\Controllers\ApiController;
use App\Modules\Documents\Repositories\DocumentRepo;
use App\Modules\Documents\Transformers\DocumentTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends ApiController
{
    public function index(DocumentRepo $documentRepo)
    {
        try {
            $params = $this->request->all();

            $result = $documentRepo->search($params);

            return $this->response->paginator($result, new DocumentTransformer());
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    public function store(DocumentRepo $documentRepo)
    {
        DB::beginTransaction();
        try {
            $params = $this->request->all();

            $documentRepo->validateCreateDocument($params);

            $doc = $documentRepo->createDocument($params);
            DB::commit();

            return $this->responseSuccess();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    public function show($docId)
    {
        try {

            $document = Document::find($docId);

            return $this->response()->item($document, new DocumentTransformer());
        } catch (\Exception $exception) {

            throw $exception;
        }

    }

    public function download(DocumentRepo $documentRepo, $docId)
    {
        try {
            ob_end_clean();

            $documentObj = Document::find($docId);

            return Storage::download($documentObj->path);

        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    public function delete(DocumentRepo $documentRepo)
    {
        DB::beginTransaction();
        try {
            $params = $this->request->all();
            $documentRepo->deleteDocument($params);

            DB::commit();

            return $this->responseSuccess();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    public function downloadZip(DocumentRepo $documentRepo)
    {
        try {
            $params = $this->request->all();

            return $documentRepo->downloadFromOwner($params);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
