<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->where('user_id', $request->user()->id)
            ->withCount('notes')
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Category::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($request->user()->id, $data['name']),
            'color' => $data['color'] ?? null,
        ]);

        return redirect()->route('categories.index')->with('status', 'Kategori dibuat.');
    }

    public function edit(Request $request, Category $category): View
    {
        $this->authorizeOwner($request, $category);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeOwner($request, $category);
        $data = $this->validated($request, $category->id);

        $category->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($request->user()->id, $data['name'], $category->id),
            'color' => $data['color'] ?? null,
        ]);

        return redirect()->route('categories.index')->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeOwner($request, $category);
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Kategori dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
    }

    private function uniqueSlug(int $userId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 2;
        while (
            Category::where('user_id', $userId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function authorizeOwner(Request $request, Category $category): void
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
