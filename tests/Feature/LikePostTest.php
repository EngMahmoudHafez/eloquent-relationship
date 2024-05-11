<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Carbon\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LikePostTest extends TestCase
{
    public function a_post_can_be_liked()
    {
        $this->actingAs(User::factory()->create());
        $post = Post::factory()->create();

        $post->like();
        $this->assertCount(1, $post->likes);
    }
}
