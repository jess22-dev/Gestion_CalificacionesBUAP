<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = Auth::user()->notificaciones()->paginate(20);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function marcarLeida(Notificacion $notificacion)
    {
        if ($notificacion->user_id !== Auth::id()) {
            abort(403);
        }

        $notificacion->update([
            'leida' => true,
        ]);

        if ($notificacion->url) {
            return redirect($notificacion->url);
        }

        return back();
    }

    public function marcarTodas()
    {
        Auth::user()->notificaciones()
            ->where('leida', false)
            ->update(['leida' => true]);

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }
}