<!-- Image stuff - kept in /resources/views/partials/forms/edit/image-upload.blade.php -->
<!-- Image Delete -->
@if (isset($item) && ($item->image) && ($item->image!=''))
<div class="form-group{{ $errors->has('image_delete') ? ' has-error' : '' }}">
    <div class="col-md-9 col-md-offset-3">
        <label class="form-control">
            {{ Form::checkbox('image_delete', '1', old('image_delete'), ['aria-label'=>'image_delete']) }}
            {{ trans('general.image_delete') }}
            {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
        </label>
    </div>
</div>
<div class="form-group">
    <div class="col-md-9 col-md-offset-3">
        <img src="{{ Storage::disk('public')->url($image_path.e($item->image)) }}" class="img-responsive">
        {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
    </div>
</div>
@endif
<!-- Image Upload and preview -->
<div class="form-group {{ $errors->has((isset($fieldname) ? $fieldname : 'image')) ? 'has-error' : '' }}">
    <label class="col-md-3 control-label" for="{{ (isset($fieldname) ? $fieldname : 'images') }}">{{ trans('general.image_upload') }}</label>
    <div class="col-md-9">

        <input type="file" id="{{ (isset($fieldname) ? $fieldname : 'images') }}[]" name="{{ (isset($fieldname) ? $fieldname : 'image') }}" aria-label="{{ (isset($fieldname) ? $fieldname : 'image') }}" class="sr-only">

        <label class="btn btn-default" aria-hidden="true">
            Select Files
            <input type="file" name="{{ (isset($fieldname) ? $fieldname : 'images') }}[]" class="js-uploadFile" id="uploadFile" data-maxsize="{{ Helper::file_upload_max_size() }}" accept="image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml" style="display:none; max-width: 90%" aria-label="{{ (isset($fieldname) ? $fieldname : 'image') }}" aria-hidden="true" multiple>
        </label>
        {{-- <span class='label label-default' id="uploadFile-info"></span> --}}

        <p class="help-block" id="uploadFile-status">{{ trans('general.image_filetypes_help', ['size' => Helper::file_upload_max_size_readable()]) }}</p>
        {!! $errors->first('image', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
    <div class="col-md-4 col-md-offset-3" aria-hidden="true">
        <!-- Container for displaying image previews -->
        <div id="uploadFile-imagePreviewContainer">
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const uploadFileInput = document.getElementById('uploadFile');
        const imagePreviewContainer = document.getElementById('uploadFile-imagePreviewContainer');

        // Function to update the image previews
        function updateImagePreviews() {
            // Clear previous image previews
            imagePreviewContainer.innerHTML = "";

            // Loop through selected files
            for (let i = 0; i < uploadFileInput.files.length; i++) {
                const file = uploadFileInput.files[i];

                // Create a container for each image with a title
                const imageContainer = document.createElement("div");
                imageContainer.style.position = "relative"; // Added

                // Add a title like "Photo 1," "Photo 2," etc.
                const title = document.createElement("div");
                title.innerHTML = `Photo ${i + 1}`;
                title.style.fontWeight = "bold";
                imageContainer.appendChild(title);

                const imagePreview = document.createElement("img");
                imagePreview.style.maxWidth = "300px";
                imagePreview.style.display = "block";
                imagePreview.alt = `Uploaded Image ${i + 1}`;

                const removeButton = document.createElement("button");
                removeButton.innerHTML = '<i class="fas fa-trash-alt" style="font-size: 24px;"></i>'; // Trash icon
                removeButton.style.position = "absolute";
                removeButton.style.top = "0";
                removeButton.style.right = "0";
                removeButton.style.background = "none";
                removeButton.style.border = "none";
                removeButton.style.cursor = "pointer";
                removeButton.style.color = "red";
                removeButton.addEventListener("click", function() {
                    // Remove the image container when the "Remove" button is clicked
                    const newFileList = new DataTransfer();
                    for (let j = 0; j < uploadFileInput.files.length; j++) {
                        if (j !== i) {
                            newFileList.items.add(uploadFileInput.files[j]);
                        }
                    }
                    uploadFileInput.files = newFileList.files;

                    // Update the image previews
                    updateImagePreviews();
                });

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                };

                reader.readAsDataURL(file);

                imageContainer.appendChild(imagePreview);
                imageContainer.appendChild(removeButton);
                imagePreviewContainer.appendChild(imageContainer);
            }
        }

        // Handle file selection from the visible input
        uploadFileInput.addEventListener("change", function() {
            // Update the image previews when files are selected
            updateImagePreviews();
        });
    });
</script>