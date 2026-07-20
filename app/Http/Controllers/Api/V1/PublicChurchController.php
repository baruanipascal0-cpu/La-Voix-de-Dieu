<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LaunchPrayerRoomRequest;
use App\Http\Resources\Api\V1\PrayerRoomResource;
use App\Http\Resources\Api\V1\ProgramResource;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicChurchController extends Controller
{
    public function info(): JsonResponse
    {
        $info = ChurchInfo::query()
            ->where('is_active', true)
            ->latest()
            ->first();

        return response()->json([
            'data' => $this->infoPayload($info),
            'info' => $this->infoPayload($info),
        ]);
    }

    public function beliefs(): JsonResponse
    {
        $beliefs = Belief::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Belief $belief): array => $this->beliefPayload($belief))
            ->values();

        return response()->json([
            'data' => $beliefs,
            'beliefs' => $beliefs,
        ]);
    }

    public function jurisdictions(): JsonResponse
    {
        $jurisdictions = Jurisdiction::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Jurisdiction $jurisdiction): array => $this->jurisdictionPayload($jurisdiction))
            ->values();

        return response()->json([
            'data' => $jurisdictions,
            'jurisdictions' => $jurisdictions,
        ]);
    }

    public function committee(): JsonResponse
    {
        $members = CommitteeMember::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (CommitteeMember $member): array => $this->committeeMemberPayload($member))
            ->values();

        return response()->json([
            'data' => $members,
            'committee' => $members,
            'members' => $members,
        ]);
    }

    public function members(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 30), 100);

        $members = ChurchMember::query()
            ->where('is_active', true)
            ->when($request->filled('q') || $request->filled('search'), function ($query) use ($request): void {
                $search = '%'.($request->string('q')->toString() ?: $request->string('search')->toString()).'%';

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('display_name', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('jurisdiction', 'like', $search);
                });
            })
            ->orderBy('display_name')
            ->paginate($perPage);

        $data = $members->getCollection()
            ->map(fn (ChurchMember $member): array => $this->memberPayload($member))
            ->values();

        return response()->json([
            'data' => $data,
            'members' => $data,
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    public function memberStats(): JsonResponse
    {
        $members = ChurchMember::query()
            ->where('is_active', true)
            ->get(['gender', 'member_type', 'jurisdiction']);

        $stats = [
            'total' => $members->count(),
            'member_count' => $members->count(),
            'memberCount' => $members->count(),
            'by_gender' => $this->countByFilled($members, 'gender'),
            'byGender' => $this->countByFilled($members, 'gender'),
            'by_type' => $this->countByFilled($members, 'member_type'),
            'byType' => $this->countByFilled($members, 'member_type'),
            'by_jurisdiction' => $this->countByFilled($members, 'jurisdiction'),
            'byJurisdiction' => $this->countByFilled($members, 'jurisdiction'),
        ];

        return response()->json([
            'data' => $stats,
            'stats' => $stats,
        ]);
    }

    public function photos(): JsonResponse
    {
        $photos = ChurchPhoto::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('taken_at')
            ->get()
            ->map(fn (ChurchPhoto $photo): array => $this->photoPayload($photo))
            ->values();

        return response()->json([
            'data' => $photos,
            'photos' => $photos,
        ]);
    }

    public function testimonials(): JsonResponse
    {
        $testimonials = Testimonial::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->latest()
            ->get()
            ->map(fn (Testimonial $testimonial): array => $this->testimonialPayload($testimonial))
            ->values();

        return response()->json([
            'data' => $testimonials,
            'testimonials' => $testimonials,
        ]);
    }

    public function programs(Request $request): JsonResponse
    {
        $programs = Program::query()
            ->where('is_active', true)
            ->when($request->boolean('today'), fn ($query) => $query->where('day_of_week', now()->dayOfWeekIso))
            ->orderByDesc('is_featured')
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Program $program): array => ProgramResource::make($program)->resolve($request))
            ->values();

        return response()->json([
            'data' => $programs,
            'programs' => $programs,
            'today' => $programs->where('day_of_week', now()->dayOfWeekIso)->values(),
            'todayPrograms' => $programs->where('dayOfWeek', now()->dayOfWeekIso)->values(),
        ]);
    }

    public function quote(): JsonResponse
    {
        $today = now()->toDateString();

        $quote = DailyQuote::query()
            ->where('is_active', true)
            ->where(function ($query) use ($today): void {
                $query
                    ->whereDate('quote_date', '<=', $today)
                    ->orWhereNull('quote_date');
            })
            ->orderByDesc('quote_date')
            ->orderBy('sort_order')
            ->latest()
            ->first();

        return response()->json([
            'data' => $quote ? $this->quotePayload($quote) : null,
            'quote' => $quote ? $this->quotePayload($quote) : null,
        ]);
    }

    public function quotes(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 30), 100);

        $quotes = DailyQuote::query()
            ->where('is_active', true)
            ->orderByDesc('quote_date')
            ->orderBy('sort_order')
            ->paginate($perPage);

        $data = $quotes->getCollection()
            ->map(fn (DailyQuote $quote): array => $this->quotePayload($quote))
            ->values();

        return response()->json([
            'data' => $data,
            'quotes' => $data,
            'meta' => [
                'current_page' => $quotes->currentPage(),
                'last_page' => $quotes->lastPage(),
                'per_page' => $quotes->perPage(),
                'total' => $quotes->total(),
            ],
        ]);
    }

    public function prayerRooms(): JsonResponse
    {
        $rooms = PrayerRoom::query()
            ->where('is_active', true)
            ->orderByDesc('is_live')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (PrayerRoom $room): array => PrayerRoomResource::make($room)->resolve(request()))
            ->values();

        return response()->json([
            'data' => $rooms,
            'prayer_rooms' => $rooms,
            'prayerRooms' => $rooms,
            'room' => $rooms->first(),
        ]);
    }

    public function launchPrayerRoom(LaunchPrayerRoomRequest $request): JsonResponse
    {
        $room = PrayerRoom::query()
            ->where('is_active', true)
            ->when($request->roomId(), fn ($query, int $id) => $query->whereKey($id))
            ->when($request->filled('slug'), fn ($query) => $query->where('slug', $request->string('slug')->toString()))
            ->when($request->filled('type'), fn ($query) => $query->where('room_type', $request->string('type')->toString()))
            ->orderByDesc('is_live')
            ->orderBy('sort_order')
            ->first();

        abort_unless($room, 404);

        $payload = [
            'room' => PrayerRoomResource::make($room)->resolve($request),
            'launch' => true,
            'can_join' => true,
            'canJoin' => true,
            'provider' => $room->meeting_url ? 'url' : 'livekit',
            'meeting_url' => $room->meeting_url,
            'meetingUrl' => $room->meeting_url,
            'livekit_room' => $room->livekit_room,
            'livekitRoom' => $room->livekit_room,
            'token' => null,
        ];

        return response()->json([
            'data' => $payload,
            'launch' => $payload,
        ]);
    }

    public function pastorCalendar(): JsonResponse
    {
        $events = PastorCalendarEvent::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('starts_at')
            ->get()
            ->map(fn (PastorCalendarEvent $event): array => $this->pastorCalendarPayload($event))
            ->values();

        return response()->json([
            'data' => $events,
            'events' => $events,
            'pastor_calendar' => $events,
            'pastorCalendar' => $events,
        ]);
    }

    private function infoPayload(?ChurchInfo $info): array
    {
        return [
            'id' => $info?->id,
            'name' => $info?->name ?? config('app.name', 'La Voix de Dieu Tabernacle de Kindu'),
            'tagline' => $info?->tagline,
            'about' => $info?->about,
            'description' => $info?->about,
            'address' => $info?->address,
            'adresse' => $info?->address,
            'city' => $info?->city,
            'country' => $info?->country,
            'phone' => $info?->phone,
            'email' => $info?->email,
            'website_url' => $info?->website_url,
            'websiteUrl' => $info?->website_url,
            'map_url' => $info?->map_url,
            'mapUrl' => $info?->map_url,
            'latitude' => $info?->latitude,
            'longitude' => $info?->longitude,
            'logo_url' => $info?->logo_url,
            'logoUrl' => $info?->logo_url,
            'cover_url' => $info?->cover_url,
            'coverUrl' => $info?->cover_url,
            'service_times' => $info?->service_times ?? [],
            'serviceTimes' => $info?->service_times ?? [],
            'social_links' => $info?->social_links ?? [],
            'socialLinks' => $info?->social_links ?? [],
        ];
    }

    private function beliefPayload(Belief $belief): array
    {
        return [
            'id' => $belief->id,
            'title' => $belief->title,
            'slug' => $belief->slug,
            'body' => $belief->body,
            'content' => $belief->body,
            'description' => $belief->body,
            'scripture_reference' => $belief->scripture_reference,
            'scriptureReference' => $belief->scripture_reference,
            'sort_order' => $belief->sort_order,
            'sortOrder' => $belief->sort_order,
        ];
    }

    private function jurisdictionPayload(Jurisdiction $jurisdiction): array
    {
        return [
            'id' => $jurisdiction->id,
            'name' => $jurisdiction->name,
            'slug' => $jurisdiction->slug,
            'description' => $jurisdiction->description,
            'leader_name' => $jurisdiction->leader_name,
            'leaderName' => $jurisdiction->leader_name,
            'address' => $jurisdiction->address,
            'phone' => $jurisdiction->phone,
            'email' => $jurisdiction->email,
            'sort_order' => $jurisdiction->sort_order,
            'sortOrder' => $jurisdiction->sort_order,
        ];
    }

    private function committeeMemberPayload(CommitteeMember $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'role' => $member->role,
            'bio' => $member->bio,
            'phone' => $member->phone,
            'email' => $member->email,
            'photo_url' => $member->photo_url,
            'photoUrl' => $member->photo_url,
            'avatar' => $member->photo_url,
            'sort_order' => $member->sort_order,
            'sortOrder' => $member->sort_order,
        ];
    }

    private function memberPayload(ChurchMember $member): array
    {
        return [
            'id' => $member->id,
            'first_name' => $member->first_name,
            'firstName' => $member->first_name,
            'last_name' => $member->last_name,
            'lastName' => $member->last_name,
            'display_name' => $member->display_name,
            'displayName' => $member->display_name,
            'name' => $member->display_name,
            'avatar_url' => $member->avatar_url,
            'avatarUrl' => $member->avatar_url,
            'avatar' => $member->avatar_url,
            'jurisdiction' => $member->jurisdiction,
            'gender' => $member->gender,
            'member_type' => $member->member_type,
            'memberType' => $member->member_type,
            'joined_at' => $member->joined_at?->toDateString(),
            'joinedAt' => $member->joined_at?->toDateString(),
        ];
    }

    private function photoPayload(ChurchPhoto $photo): array
    {
        return [
            'id' => $photo->id,
            'title' => $photo->title,
            'caption' => $photo->caption,
            'image_url' => $photo->image_url,
            'imageUrl' => $photo->image_url,
            'url' => $photo->image_url,
            'thumbnail_url' => $photo->thumbnail_url,
            'thumbnailUrl' => $photo->thumbnail_url,
            'taken_at' => $photo->taken_at?->toISOString(),
            'takenAt' => $photo->taken_at?->toISOString(),
        ];
    }

    private function testimonialPayload(Testimonial $testimonial): array
    {
        return [
            'id' => $testimonial->id,
            'title' => $testimonial->title,
            'author' => $testimonial->author ?? 'Temoignage',
            'name' => $testimonial->author ?? 'Temoignage',
            'role' => $testimonial->role,
            'content' => $testimonial->content,
            'body' => $testimonial->content,
            'text' => $testimonial->content,
            'image_url' => $testimonial->image_url,
            'imageUrl' => $testimonial->image_url,
            'image' => $testimonial->image_url,
            'published_at' => $testimonial->published_at?->toISOString(),
            'publishedAt' => $testimonial->published_at?->toISOString(),
        ];
    }

    private function programPayload(Program $program): array
    {
        return [
            'id' => $program->id,
            'title' => $program->title,
            'slug' => $program->slug,
            'description' => $program->description,
            'day_of_week' => $program->day_of_week,
            'dayOfWeek' => $program->day_of_week,
            'starts_at' => $this->timeValue($program->starts_at),
            'startsAt' => $this->timeValue($program->starts_at),
            'ends_at' => $this->timeValue($program->ends_at),
            'endsAt' => $this->timeValue($program->ends_at),
            'location' => $program->location,
            'speaker' => $program->speaker,
            'image_url' => $program->image_url,
            'imageUrl' => $program->image_url,
            'image' => $program->image_url,
            'is_featured' => $program->is_featured,
            'isFeatured' => $program->is_featured,
            'sort_order' => $program->sort_order,
            'sortOrder' => $program->sort_order,
        ];
    }

    private function quotePayload(DailyQuote $quote): array
    {
        return [
            'id' => $quote->id,
            'quote' => $quote->quote,
            'text' => $quote->quote,
            'reference' => $quote->reference,
            'verse' => $quote->reference,
            'author' => $quote->author,
            'image_url' => $quote->image_url,
            'imageUrl' => $quote->image_url,
            'image' => $quote->image_url,
            'quote_date' => $quote->quote_date?->toDateString(),
            'quoteDate' => $quote->quote_date?->toDateString(),
        ];
    }

    private function prayerRoomPayload(PrayerRoom $room): array
    {
        return [
            'id' => $room->id,
            'title' => $room->title,
            'slug' => $room->slug,
            'description' => $room->description,
            'room_type' => $room->room_type,
            'roomType' => $room->room_type,
            'meeting_url' => $room->meeting_url,
            'meetingUrl' => $room->meeting_url,
            'livekit_room' => $room->livekit_room,
            'livekitRoom' => $room->livekit_room,
            'starts_at' => $room->starts_at?->toISOString(),
            'startsAt' => $room->starts_at?->toISOString(),
            'ends_at' => $room->ends_at?->toISOString(),
            'endsAt' => $room->ends_at?->toISOString(),
            'is_live' => $room->is_live,
            'isLive' => $room->is_live,
        ];
    }

    private function pastorCalendarPayload(PastorCalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'event_type' => $event->event_type,
            'eventType' => $event->event_type,
            'starts_at' => $event->starts_at?->toISOString(),
            'startsAt' => $event->starts_at?->toISOString(),
            'ends_at' => $event->ends_at?->toISOString(),
            'endsAt' => $event->ends_at?->toISOString(),
        ];
    }

    private function countByFilled(Collection $members, string $key): array
    {
        return $members
            ->map(fn (ChurchMember $member): ?string => $member->{$key})
            ->filter()
            ->countBy()
            ->all();
    }

    private function timeValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}
