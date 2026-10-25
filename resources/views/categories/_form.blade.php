<div class="field">
    <label for="name">Nama kategori</label>
    <input id="name" name="name" class="input @error('name') invalid @enderror" value="{{ old('name', $category->name ?? '') }}" required maxlength="100">
    @error('name') <div class="error">{{ $message }}</div> @enderror
</div>
<div class="field">
    <label for="description">Deskripsi <span class="muted">(opsional)</span></label>
    <input id="description" name="description" class="input @error('description') invalid @enderror" value="{{ old('description', $category->description ?? '') }}" maxlength="255">
    @error('description') <div class="error">{{ $message }}</div> @enderror
</div>
