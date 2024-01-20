<?php

namespace App\Modules\Documents\Repositories;

use App\Entities\Document;
use App\Modules\Documents\Validators\CreateDocumentValidator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DocumentRepo
{
    const SOURCE_UPLOAD = 'documents';

    /**
     * @return string
     */
    public function model()
    {
        return Document::class;
    }

    public function search($input = [])
    {
        $query = Document::query()
            ->select([
                'documents.*',
            ]);

        $this->searchFields($query, $input);

        return $query->paginate($input['limit'] ?? 10);
    }

    /**
     * @param $query
     * @param $input
     */
    private function searchFields(\Illuminate\Database\Eloquent\Builder &$query, $input)
    {
        $query->when(array_get($input, 'owner'), function ($query, $owner) {
            $query->where('owner', $owner);
        });

        $query->when(array_get($input, 'title'), function ($query, $value) {
            $query->where('title', 'like', '%' . $value . '%');
        });

        $query->when(array_get($input, 'file_name'), function ($query, $value) {
            $query->where('file_name', 'like', '%' . $value . '%');
        });

        $query->when(array_get($input, 'file_extension'), function ($query, $value) {
            $query->where('file_extension', 'like', '%' . $value . '%');
        });
    }

    public function createDocument($params)
    {
        $params['file_name'] = $params['file']->getClientOriginalName();
        $params['file_extension'] = $params['file']->getClientOriginalExtension();
        $params['type'] = $params['file']->getMimeType();
        $params['size'] = $params['file']->getSize();
        $params['owner'] = Arr::get($params, 'owner');
        $pathFile = $this->uploadFile($params);
        $params['path'] = $pathFile;

        return Document::create($params);

    }

    public function validateCreateDocument(array $params)
    {
        $validator = new CreateDocumentValidator();
        $validator->validate($params);
    }

    public function uploadFile($params)
    {
        $pathFile = $this->getPathFile($params);
        $generateFileName = $this->generateFileNameUpload($params['file']);
        Storage::putFileAs($pathFile, $params['file'], $generateFileName);

        return $pathFile . DIRECTORY_SEPARATOR . $generateFileName;
    }

    private function generateFileNameUpload($file)
    {
        return uniqid(time()) . '_' . $file->getClientOriginalName();
    }

    private function getPathFile($params)
    {
        //exp: documents/KL1-001/file.txt
        return env('DOC_PREFIX_ENV', 'local') .
            DIRECTORY_SEPARATOR . self::SOURCE_UPLOAD . DIRECTORY_SEPARATOR . $params['owner'];

    }

    public function deleteDocument(array $params)
    {
        if (!$params) {
            return false;
        }

        $documents = Document::whereIn('id', $params)->get();
        foreach ($documents as $document) {
            Storage::delete($document->path);
        }

        Document::whereIn('id', $params)->delete();
    }

    public function downloadFromOwner($params)
    {
        $zipFileName = $params['owner'] . '.zip';
        $directory = $this->getPathFile($params);
        $zip = new ZipArchive();

//        $path = env('DOC_PREFIX_ENV', 'local') . DIRECTORY_SEPARATOR . self::SOURCE_UPLOAD . DIRECTORY_SEPARATOR . $zipFileName;
        $zipFilePath = public_path($zipFileName);
//        $zipFilePath = env('DOC_PREFIX_ENV', 'local') . DIRECTORY_SEPARATOR . self::SOURCE_UPLOAD . DIRECTORY_SEPARATOR . $zipFileName;

        // Open the zip file for writing
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Get all files and directories from the specified S3 directory
//            $directory .= '%0A';
            $files = Storage::files($directory);
            $directories = Storage::directories($directory);
            // Add files to the zip archive
            foreach ($files as $file) {
                $relativePath = basename($file);
                $fileContents = Storage::get($file);
                $zip->addFromString($relativePath, $fileContents);
            }

            // Add directories to the zip archive
            foreach ($directories as $dir) {
                $relativePath = str_replace($directory . '/', '', $dir);
                $zip->addEmptyDir($relativePath);
            }

            // Close the zip file
            $zip->close();

            // Set the appropriate headers for downloading the zip file
            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            ];

            // Generate the download response
            return response()->download($zipFilePath, $zipFileName, $headers);
        } else {
            // Failed to create the zip file
            return response()->json(['error' => 'Failed to create the zip file.']);
        }

    }
}
