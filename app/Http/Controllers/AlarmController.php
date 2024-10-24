<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alarm;

class AlarmController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        // Validasi atau logika lain sesuai kebutuhan

        // Ambil data dari request
        $status = $request->input('status');

        // Update status alarm sesuai dengan data yang diterima
        $alarm = Alarm::findOrFail($id);
        $alarm->status = $status;
        $alarm->save();

        return response()->json(['message' => 'Status updated successfully']);
    }


    public function deleteAlarm($id)
    {
        try {
            // Hapus alarm berdasarkan ID
            $alarm = Alarm::findOrFail($id);
            $alarm->delete();

            return response()->json(['message' => 'Alarm deleted successfully']);
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan saat menghapus
            return response()->json(['error' => 'Error deleting alarm']);
        }
    }

    public function saveTask(Request $request)
    {
        // Validasi request
        $request->validate([
            'task_name' => 'required|string|max:255',
            'execution_type' => 'required|in:current_date_time,specific_date',
            'specific_date_text' => ($request->input('execution_type') == 'specific_date') ? 'required|date_format:Y-m-d H:i' : '',
        ]);

        // Simpan data tugas
        $alarm = new Alarm;
        $alarm->task = $request->input('task_name');
        $alarm->due_date = ($request->input('execution_type') == 'specific_date') ? $request->input('specific_date_text') : now();
        $alarm->status = false; // Default status false (belum selesai)
        $alarm->save();

        return response()->json(['message' => 'Task saved successfully'], 200);
    }
}
