<?php
// app/Http\Controllers\Api\PanicController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;


class PanicController extends Controller
{
    /**
     * POST /api/panic/trigger
     * Disparar una alerta de pánico (adaptado a TU estructura)
     */
    public function trigger(Request $request)
    {
        try {
            // 1. Obtener datos JSON directamente
            $input = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'JSON inválido'], 400);
            }

            // 2. Validar manualmente
            $errors = [];

            if (!isset($input['student_id'])) {
                $errors[] = 'student_id es requerido';
            } elseif (!DB::table('students')->where('id', $input['student_id'])->exists()) {
                $errors[] = 'student_id no existe';
            }

            if (!isset($input['triggered_by_type']) || !in_array($input['triggered_by_type'], ['guardian', 'student'])) {
                $errors[] = 'triggered_by_type debe ser "guardian" o "student"';
            }

            if (!isset($input['source']) || !in_array($input['source'], ['app', 'device'])) {
                $errors[] = 'source debe ser "app" o "device"';
            }

            if (!empty($errors)) {
                return response()->json(['error' => 'Validación falló', 'errors' => $errors], 422);
            }

            // 3. Obtener estudiante
            $student = DB::table('students')->where('id', $input['student_id'])->first();

            // 4. Insertar
            $panicId = DB::table('panic_events')->insertGetId([
                'school_id' => $student->school_id,
                'student_id' => $input['student_id'],
                'triggered_by_type' => $input['triggered_by_type'],
                'source' => $input['source'],
                'note' => $input['note'] ?? 'Alerta de pánico',
                'triggered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'panic_id' => $panicId,
                'message' => 'Alerta de pánico registrada',
                'timestamp' => now()->toISOString()
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Panic trigger error', ['exception' => $e]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
    /**
     * GET /api/panic/events
     * Listar eventos de pánico
     */
    public function list(Request $request)
    {
        $query = DB::table('panic_events as p')
            ->join('students as s', 'p.student_id', '=', 's.id')
            ->join('schools as sc', 'p.school_id', '=', 'sc.id')
            ->leftJoin('guardians as g', 'p.guardian_id', '=', 'g.id')
            ->leftJoin('devices as d', 'p.device_id', '=', 'd.id')
            ->leftJoin('users as u', 'p.triggered_by_user_id', '=', 'u.id')
            ->select(
                'p.id',
                'p.triggered_by_type',
                'p.source',
                'p.note',
                'p.priority',
                'p.resolved',
                'p.triggered_at',
                'p.resolved_at',
                's.id as student_id',
                's.first_name as student_first_name',
                's.last_name as student_last_name',
                'sc.name as school_name',
                DB::raw("CONCAT(g.first_name, ' ', g.last_name) as guardian_name"),
                'd.uid as device_uid',
                'u.name as triggered_by_user_name'
            )
            ->orderBy('p.triggered_at', 'desc');

        // Filtros
        if ($request->filled('resolved')) {
            $query->where('p.resolved', $request->boolean('resolved'));
        }

        if ($request->filled('school_id')) {
            $query->where('p.school_id', $request->school_id);
        }

        if ($request->filled('student_id')) {
            $query->where('p.student_id', $request->student_id);
        }

        if ($request->filled('priority')) {
            $query->where('p.priority', $request->priority);
        }

        // Paginación
        $events = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'total' => $events->total(),
            'data' => $events->items()
        ]);
    }

    /**
     * POST /api/panic/events/{id}/resolve
     * Marcar evento como resuelto
     */
    public function resolve($panicId, Request $request)
    {
        $user = $request->user();

        // Verificar que el evento existe
        $panicEvent = DB::table('panic_events')->where('id', $panicId)->first();

        if (!$panicEvent) {
            return response()->json(['error' => 'Evento de pánico no encontrado'], 404);
        }

        $validated = $request->validate([
            'resolution_note' => 'required|string|min:10',
        ]);

        // Actualizar evento
        DB::table('panic_events')
            ->where('id', $panicId)
            ->update([
                'resolved' => 1,
                'resolved_at' => now(),
                'resolved_by_user_id' => $user->id,
                'updated_at' => now(),
            ]);

        // Aquí podrías crear una tabla panic_event_resolutions si necesitas historial
        // Por ahora solo actualizamos el campo note
        if ($panicEvent->note) {
            $newNote = $panicEvent->note . "\n--- RESUELTO ---\n" . $validated['resolution_note'];
            DB::table('panic_events')
                ->where('id', $panicId)
                ->update(['note' => $newNote]);
        }

        Log::info('Evento de pánico resuelto', [
            'panic_id' => $panicId,
            'resolved_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evento de pánico marcado como resuelto',
            'panic_id' => $panicId
        ]);
    }
}