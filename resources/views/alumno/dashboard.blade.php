<x-app-layout>
    {{-- Header idéntico al del Profesor --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-white tracking-tight italic">
                <span class="text-blue-200 font-light ml-2"> Estudiante BUAP</span>
            </h2>
            <div class="hidden md:block text-right">
                <p class="text-blue-200 font-light ml-2">Primavera 2026</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Encabezado de bienvenida (Mismo estilo que Profesor) --}}
            <div class="mb-10 ml-4 lg:ml-0">
                <h3 class="text-3xl font-black text-[#002d62] tracking-tighter uppercase italic">Mi Carga Académica</h3>
                <p class="text-gray-500 font-medium italic">Consulta tus materias inscritas y tus claves de seguimiento.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Materia de Ejemplo 1 --}}
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-gray-100 overflow-hidden transform hover:scale-[1.03] transition-all duration-300 group">
                    
                    {{-- Cabecera con Degradado (Igual al Profesor) --}}
                    <div class="bg-gradient-to-br from-[#1e4b8a] to-[#002d62] p-8 h-32 flex items-start justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-1">Asignatura</p>
                            <h4 class="text-xl font-black text-white leading-tight italic uppercase">Programación Web</h4>
                        </div>
                        {{-- Icono flotante de fondo --}}
                        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10 transform rotate-12" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 9.528c.538.303.812.646.812.972 0 .326-.274.669-.812.972l-3.394 1.912a1 1 0 01-.788 0L6.414 11.472c-.538-.303-.812-.646-.812-.972 0-.326.274-.669.812-.972L10 11.528l4-2z"></path>
                        </svg>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        {{-- Badge de Datos (Mismo estilo que Profesor) --}}
                        <div class="bg-blue-50 rounded-2xl p-4 flex justify-between items-center border border-blue-100">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">NRC</span>
                                <span class="text-lg font-black text-[#002d62]">12345</span>
                            </div>
                            <div class="h-8 w-px bg-blue-200"></div>
                            <div class="flex flex-col text-right">
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Sección</span>
                                <span class="text-lg font-black text-[#002d62] italic">001</span>
                            </div>
                        </div>
                        
                        {{-- Clave Única resaltada --}}
                        <div class="space-y-1">
                            <p class="text-[#1e4b8a] text-xs font-bold flex items-center italic">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                CLAVE ÚNICA: <span class="ml-1 font-black">WEB-777-XYZ</span>
                            </p>
                            <p class="text-gray-400 text-[10px] leading-relaxed">Profesor: <span class="font-bold">DR. JUAN PÉREZ HERNÁNDEZ</span></p>
                        </div>
                        
                        {{-- Botón de Acción (Igual al Profesor) --}}
                        <a href="#" 
                           class="flex items-center justify-center w-full py-4 bg-[#002d62] text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-[#1e4b8a] transition-all shadow-lg shadow-blue-900/20 active:scale-95 group">
                            Detalles de materia
                            <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Materia de Ejemplo 2 --}}
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-gray-100 overflow-hidden transform hover:scale-[1.03] transition-all duration-300 group">
                    <div class="bg-gradient-to-br from-[#1e4b8a] to-[#002d62] p-8 h-32 flex items-start justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-1">Asignatura</p>
                            <h4 class="text-xl font-black text-white leading-tight italic uppercase">Base de Datos</h4>
                        </div>
                        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10 transform rotate-12" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 9.528c.538.303.812.646.812.972 0 .326-.274.669-.812.972l-3.394 1.912a1 1 0 01-.788 0L6.414 11.472c-.538-.303-.812-.646-.812-.972 0-.326.274-.669.812-.972L10 11.528l4-2z"></path>
                        </svg>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="bg-blue-50 rounded-2xl p-4 flex justify-between items-center border border-blue-100">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">NRC</span>
                                <span class="text-lg font-black text-[#002d62]">67890</span>
                            </div>
                            <div class="h-8 w-px bg-blue-200"></div>
                            <div class="flex flex-col text-right">
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Sección</span>
                                <span class="text-lg font-black text-[#002d62] italic">005</span>
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <p class="text-[#1e4b8a] text-xs font-bold flex items-center italic">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                CLAVE ÚNICA: <span class="ml-1 font-black">SQL-999-ABC</span>
                            </p>
                            <p class="text-gray-400 text-[10px] leading-relaxed">Profesor: <span class="font-bold">MTRA. MARÍA GARCÍA SOSA</span></p>
                        </div>
                        
                        <a href="#" 
                           class="flex items-center justify-center w-full py-4 bg-[#002d62] text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-[#1e4b8a] transition-all shadow-lg shadow-blue-900/20 active:scale-95 group">
                            Detalles de materia
                            <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>