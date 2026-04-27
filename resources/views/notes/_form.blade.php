@php($isEdit = $note->exists ?? false)
<label for="title">Judul</label>
<input id="title" name="title" type="text" maxlength="200" required value="{{ old('title', $note->title) }}">

<label for="category_id">Kategori</label>
<select id="category_id" name="category_id">
    <option value="">— Tanpa kategori —</option>
    @foreach($categories as $cat)
        <option value="{{ $cat->id }}" @selected((int) old('category_id', $note->category_id) === $cat->id)>{{ $cat->name }}</option>
    @endforeach
</select>

<label for="body">Isi</label>
<textarea id="body" name="body" required maxlength="200000">{{ old('body', $note->body) }}</textarea>

<label for="reference_url">Referensi URL (opsional)</label>
<input id="reference_url" name="reference_url" type="url" maxlength="2048" value="{{ old('reference_url', $note->reference_url) }}">

<label for="thumbnail">Thumbnail (opsional, jpg/jpeg/png, max 8 MB)</label>
<input id="thumbnail" name="thumbnail" type="file" accept="image/png,image/jpeg">
@if($isEdit && $note->thumbnail_path)
    <p class="muted" style="margin-top:6px;">
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($note->thumbnail_path) }}" alt="" style="max-width:200px;border-radius:8px;">
        <label><input type="checkbox" name="remove_thumbnail" value="1"> Hapus thumbnail saat ini</label>
    </p>
@endif

<fieldset style="margin-top:18px;border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
    <legend>Lampiran (opsional)</legend>
    <p class="muted" style="margin-top:0;">Diizinkan: {{ implode(', .', array_merge([''], $allowedExt)) }}. Klik "+ Tambah" untuk menambah field.</p>
    <div id="attachmentsList">
        @if($isEdit)
            @foreach($note->attachments as $att)
                <div class="attachment-row">
                    <a href="{{ route('notes.attachments.download', [$note, $att]) }}">{{ $att->original_name }}</a>
                    <label class="muted" style="margin-left:8px;">
                        <input type="checkbox" name="delete_attachments[]" value="{{ $att->id }}"> Hapus
                    </label>
                </div>
            @endforeach
        @endif
    </div>
    <template id="attachmentTemplate">
        <div class="attachment-row">
            <input type="file" name="attachments[]" accept=".txt,.pdf,.jpg,.jpeg,.png,.xlsx,.docx" required>
            <button type="button" class="btn-secondary" data-attachments-remove>Hapus</button>
        </div>
    </template>
    <button type="button" class="btn-secondary" data-attachments-add>+ Tambah Lampiran</button>
</fieldset>

<fieldset style="margin-top:18px;border:1px solid var(--border);border-radius:10px;padding:12px 16px;">
    <legend>Pengaturan Berbagi</legend>
    <label for="share_password">Password berbagi (opsional, min 8 karakter)</label>
    <input id="share_password" name="share_password" type="password" minlength="8" maxlength="128" autocomplete="new-password">
    @if($isEdit && $note->requiresPassword())
        <label style="margin-top:6px;display:block;"><input type="checkbox" name="clear_share_password" value="1"> Hapus password berbagi saat ini</label>
    @endif

    <label for="share_expires_at">Tanggal kedaluwarsa berbagi (opsional)</label>
    <input id="share_expires_at" name="share_expires_at" type="datetime-local"
           value="{{ old('share_expires_at', optional($note->share_expires_at)->format('Y-m-d\TH:i')) }}">
</fieldset>
