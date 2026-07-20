# Mobile integration contract

Application name: La Voix de Dieu Tabernacle de Kindu.

## Bootstrap

- `GET /api/v1/mobile/bootstrap`
- Public endpoint used before login.
- Returns feature flags and canonical endpoint paths.

## Authentication

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/refresh`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/device-token`
- `DELETE /api/v1/auth/device-token`

Authenticated responses include `roles` and `permissions`.

## Media uploads

- `POST /api/v1/media/uploads`
- Multipart field: `file`
- Optional fields: `collection`, `metadata`
- Supported media: images, audio, video, PDF
- Response includes `media_url` and `mediaUrl`, usable in chat/media fields.

## Notifications

- `GET /api/v1/notifications`
- `GET /api/v1/notifications/unread-count`
- `POST /api/v1/notifications/{id}/read`
- `POST /api/v1/notifications/read-all`

Notifications are stored in `push_notifications` and broadcast on `private-users.{id}`.
External FCM delivery can be attached later from queued rows.

## Realtime

- Broadcast auth endpoint: `/broadcasting/auth`
- Public channels: `public.chat`, `public.calls`
- Private channels:
  - `private-users.{id}`
  - `private-groups.{id}`
  - `private-dm.{id}`
  - `private-calls.{id}`

Events:

- `.chat.message.sent`
- `.call.session.updated`
- `.call.signal.sent`
- `.notification.created`

## Calls

- `POST /api/v1/realtime/calls/{call}/token`
- Returns LiveKit room data.
- If `LIVEKIT_URL`, `LIVEKIT_API_KEY`, and `LIVEKIT_API_SECRET` are configured, the response includes a join token.
