<label for="name">Nama</label>
<input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $category->name ?? '') }}">

<label for="color">Warna (opsional, hex #RRGGBB)</label>
<input id="color" name="color" type="text" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" value="{{ old('color', $category->color ?? '') }}">
