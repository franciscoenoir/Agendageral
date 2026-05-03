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
            <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
            <input type="text" name="titulo" value="{{ old('titulo', $demanda?->titulo) }}" required
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria *</label>
                <select name="categoria" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['Engenharia','Firedrill','Rosa Garden','Particular','Família','Administrativo','Outro'] as $cat)
                        <option value="{{ $cat }}" {{ old('categoria', $demanda?->categoria) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
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
