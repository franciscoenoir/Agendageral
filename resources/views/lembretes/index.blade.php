@extends('layouts.app')
@section('title', 'Lembretes')

@section('content')
<div
    id="canvas"
    x-data="lembretes()"
    x-init="init()"
    class="relative w-full select-none"
    style="height: calc(100vh - 120px);"
    @mousemove.window="onMouseMove($event)"
    @mouseup.window="onMouseUp()"
>
    {{-- Toolbar --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-sm font-semibold text-gray-700 mr-1">Novo lembrete:</span>
        @foreach([
            'yellow' => '#fef08a',
            'pink'   => '#fbcfe8',
            'blue'   => '#bfdbfe',
            'green'  => '#bbf7d0',
            'purple' => '#e9d5ff',
        ] as $cor => $hex)
        <button
            @click="criar('{{ $cor }}')"
            class="w-8 h-8 rounded shadow hover:scale-110 transition-transform border-2 border-white"
            style="background-color: {{ $hex }};"
            title="{{ ucfirst($cor) }}"
        ></button>
        @endforeach
        <span class="ml-3 text-xs text-gray-400">Arraste pelo cabeçalho • Clique no texto para editar</span>
    </div>

    {{-- Post-its --}}
    <template x-for="note in notes" :key="note.id">
        <div
            class="absolute w-56 rounded-md shadow-xl flex flex-col"
            :class="bgClass(note.cor)"
            :style="`left: ${note.pos_x}px; top: ${note.pos_y}px; z-index: ${dragging && dragging.id === note.id ? 999 : 10};`"
        >
            {{-- Cabeçalho: drag handle + ações --}}
            <div
                class="flex items-center justify-between px-2.5 py-1.5 rounded-t-md cursor-grab active:cursor-grabbing"
                :class="headerClass(note.cor)"
                @mousedown.prevent="startDrag($event, note)"
            >
                {{-- Ícone de arrastar --}}
                <svg class="w-4 h-4 text-gray-400 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM13 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM13 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM13 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>

                {{-- Seletor de cor --}}
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

                {{-- Botão excluir --}}
                <button
                    @mousedown.stop
                    @click.stop="excluir(note)"
                    class="w-5 h-5 flex items-center justify-center rounded-full bg-white/40 hover:bg-red-500 hover:text-white text-gray-500 text-xs font-bold transition-colors"
                    title="Excluir lembrete"
                >✕</button>
            </div>

            {{-- Texto --}}
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

    {{-- Estado vazio --}}
    <div x-show="notes.length === 0"
         class="absolute inset-0 flex flex-col items-center justify-center text-gray-300 pointer-events-none mt-10">
        <div class="text-6xl mb-3">🗒️</div>
        <p class="text-sm">Escolha uma cor acima para criar seu primeiro lembrete</p>
    </div>
</div>

<script>
function lembretes() {
    return {
        notes: @json($lembretes),
        dragging: null,
        offsetX: 0,
        offsetY: 0,
        csrf: document.querySelector('meta[name="csrf-token"]').content,

        init() {},

        async criar(cor) {
            const canvas = document.getElementById('canvas');
            const rect   = canvas.getBoundingClientRect();
            const pos_x  = Math.floor(Math.random() * (rect.width  - 240) + 20);
            const pos_y  = Math.floor(Math.random() * (rect.height - 240) + 60);

            const res  = await this.fetchJson('POST', '/lembretes', { cor, pos_x, pos_y });
            const note = await res.json();
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

        fetchJson(method, url, data = null) {
            return fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
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
