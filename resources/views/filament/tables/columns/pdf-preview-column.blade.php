@php
    $record = $getRecord();
    $signedFilePath = $record && isset($record->signed_file_path) ? $record->signed_file_path : null;
    $fileUrl = $signedFilePath ? Storage::url($signedFilePath) : null;
@endphp

@if($fileUrl)
<div class="flex items-center justify-center">
    <a 
        href="{{ $fileUrl }}"
        target="_blank"
        class="inline-flex items-center justify-center rounded-lg font-medium tracking-tight focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 text-white px-3 py-2 text-sm"
        title="مشاهده فایل امضا شده"
    >
        <span class="flex items-center gap-1">
            <x-heroicon-o-document-text class="h-5 w-5" />
            <span>مشاهده PDF</span>
        </span>
    </a>
</div>
@else
<div class="text-center text-gray-500 text-sm">
    فایل امضا شده موجود نیست
</div>
@endif
