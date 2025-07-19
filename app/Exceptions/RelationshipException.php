<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

/**
 * کلاس مدیریت خطاهای مربوط به روابط بین مدل‌ها.
 */
class RelationshipException extends Exception
{
    /**
     * مدل منشاء خطا
     * 
     * @var string
     */
    protected $sourceModel;

    /**
     * مدل مقصد که رابطه به آن وصل می‌شود
     * 
     * @var string
     */
    protected $targetModel;

    /**
     * نام رابطه
     * 
     * @var string
     */
    protected $relationName;

    /**
     * سازنده کلاس
     * 
     * @param string $message پیام خطا
     * @param string $sourceModel نام مدل منشاء
     * @param string $targetModel نام مدل مقصد
     * @param string $relationName نام رابطه
     * @param int $code کد خطا
     * @param Throwable|null $previous خطای قبلی
     */
    public function __construct(
        string $message, 
        string $sourceModel = null, 
        string $targetModel = null, 
        string $relationName = null, 
        int $code = 0, 
        Throwable $previous = null
    ) {
        $this->sourceModel = $sourceModel;
        $this->targetModel = $targetModel;
        $this->relationName = $relationName;
        
        // ساخت پیام خطای کامل
        $fullMessage = $message;
        if ($sourceModel && $targetModel && $relationName) {
            $fullMessage .= " (خطای رابطه: {$sourceModel}->{$relationName} به {$targetModel})";
        }
        
        // ثبت خطا در فایل لاگ
        $this->logError($fullMessage);
        
        parent::__construct($fullMessage, $code, $previous);
    }

    /**
     * گرفتن مدل منشاء
     * 
     * @return string|null
     */
    public function getSourceModel(): ?string
    {
        return $this->sourceModel;
    }

    /**
     * گرفتن مدل مقصد
     * 
     * @return string|null
     */
    public function getTargetModel(): ?string
    {
        return $this->targetModel;
    }

    /**
     * گرفتن نام رابطه
     * 
     * @return string|null
     */
    public function getRelationName(): ?string
    {
        return $this->relationName;
    }
    
    /**
     * ثبت خطا در لاگ
     * 
     * @param string $message
     * @return void
     */
    protected function logError(string $message): void
    {
        Log::error('خطای رابطه-مدل: ' . $message, [
            'source_model' => $this->sourceModel,
            'target_model' => $this->targetModel,
            'relation_name' => $this->relationName,
            'stack_trace' => $this->getTraceAsString(),
        ]);
    }
}
