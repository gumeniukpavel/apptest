<?php

namespace App\Db\Entity\Extensions;

trait PrepareToArrayData
{
    public function toArray()
    {
        $modelAsArray = parent::toArray();
        if ($this->timestamps)
        {
            if (isset($modelAsArray['created_at']) && !empty($modelAsArray['created_at']))
            {
                $modelAsArray['created_at'] = $this->created_at->timestamp;
            }
            if (isset($modelAsArray['updated_at']) && !empty($modelAsArray['updated_at']))
            {
                $modelAsArray['updated_at'] = $this->updated_at->timestamp;
            }
            if (isset($modelAsArray['deleted_at']))
            {
                unset($modelAsArray['deleted_at']);
            }
        }
        if (count($this->dates))
        {
            foreach ($this->dates as $dateFieldName)
            {
                if (isset($modelAsArray[$dateFieldName]))
                {
                    $modelAsArray[$dateFieldName] = $this->$dateFieldName->timestamp;
                }
            }
        }
        return $modelAsArray;
    }
}
