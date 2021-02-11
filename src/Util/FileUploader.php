<?php
namespace App\Util;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $uploadDirCsv;

    public function __construct($uploadDirCsv)
    {
        $this->uploadDirCsv = $uploadDirCsv;
    }
    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $uploadDirCsv = $this->uploadDirCsv;

        $fileName = $originalFilename.'-'.uniqid().'.'.$file->guessExtension();

        try
        {
            $file->move($uploadDirCsv, $fileName);
        } catch (FileException $e)
        {
            return null;
        }

        return $fileName;
    }

}