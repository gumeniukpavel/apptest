<?php

namespace App\Service;

use App\Db\Entity\CandidateFile;
use App\Db\Entity\EmployeeRegistries;
use App\Db\Entity\EmployeeRegistriesFile;
use App\Db\Entity\ExpertFile;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class StorageService
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function saveFile(UploadedFile $file, string $folder): string
    {
        $user = $this->authService->getUser();

        if ($user->bucket_id && $user->secret_token)
        {
            Config::set('filesystems.disks.s3.bucket', $user->bucket_id);
            Config::set('filesystems.disks.s3.secret', $user->amazonSecretToken);
        }

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $disk = Storage::disk('s3');
        $disk->put(
            '/'.$folder.'/'.$filename,
            file_get_contents($file->getRealPath())
        );

        return $folder.'/'.$filename;
    }

    public function saveExportedPDF(PDF $pdf, string $folder, string $name): string
    {
        $user = $this->authService->getUser();

        if ($user->bucket_id && $user->secret_token)
        {
            Config::set('filesystems.disks.s3.bucket', $user->bucket_id);
            Config::set('filesystems.disks.s3.secret', $user->amazonSecretToken);
        }

        $pdfName = $name.date('mdYHis') . uniqid() . '.pdf';
        $disk = Storage::disk('s3');
        $disk->put(
            '/'.$folder.'/'.$pdfName,
            $pdf->stream()
        );

        if ($user->bucket_id)
        {
            $bucketId = $user->bucket_id;
        }
        else
        {
            $bucketId = config('filesystems.disks.s3.bucket');
        }
        return "https://" . $bucketId . ".s3.eu-west-1.amazonaws.com/" . $folder.'/'.$pdfName;
    }

    public function saveImage(
        UploadedFile $image,
        string $folder
    ): ImageModel
    {
        $user = $this->authService->getUser();

        if ($user->bucket_id && $user->secret_token)
        {
            Config::set('filesystems.disks.s3.bucket', $user->bucket_id);
            Config::set('filesystems.disks.s3.secret', $user->amazonSecretToken);
        }

        $fullImage = Image::make($image->getRealPath())->resize(1024, 1024, function($constraint){
            $constraint->aspectRatio();
        });

        $croppedImage = Image::make($image->getRealPath())->resize(256, 256, function($constraint){
            $constraint->aspectRatio();
        });

        $extension = $image->getClientOriginalExtension();
        $name = uniqid();
        $filename = $name . '.' . $extension;
        $disk = Storage::disk('s3');
        $disk->put(
            '/'.$folder.'/'.$filename,
            $fullImage->encode($extension)
        );

        $filenameCropped = $name . '.cropped.' . $extension;
        $disk->put(
            '/'.$folder.'/'.$filenameCropped,
            $croppedImage->encode($extension)
        );

        return new ImageModel([
            'path' => $folder.'/'.$filename,
            'croppedPath' => $folder.'/'.$filenameCropped,
        ]);
    }

    public function saveQuestionAudioFile(UploadedFile $file): string
    {
        return $this->saveFile($file, 'question/audio/'.$this->getStorageSavePath());
    }

    public function saveQuestionVideoFile(UploadedFile $file): string
    {
        return $this->saveFile($file, 'question/video/'.$this->getStorageSavePath());
    }

    public function saveQuestionImageFile(UploadedFile $file): ImageModel
    {
        return $this->saveImage($file, 'question/image/'.$this->getStorageSavePath());
    }

    public function saveCandidateImageFile(UploadedFile $file): ImageModel
    {
        return $this->saveImage($file, 'candidate/image/'.$this->getStorageSavePath());
    }

    public function saveExpertImageFile(UploadedFile $file): ImageModel
    {
        return $this->saveImage($file, 'expert/image/'.$this->getStorageSavePath());
    }

    public function saveCandidateDocumentFile(UploadedFile $file): string
    {
        return $this->saveFile($file, 'candidate/documents/'.$this->getStorageSavePath());
    }

    public function deleteCandidateDocumentFile(CandidateFile $candidateFile)
    {
        Storage::delete(storage_path('app/public/'.$candidateFile->path));
        $candidateFile->delete();
    }

    public function saveExpertDocumentFile(UploadedFile $file): string
    {
        return $this->saveFile($file, 'expert/documents/'.$this->getStorageSavePath());
    }

    public function deleteExpertDocumentFile(ExpertFile $expertFile)
    {
        Storage::delete(storage_path('app/public/'.$expertFile->path));
        $expertFile->delete();
    }

    public function saveEmployeeRegistriesDocumentFile(UploadedFile $file): string
    {
        return $this->saveFile($file, 'employee/documents/'.$this->getStorageSavePath());
    }

    public function deleteEmployeeRegistriesDocumentFile(EmployeeRegistriesFile $employeeRegistriesFile)
    {
        Storage::delete(storage_path('app/public/'.$employeeRegistriesFile->path));
        $employeeRegistriesFile->forceDelete();
    }

    public function deleteEmployeeRegistries(EmployeeRegistries $employeeRegistries)
    {
        $employeeRegistriesFiles = $employeeRegistries->documents;

        foreach ($employeeRegistriesFiles as $employeeRegistriesFile) {
            $this->deleteEmployeeRegistriesDocumentFile($employeeRegistriesFile);
        }
        $employeeRegistries->delete();
    }

    public function getUrl(string $fileName): string
    {
        return asset(
            Storage::url($fileName)
        );
    }

    public static function getPublicUrl(string $fileName): string
    {
        return asset(
            Storage::url($fileName)
        );
    }

    private function getStorageSavePath()
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;

        return "$year/$month/$day";
    }
}
