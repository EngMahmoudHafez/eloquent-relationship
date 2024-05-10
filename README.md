# Laravel Eloquent Mastery: 30 Pro Tips to Supercharge Your Queries .

-   Laravel's Eloquent ORM is a powerful and intuitive Active Record implementation for working with your database. However, as with any tool, there's a difference between just using it and unlocking its full potential. In this series, we'll walk through 30 advanced tricks for efficient Eloquent querying, complete with real-world examples that will turn you into an Eloquent wizard.

### Scenario 1: Filtering Relations Without Loading Them

-   **Good Practice:** Use `whereHas` to filter relations without loading them.

```php

// Retrieve posts with at least one comment containing the word 'Eloquent'
$posts = App\\Models\\Post::whereHas('comments', function ($query) {
    $query->where('content', 'like', '%Eloquent%');
})->get();
```

-   **Bad Practice:** Loading the relation unnecessarily.

```php
// This will load all the comments even if we only need to check their existence
$posts = App\\Models\\Post::with('comments')->get()->filter(function ($post) {
    return $post->comments->contains('content', 'like', '%Eloquent%');
});
```

### Scenario 2: Eager Loading vs. Lazy Loading

-   **Good Practice:** Use eager loading to prevent the N+1 query problem.

```php

// Eager load comments when retrieving posts
$posts = App\\Models\\Post::with('comments')->get();

```

-   **Bad Practice:** Lazy loading each relation in a loop.

```php
// This will load all the comments even if we only need to check their existence
$posts = App\\Models\\Post::with('comments')->get()->filter(function ($post) {
    return $post->comments->contains('content', 'like', '%Eloquent%');
});
```

### Scenario 3: Selective Columns in Eager Loading

-   **Good Practice:** When eager loading, specify the columns you need to improve performance.

```php
// Only select the 'id' and 'title' of the posts along with their 'username' from the user relationship
$posts = App\\Models\\Post::with('user:id,username')->get(['id', 'title']);
```

-   **Bad Practice:** Eager loading all columns without consideration.

```php
// This will select all columns from the related tables
$posts = App\\Models\\Post::with('user')->get();
```

### Scenario 4: Complex Ordering with Subqueries

-   **Good Practice:** Use subqueries to order by aggregated or related data.

```php

use Illuminate\\Database\\Eloquent\\Builder;

$users = App\\Models\\User::addSelect(['last_post_created_at' => App\\Models\\Post::select('created_at')
    ->whereColumn('user_id', 'users.id')
    ->orderBy('created_at', 'desc')
    ->limit(1)
])->orderBy('last_post_created_at', 'desc')->get();

```

-   **Bad Practice:** Fetching the entire collection and sorting in-memory.

```php
$users = App\\Models\\User::all()->sortByDesc(function ($user) {
    return $user->posts->max('created_at');
});
```

### Scenario 5: Mass Assignment Protection

-   **Good Practice:** Use fillable or guarded properties to protect against mass assignment vulnerabilities.

```php
class User extends Model
{
    protected $fillable = ['name', 'email'];
    // or guarded
    protected $guarded = ['password','phone number'];
}
```

-   **Bad Practice:** Not specifying fillable fields and risking mass assignment attacks.

```php
class User extends Model
{
    // No $fillable or $guarded property set
}
```

### Highlighting a New Feature in Laravel 10

-   **Laravel 10 Tip:** Use the new `whereRelation` method to write cleaner code when querying based on simple relation constraints.

```php
// Laravel 10 introduces whereRelation for simpler queries
$posts = App\\Models\\Post::whereRelation('comments', 'content', 'like', '%Eloquent%')->get();
```

### Scenario 6: Efficient Relationship Counts

-   **Good Practice:** Use `withCount` for an efficient way to count related models without loading them.

```php

// Get users along with the count of their posts
$users = App\\Models\\User::withCount('posts')->get();

// You can access the count via the {relation}_count attribute
foreach ($users as $user) {
    echo $user->posts_count;
}
```

-   **Bad Practice:** Loading the entire relationship only to count it.

```php
$users = App\\Models\\User::with('posts')->get();

foreach ($users as $user) {
    echo $user->posts->count();
}
```

### Scenario 7: Batch Updates

-   **Good Practice:** Use Eloquent's `update` method when performing batch updates.

```php

// Update all posts marked as 'draft' to 'published'
App\\Models\\Post::where('status', 'draft')->update(['status' => 'published']);
```

-   **Bad Practice:** Looping through models to update them individually.

```php
$posts = App\\Models\\Post::where('status', 'draft')->get();

foreach ($posts as $post) {
    $post->status = 'published';
    $post->save();
}
```

### Scenario 8: Chunking Results for Memory Efficiency

-   **Good Practice:** Use `chunk` or `lazy` to handle large datasets efficiently.

```php

// Process large datasets using chunk to manage memory consumption
App\\Models\\User::where('active', true)->chunk(100, function ($users) {
    // Perform actions on chunks of 100 users at a time
});
```

-   **Bad Practice:** Loading entire datasets into memory.

```php
$users = App\\Models\\User::where('active', true)->get(); // Memory-intensive on large datasets

foreach ($users as $user) {
    // ...
}
```

### Scenario 9: Avoiding Redundant Relationship Queries

-   **Good Practice:** Use the `onceWith` method to load a relationship only once.

```php

$users = App\\Models\\User::all();

$users->each(function ($user) {
    // The 'profile' relation is loaded only once then reused
    $profile = $user->onceWith('profile')->profile;
});
```

-   **Bad Practice:** Loading the same relationship multiple times.

```php
$users = App\\Models\\User::all();

$users->each(function ($user) {
    // The 'profile' relationship is loaded for each iteration
    $profile = $user->profile;
});
```

### Scenario 10: Cautious Attribute Casting

-   **Good Practice:** Cast attributes to the correct type to ensure proper data handling.

```php

class Order extends Model
{
    protected $casts = [
        'is_processed' => 'boolean',
        'order_date' => 'datetime',
    ];
}
```

-   **Bad Practice:** Ignoring attribute casting, which can lead to unexpected behavior.

```php
class Order extends Model
{
    // Lack of casting may result in 'is_processed' being treated as a string
}
```

### Scenario 11: Selective Relationship Loading with Constraints

-   **Good Practice:** Load relations conditionally with constraints to reduce the dataset.

```php

// Load users with their recent active posts
$users = App\\Models\\User::with(['posts' => function ($query) {
    $query->where('created_at', '>', now()->subDays(30))
          ->where('status', 'active');
}])->get();
```

-   **Bad Practice:** Loading all related data regardless of the need.

```php
// Retrieves all posts for each user
$users = App\\Models\\User::with('posts')->get();
```
