<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\LiveStream;
use App\Models\RadioStream;
use App\Models\Sermon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_sermons_endpoint_returns_published_sermons(): void
    {
        $category = Category::create([
            'name' => 'Predications',
            'slug' => 'predications',
        ]);

        Sermon::create([
            'category_id' => $category->id,
            'title' => 'La foi qui tient ferme',
            'slug' => 'la-foi-qui-tient-ferme',
            'preacher' => 'Pasteur principal',
            'audio_url' => 'https://media.example.test/sermon.mp3',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc123',
            'youtube_id' => 'abc123',
            'published_at' => now(),
            'is_featured' => true,
            'is_published' => true,
        ]);

        Sermon::create([
            'title' => 'Brouillon',
            'slug' => 'brouillon',
            'is_published' => false,
        ]);

        $this
            ->getJson('/api/v1/public/sermons/')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'La foi qui tient ferme')
            ->assertJsonPath('data.0.audioUrl', 'https://media.example.test/sermon.mp3')
            ->assertJsonPath('data.0.youtubeId', 'abc123')
            ->assertJsonPath('data.0.category.slug', 'predications');
    }

    public function test_public_sermon_detail_endpoint_returns_one_sermon(): void
    {
        $sermon = Sermon::create([
            'title' => 'Message du dimanche',
            'slug' => 'message-du-dimanche',
            'description' => 'Un message pour la communaute.',
            'is_published' => true,
        ]);

        $this
            ->getJson('/api/v1/public/sermons/message-du-dimanche/')
            ->assertOk()
            ->assertJsonPath('data.slug', 'message-du-dimanche')
            ->assertJsonPath('sermon.description', 'Un message pour la communaute.');

        $this
            ->getJson('/api/v1/public/sermons/'.$sermon->id.'/')
            ->assertOk()
            ->assertJsonPath('data.slug', 'message-du-dimanche');
    }

    public function test_public_radio_endpoint_returns_active_streams(): void
    {
        RadioStream::create([
            'title' => 'Radio La Voix de Dieu',
            'slug' => 'radio-la-voix-de-dieu',
            'stream_url' => 'https://stream.example.test/live.mp3',
            'is_active' => true,
        ]);

        RadioStream::create([
            'title' => 'Inactive',
            'slug' => 'inactive',
            'stream_url' => 'https://stream.example.test/inactive.mp3',
            'is_active' => false,
        ]);

        $this
            ->getJson('/api/v1/public/radio/')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('radio.streamUrl', 'https://stream.example.test/live.mp3');
    }

    public function test_public_live_endpoint_returns_published_live_streams(): void
    {
        LiveStream::create([
            'title' => 'Culte en direct',
            'slug' => 'culte-en-direct',
            'youtube_url' => 'https://www.youtube.com/watch?v=live123',
            'youtube_id' => 'live123',
            'starts_at' => now(),
            'is_live' => true,
            'is_published' => true,
        ]);

        $this
            ->getJson('/api/v1/public/live/')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('live.youtubeId', 'live123')
            ->assertJsonPath('live.isLive', true);
    }
}
