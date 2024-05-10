# Laravel Eloquent Mastery: 30 Pro Tips to Supercharge Your Queries .

-   Laravel's Eloquent ORM is a powerful and intuitive Active Record implementation for working with your database. However, as with any tool, there's a difference between just using it and unlocking its full potential. In this series, we'll walk through 30 advanced tricks for efficient Eloquent querying, complete with real-world examples that will turn you into an Eloquent wizard.

### Scenario 1: Filtering Relations Without Loading Them

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `whereHas` to filter relations without loading them.

```php

// Retrieve posts with at least one comment containing the word 'Eloquent'
$posts = App\\Models\\Post::whereHas('comments', function ($query) {
    $query->where('content', 'like', '%Eloquent%');
})->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Loading the relation unnecessarily.

```php
// This will load all the comments even if we only need to check their existence
$posts = App\\Models\\Post::with('comments')->get()->filter(function ($post) {
    return $post->comments->contains('content', 'like', '%Eloquent%');
});
```

### Scenario 2: Eager Loading vs. Lazy Loading

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use eager loading to prevent the N+1 query problem.

```php

// Eager load comments when retrieving posts
$posts = App\\Models\\Post::with('comments')->get();

```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Lazy loading each relation in a loop.

```php
// This will load all the comments even if we only need to check their existence
$posts = App\\Models\\Post::with('comments')->get()->filter(function ($post) {
    return $post->comments->contains('content', 'like', '%Eloquent%');
});
```

### Scenario 3: Selective Columns in Eager Loading

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** When eager loading, specify the columns you need to improve performance.

```php
// Only select the 'id' and 'title' of the posts along with their 'username' from the user relationship
$posts = App\\Models\\Post::with('user:id,username')->get(['id', 'title']);
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Eager loading all columns without consideration.

```php
// This will select all columns from the related tables
$posts = App\\Models\\Post::with('user')->get();
```

### Scenario 4: Complex Ordering with Subqueries

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use subqueries to order by aggregated or related data.

```php

use Illuminate\\Database\\Eloquent\\Builder;

$users = App\\Models\\User::addSelect(['last_post_created_at' => App\\Models\\Post::select('created_at')
    ->whereColumn('user_id', 'users.id')
    ->orderBy('created_at', 'desc')
    ->limit(1)
])->orderBy('last_post_created_at', 'desc')->get();

```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Fetching the entire collection and sorting in-memory.

```php
$users = App\\Models\\User::all()->sortByDesc(function ($user) {
    return $user->posts->max('created_at');
});
```

### Scenario 5: Mass Assignment Protection

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use fillable or guarded properties to protect against mass assignment vulnerabilities.

```php
class User extends Model
{
    protected $fillable = ['name', 'email'];
    // or guarded
    protected $guarded = ['password','phone number'];
}
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Not specifying fillable fields and risking mass assignment attacks.

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

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `withCount` for an efficient way to count related models without loading them.

```php

// Get users along with the count of their posts
$users = App\\Models\\User::withCount('posts')->get();

// You can access the count via the {relation}_count attribute
foreach ($users as $user) {
    echo $user->posts_count;
}
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Loading the entire relationship only to count it.

```php
$users = App\\Models\\User::with('posts')->get();

foreach ($users as $user) {
    echo $user->posts->count();
}
```

### Scenario 7: Batch Updates

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use Eloquent's `update` method when performing batch updates.

```php

// Update all posts marked as 'draft' to 'published'
App\\Models\\Post::where('status', 'draft')->update(['status' => 'published']);
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Looping through models to update them individually.

```php
$posts = App\\Models\\Post::where('status', 'draft')->get();

foreach ($posts as $post) {
    $post->status = 'published';
    $post->save();
}
```

### Scenario 8: Chunking Results for Memory Efficiency

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `chunk` or `lazy` to handle large datasets efficiently.

```php

// Process large datasets using chunk to manage memory consumption
App\\Models\\User::where('active', true)->chunk(100, function ($users) {
    // Perform actions on chunks of 100 users at a time
});
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Loading entire datasets into memory.

```php
$users = App\\Models\\User::where('active', true)->get(); // Memory-intensive on large datasets

foreach ($users as $user) {
    // ...
}
```

### Scenario 9: Avoiding Redundant Relationship Queries

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use the `onceWith` method to load a relationship only once.

```php

$users = App\\Models\\User::all();

$users->each(function ($user) {
    // The 'profile' relation is loaded only once then reused
    $profile = $user->onceWith('profile')->profile;
});
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Loading the same relationship multiple times.

```php
$users = App\\Models\\User::all();

$users->each(function ($user) {
    // The 'profile' relationship is loaded for each iteration
    $profile = $user->profile;
});
```

### Scenario 10: Cautious Attribute Casting

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Cast attributes to the correct type to ensure proper data handling.

```php

class Order extends Model
{
    protected $casts = [
        'is_processed' => 'boolean',
        'order_date' => 'datetime',
    ];
}
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Ignoring attribute casting, which can lead to unexpected behavior.

```php
class Order extends Model
{
    // Lack of casting may result in 'is_processed' being treated as a string
}
```

### Scenario 11: Selective Relationship Loading with Constraints

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Load relations conditionally with constraints to reduce the dataset.

```php

// Load users with their recent active posts
$users = App\\Models\\User::with(['posts' => function ($query) {
    $query->where('created_at', '>', now()->subDays(30))
          ->where('status', 'active');
}])->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Loading all related data regardless of the need.

```php
// Retrieves all posts for each user
$users = App\\Models\\User::with('posts')->get();
```

### Scenario 12: Using `whereColumn` for Comparing Columns

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `whereColumn` for intuitive comparisons between two columns.

```php

// Retrieve orders where the shipped date is later than the ordered date
$orders = App\\Models\\Order::whereColumn('shipped_date', '>', 'ordered_date')->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manual PHP comparisons that can lead to errors and inefficient queries.

```php
$orders = App\\Models\\Order::all()->filter(function ($order) {
    return strtotime($order->shipped_date) > strtotime($order->ordered_date);
});
```

### Scenario 13: Scope Query to Current Model Instance

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Define local scopes for reusability and maintainability.

```php

class Post extends Model
{
    // Define a local scope for published posts
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}

$publishedPosts = App\\Models\\Post::published()->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Repeating query logic throughout the application.

```php
$publishedPosts = App\\Models\\Post::where('status', 'published')->get();
```

### Scenario 14: Efficient Pivot Table Interaction with `wherePivot`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `wherePivot` for efficient querying on pivot table fields.

```php
// Retrieve roles of the user where pivot column 'expires_at' is in the future
$userRoles = $user->roles()->wherePivot('expires_at', '>', now())->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Filtering through pivot fields manually.

```php
$userRoles = $user->roles->filter(function ($role) {
    return $role->pivot->expires_at > now();
});
```

### Scenario 15: Prudent Use of `latest` and `oldest`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use latest and oldest for a quick order by the creation date.

```php
// Get the latest users by creation date
$latestUsers = App\\Models\\User::latest()->take(10)->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manually specifying orderBy for default timestamps.

```php
// More verbose and error-prone
$latestUsers = App\\Models\\User::orderBy('created_at', 'desc')->take(10)->get();
```

### Scenario 16: Advanced Queries with Raw Expressions

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use raw expressions judiciously for complex queries that aren't easily handled by Eloquent's fluent methods.

```php

// Using a raw expression to get the total amount spent by users
$users = App\\Models\\User::select([
    'users.*',
    \\DB::raw('(SELECT SUM(orders.amount) FROM orders WHERE orders.user_id = users.id) AS total_spent')
])->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Executing separate queries to calculate aggregates can be less efficient.

```php
$users = App\\Models\\User::all()->each(function ($user) {
    $user->total_spent = $user->orders->sum('amount');
});
```

### Scenario 17: Optimal Use of `increment` and `decrement`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Utilize the `increment` and `decrement` methods for updating numeric columns efficiently.

```php
// Increment the view count without loading the model
App\\Models\\Post::where('id', 1)->increment('view_count');
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manually incrementing and saving the model, which is less efficient.

```php
$post = App\\Models\\Post::find(1);
$post->view_count++;
$post->save();
```

### Scenario 18: Dynamic Where Clauses with `where*`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use Eloquent’s dynamic `where` methods to build expressive queries.

```php

// Retrieve posts with status 'published' using dynamic where
$posts = App\\Models\\Post::whereStatus('published')->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Using generic where clauses for columns that could benefit from dynamic where.

```php
// Less expressive
$posts = App\\Models\\Post::where('status', 'published')->get();
```

### Scenario 19: Using `having` in Aggregated Queries

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use the `having` clause in conjunction with group by for conditions on aggregated data.

```php

// Select users with more than 10 orders
$users = App\\Models\\User::groupBy('id')
    ->having(DB::raw('COUNT(orders.id)'), '>', 10)
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Retrieving all data and filtering in-memory.

```php
$users = App\\Models\\User::all()->filter(function ($user) {
    return $user->orders->count() > 10;
});
```

### Scenario 20: Relation Existence Queries

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Utilize relation existence queries to filter models based on relationships elegantly.

```php

// Retrieve posts that have comments
$posts = App\\Models\\Post::has('comments')->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Loading models and their relationships without leveraging query scopes.

```php
// Inefficient if you just want to know existence
$posts = App\\Models\\Post::all()->filter(function ($post) {
    return $post->comments->isNotEmpty();
});
```

### Scenario 21: Utilizing Subquery Selects

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use subquery selects to include information from related tables in your initial query results, avoiding the N+1 problem.

```php

// Add the latest post title to each user result
$users = App\\Models\\User::addSelect(['latest_post_title' => App\\Models\\Post::select('title')
    ->whereColumn('user_id', 'users.id')
    ->latest()
    ->limit(1)
])->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Attaching related data via a loop; this leads to unnecessary queries.

```php
$users = App\\Models\\User::all()->each(function ($user) {
    $user->latest_post_title = $user->posts()->latest()->first()->title;
});
```

### Scenario 22: The Power of `withSum`, `withAvg`, and Related Methods

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Optimize summary calculations on relationships using Eloquent's `withSum`, `withAvg`, and other similar methods.

```php

// Get users with the sum of their order amounts
$users = App\\Models\\User::withSum('orders', 'amount')->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Performing manual calculations after loading relationships.

```php
$users = App\\Models\\User::with('orders')->get()->each(function ($user) {
    $user->orders_sum = $user->orders->sum('amount');
});
```

### Scenario 23: Use of `findMany` for Multiple Models

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Efficient retrieval of many models by an array of primary keys using `findMany`.

```php

// Get multiple posts by an array of IDs
$postIds = [1, 2, 3];
$posts = App\\Models\\Post::findMany($postIds);

```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Looping through IDs to retrieve models one at a time.

```php
$postIds = [1, 2, 3];
$posts = collect($postIds)->map(function ($id) {
    return App\\Models\\Post::find($id);
});
```

### Scenario 24: The Usefulness of `sole` Method

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Using `sole` method to retrieve a single record that matches the query constraints and fail if there are no or multiple records.

```php

// Retrieve a single user by email or fail if it does not exist or multiple exist
$user = App\\Models\\User::where('email', 'john@example.com')->sole();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manually verifying the number of records to find a single expected entry.

```php
$users = App\\Models\\User::where('email', 'john@example.com')->get();
if ($users->count() !== 1) {
    throw new Exception('Expected only one user.');
}
$user = $users->first();
```

### Scenario 25: Leveraging `lockForUpdate`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `lockForUpdate` to prevent the selected rows from being modified or from selecting by other shared lock until the transaction is complete when dealing with concurrent database access.

```php

DB::transaction(function () {
    $affectedRows = App\\Models\\User::where('votes', '>', 100)->lockForUpdate()->get();
    // Perform operation on the affected rows
});
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Not using proper locking mechanisms, which can lead to race conditions and data corruption.

```php
/$affectedRows = App\\Models\\User::where('votes', '>', 100)->get();
// If another process modifies the data here, it could cause issues
// Perform operation on the affected rows
```

### Scenario 26: Utilizing `replicate` for Cloning Models

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Make an exact copy of a model, except for its primary key, using the `replicate` method.

```php

$originalPost = App\\Models\\Post::find(1);
$clonedPost = $originalPost->replicate();
$clonedPost->save(); // Save the cloned model as a new record

```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manually setting attributes to create a new, similar record, which is error-prone.

```php
$originalPost = App\\Models\\Post::find(1);
$clonedPost = new App\\Models\\Post($originalPost->toArray());
$clonedPost->id = null; // Remember to exclude the primary key
$clonedPost->save();
```

### Scenario 27: Smart Relationship Refreshing with `refresh`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Update the parent model and all of its relationships using the `refresh` method.

```php

$post = App\\Models\\Post::with('comments')->find(1);
// ...after some updates to the comments
$post->refresh(); // The $post model and its 'comments' relationship are reloaded from the database
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manually reloading the model and relations which could lead to inconsistencies.

```php
$post = App\\Models\\Post::with('comments')->find(1);
// ...after some updates to the comments
$post->load('comments'); // Only the 'comments' relationship is refreshed, the parent $post model is untouched
```

### Scenario 28: Avoid Overriding Primary Keys Unintentionally

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Ensure mass assignment doesn't inadvertently overwrite primary key values by setting the `$guarded` property appropriately.

```php

class Post extends Model
{
    protected $guarded = ['id']; // Guard the 'id' to prevent overwriting
}
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** A lack of protection that allows mass assignments to modify primary keys.

```php
class Post extends Model
{
    // Without the 'id' guarded, a mass assignment could override it
    protected $fillable = ['title', 'content']; // 'id' is not guarded
}
```

### Scenario 29: Tactical Use of `whereExists` and `whereNotExists`

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Use `whereExists` for queries that only need to check the presence of a related record.

```php

$usersWithPosts = App\\Models\\User::whereExists(function ($query) {
    $query->select(DB::raw(1))
          ->from('posts')
          ->whereRaw('posts.user_id = users.id');
})->get();
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** An inefficient way of checking for related records' existence.

```php
$usersWithPosts = App\\Models\\User::all()->filter(function ($user) {
    return $user->posts()->exists();
});
```

### Scenario 30: Restricting Relations with Global Scopes

-   ![#c5f015](https://via.placeholder.com/15/c5f015/000000?text=+) **Good Practice:** Apply a global scope to a relation to enforce a constraint every time that relation is queried.

```php

class ActiveCommentScope implements \\Illuminate\\Database\\Eloquent\\Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('active', 1);
    }
}

// Usage in the related model
class Post extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new ActiveCommentScope);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

// Every time you retrieve comments from a post, they will be active comments.
```

-   ![#f03c15](https://via.placeholder.com/15/f03c15/000000?text=+) **Bad Practice:** Manually applying the same constraints repeatedly.

```php
$post = App\\Models\\Post::find(1);
$comments = $post->comments()->where('active', 1)->get(); // Constraint is manually applied
```
