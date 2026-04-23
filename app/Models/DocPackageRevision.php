<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocPackageRevision extends Model
{
    protected $table = 'doc_package_revisions';

    public function package()
    {
        return $this->belongsTo(DocPackage::class, 'package_id');
    }
}
