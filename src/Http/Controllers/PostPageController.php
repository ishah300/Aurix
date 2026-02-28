<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Aurix\Models\Post;
use Aurix\Services\MenuAccessService;

class PostPageController extends Controller
{
    public function index(Request $request, MenuAccessService $access): View
    {
        $query = Post::query()->latest('id');
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $posts = $query->paginate(12)->withQueryString();

        return view('aurix::rbac.index', [
            'title' => 'Posts',
            'layoutView' => $this->resolveLayoutView(),
            'contentView' => 'aurix::pages.posts.index',
            'posts' => $posts,
            'search' => $search,
            'canInsert' => $access->can($request->user(), 'posts', 'insert'),
            'canUpdate' => $access->can($request->user(), 'posts', 'update'),
            'canDelete' => $access->can($request->user(), 'posts', 'delete'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ])->validate();

        $title = (string) $payload['title'];

        Post::query()->create([
            'title' => $title,
            'slug' => $this->generateUniqueSlug($title),
            'content' => $payload['content'] ?? null,
            'is_published' => (bool) ($payload['is_published'] ?? true),
            'published_at' => (bool) ($payload['is_published'] ?? true) ? now() : null,
        ]);

        return back()->with('status', 'Post created successfully.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $payload = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ])->validate();

        $title = (string) $payload['title'];

        $post->fill([
            'title' => $title,
            'content' => $payload['content'] ?? null,
            'is_published' => (bool) ($payload['is_published'] ?? true),
            'published_at' => (bool) ($payload['is_published'] ?? true) ? ($post->published_at ?? now()) : null,
        ])->save();

        return back()->with('status', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Post deleted successfully.');
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = str($title)->lower()->slug()->value();
        $slug = $base !== '' ? $base : 'post';
        $i = 1;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function resolveLayoutView(): string
    {
        if (view()->exists('components.app-layout')) {
            return 'aurix::rbac.layouts.app-layout';
        }

        if (view()->exists('layouts.app') && $this->layoutUsesSlot()) {
            return 'aurix::rbac.layouts.layouts-app-slot-component';
        }

        if (view()->exists('layouts.app')) {
            return 'aurix::rbac.layouts.classic-layout';
        }

        return 'aurix::rbac.layouts.standalone';
    }

    private function layoutUsesSlot(): bool
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');

        if (! is_file($layoutPath)) {
            return false;
        }

        $contents = file_get_contents($layoutPath);

        return $contents !== false && str_contains($contents, '$slot');
    }
}
