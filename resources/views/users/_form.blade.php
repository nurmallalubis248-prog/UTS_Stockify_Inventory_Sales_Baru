@php $editing = isset($user); @endphp
<div class="form-grid">
    <div class="field">
        <label for="name">Nama</label>
        <input id="name" name="name" class="input @error('name') invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required maxlength="100">
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="input @error('email') invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
        @error('email') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label for="password">Kata sandi @if($editing)<span class="muted">(kosongkan jika tidak diubah)</span>@endif</label>
        <input id="password" name="password" type="password" class="input @error('password') invalid @enderror" autocomplete="new-password" @unless($editing) required @endunless>
        @error('password') <div class="error">{{ $message }}</div> @enderror
        <div class="hint">Minimal 8 karakter.</div>
    </div>
    <div class="field">
        <label for="password_confirmation">Ulangi kata sandi</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password">
    </div>
    <div class="field full">
        <label for="role">Peran</label>
        <select id="role" name="role" class="input @error('role') invalid @enderror" required>
            @foreach(['staff' => 'Staff — membuat transaksi, melihat produk', 'admin' => 'Admin — akses penuh', 'user' => 'User — hanya melihat produk dan stok'] as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role ?? 'staff') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
