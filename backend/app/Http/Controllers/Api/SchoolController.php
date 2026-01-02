<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $query->where('status', 'active');
        $perPage = $request->input('per_page', 10);
        $schools = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $schools->items(),
            'pagination' => [
                'total' => $schools->total(),
                'current_page' => $schools->currentPage(),
                'last_page' => $schools->lastPage(),
                'per_page' => $schools->perPage(),
            ]
        ]);
    }
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:160',
            'code' => 'required|string|max:64|unique:schools,code',
            'timezone' => 'nullable|string|max:64',
            'city' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:240',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];

        $messages = [
            'name.required' => 'Oye, el nombre de la escuela es obligatorio.',
            'name.max' => 'El nombre es muy largo, máximo 160 caracteres.',
            'code.required' => 'El código de la escuela (Ej: SAG-01) es necesario.',
            'code.unique' => '¡Error! Este código ya existe en otra escuela.',
            'status.in' => 'El estado debe ser solamente: active o inactive.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $school = School::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Escuela guardada con éxito',
            'school' => $school
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $school = School::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:160',
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('schools', 'code')->ignore($id)
            ],
            'timezone' => 'nullable|string|max:64',
            'city' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:240',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        $messages = [
            'code.unique' => 'Este código ya está siendo usado por otra escuela.',
            'status.in' => 'El estado debe ser obligatoriamente active o inactive.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $school->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Escuela actualizada con éxito.',
            'school' => $school
        ]);
    }
    public function show($id)
    {
        $school = School::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $school
        ]);
    }
    public function destroy($id)
    {
        // 1. Buscamos la escuela
        $school = School::findOrFail($id);

        // 2. Aplicamos el borrado lógico (Desactivación)
        // En lugar de eliminar el registro, cambiamos su estado
        $school->update(['status' => 'inactive']);

        // 3. Respuesta profesional
        return response()->json([
            'success' => true,
            'message' => "La escuela '{$school->name}' ha sido desactivada correctamente.",
            'data' => $school
        ]);
    }
    public function students($schoolId, Request $request)
    {
        $user = $request->user();

        if (
            $user->hasRole('admin') ||
            ($user->hasRole('school_admin') && $user->schools()->where('schools.id', $schoolId)->exists())
        ) {

            $students = DB::table('students as s')
                ->join('grades as g', 's.grade_id', '=', 'g.id')
                ->where('s.school_id', $schoolId)
                ->where('s.status', 'active')
                ->select('s.id', 's.first_name', 's.last_name', 's.student_code', 'g.name as grade')
                ->get();

            return response()->json([
                'success' => true,
                'school_id' => $schoolId,
                'students' => $students
            ]);
        }

        return response()->json(['error' => 'No autorizado'], 403);
    }
}
