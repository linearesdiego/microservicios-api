<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChannelController extends Controller
{
    /**
     * GET /api/channels
     * Listar todos los canales (con paginación y filtros)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');

        $query = Channel::query()->with(['medias', 'users']);

        // Filtro por búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por estado activo/inactivo
        if ($status !== null) {
            $isActive = $status === 'active' || $status === '1' || $status === 'true';
            $query->where('is_active', $isActive);
        }

        // Filtro por tipo
        if ($type) {
            $query->where('type', $type);
        }

        // Ordenamiento
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $channels = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $channels,
            'message' => 'Canales obtenidos exitosamente'
        ], 200);
    }

    /**
     * POST /api/channels
     * Crear un nuevo canal
     * Acceso: Solo Admin
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:channels,name',
            'description' => 'required|string|max:1000',
            'semantic_context' => 'nullable|string|max:2000',
            'type' => 'required|string|in:departamento,instituto,secretaría,centro',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Error de validación'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Crear el canal
            $channel = Channel::create([
                'name' => $request->name,
                'description' => $request->description,
                'semantic_context' => $request->semantic_context,
                'type' => $request->type,
                'is_active' => $request->input('is_active', true)
            ]);

            // Asociar medios si se proporcionaron
            if ($request->has('media_ids') && is_array($request->media_ids)) {
                $channel->medias()->attach($request->media_ids);
            }

            // Cargar relaciones para la respuesta
            $channel->load('medias');

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $channel,
                'message' => 'Canal creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el canal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/channels/{id}
     * Actualizar canal
     * Acceso: Solo Admin
     */
    public function update(Request $request, $id)
    {
        $channel = Channel::find($id);

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Canal no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('channels', 'name')->ignore($id)],
            'description' => 'sometimes|required|string|max:1000',
            'semantic_context' => 'nullable|string|max:2000',
            'type' => 'sometimes|required|string|in:departamento,instituto,secretaría,centro',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Error de validación'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Actualizar campos del canal
            $channel->update($request->only([
                'name',
                'description',
                'semantic_context',
                'type',
                'is_active'
            ]));

            // Actualizar medios si se proporcionaron
            if ($request->has('media_ids')) {
                $channel->medias()->sync($request->media_ids);
            }

            // Cargar relaciones actualizadas
            $channel->load('medias', 'users');

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $channel,
                'message' => 'Canal actualizado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el canal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/users/{id?}/channels
     * Listar canales asignados a un usuario específico o al autenticado
     * Si no se proporciona ID, retorna los canales del usuario autenticado
     */
    public function getUserChannels(Request $request, $id = null)
    {
        // Si no se proporciona ID, usar el usuario autenticado
        if ($id === null) {
            $user = Auth::user();
        } else {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Verificar permisos: solo puede ver sus propios canales o ser admin/moderator
            $authUser = Auth::user();
            if ($authUser->id !== (int)$id && !$authUser->hasRole(['admin', 'moderator'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver estos canales'
                ], 403);
            }
        }

        $query = $user->channels();

        // Filtrar por estado de aprobación
        if ($request->has('is_approved')) {
            $isApproved = filter_var($request->is_approved, FILTER_VALIDATE_BOOLEAN);
            $query->wherePivot('is_approved', $isApproved);
        }

        $channels = $query->get();

        return response()->json([
            'success' => true,
            'data' => $channels,
            'message' => 'Canales del usuario obtenidos exitosamente'
        ], 200);
    }

    /**
     * POST /api/channels/{channel}/users/{user}
     * Asignar un canal a un usuario específico
     * Acceso: Solo Admin
     */
    public function assignUserToChannel($channelId, $userId)
    {
        // Verificar que el canal existe
        $channel = Channel::find($channelId);
        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Canal no encontrado'
            ], 404);
        }

        // Verificar que el usuario existe
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar si el usuario ya está asignado al canal
        if ($channel->users()->where('users.id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario ya está asignado a este canal'
            ], 409);
        }

        // Asignar el usuario al canal con aprobación automática
        $channel->users()->attach($userId, [
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        // Recargar la relación
        $channel->load('users');

        return response()->json([
            'success' => true,
            'data' => [
                'channel' => $channel,
                'user' => $user
            ],
            'message' => 'Usuario asignado al canal exitosamente'
        ], 200);
    }
}
