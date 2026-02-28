<div class="la-wrap">
    @include('aurix::rbac.partials.ui-styles')

    <div class="la-shell">
        <div class="la-card">
            <div class="la-head">
                <h2>Posts</h2>
                <form method="GET" action="{{ route('aurix.posts.index') }}" class="la-head-search">
                    <input class="la-input" type="text" name="q" value="{{ $search }}" placeholder="Search posts">
                    <button type="submit" class="la-btn">Search</button>
                </form>
            </div>

            <div class="la-body">
                @if(session('status'))
                    <div class="la-status ok la-mb-10">{{ session('status') }}</div>
                @endif

                @if($canInsert)
                    <details class="la-details">
                        <summary>Create Post</summary>
                        <div class="la-details-body">
                            <form method="POST" action="{{ route('aurix.posts.store') }}" class="la-form-stack">
                                @csrf
                                <input class="la-input" type="text" name="title" required placeholder="Title">
                                <textarea class="la-textarea" name="content" rows="4" placeholder="Content"></textarea>
                                <label><input type="checkbox" name="is_published" value="1" checked> Published</label>
                                <div><button type="submit" class="la-btn primary">Create</button></div>
                            </form>
                        </div>
                    </details>
                @endif

                <div class="la-table-wrap">
                    <table class="la-table">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td>{{ $post->title }}</td>
                                <td class="la-muted">{{ $post->slug }}</td>
                                <td>{{ $post->is_published ? 'Yes' : 'No' }}</td>
                                <td>
                                    <div class="la-row-actions">
                                        @if($canUpdate)
                                            <details>
                                                <summary class="la-btn">Edit</summary>
                                                <div class="la-popover">
                                                    <form method="POST" action="{{ route('aurix.posts.update', $post) }}" class="la-form-stack">
                                                        @csrf
                                                        @method('PUT')
                                                        <input class="la-input" type="text" name="title" value="{{ $post->title }}" required>
                                                        <textarea class="la-textarea" name="content" rows="3">{{ $post->content }}</textarea>
                                                        <label><input type="checkbox" name="is_published" value="1" @checked($post->is_published)> Published</label>
                                                        <button type="submit" class="la-btn primary">Update</button>
                                                    </form>
                                                </div>
                                            </details>
                                        @endif

                                        @if($canDelete)
                                            <form method="POST" action="{{ route('aurix.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="la-btn danger">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="la-empty-cell">No posts found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="la-pagination-wrap">{{ $posts->links() }}</div>
            </div>
        </div>
    </div>
</div>
