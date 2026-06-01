<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // Listar usuarios
    public function index()
    {
        $usuarios = Usuario::with('rol')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $roles = Rol::all();
        return view('admin.usuarios.create', compact('roles'));
    }

    // Guardar usuario
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios',
            'username' => 'required|string|unique:usuarios',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:roles,id_rol',
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_rol' => $request->id_rol,
            'activo' => 1,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();
        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    // Actualizar usuario
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email,' . $id . ',id_usuario',
            'username' => 'required|string|unique:usuarios,username,' . $id . ',id_usuario',
            'id_rol' => 'required|exists:roles,id_rol',
        ]);

        $usuario->update($request->only(['nombre', 'email', 'username', 'id_rol']));

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    // Eliminar usuario
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        if ($usuario->id_usuario == auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        
        $usuario->delete();
        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    // Activar/Desactivar usuario
    public function toggleActivo($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->activo = !$usuario->activo;
        $usuario->save();
        
        return response()->json(['success' => true, 'activo' => $usuario->activo]);
    }
}