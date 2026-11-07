<details class="bg-slate-50 rounded border text-[11px]">
    <summary class="cursor-pointer px-2 py-1 font-semibold truncate">
        {{ $branch->icon }} {{ $branch->name }}
        @if(!$branch->is_active)
            <span class="text-amber-700 font-bold">(gizli)</span>
        @endif
        <span class="text-indigo-600 font-bold">✏️</span>
    </summary>
    <div class="p-2 space-y-1.5 border-t">
        <form action="{{ route('admin.branches.update', $branch->id) }}" method="POST" class="space-y-1.5">
            @csrf
            <input type="hidden" name="return_tab" value="{{ $returnTab }}">
            <div class="grid grid-cols-4 gap-1">
                <input type="text" name="icon" value="{{ $branch->icon }}" required aria-label="İkon" class="border rounded-lg px-1 py-1 text-center bg-white">
                <input type="text" name="name" value="{{ $branch->name }}" required aria-label="Branş adı" class="col-span-3 border rounded-lg px-2 py-1 bg-white font-bold">
            </div>
            <div class="grid grid-cols-2 gap-1">
                <input type="text" name="age_range" value="{{ $branch->age_range }}" required placeholder="Yaş aralığı" class="border rounded-lg px-2 py-1 bg-white">
                <input type="text" name="level" value="{{ $branch->level }}" required placeholder="Seviye" class="border rounded-lg px-2 py-1 bg-white">
            </div>
            <textarea name="description" rows="2" placeholder="Açıklama" class="w-full border rounded-lg px-2 py-1 bg-white">{{ $branch->description }}</textarea>
            <input type="text" name="benefits" value="{{ $branch->benefits }}" placeholder="Kazanımlar" class="w-full border rounded-lg px-2 py-1 bg-white">
            <input type="number" name="sort_order" value="{{ $branch->sort_order }}" placeholder="Sıra" aria-label="Sıra" class="w-full border rounded-lg px-2 py-1 bg-white">
            <label class="flex items-center gap-1.5 cursor-pointer font-bold">
                <input type="checkbox" name="hide_on_site" value="1" {{ $branch->is_active ? '' : 'checked' }}>
                <span>🙈 Ana sayfada gizle</span>
            </label>
            <button type="submit" class="w-full py-1 rounded-lg bg-indigo-600 text-white font-bold">💾 Kaydet</button>
        </form>
        <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm(@js($branch->name . ' branşını silmek istediğinize emin misiniz?'))">
            @csrf
            <input type="hidden" name="return_tab" value="{{ $returnTab }}">
            <button type="submit" class="w-full py-1 rounded-lg text-rose-600 font-bold">🗑️ Branşı Sil</button>
        </form>
    </div>
</details>
