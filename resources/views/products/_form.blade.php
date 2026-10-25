@php $editing = isset($product); @endphp
<div class="form-grid">
    <div class="field">
        <label for="sku">SKU</label>
        <input id="sku" name="sku" class="input @error('sku') invalid @enderror" value="{{ old('sku', $product->sku ?? '') }}" required maxlength="50">
        @error('sku') <div class="error">{{ $message }}</div> @enderror
        <div class="hint">Kode unik produk, contoh: BVG-0001.</div>
    </div>
    <div class="field">
        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id" class="input @error('category_id') invalid @enderror" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field full">
        <label for="name">Nama produk</label>
        <input id="name" name="name" class="input @error('name') invalid @enderror" value="{{ old('name', $product->name ?? '') }}" required maxlength="150">
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="purchase_price">Harga beli (Rp)</label>
        <input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="input @error('purchase_price') invalid @enderror" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" required>
        @error('purchase_price') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="selling_price">Harga jual (Rp)</label>
        <input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="input @error('selling_price') invalid @enderror" value="{{ old('selling_price', $product->selling_price ?? '') }}" required>
        @error('selling_price') <div class="error">{{ $message }}</div> @enderror
    </div>
    @unless($editing)
    <div class="field">
        <label for="stock">Stok awal</label>
        <input id="stock" name="stock" type="number" min="0" class="input @error('stock') invalid @enderror" value="{{ old('stock', 0) }}" required>
        @error('stock') <div class="error">{{ $message }}</div> @enderror
        <div class="hint">Setelah dibuat, stok hanya berubah lewat transaksi.</div>
    </div>
    @endunless
    <div class="field">
        <label for="minimum_stock">Stok minimum</label>
        <input id="minimum_stock" name="minimum_stock" type="number" min="0" class="input @error('minimum_stock') invalid @enderror" value="{{ old('minimum_stock', $product->minimum_stock ?? 5) }}" required>
        @error('minimum_stock') <div class="error">{{ $message }}</div> @enderror
        <div class="hint">Produk masuk daftar stok rendah saat stok ≤ nilai ini.</div>
    </div>
</div>
