<div class="form-grid">
    <div class="field full">
        <label for="name">Nama pemasok</label>
        <input id="name" name="name" class="input @error('name') invalid @enderror" value="{{ old('name', $supplier->name ?? '') }}" required maxlength="150">
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="phone">Telepon</label>
        <input id="phone" name="phone" class="input @error('phone') invalid @enderror" value="{{ old('phone', $supplier->phone ?? '') }}" maxlength="30">
        @error('phone') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="input @error('email') invalid @enderror" value="{{ old('email', $supplier->email ?? '') }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field full">
        <label for="address">Alamat</label>
        <input id="address" name="address" class="input @error('address') invalid @enderror" value="{{ old('address', $supplier->address ?? '') }}" maxlength="255">
        @error('address') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
