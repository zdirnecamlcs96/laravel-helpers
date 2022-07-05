<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

trait FileSystem
{
    private function __directoryExist($directory, $target)
    {
        $all = Storage::allDirectories($directory);
        return in_array($target, $all);
    }

    /**
     * Store image file to storage directory with different resolutions
     *
     * @param  mixed $file
     * @param  mixed $type
     * @param  mixed $fullReso
     * @return void
     */
    function __storeImage($file, $type, $fullReso = false)
    {
        try {
            $path = $this->__normalizeSystemPath(File::PATH_TO_STORAGE . $type . "/");
            $file_name = mb_strtolower($type) . "_" . date("Ymdhis") . rand(11, 99);
            $ofile = Image::make($file);
            $ofile->orientate();
            $extension = str_replace("image/", '', $ofile->mime());
            $directory = str_replace('storage', 'public', $path);
            if (!$this->__directoryExist(storage_path(), $directory)) {
                Storage::makeDirectory($directory);
            }
            $ofile->save(public_path("{$path}{$file_name}.{$extension}"), 100);

            if ($fullReso) {
                $this->__moveFile($file, $type, $file_name);
                $file->storeAs(str_replace('storage', 'public', $path), $file_name . '_high_resolution.' . $extension);
                $hfile = $file_name . '_high_resolution';

                $lfile = Image::make($file);
                $lfile->orientate();
                $lfile->crop($lfile->height(), $lfile->height());
                $lfile->save(public_path($path . $file_name . '_low_resolution.' . $extension), 50);
            }

            return $fullReso ? (object)[
                "original" => $ofile,
                "low_reso" => $lfile,
                "high_reso" => $hfile, // It's not an image class, just a `File Name` string
            ] : $ofile;
        } catch (\Throwable $th) {
            Log::info('Failed to upload: '.var_export($th->getMessage() ?? '', true));
            return;
        }
    }

    /**
     * Move document file to storage directory
     *
     * @param  mixed $file
     * @param  mixed $type
     * @param  mixed $file_name
     * @return void
     */
    function __moveFile($file, $type, $file_name = null)
    {
        try {
            $name = $file->getClientOriginalName();

            $file_name = $file_name ?: mb_strtolower(pathinfo($name, PATHINFO_FILENAME)) . "_" . date("Ymdhis") . rand(11, 99);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $file->storeAs("public/$type", $file_name . '.' . $extension);

            $publicPath = "storage/$type/$file_name.$extension";
            $storagePath = "public/$type/$file_name.$extension";

            return (object) [
                "filename" => $file_name,
                "extension" => $extension,
                "publicPath" => $publicPath,
                "storagePath" => $storagePath,
                "file" => Storage::disk('local')->get($storagePath)
            ];
        } catch (\Throwable $th) {
            Log::info('Failed to upload: '.var_export($th->getMessage() ?? '', true));
            return;
        }
    }

    function __getFileOriginalName($file)
    {
        return $file->getClientOriginalName();
    }

    function __normalizeSystemPath(string $path) : string
    {
        return str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Upload image file
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\UploadedFile $file
     * @return null|\App\Models\File
     */
    public function __uploadImageFile(Request $request, UploadedFile $file, $type, $storagePath = "storage/")
    {
        $uploadedFile = $this->__storeImage($file, $type);
        if(empty($uploadedFile)) {
            return null;
        }

        $oFile = $uploadedFile->original ?? $uploadedFile;
        $newFile = File::create([
            "name" => $oFile->filename,
            "original_name" => $this->__getFileOriginalName($file),
            "extension" => $oFile->extension,
            "mime" => $oFile->mime(),
            "size" => $oFile->filesize(),
            "path" => "{$storagePath}{$type}/",
            "ip_address" => $request->ip()
        ]);

        return $newFile;
    }
}