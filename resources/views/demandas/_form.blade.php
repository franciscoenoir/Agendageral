<form method="POST" action="{{ $action }}"
      x-data="{
          links: {{ $demanda ? json_encode($demanda->links->map(fn($l) => ['url' => $l->url, 'label' => $l->label ?? ''])->toArray()) : '[]' }},
          addLink() { this.links.push({ url: '', label: '' }) },
          removeLink(i) { this.links.splice(i, 1) }
      }">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pasta</label>
            <select name="pasta_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— Sem pasta —</option>
                @foreach($pastas as $pasta)
                    <option value="{{ $pasta->id }}" {{ old('pasta_id', $demanda?->pasta_id) == $pasta->id ? 'selected' : '' }}>
                        📁 {{ $pasta->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
            <input type="text" name="titulo" value="{{ old('titulo', $demanda?->titulo) }}" required
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div x-data="categoriaSelector(@json($categorias->pluck('nome')), @json($categorias->pluck('id', 'nome')), @js(old('categoria', $demanda?->categoria ?? '')))">
                <input type="hidden" name="categoria" :value="val">
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria *</label>
                <div x-show="!nova" class="flex gap-1">
                    <select x-model="val" required class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <template x-for="c in cats" :key="c">
                            <option :value="c" x-text="c" :selected="c === val"></option>
                        </template>
                    </select>
                    <button type="button" @click="nova = true"
                            class="px-2 py-1 text-xs bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 shrink-0" title="Nova categoria">+</button>
                    <button type="button" @click="excluir(val)"
                            class="px-2 py-1 text-xs bg-red-50 text-red-500 rounded-lg hover:bg-red-100 shrink-0" title="Excluir categoria selecionada">🗑</button>
                </div>
                <div x-show="nova" class="flex gap-1">
                    <input x-model="novoNome" @keydown.enter.prevent="criar()" @keydown.escape="nova=false;novoNome=''" type="text"
                           placeholder="Nome da categoria..."
                           class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           x-ref="novoInput" x-init="$watch('nova', v => v && $nextTick(() => $refs.novoInput.focus()))">
                    <button type="button" @click="criar()"
                            class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shrink-0">Salvar</button>
                    <button type="button" @click="nova=false;novoNome=''"
                            class="px-2 py-1 text-xs text-gray-400 hover:text-gray-700 shrink-0">✕</button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urgência *</label>
                <select name="urgencia" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['urgente'=>'Urgente','alta'=>'Alta','media'=>'Média','baixa'=>'Baixa'] as $val => $label)
                        <option value="{{ $val }}" {{ old('urgencia', $demanda?->urgencia ?? 'media') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de início</label>
                <input type="date" name="data_inicio" value="{{ old('data_inicio', $demanda?->data_inicio?->format('Y-m-d')) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data limite *</label>
                <input type="date" name="data_limite" value="{{ old('data_limite', $demanda?->data_limite?->format('Y-m-d')) }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
            <input type="text" name="responsavel" value="{{ old('responsavel', $demanda?->responsavel) }}"
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="observacoes" rows="3"
                      class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('observacoes', $demanda?->observacoes) }}</textarea>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700">Links</label>
                <button type="button" @click="addLink()" class="text-xs text-blue-600 hover:underline">+ Adicionar link</button>
            </div>
            <template x-for="(link, i) in links" :key="i">
                <div class="flex gap-2 mb-2">
                    <input type="url" :name="`links[${i}][url]`" x-model="link.url" placeholder="https://..."
                           class="flex-1 border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" :name="`links[${i}][label]`" x-model="link.label" placeholder="Rótulo (opcional)"
                           class="w-36 border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" @click="removeLink(i)" class="text-red-400 hover:text-red-600 text-sm px-2">✕</button>
                </div>
            </template>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 font-medium">
                {{ $demanda ? 'Salvar alterações' : 'Criar demanda' }}
            </button>
            <a href="{{ route('dashboard') }}" class="px-5 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                Cancelar
            </a>
        </div>
    </div>
</form>

<script>
function categoriaSelector(cats, catIds, initial) {
    return {
        cats,
        catIds,
        val: initial || (cats[0] ?? ''),
        nova: false,
        novoNome: '',
        csrf: document.querySelector('meta[name=csrf-token]').content,

        async criar() {
            const nome = this.novoNome.trim();
            if (!nome) return;
            const r = await fetch('/categorias', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ nome }),
            });
            if (!r.ok) { alert('Categoria já existe ou nome inválido.'); return; }
            const cat = await r.json();
            this.cats.push(cat.nome);
            this.catIds[cat.nome] = cat.id;
            this.val = cat.nome;
            this.nova = false;
            this.novoNome = '';
        },

        async excluir(nome) {
            if (!confirm('Excluir a categoria «' + nome + '»?')) return;
            const id = this.catIds[nome];
            await fetch('/categorias/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrf } });
            this.cats = this.cats.filter(c => c !== nome);
            this.val = this.cats[0] ?? '';
        },
    };
}
</script>
