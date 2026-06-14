@extends('layouts.app')
@section('title', 'Lembretes')

@section('content')
<div
    x-data="lembretes()"
    x-init="init()"
    @mousemove.window="onMouseMove($event)"
    @mouseup.window="onMouseUp()"
>

{{-- Toolbar --}}
<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <div class="flex items-center gap-2">
        {{-- Abas --}}
        <button @click="aba='postits'"
                :class="aba==='postits' ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200'"
                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
            📌 Post-its
        </button>
        <button @click="aba='matriz'"
                :class="aba==='matriz' ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200'"
                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
            🎯 Matriz de Prioridades
        </button>
    </div>

    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-400" x-show="aba==='postits'">Cor para novo lembrete:</span>
        @foreach(['yellow' => '#fef08a', 'pink' => '#fbcfe8', 'blue' => '#bfdbfe', 'green' => '#bbf7d0', 'purple' => '#e9d5ff'] as $cor => $hex)
        <button
            @click="criar('{{ $cor }}')"
            class="w-7 h-7 rounded shadow hover:scale-110 transition-transform border-2 border-white"
            style="background-color: {{ $hex }};"
            title="{{ ucfirst($cor) }}"
        ></button>
        @endforeach
    </div>
</div>

{{-- ===== ABA POST-ITS ===== --}}
<div
    x-show="aba==='postits'"
    id="canvas"
    class="relative w-full select-none"
    style="height: calc(100vh - 160px);"
>
    <template x-for="note in notes" :key="note.id">
        <div
            class="absolute w-56 rounded-md shadow-xl flex flex-col"
            :class="bgClass(note.cor)"
            :style="`left: ${note.pos_x}px; top: ${note.pos_y}px; z-index: ${dragging && dragging.id === note.id ? 999 : 10};`"
        >
            <div
                class="flex items-center justify-between px-2.5 py-1.5 rounded-t-md cursor-grab active:cursor-grabbing"
                :class="headerClass(note.cor)"
                @mousedown.prevent="startDrag($event, note)"
            >
                <svg class="w-4 h-4 text-gray-400 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM13 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM13 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM13 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
                <div class="flex gap-1">
                    @foreach(['yellow' => '#fef08a', 'pink' => '#fbcfe8', 'blue' => '#bfdbfe', 'green' => '#bbf7d0', 'purple' => '#e9d5ff'] as $cor => $hex)
                    <button
                        @mousedown.stop
                        @click.stop="mudarCor(note, '{{ $cor }}')"
                        class="w-3 h-3 rounded-full border border-white/60 hover:scale-125 transition-transform"
                        style="background-color: {{ $hex }};"
                    ></button>
                    @endforeach
                </div>
                <button
                    @mousedown.stop
                    @click.stop="excluir(note)"
                    class="w-5 h-5 flex items-center justify-center rounded-full bg-white/40 hover:bg-red-500 hover:text-white text-gray-500 text-xs font-bold transition-colors"
                    title="Excluir lembrete"
                >✕</button>
            </div>
            <textarea
                x-model="note.texto"
                @change="salvarTexto(note)"
                @mousedown.stop
                placeholder="Escreva aqui..."
                class="w-full bg-transparent resize-none text-sm text-gray-800 placeholder-gray-400 px-3 py-2 focus:outline-none rounded-b-md"
                rows="5"
            ></textarea>
        </div>
    </template>

    <div x-show="notes.length === 0"
         class="absolute inset-0 flex flex-col items-center justify-center text-gray-300 pointer-events-none mt-10">
        <div class="text-6xl mb-3">🗒️</div>
        <p class="text-sm">Escolha uma cor acima para criar seu primeiro lembrete</p>
    </div>
</div>

{{-- ===== ABA MATRIZ ===== --}}
<div x-show="aba==='matriz'" class="select-none">

    {{-- Lembretes sem quadrante --}}
    <div class="mb-4">
        <p class="text-xs text-gray-400 mb-2">Arraste os lembretes para os quadrantes:</p>
        <div
            class="min-h-[48px] flex flex-wrap gap-2 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-2"
            @dragover.prevent
            @drop.prevent="moverParaQuadrante($event, null)"
        >
            <template x-for="note in semQuadrante" :key="note.id">
                <div
                    draggable="true"
                    @dragstart="iniciarDragMatriz($event, note)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium cursor-grab shadow-sm border border-white/60 max-w-[200px] truncate"
                    :class="bgClass(note.cor)"
                    :title="note.texto || 'Sem texto'"
                    x-text="note.texto || '(sem texto)'"
                ></div>
            </template>
            <span x-show="semQuadrante.length === 0" class="text-xs text-gray-300 italic self-center">Nenhum lembrete aqui</span>
        </div>
    </div>

    {{-- Grade 2x2 --}}
    <div class="grid grid-cols-2 gap-3" style="height: calc(100vh - 280px);">
        @php
        $quadrantes = [
            1 => ['titulo' => 'Faça Agora',  'sub' => 'Urgente + Importante',     'bg' => 'bg-red-50',    'border' => 'border-red-200',    'header' => 'bg-red-100 text-red-700',    'icon' => '🔴'],
            2 => ['titulo' => 'Agende',       'sub' => 'Importante + Não urgente', 'bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'header' => 'bg-blue-100 text-blue-700',  'icon' => '🔵'],
            3 => ['titulo' => 'Delegue',      'sub' => 'Urgente + Não importante', 'bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'header' => 'bg-orange-100 text-orange-700', 'icon' => '🟠'],
            4 => ['titulo' => 'Elimine',      'sub' => 'Não urgente + Não importante', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'header' => 'bg-gray-100 text-gray-600', 'icon' => '⚪'],
        ];
        @endphp

        @foreach($quadrantes as $num => $q)
        <div
            class="rounded-xl border-2 {{ $q['border'] }} {{ $q['bg'] }} flex flex-col overflow-hidden"
            @dragover.prevent="dragSobre = {{ $num }}"
            @dragleave="dragSobre = null"
            @drop.prevent="moverParaQuadrante($event, {{ $num }})"
            :class="dragSobre === {{ $num }} ? 'ring-2 ring-blue-400 ring-offset-1' : ''"
        >
            <div class="px-3 py-2 {{ $q['header'] }} flex items-center gap-1.5 shrink-0">
                <span>{{ $q['icon'] }}</span>
                <div>
                    <div class="text-xs font-bold">{{ $q['titulo'] }}</div>
                    <div class="text-xs opacity-70">{{ $q['sub'] }}</div>
                </div>
            </div>
            <div class="flex-1 p-2 overflow-y-auto flex flex-wrap content-start gap-2">
                <template x-for="note in notesDoQuadrante({{ $num }})" :key="note.id">
                    <div
                        draggable="true"
                        @dragstart="iniciarDragMatriz($event, note)"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium cursor-grab shadow-sm border border-white/60 max-w-full break-words"
                        :class="bgClass(note.cor)"
                        :title="note.texto || 'Sem texto'"
                        x-text="note.texto || '(sem texto)'"
                    ></div>
                </template>
                <div x-show="notesDoQuadrante({{ $num }}).length === 0"
                     class="text-xs text-gray-300 italic w-full text-center mt-4">
                    Arraste aqui
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

</div>

<script>
function lembretes() {
    return {
        notes: @json($lembretes),
        aba: 'postits',
        // Post-its
        dragging: null,
        offsetX: 0,
        offsetY: 0,
        // Matriz
        dragNote: null,
        dragSobre: null,
        csrf: document.querySelector('meta[name="csrf-token"]').content,

        init() {},

        // ---- Post-its ----

        async criar(cor) {
            const canvas = document.getElementById('canvas');
            const rect   = canvas ? canvas.getBoundingClientRect() : { width: 800, height: 500 };
            const pos_x  = Math.floor(Math.random() * (rect.width  - 240) + 20);
            const pos_y  = Math.floor(Math.random() * (rect.height - 240) + 60);
            const res    = await this.fetchJson('POST', '/lembretes', { cor, pos_x, pos_y });
            const note   = await res.json();
            this.notes.push(note);
        },

        async salvarTexto(note) {
            await this.fetchJson('PATCH', `/lembretes/${note.id}`, { texto: note.texto });
        },

        async mudarCor(note, cor) {
            note.cor = cor;
            await this.fetchJson('PATCH', `/lembretes/${note.id}`, { cor });
        },

        async excluir(note) {
            if (!confirm('Excluir este lembrete?')) return;
            await this.fetchJson('DELETE', `/lembretes/${note.id}`);
            this.notes = this.notes.filter(n => n.id !== note.id);
        },

        startDrag(event, note) {
            this.dragging = note;
            this.offsetX  = event.clientX - note.pos_x;
            this.offsetY  = event.clientY - note.pos_y;
        },

        onMouseMove(event) {
            if (!this.dragging) return;
            this.dragging.pos_x = event.clientX - this.offsetX;
            this.dragging.pos_y = event.clientY - this.offsetY;
        },

        onMouseUp() {
            if (!this.dragging) return;
            this.fetchJson('PATCH', `/lembretes/${this.dragging.id}`, {
                pos_x: this.dragging.pos_x,
                pos_y: this.dragging.pos_y,
            });
            this.dragging = null;
        },

        // ---- Matriz ----

        get semQuadrante() {
            return this.notes.filter(n => !n.quadrante);
        },

        notesDoQuadrante(q) {
            return this.notes.filter(n => n.quadrante === q);
        },

        iniciarDragMatriz(event, note) {
            this.dragNote = note;
            event.dataTransfer.effectAllowed = 'move';
        },

        async moverParaQuadrante(event, quadrante) {
            this.dragSobre = null;
            if (!this.dragNote) return;
            const note = this.notes.find(n => n.id === this.dragNote.id);
            if (!note) return;
            note.quadrante = quadrante;
            await this.fetchJson('PATCH', `/lembretes/${note.id}`, { quadrante });
            this.dragNote = null;
        },

        // ---- Helpers ----

        fetchJson(method, url, data = null) {
            return fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: data ? JSON.stringify(data) : null,
            });
        },

        bgClass(cor) {
            return { yellow:'bg-yellow-100', pink:'bg-pink-100', blue:'bg-blue-100', green:'bg-green-100', purple:'bg-purple-100' }[cor] ?? 'bg-yellow-100';
        },

        headerClass(cor) {
            return { yellow:'bg-yellow-200', pink:'bg-pink-200', blue:'bg-blue-200', green:'bg-green-200', purple:'bg-purple-200' }[cor] ?? 'bg-yellow-200';
        },
    }
}
</script>
@endsection
