<?php

namespace Tests\Feature\Api;

use App\Models\Belief;
use App\Models\ChurchInfo;
use App\Models\ChurchMember;
use App\Models\ChurchPhoto;
use App\Models\CommitteeMember;
use App\Models\DailyQuote;
use App\Models\Jurisdiction;
use App\Models\PastorCalendarEvent;
use App\Models\PrayerRoom;
use App\Models\Program;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicChurchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_info_and_institutional_endpoints_return_active_records(): void
    {
        ChurchInfo::create([
            'name' => 'La Voix de Dieu Tabernacle de Kindu',
            'about' => 'Eglise locale a Kindu.',
            'address' => 'Kindu',
            'phone' => '+243990000000',
            'service_times' => ['Dimanche 09:00'],
            'social_links' => ['youtube' => 'https://youtube.example.test'],
        ]);

        Belief::create([
            'title' => 'La Parole',
            'slug' => 'la-parole',
            'body' => 'Nous croyons a la Parole revelee.',
        ]);

        Belief::create([
            'title' => 'Inactive',
            'slug' => 'inactive',
            'is_active' => false,
        ]);

        Jurisdiction::create([
            'name' => 'Kindu Centre',
            'slug' => 'kindu-centre',
            'leader_name' => 'Ancien responsable',
        ]);

        CommitteeMember::create([
            'name' => 'Responsable local',
            'role' => 'Pasteur',
            'photo_url' => 'https://img.example.test/pasteur.jpg',
        ]);

        ChurchMember::create([
            'display_name' => 'Membre public',
            'gender' => 'male',
            'member_type' => 'adult',
            'jurisdiction' => 'Kindu Centre',
            'phone' => '+243991111111',
            'show_contacts' => false,
        ]);

        ChurchMember::create([
            'display_name' => 'Membre masque',
            'is_active' => false,
        ]);

        $this
            ->getJson('/api/v1/public/info/')
            ->assertOk()
            ->assertJsonPath('data.name', 'La Voix de Dieu Tabernacle de Kindu')
            ->assertJsonPath('data.serviceTimes.0', 'Dimanche 09:00');

        $this
            ->getJson('/api/v1/public/beliefs/')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('beliefs.0.slug', 'la-parole');

        $this
            ->getJson('/api/v1/public/jurisdictions/')
            ->assertOk()
            ->assertJsonPath('data.0.leaderName', 'Ancien responsable');

        $this
            ->getJson('/api/v1/public/committee/')
            ->assertOk()
            ->assertJsonPath('committee.0.photoUrl', 'https://img.example.test/pasteur.jpg');

        $this
            ->getJson('/api/v1/public/members/')
            ->assertOk()
            ->assertJsonCount(1, 'members')
            ->assertJsonMissingPath('members.0.phone')
            ->assertJsonMissingPath('members.0.email');

        $this
            ->getJson('/api/v1/public/members/stats/')
            ->assertOk()
            ->assertJsonPath('data.memberCount', 1)
            ->assertJsonPath('data.byGender.male', 1)
            ->assertJsonPath('data.byType.adult', 1);
    }

    public function test_public_programs_photos_and_quotes_match_mobile_payloads(): void
    {
        ChurchPhoto::create([
            'title' => 'Culte',
            'image_url' => 'https://img.example.test/culte.jpg',
            'thumbnail_url' => 'https://img.example.test/culte-thumb.jpg',
            'is_published' => true,
        ]);

        ChurchPhoto::create([
            'image_url' => 'https://img.example.test/private.jpg',
            'is_published' => false,
        ]);

        Testimonial::create([
            'title' => 'Dieu m\'a visite',
            'author' => 'Membre temoin',
            'content' => 'Un temoignage public pour encourager l\'eglise.',
            'image_url' => 'https://img.example.test/temoin.jpg',
            'published_at' => now(),
            'is_published' => true,
        ]);

        Testimonial::create([
            'author' => 'Temoignage masque',
            'content' => 'Invisible',
            'is_published' => false,
        ]);

        Program::create([
            'title' => 'Programme du jour',
            'slug' => 'programme-du-jour',
            'day_of_week' => now()->dayOfWeekIso,
            'starts_at' => '09:00',
            'ends_at' => '11:00',
            'location' => 'Temple',
            'is_featured' => true,
        ]);

        Program::create([
            'title' => 'Programme inactif',
            'slug' => 'programme-inactif',
            'is_active' => false,
        ]);

        DailyQuote::create([
            'quote' => 'Dieu est amour.',
            'reference' => '1 Jean 4:8',
            'quote_date' => now()->toDateString(),
        ]);

        $this
            ->getJson('/api/v1/public/photos/')
            ->assertOk()
            ->assertJsonCount(1, 'photos')
            ->assertJsonPath('photos.0.imageUrl', 'https://img.example.test/culte.jpg');

        $this
            ->getJson('/api/v1/public/testimonials/')
            ->assertOk()
            ->assertJsonCount(1, 'testimonials')
            ->assertJsonPath('testimonials.0.author', 'Membre temoin')
            ->assertJsonPath('testimonials.0.content', 'Un temoignage public pour encourager l\'eglise.')
            ->assertJsonPath('testimonials.0.imageUrl', 'https://img.example.test/temoin.jpg');

        $this
            ->getJson('/api/v1/public/programs/')
            ->assertOk()
            ->assertJsonCount(1, 'programs')
            ->assertJsonPath('programs.0.startsAt', '09:00')
            ->assertJsonPath('todayPrograms.0.slug', 'programme-du-jour');

        $this
            ->getJson('/api/v1/public/programs/')
            ->assertOk()
            ->assertJsonPath('programs.0.title', 'Programme du jour');

        $this
            ->getJson('/api/v1/public/quote/')
            ->assertOk()
            ->assertJsonPath('quote.text', 'Dieu est amour.')
            ->assertJsonPath('quote.verse', '1 Jean 4:8');

        $this
            ->getJson('/api/v1/public/quotes/')
            ->assertOk()
            ->assertJsonCount(1, 'quotes');
    }

    public function test_public_prayer_rooms_launch_and_pastor_calendar(): void
    {
        PrayerRoom::create([
            'title' => 'Priere generale',
            'slug' => 'priere-generale',
            'room_type' => 'general',
            'meeting_url' => 'https://meet.example.test/priere',
            'is_live' => true,
        ]);

        PrayerRoom::create([
            'title' => 'Salle inactive',
            'slug' => 'salle-inactive',
            'is_active' => false,
        ]);

        PastorCalendarEvent::create([
            'title' => 'Rendez-vous public',
            'location' => 'Bureau pastoral',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
        ]);

        PastorCalendarEvent::create([
            'title' => 'Rendez-vous prive',
            'starts_at' => now()->addDay(),
            'is_public' => false,
        ]);

        $this
            ->getJson('/api/v1/public/prayer-rooms/')
            ->assertOk()
            ->assertJsonCount(1, 'prayerRooms')
            ->assertJsonPath('room.slug', 'priere-generale');

        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('member');
        $token = $user->createToken('mobile')->plainTextToken;

        $this
            ->getJson('/api/v1/public/prayer-rooms/launch/')
            ->assertMethodNotAllowed();

        $this
            ->postJson('/api/v1/public/prayer-rooms/launch/', [
                'slug' => 'priere-generale',
            ])
            ->assertUnauthorized();

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/prayer-rooms/launch/', [
                'slug' => 'priere-generale',
            ])
            ->assertOk()
            ->assertJsonPath('data.canJoin', true)
            ->assertJsonPath('data.meetingUrl', 'https://meet.example.test/priere')
            ->assertJsonPath('data.roomName', 'prayer-room-1');

        $this
            ->getJson('/api/v1/public/pastor-calendar/')
            ->assertOk()
            ->assertJsonCount(1, 'pastorCalendar')
            ->assertJsonPath('pastorCalendar.0.location', 'Bureau pastoral');
    }
}
