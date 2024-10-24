<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class AssetImage extends SnipeModel
{
    
    use HasFactory;
    
    protected $fillable = [
        'assets_id', 'image'
    ];
    protected $table = 'assets_image';


    /**
     * Get the image URL of the asset.
     *
     * Check first to see if there is a specific image uploaded to the asset,
     * and if not, check for an image uploaded to the asset model.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return string | false
     */
    public function getImageUrl2()
    {
        if (
            $this->image && !empty($this->image)
        ) {
            return Storage::disk('public')->url(app('assets_upload_path') . e($this->image));
        } elseif ($this->model && !empty($this->model->image)) {
            return Storage::disk('public')->url(app('models_upload_path') . e($this->model->image));
        }

        return false;
    }
}
