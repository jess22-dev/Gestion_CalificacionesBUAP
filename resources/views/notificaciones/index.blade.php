<x-app-layout>
    <x-slot name="header">
        <span class="text-white font-bold text-xl">Notificaciones</span>
    </x-slot>

    <div class="py-10 bg-[#f8fafc] min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700 font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <h1 class="text-2xl font-black text-[#002d62]">Mis notificaciones</h1>

                    <form method="POST" action="{{ route('notificaciones.leer_todas') }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-[#002d62] text-white rounded-xl font-bold hover:bg-[#1e4b8a] transition">
                            Marcar todas como leídas
                        </button>
                    </form>
                </div>

                <div class="space-y-4">
                    @forelse($notificaciones as $notificacion)
                        <div class="border rounded-2xl p-5 {{ $notificacion->leida ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-200' }}">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div>
                                    <h3 class="font-black text-[#002d62] text-lg">
                                        {{ $notificacion->titulo }}
                                    </h3>

                                    <p class="text-sm text-gray-700 mt-2">
                                        {{ $notificacion->mensaje }}
                                    </p>

                                    <p class="text-xs text-gray-400 mt-3">
                                        {{ $notificacion->created_at->format('d/m/Y h:i A') }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    @if(!$notificacion->leida)
                                        <form method="POST" action="{{ route('notificaciones.leer', $notificacion->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition">
                                                Abrir
                                            </button>
                                        </form>
                                    @elseif($notificacion->url)
                                        <a href="{{ $notificacion->url }}"
                                           class="px-4 py-2 bg-[#002d62] text-white rounded-xl text-sm font-bold hover:bg-[#1e4b8a] transition">
                                            Ir
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-gray-500 font-semibold text-center">
                            No tienes notificaciones todavía.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $notificaciones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>