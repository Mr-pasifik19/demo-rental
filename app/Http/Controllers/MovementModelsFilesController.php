<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Http\Requests\MovementFileRequest;
use App\Models\Actionlog;
use App\Models\MovementModel;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use enshrined\svgSanitize\Sanitizer;

class MovementModelsFilesController extends Controller
{
    /**
     * Upload a file to the server.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param MovementFileRequest $request
     * @param int $modelId
     * @return Redirect
     * @since [v1.0]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(MovementFileRequest $request, $modelId = null)
    {
        if (! $model = MovementModel::find($modelId)) {
            return redirect()->route('models.index')->with('error', trans('admin/movement/message.does_not_exist'));
        }

        $this->authorize('update', $model);

        if ($request->hasFile('file')) {
            if (! Storage::exists('private_uploads/movementmodels')) {
                Storage::makeDirectory('private_uploads/movementmodels', 775);
            }

            foreach ($request->file('file') as $file) {

                $extension = $file->getClientOriginalExtension();
                $file_name = 'model-'.$model->id.'-'.str_random(8).'-'.str_slug(basename($file->getClientOriginalName(), '.'.$extension)).'.'.$extension;

                // Check for SVG and sanitize it
                if ($extension=='svg') {
                    \Log::debug('This is an SVG');

                    $sanitizer = new Sanitizer();
                    $dirtySVG = file_get_contents($file->getRealPath());
                    $cleanSVG = $sanitizer->sanitize($dirtySVG);

                    try {
                        Storage::put('private_uploads/movementmodels/'.$file_name, $cleanSVG);
                    } catch (\Exception $e) {
                        \Log::debug('Upload no workie :( ');
                        \Log::debug($e);
                    }
                } else {
                    Storage::put('private_uploads/movementmodels/'.$file_name, file_get_contents($file));
                }


                $model->logUpload($file_name, e($request->get('notes')));
            }

            return redirect()->back()->with('success', trans('general.file_upload_success'));
        }

        return redirect()->back()->with('error', trans('admin/movement/message.upload.nofiles'));
    }

    /**
     * Check for permissions and display the file.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $modelId
     * @param  int $fileId
     * @since [v1.0]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($modelId = null, $fileId = null, $download = true)
    {
        $model = MovementModel::find($modelId);
        // the Movement is valid
        if (isset($model->id)) {
            $this->authorize('view', $model);

            if (! $log = Actionlog::find($fileId)) {
                return response('No matching record for that model/file', 500)
                    ->header('Content-Type', 'text/plain');
            }

            $file = 'private_uploads/movementmodels/'.$log->filename;
            \Log::debug('Checking for '.$file);


            if (! Storage::exists($file)) {
                return response('File '.$file.' not found on server', 404)
                    ->header('Content-Type', 'text/plain');
            }

            if ($download != 'true') {
                if ($contents = file_get_contents(Storage::url($file))) {
                    return Response::make(Storage::url($file)->header('Content-Type', mime_content_type($file)));
                }

                return JsonResponse::create(['error' => 'Failed validation: '], 500);
            }

            return StorageHelper::downloader($file);
        }
        // Prepare the error message
        $error = trans('admin/movement/message.does_not_exist', ['id' => $fileId]);

        // Redirect to the movement management page
        return redirect()->route('movement.index')->with('error', $error);
    }

    /**
     * Delete the associated file
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $modelId
     * @param  int $fileId
     * @since [v1.0]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($modelId = null, $fileId = null)
    {
        $model = MovementModel::find($modelId);
        $this->authorize('update', $model);
        $rel_path = 'private_uploads/movementmodels';

        // the movement is valid
        if (isset($model->id)) {
            $this->authorize('update', $model);
            $log = Actionlog::find($fileId);
            if ($log) {
                if (Storage::exists($rel_path.'/'.$log->filename)) {
                    Storage::delete($rel_path.'/'.$log->filename);
                }
                $log->delete();

                return redirect()->back()->with('success', trans('admin/movement/message.deletefile.success'));
            }

            return redirect()->back()
                ->with('success', trans('admin/movement/message.deletefile.success'));
        }

        // Redirect to the movement management page
        return redirect()->route('movement.index')->with('error', trans('admin/movement/message.does_not_exist'));
    }
}
